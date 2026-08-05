<?php

namespace App\Livewire\Money;

use App\Models\DistributionRun;
use App\Policies\FinancePolicy;
use App\Services\CsvExportService;
use App\Services\DistributionService;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Distribution run')]
class DistributionShow extends Component
{
    public DistributionRun $run;

    public string $void_reason = '';

    public function mount(DistributionRun $run): void
    {
        abort_unless(FinancePolicy::viewDistributions(Auth::user()), 403);
        $this->run = $run->load(['lines.user', 'lines.project', 'creator', 'approver']);
    }

    public function approve(DistributionService $distributions): void
    {
        abort_unless(FinancePolicy::approveDistributions(Auth::user()), 403);
        $this->run = $distributions->approve($this->run, Auth::user());
        $this->dispatch('toast', message: 'Distribution approved and locked. Partner ledger credited.', tone: 'success');
    }

    public function void(DistributionService $distributions): void
    {
        abort_unless(FinancePolicy::approveDistributions(Auth::user()), 403);
        $this->validate(['void_reason' => ['required', 'string', 'max:1000']]);
        $this->run = $distributions->void($this->run, Auth::user(), $this->void_reason);
        $this->dispatch('toast', message: 'Distribution voided with ledger adjustments.', tone: 'success');
    }

    public function exportCsv(CsvExportService $csv)
    {
        abort_unless(FinancePolicy::export(Auth::user()), 403);
        $rows = $this->run->lines->map(fn ($l) => [
            $l->id,
            $l->project?->domain,
            $l->user?->name,
            $l->share_bps / 100,
            Money::paisaToMajor((int) $l->net_profit_paisa),
            Money::paisaToMajor((int) $l->gross_share_paisa),
            Money::paisaToMajor((int) $l->holdback_paisa),
            Money::paisaToMajor((int) $l->credited_paisa),
        ]);

        return $csv->download('distribution-'.$this->run->id.'-lines.csv', [
            'id', 'project', 'partner', 'share_pct', 'net_profit_pkr', 'gross_share_pkr', 'holdback_pkr', 'credited_pkr',
        ], $rows);
    }

    public function render()
    {
        return view('livewire.money.distribution-show', [
            'canApprove' => FinancePolicy::approveDistributions(Auth::user()),
            'locked' => $this->run->isLocked(),
        ]);
    }
}
