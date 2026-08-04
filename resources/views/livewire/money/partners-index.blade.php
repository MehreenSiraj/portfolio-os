<div>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Partners</h1>
            <p class="mt-1 text-sm text-muted">Capital, withdrawals, and running balance</p>
        </div>
        <div class="flex gap-2">
            @if ($canManage)
                <x-button type="button" wire:click="$set('showCapital', true)">Record capital / withdrawal</x-button>
            @endif
            <x-button type="button" variant="ghost" wire:click="exportCsv">Export ledger CSV</x-button>
        </div>
    </div>

    @if ($showCapital && $canManage)
        <div class="mb-8 rounded-xl border border-line bg-surface p-5">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="text-sm font-medium">Partner</label>
                    <select wire:model="user_id" class="mt-1 block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        <option value="">Select…</option>
                        @foreach ($partners as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Type</label>
                    <select wire:model="entry_type" class="mt-1 block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        <option value="capital_in">Capital in</option>
                        <option value="withdrawal">Withdrawal</option>
                    </select>
                </div>
                <x-input label="Amount PKR" wire:model="amount" />
                <x-input label="Notes" wire:model="notes" />
            </div>
            <div class="mt-4 flex gap-2">
                <x-button type="button" wire:click="postEntry">Post</x-button>
                <x-button type="button" variant="ghost" wire:click="$set('showCapital', false)">Cancel</x-button>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-line">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-line bg-canvas/60 font-mono text-[11px] tracking-wide text-muted uppercase">
                <tr>
                    <th class="px-4 py-3">Partner</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3 text-right">Balance PKR</th>
                    <th class="px-4 py-3">Payout</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($partners as $p)
                    <tr class="hover:bg-canvas/40">
                        <td class="px-4 py-3 font-medium">{{ $p->name }}</td>
                        <td class="px-4 py-3 text-muted">{{ $p->email }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format(($balances[$p->id] ?? 0) / 100, 2) }}</td>
                        <td class="px-4 py-3 text-xs text-muted">{{ $profiles[$p->id]->payout_method ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('money.partners.statement', ['user' => $p->id]) }}" wire:navigate class="text-xs text-accent">Statement</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-muted">No partners found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
