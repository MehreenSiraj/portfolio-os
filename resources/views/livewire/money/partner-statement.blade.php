<div>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ route('money.partners') }}" wire:navigate class="text-sm text-muted hover:text-ink">← Partners</a>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $partner->name }}</h1>
            <p class="mt-1 text-sm text-muted">{{ $partner->email }} · Running balance
                <span class="font-mono font-semibold text-ink">{{ number_format($balance / 100, 2) }} PKR</span>
            </p>
        </div>
        <x-button type="button" variant="ghost" wire:click="exportCsv">Export CSV</x-button>
    </div>

    <div class="overflow-x-auto rounded-xl border border-line">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-line bg-canvas/60 font-mono text-[11px] tracking-wide text-muted uppercase">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($entries as $e)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs">{{ $e->entry_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">{{ $e->type->label() }}</td>
                        <td class="px-4 py-3">{{ $e->description }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs {{ $e->amount_paisa < 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($e->amount_paisa / 100, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($e->balance_after_paisa / 100, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-muted">No ledger entries.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $entries->links() }}</div>
</div>
