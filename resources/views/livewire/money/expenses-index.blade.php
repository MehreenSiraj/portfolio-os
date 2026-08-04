<div>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Expenses</h1>
            <p class="mt-1 text-sm text-muted">Direct + shared (allocated by revenue). Bulk mark paid. Receipts optional.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($canManage)
                <x-button type="button" variant="secondary" wire:click="$set('showRecurring', true)">Recurring</x-button>
                <x-button type="button" wire:click="create">Add expense</x-button>
                <x-button type="button" variant="secondary" wire:click="bulkMarkPaid">Mark selected paid</x-button>
            @endif
            <x-button type="button" variant="ghost" wire:click="exportCsv">Export CSV</x-button>
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-3">
        <x-input wire:model.live.debounce.300ms="search" placeholder="Search" class="min-w-[12rem]" />
        <select wire:model.live="projectFilter" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm">
            <option value="">All projects</option>
            @foreach ($projects as $p)
                <option value="{{ $p->id }}">{{ $p->domain }}</option>
            @endforeach
        </select>
        <x-input type="month" wire:model.live="monthFilter" />
        <select wire:model.live="paidFilter" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm">
            <option value="">Paid status</option>
            <option value="paid">Paid</option>
            <option value="unpaid">Unpaid</option>
        </select>
    </div>

    @if ($showForm && $canManage)
        <div class="mb-8 rounded-xl border border-line bg-surface p-5">
            <h2 class="text-sm font-semibold">{{ $editingId ? 'Edit expense' : 'New expense' }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <label class="flex items-center gap-2 text-sm sm:col-span-2">
                    <input type="checkbox" wire:model.live="is_shared" class="rounded border-line" />
                    Shared cost (split by monthly revenue across active sites)
                </label>
                @unless ($is_shared)
                    <div>
                        <label class="text-sm font-medium">Project</label>
                        <select wire:model="project_id" class="mt-1 block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                            <option value="">Select…</option>
                            @foreach ($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->domain }}</option>
                            @endforeach
                        </select>
                    </div>
                @endunless
                <div>
                    <label class="text-sm font-medium">Category</label>
                    <select wire:model="expense_category_id" class="mt-1 block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        <option value="">—</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input label="Amount PKR" wire:model="amount" />
                <x-input label="Description" wire:model="description" />
                <x-input type="date" label="Date" wire:model="expense_date" />
                <x-input label="Notes" wire:model="notes" />
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model="is_paid" class="rounded border-line" /> Paid
                </label>
                <x-file-input
                    label="Receipt"
                    wire:model="receipt"
                    :filename="$receipt?->getClientOriginalName()"
                    :error="$errors->first('receipt')"
                />
            </div>
            <div class="mt-4 flex gap-2">
                <x-button type="button" wire:click="save">Save</x-button>
                <x-button type="button" variant="ghost" wire:click="$set('showForm', false)">Cancel</x-button>
            </div>
        </div>
    @endif

    @if ($showRecurring && $canManage)
        <div class="mb-8 rounded-xl border border-line bg-surface p-5">
            <h2 class="text-sm font-semibold">Recurring expense template</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <x-input label="Description" wire:model="rec_description" />
                <x-input label="Amount PKR" wire:model="rec_amount" />
                <x-input type="number" label="Day of month (1–28)" wire:model="rec_day" />
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.live="rec_is_shared" class="rounded border-line" /> Shared
                </label>
                @unless ($rec_is_shared)
                    <select wire:model="rec_project_id" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        <option value="">Project…</option>
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->domain }}</option>
                        @endforeach
                    </select>
                @endunless
                <select wire:model="rec_category_id" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                    <option value="">Category…</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-4 flex gap-2">
                <x-button type="button" wire:click="saveRecurring">Save template</x-button>
                <x-button type="button" variant="ghost" wire:click="$set('showRecurring', false)">Cancel</x-button>
            </div>
            @if ($recurring->isNotEmpty())
                <ul class="mt-4 space-y-1 text-xs text-muted">
                    @foreach ($recurring as $r)
                        <li>{{ $r->description }} — next {{ $r->next_run_date->format('Y-m-d') }} · {{ number_format($r->amount_paisa / 100, 2) }} PKR</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-line">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-line bg-canvas/60 font-mono text-[11px] tracking-wide text-muted uppercase">
                <tr>
                    @if ($canManage)<th class="px-3 py-3"></th>@endif
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Project</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3 text-right">PKR</th>
                    <th class="px-4 py-3">Paid</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($expenses as $e)
                    <tr class="hover:bg-canvas/40">
                        @if ($canManage)
                            <td class="px-3 py-3">
                                <input type="checkbox" wire:model="selected.{{ $e->id }}" class="rounded border-line" />
                            </td>
                        @endif
                        <td class="px-4 py-3 font-mono text-xs">{{ $e->expense_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            @if ($e->is_shared)
                                <x-badge tone="warn">Shared</x-badge>
                            @else
                                {{ $e->project?->domain }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-muted">{{ $e->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $e->description }}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($e->amount_paisa / 100, 2) }}</td>
                        <td class="px-4 py-3">
                            @if ($e->is_paid)
                                <x-badge tone="success">Paid</x-badge>
                            @else
                                <x-badge>Unpaid</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($canManage)
                                <div class="inline-flex justify-end gap-1">
                                    <x-button type="button" size="sm" variant="ghost" wire:click="edit({{ $e->id }})">Edit</x-button>
                                    <x-button type="button" size="sm" variant="ghost" class="text-danger hover:text-danger" wire:click="delete({{ $e->id }})" wire:confirm="Soft-delete?">Delete</x-button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-muted">No expenses.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $expenses->links() }}</div>
</div>
