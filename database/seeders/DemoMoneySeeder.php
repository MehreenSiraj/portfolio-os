<?php

namespace Database\Seeders;

use App\Enums\DistributionStatus;
use App\Enums\PartnerLedgerType;
use App\Enums\RevenueSource;
use App\Models\Expense;
use App\Models\PartnerProfile;
use App\Models\Project;
use App\Models\RecurringExpense;
use App\Models\User;
use App\Services\DistributionService;
use App\Services\ExpenseService;
use App\Services\PartnerLedgerService;
use App\Services\RevenueService;
use App\Support\AppSettings;
use App\Support\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoMoneySeeder extends Seeder
{
    public function run(): void
    {
        AppSettings::set('fx_defaults', [
            'USD_to_PKR' => '278.50',
            'note' => 'Demo default FX for M5.',
        ]);

        $admin = User::query()->where('email', 'admin@example.com')->first();
        $partner = User::query()->where('email', 'partner@example.com')->first();

        $accountant = User::query()->updateOrCreate(
            ['email' => 'accountant@example.com'],
            [
                'name' => 'Demo Accountant',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
        $accountant->assignRole('accountant');

        if (! $admin || ! $partner) {
            return;
        }

        app(ExpenseService::class)->seedSystemCategories();
        $revenues = app(RevenueService::class);
        $expenses = app(ExpenseService::class);
        $ledger = app(PartnerLedgerService::class);

        $alpha = Project::query()->where('domain', 'alpha-demo.test')->first();
        $beta = Project::query()->where('domain', 'beta-demo.test')->first();
        if (! $alpha || ! $beta) {
            return;
        }

        $month = now('UTC')->startOfMonth()->toDateString();
        $prev = now('UTC')->subMonth()->startOfMonth()->toDateString();

        PartnerProfile::query()->updateOrCreate(
            ['user_id' => $partner->id],
            [
                'payout_method' => 'Bank transfer',
                'payout_details' => 'HBL · ****4521 (demo — not real)',
                'notes' => 'Demo partner payout profile',
                'is_active' => true,
            ],
        );

        // Capital in (idempotent-ish: only if no capital yet)
        if (! \App\Models\PartnerLedgerEntry::query()->where('user_id', $partner->id)->where('type', PartnerLedgerType::CapitalIn->value)->exists()) {
            $ledger->recordCapitalIn($partner->id, 500_000_00, $admin, 'Seed seed capital', $prev);
        }

        if (! \App\Models\Revenue::query()->where('project_id', $alpha->id)->whereDate('period_month', $month)->exists()) {
            $revenues->create([
                'project_id' => $alpha->id,
                'period_month' => $month,
                'source' => RevenueSource::Adsense->value,
                'amount_usd' => '420.00',
                'fx_rate' => '278.50',
                'notes' => 'Demo AdSense current month',
            ], $admin);

            $revenues->create([
                'project_id' => $alpha->id,
                'period_month' => $month,
                'source' => RevenueSource::Affiliate->value,
                'amount_usd' => '80.00',
                'fx_rate' => '278.50',
                'notes' => 'Demo affiliate',
            ], $admin);

            $revenues->create([
                'project_id' => $beta->id,
                'period_month' => $month,
                'source' => RevenueSource::Adsense->value,
                'amount_usd' => '150.00',
                'fx_rate' => '278.50',
                'notes' => 'Demo AdSense beta',
            ], $admin);
        }

        if (! \App\Models\Revenue::query()->where('project_id', $alpha->id)->whereDate('period_month', $prev)->exists()) {
            $revenues->create([
                'project_id' => $alpha->id,
                'period_month' => $prev,
                'source' => RevenueSource::Adsense->value,
                'amount_usd' => '390.00',
                'fx_rate' => '277.00',
                'notes' => 'Prior month AdSense (frozen older FX)',
            ], $admin);

            $revenues->create([
                'project_id' => $beta->id,
                'period_month' => $prev,
                'source' => RevenueSource::Adsense->value,
                'amount_usd' => '120.00',
                'fx_rate' => '277.00',
            ], $admin);
        }

        $hosting = app(ExpenseService::class)->ensureCategory('hosting', 'Hosting');
        $tools = app(ExpenseService::class)->ensureCategory('tools', 'Tools');

        if (! Expense::query()->where('description', 'Demo shared SaaS tools')->exists()) {
            $expenses->createManual([
                'is_shared' => true,
                'expense_category_id' => $tools->id,
                'amount' => '50000',
                'description' => 'Demo shared SaaS tools',
                'expense_date' => $month,
                'is_paid' => true,
                'notes' => 'Split by revenue share',
            ], $admin);
        }

        if (! Expense::query()->where('description', 'Demo alpha hosting')->exists()) {
            $expenses->createManual([
                'project_id' => $alpha->id,
                'is_shared' => false,
                'expense_category_id' => $hosting->id,
                'amount' => '15000',
                'description' => 'Demo alpha hosting',
                'expense_date' => $month,
                'is_paid' => false,
            ], $admin);
        }

        if (! RecurringExpense::query()->where('description', 'Monthly domain renewals pool')->exists()) {
            RecurringExpense::query()->create([
                'is_shared' => true,
                'expense_category_id' => app(ExpenseService::class)->ensureCategory('domain', 'Domain')->id,
                'amount_paisa' => Money::pkrToPaisa('8000'),
                'description' => 'Monthly domain renewals pool',
                'day_of_month' => 1,
                'next_run_date' => now('UTC')->startOfMonth()->addMonth()->toDateString(),
                'is_active' => true,
                'created_by' => $admin->id,
            ]);
        }

        // Draft distribution for previous month (not approved)
        $hasDraft = \App\Models\DistributionRun::query()
            ->whereDate('period_month', $prev)
            ->where('status', DistributionStatus::Draft->value)
            ->exists();

        if (! $hasDraft) {
            try {
                app(DistributionService::class)->createDraft(
                    $prev,
                    $admin,
                    holdbackBps: 1000, // 10%
                    notes: 'Demo draft distribution — approve to lock & credit ledger',
                );
            } catch (\Throwable) {
                // ignore if data insufficient
            }
        }
    }
}
