<div>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Revenue</h1>
            <p class="mt-1 text-sm text-muted">Per site · per month · USD frozen with FX → PKR paisa</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($canManage)
                <x-button type="button" variant="secondary" wire:click="$set('showImport', true)">Import CSV</x-button>
                <x-button type="button" wire:click="create">Add revenue</x-button>
            @endif
            <x-button type="button" variant="ghost" wire:click="exportCsv">Export CSV</x-button>
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-3">
        <x-input wire:model.live.debounce.300ms="search" placeholder="Search domain or notes" class="min-w-[12rem]" />
        <select wire:model.live="projectFilter" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm">
            <option value="">All projects</option>
            @foreach ($projects as $p)
                <option value="{{ $p->id }}">{{ $p->domain }}</option>
            @endforeach
        </select>
        <x-input type="month" wire:model.live="monthFilter" />
    </div>

    @if ($showForm && $canManage)
        <div class="mb-8 rounded-xl border border-line bg-surface p-5">
            <h2 class="text-sm font-semibold">{{ $editingId ? 'Edit revenue' : 'New revenue' }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="text-sm font-medium">Project</label>
                    <select wire:model="project_id" class="mt-1 block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        <option value="">Select…</option>
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->domain }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <x-input type="month" label="Month" wire:model="period_month" error="{{ $errors->first('period_month') }}" />
                <div>
                    <label class="text-sm font-medium">Source</label>
                    <select wire:model="source" class="mt-1 block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        @foreach ($sources as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input label="Amount USD" wire:model="amount_usd" error="{{ $errors->first('amount_usd') }}" />
                <x-input label="FX rate (PKR per 1 USD)" wire:model="fx_rate" error="{{ $errors->first('fx_rate') }}"
                         hint="Stored on the row; never recalculated later at a new rate." />
                <x-input label="Notes" wire:model="notes" />
            </div>
            <div class="mt-4 flex gap-2">
                <x-button type="button" wire:click="save">Save</x-button>
                <x-button type="button" variant="ghost" wire:click="$set('showForm', false)">Cancel</x-button>
            </div>
        </div>
    @endif

    @if ($showImport && $canManage)
        <div class="mb-8 rounded-xl border border-line bg-surface p-5">
            <h2 class="text-sm font-semibold">Import AdSense CSV</h2>
            <p class="mt-1 text-xs text-muted">Columns: domain, month (YYYY-MM), amount_usd [, fx_rate]</p>
            <x-file-input
                class="mt-3"
                wire:model="importFile"
                accept=".csv,text/csv"
                :filename="$importFile?->getClientOriginalName()"
                :error="$errors->first('importFile')"
                hint="CSV only"
            />
            <div class="mt-4 flex gap-2">
                <x-button type="button" wire:click="import">Import</x-button>
                <x-button type="button" variant="ghost" wire:click="$set('showImport', false)">Cancel</x-button>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-line">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-line bg-canvas/60 font-mono text-[11px] tracking-wide text-muted uppercase">
                <tr>
                    <th class="px-4 py-3">Month</th>
                    <th class="px-4 py-3">Project</th>
                    <th class="px-4 py-3">Source</th>
                    <th class="px-4 py-3 text-right">USD</th>
                    <th class="px-4 py-3 text-right">FX</th>
                    <th class="px-4 py-3 text-right">PKR</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($revenues as $row)
                    <tr class="hover:bg-canvas/40">
                        <td class="px-4 py-3 font-mono text-xs">{{ $row->period_month->format('Y-m') }}</td>
                        <td class="px-4 py-3">{{ $row->project?->domain }}</td>
                        <td class="px-4 py-3">{{ $row->source->label() }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($row->amount_usd_cents / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ \App\Support\Money::fxRateFromE6($row->fx_rate_e6) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($row->amount_pkr_paisa / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($canManage)
                                <button type="button" class="text-xs text-accent" wire:click="edit({{ $row->id }})">Edit</button>
                                <button type="button" class="ml-2 text-xs text-danger" wire:click="delete({{ $row->id }})" wire:confirm="Soft-delete this revenue?">Delete</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-muted">No revenue rows yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $revenues->links() }}</div>
</div>
