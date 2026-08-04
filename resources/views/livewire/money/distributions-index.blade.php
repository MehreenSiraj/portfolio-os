<div>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Distributions</h1>
            <p class="mt-1 text-sm text-muted">Manual approve only · ownership frozen on approval</p>
        </div>
        <div class="flex gap-2">
            @if ($canManage)
                <x-button type="button" wire:click="$set('showCreate', true)">New draft for month</x-button>
            @endif
            <x-button type="button" variant="ghost" wire:click="exportCsv">Export CSV</x-button>
        </div>
    </div>

    @if ($showCreate && $canManage)
        <div class="mb-8 rounded-xl border border-line bg-surface p-5">
            <h2 class="text-sm font-semibold">Create distribution draft</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <x-input type="month" label="Month" wire:model="period_month" />
                <x-input label="Reinvestment holdback %" wire:model="holdback_percent" hint="0–100; applied after ownership share" />
                <x-input label="Notes" wire:model="notes" />
            </div>
            <div class="mt-4 flex gap-2">
                <x-button type="button" wire:click="createDraft">Build draft</x-button>
                <x-button type="button" variant="ghost" wire:click="$set('showCreate', false)">Cancel</x-button>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-line">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-line bg-canvas/60 font-mono text-[11px] tracking-wide text-muted uppercase">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Period</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Net profit</th>
                    <th class="px-4 py-3 text-right">Credited</th>
                    <th class="px-4 py-3 text-right">Holdback</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($runs as $run)
                    <tr class="hover:bg-canvas/40">
                        <td class="px-4 py-3 font-mono text-xs">{{ $run->id }}</td>
                        <td class="px-4 py-3">{{ $run->period_month->format('Y-m') }}</td>
                        <td class="px-4 py-3">
                            <x-badge :tone="$run->status->value === 'approved' ? 'success' : ($run->status->value === 'voided' ? 'danger' : 'neutral')">
                                {{ $run->status->label() }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($run->total_net_profit_paisa / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($run->total_credited_paisa / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($run->total_holdback_paisa / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('money.distributions.show', $run) }}" wire:navigate class="text-xs text-accent">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-muted">No distribution runs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $runs->links() }}</div>
</div>
