<div>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Profit &amp; Loss</h1>
            <p class="mt-1 text-sm text-muted">Revenue − direct expenses − share of shared costs (by revenue)</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-input type="month" wire:model.live="month" />
            <x-button type="button" variant="ghost" wire:click="exportCsv">Export CSV</x-button>
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-line bg-surface p-4">
            <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Revenue</p>
            <p class="mt-1 text-xl font-semibold font-mono">{{ number_format($report['totals']['revenue_paisa'] / 100, 2) }}</p>
        </div>
        <div class="rounded-xl border border-line bg-surface p-4">
            <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Direct costs</p>
            <p class="mt-1 text-xl font-semibold font-mono">{{ number_format($report['totals']['direct_expense_paisa'] / 100, 2) }}</p>
        </div>
        <div class="rounded-xl border border-line bg-surface p-4">
            <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Shared share</p>
            <p class="mt-1 text-xl font-semibold font-mono">{{ number_format($report['totals']['shared_expense_paisa'] / 100, 2) }}</p>
        </div>
        <div class="rounded-xl border border-line bg-surface p-4">
            <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Net profit</p>
            <p class="mt-1 text-xl font-semibold font-mono {{ $report['totals']['net_profit_paisa'] < 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format($report['totals']['net_profit_paisa'] / 100, 2) }}
            </p>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-line">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-line bg-canvas/60 font-mono text-[11px] tracking-wide text-muted uppercase">
                <tr>
                    <th class="px-4 py-3">Project</th>
                    <th class="px-4 py-3 text-right">Revenue</th>
                    <th class="px-4 py-3 text-right">Direct</th>
                    <th class="px-4 py-3 text-right">Shared</th>
                    <th class="px-4 py-3 text-right">Expenses</th>
                    <th class="px-4 py-3 text-right">Net</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($report['projects'] as $row)
                    <tr class="hover:bg-canvas/40">
                        <td class="px-4 py-3">{{ $row['domain'] }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($row['revenue_paisa'] / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($row['direct_expense_paisa'] / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($row['shared_expense_paisa'] / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($row['total_expense_paisa'] / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs {{ $row['net_profit_paisa'] < 0 ? 'text-danger' : '' }}">
                            {{ number_format($row['net_profit_paisa'] / 100, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
