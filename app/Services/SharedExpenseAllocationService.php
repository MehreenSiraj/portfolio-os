<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Expense;
use App\Models\ExpenseAllocation;
use App\Models\Project;
use App\Models\Revenue;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Allocate shared expenses across active sites by that month's revenue share.
 */
class SharedExpenseAllocationService
{
    public function rebuildForMonth(string $periodMonth): void
    {
        $month = Carbon::parse($periodMonth)->startOfMonth()->toDateString();
        $start = $month;
        $end = Carbon::parse($month)->endOfMonth()->toDateString();

        $shared = Expense::query()
            ->shared()
            ->whereBetween('expense_date', [$start, $end])
            ->get();

        foreach ($shared as $expense) {
            $this->allocateExpense($expense, $month);
        }
    }

    /**
     * @return Collection<int, Project>
     */
    public function allocationProjects(): Collection
    {
        return Project::query()
            ->whereIn('status', [
                ProjectStatus::Live->value,
                ProjectStatus::Monetized->value,
                ProjectStatus::Setup->value,
            ])
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<int, int> project_id => paisa
     */
    public function revenueByProject(string $periodMonth): array
    {
        $month = Carbon::parse($periodMonth)->startOfMonth()->toDateString();

        return Revenue::query()
            ->whereDate('period_month', $month)
            ->selectRaw('project_id, SUM(amount_pkr_paisa) as total')
            ->groupBy('project_id')
            ->pluck('total', 'project_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    public function allocateExpense(Expense $expense, ?string $periodMonth = null): void
    {
        if (! $expense->is_shared) {
            ExpenseAllocation::query()->where('expense_id', $expense->id)->delete();

            return;
        }

        $month = $periodMonth
            ? Carbon::parse($periodMonth)->startOfMonth()->toDateString()
            : $expense->expense_date->copy()->startOfMonth()->toDateString();

        DB::transaction(function () use ($expense, $month) {
            ExpenseAllocation::query()->where('expense_id', $expense->id)->delete();

            $projects = $this->allocationProjects();
            if ($projects->isEmpty()) {
                return;
            }

            $revenues = $this->revenueByProject($month);
            $portfolioRevenue = (int) array_sum($revenues);
            $amount = (int) $expense->amount_paisa;
            $allocations = [];

            if ($portfolioRevenue > 0) {
                $targets = $projects->filter(fn (Project $p) => ($revenues[$p->id] ?? 0) > 0)->values();
                if ($targets->isEmpty()) {
                    $targets = $projects->values();
                }
                $allocated = 0;
                foreach ($targets as $i => $project) {
                    $rev = (int) ($revenues[$project->id] ?? 0);
                    if ($i === $targets->count() - 1) {
                        $share = $amount - $allocated;
                    } else {
                        $share = (int) floor(($amount * $rev) / $portfolioRevenue);
                        $allocated += $share;
                    }
                    $allocations[] = [
                        'expense_id' => $expense->id,
                        'project_id' => $project->id,
                        'period_month' => $month,
                        'amount_paisa' => max(0, $share),
                        'revenue_share_bps' => (int) round(($rev / $portfolioRevenue) * 10000),
                        'project_revenue_paisa' => $rev,
                        'portfolio_revenue_paisa' => $portfolioRevenue,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            } else {
                $count = $projects->count();
                $base = intdiv($amount, $count);
                $remainder = $amount - ($base * $count);
                foreach ($projects->values() as $i => $project) {
                    $share = $base + ($i === 0 ? $remainder : 0);
                    $allocations[] = [
                        'expense_id' => $expense->id,
                        'project_id' => $project->id,
                        'period_month' => $month,
                        'amount_paisa' => $share,
                        'revenue_share_bps' => (int) floor(10000 / $count),
                        'project_revenue_paisa' => 0,
                        'portfolio_revenue_paisa' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if ($allocations !== []) {
                ExpenseAllocation::query()->insert($allocations);
            }
        });
    }

    public function sharedCostForProject(int $projectId, string $from, string $to): int
    {
        $fromMonth = Carbon::parse($from)->startOfMonth()->toDateString();
        $toMonth = Carbon::parse($to)->startOfMonth()->toDateString();

        $cursor = Carbon::parse($fromMonth)->startOfMonth();
        $end = Carbon::parse($toMonth)->startOfMonth();
        while ($cursor->lte($end)) {
            $this->rebuildForMonth($cursor->toDateString());
            $cursor->addMonth();
        }

        return (int) ExpenseAllocation::query()
            ->where('project_id', $projectId)
            ->whereDate('period_month', '>=', $fromMonth)
            ->whereDate('period_month', '<=', $toMonth)
            ->sum('amount_paisa');
    }
}
