<div>
    <div class="mb-6">
        <a href="{{ route('money.distributions') }}" wire:navigate class="text-sm text-muted hover:text-ink">← Distributions</a>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-ink">
            Distribution #{{ $run->id }}
            <span class="text-base font-normal text-muted">· {{ $run->period_month->format('Y-m') }}</span>
        </h1>
        <p class="mt-1 text-sm text-muted">
            Status: <strong>{{ $run->status->label() }}</strong>
            @if ($run->approved_at)
                · Approved {{ $run->approved_at->timezone('Asia/Karachi')->format('Y-m-d H:i') }}
            @endif
            @if ($locked)
                · Locked (edit not allowed)
            @endif
        </p>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        @if ($canApprove && $run->status->value === 'draft')
            <x-button type="button" wire:click="approve" wire:confirm="Approve and lock this run? Partner ledger will be credited.">
                Approve &amp; lock
            </x-button>
        @endif
        @if ($canApprove && $run->status->value === 'approved')
            <div class="flex flex-wrap items-end gap-2">
                <x-input label="Void reason" wire:model="void_reason" class="min-w-[16rem]" />
                <x-button type="button" variant="danger" wire:click="void" wire:confirm="Void and post reversing ledger entries?">Void run</x-button>
            </div>
        @endif
        <x-button type="button" variant="ghost" wire:click="exportCsv">Export lines CSV</x-button>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-line bg-surface p-4">
            <p class="font-mono text-[11px] text-muted uppercase">Total net</p>
            <p class="mt-1 font-mono text-lg">{{ number_format($run->total_net_profit_paisa / 100, 2) }}</p>
        </div>
        <div class="rounded-xl border border-line bg-surface p-4">
            <p class="font-mono text-[11px] text-muted uppercase">Credited</p>
            <p class="mt-1 font-mono text-lg">{{ number_format($run->total_credited_paisa / 100, 2) }}</p>
        </div>
        <div class="rounded-xl border border-line bg-surface p-4">
            <p class="font-mono text-[11px] text-muted uppercase">Holdback ({{ number_format($run->holdback_bps / 100, 2) }}%)</p>
            <p class="mt-1 font-mono text-lg">{{ number_format($run->total_holdback_paisa / 100, 2) }}</p>
        </div>
    </div>

    @if ($run->ownership_snapshot)
        <details class="mb-6 rounded-xl border border-line bg-canvas/40 p-4 text-sm">
            <summary class="cursor-pointer font-medium">Ownership snapshot (frozen)</summary>
            <pre class="mt-3 overflow-x-auto font-mono text-xs text-muted">{{ json_encode($run->ownership_snapshot, JSON_PRETTY_PRINT) }}</pre>
        </details>
    @endif

    <div class="overflow-x-auto rounded-xl border border-line">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-line bg-canvas/60 font-mono text-[11px] tracking-wide text-muted uppercase">
                <tr>
                    <th class="px-4 py-3">Project</th>
                    <th class="px-4 py-3">Partner</th>
                    <th class="px-4 py-3 text-right">Share %</th>
                    <th class="px-4 py-3 text-right">Net profit</th>
                    <th class="px-4 py-3 text-right">Gross share</th>
                    <th class="px-4 py-3 text-right">Holdback</th>
                    <th class="px-4 py-3 text-right">Credited</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($run->lines as $line)
                    <tr>
                        <td class="px-4 py-3">{{ $line->project?->domain }}</td>
                        <td class="px-4 py-3">{{ $line->user?->name }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($line->share_bps / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($line->net_profit_paisa / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($line->gross_share_paisa / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($line->holdback_paisa / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($line->credited_paisa / 100, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
