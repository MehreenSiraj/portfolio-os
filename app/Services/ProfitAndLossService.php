<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseAllocation;
use App\Models\Project;
use App\Models\Revenue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Reusable read-only P&L report (whitelist-friendly for future AI).
 */
class ProfitAndLossService
{
    public function __construct(
        protected SharedExpenseAllocationService $allocations,
    ) {}

    /**
     * @return array{
     *   from: string,
     *   to: string,
     *   projects: list<array{
     *     project_id: int,
     *     domain: string,
     *     revenue_paisa: int,
     *     direct_expense_paisa: int,
     *     shared_expense_paisa: int,
     *     total_expense_paisa: int,
     *     net_profit_paisa: int
     *   }>,
     *   totals: array{
     *     revenue_paisa: int,
     *     direct_expense_paisa: int,
     *     shared_expense_paisa: int,
     *     total_expense_paisa: int,
     *     net_profit_paisa: int
     *   }
     * }
     */
    public function report(?User $user, string $from, string $to, ?array $projectIds = null): array
    {
        $from = Carbon::parse($from)->toDateString();
        $to = Carbon::parse($to)->toDateString();

        $query = Project::query()->orderBy('domain');
        if ($user && ! $user->hasPortfolioFinanceAccess()) {
            $ids = $user->accessibleProjectIds() ?: [0];
            $query->whereIn('id', $ids);
        }
        if ($projectIds !== null) {
            $query->whereIn('id', $projectIds);
        }

        /** @var Collection<int, Project> $projects */
        $projects = $query->get();

        // Ensure shared allocations cover months in range
        $cursor = Carbon::parse($from)->startOfMonth();
        $endMonth = Carbon::parse($to)->startOfMonth();
        while ($cursor->lte($endMonth)) {
            $this->allocations->rebuildForMonth($cursor->toDateString());
            $cursor->addMonth();
        }

        $fromMonth = Carbon::parse($from)->startOfMonth()->toDateString();
        $toMonth = Carbon::parse($to)->startOfMonth()->toDateString();

        $rows = [];
        $totals = [
            'revenue_paisa' => 0,
            'direct_expense_paisa' => 0,
            'shared_expense_paisa' => 0,
            'total_expense_paisa' => 0,
            'net_profit_paisa' => 0,
        ];

        foreach ($projects as $project) {
            $revenue = (int) Revenue::query()
                ->where('project_id', $project->id)
                ->whereDate('period_month', '>=', $fromMonth)
                ->whereDate('period_month', '<=', $toMonth)
                ->sum('amount_pkr_paisa');

            $direct = (int) Expense::query()
                ->directForProject($project->id)
                ->whereDate('expense_date', '>=', $from)
                ->whereDate('expense_date', '<=', $to)
                ->sum('amount_paisa');

            $shared = (int) ExpenseAllocation::query()
                ->where('project_id', $project->id)
                ->whereDate('period_month', '>=', $fromMonth)
                ->whereDate('period_month', '<=', $toMonth)
                ->sum('amount_paisa');

            $totalExpense = $direct + $shared;
            $net = $revenue - $totalExpense;

            $rows[] = [
                'project_id' => $project->id,
                'domain' => $project->domain,
                'revenue_paisa' => $revenue,
                'direct_expense_paisa' => $direct,
                'shared_expense_paisa' => $shared,
                'total_expense_paisa' => $totalExpense,
                'net_profit_paisa' => $net,
            ];

            $totals['revenue_paisa'] += $revenue;
            $totals['direct_expense_paisa'] += $direct;
            $totals['shared_expense_paisa'] += $shared;
            $totals['total_expense_paisa'] += $totalExpense;
            $totals['net_profit_paisa'] += $net;
        }

        return [
            'from' => $from,
            'to' => $to,
            'projects' => $rows,
            'totals' => $totals,
        ];
    }

    /**
     * Single calendar month report.
     *
     * @return array<string, mixed>
     */
    public function forMonth(?User $user, string $yearMonth, ?array $projectIds = null): array
    {
        $start = Carbon::parse(strlen($yearMonth) === 7 ? $yearMonth.'-01' : $yearMonth)->startOfMonth();

        return $this->report(
            $user,
            $start->toDateString(),
            $start->copy()->endOfMonth()->toDateString(),
            $projectIds,
        );
    }

    public function projectRowForMonth(Project $project, string $yearMonth): array
    {
        $report = $this->forMonth(null, $yearMonth, [$project->id]);

        return $report['projects'][0] ?? [
            'project_id' => $project->id,
            'domain' => $project->domain,
            'revenue_paisa' => 0,
            'direct_expense_paisa' => 0,
            'shared_expense_paisa' => 0,
            'total_expense_paisa' => 0,
            'net_profit_paisa' => 0,
        ];
    }
}
