<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Work</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Links</h1>
            <p class="mt-1 text-sm text-muted">Monthly targets, individual approval, domain duplicate warnings.</p>
        </div>
        @if ($canCreate)
            <x-button wire:click="create">Log link</x-button>
        @endif
    </div>

    <div class="flex flex-col gap-3 lg:flex-row">
        <div class="max-w-sm flex-1">
            <x-input type="search" wire:model.live.debounce.300ms="search" placeholder="Search URL, domain, anchor…" />
        </div>
        <select wire:model.live="projectFilter" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm sm:w-48">
            <option value="">All projects</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->domain }}</option>
            @endforeach
        </select>
        <select wire:model.live="statusFilter" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm sm:w-40">
            <option value="">All workflows</option>
            @foreach ($statusOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    @if ($budgetProject)
        <div class="rounded-xl border border-line bg-surface p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold">Monthly link plan · {{ $budgetProject->domain }}</p>
                    <p class="mt-1 text-sm text-muted">
                        {{ $monthApprovedCount }} / {{ $budgetProject->monthly_link_target }} approved ·
                        {{ number_format($monthSpend / 100, 0) }} / {{ number_format($budgetProject->monthly_link_budget_paisa / 100, 0) }} PKR
                    </p>
                </div>
                @if ($canEditBudget)
                    <x-button size="sm" variant="ghost" wire:click="$toggle('editingBudget')">
                        {{ $editingBudget ? 'Close' : 'Edit target' }}
                    </x-button>
                @endif
            </div>
            @if ($editingBudget && $canEditBudget)
                <form wire:submit="saveBudget" class="mt-4 grid gap-3 sm:grid-cols-3">
                    <x-input label="Monthly target (count)" type="number" min="0" wire:model="monthly_link_target" />
                    <x-input label="Monthly budget (PKR)" type="number" step="0.01" min="0" wire:model="monthly_link_budget" />
                    <div class="flex items-end">
                        <x-button type="submit" size="sm">Save plan</x-button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    @if ($showForm)
        <div class="rounded-xl border border-line bg-surface p-5">
            <h2 class="text-base font-semibold">{{ $editingId ? 'Edit link' : 'Log link' }}</h2>
            <form wire:submit="save" class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="block text-sm font-medium">Project</label>
                    <select wire:model="project_id" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        <option value="">Select…</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->domain }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <x-input label="Source URL" wire:model="source_url" error="{{ $errors->first('source_url') }}" class="sm:col-span-2" />
                <x-input label="Target page" wire:model="target_page" error="{{ $errors->first('target_page') }}" />
                <x-input label="Anchor text" wire:model="anchor_text" error="{{ $errors->first('anchor_text') }}" />
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium">Type</label>
                    <select wire:model="type" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        @foreach ($typeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input label="Cost (PKR)" type="number" step="0.01" min="0" wire:model="cost" />
                <x-input label="Date" type="date" wire:model="link_date" />
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium">Live status</label>
                    <select wire:model="live_status" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        @foreach ($liveStatusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <button type="button" class="text-sm font-medium text-accent hover:underline" wire:click="$toggle('showMore')">
                        {{ $showMore ? 'Hide' : 'More' }} details
                    </button>
                </div>
                @if ($showMore)
                    <x-input label="DR" type="number" min="0" max="100" wire:model="dr" />
                    <x-input label="DA" type="number" min="0" max="100" wire:model="da" />
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="block text-sm font-medium">Assignee</label>
                        <select wire:model="assigned_to" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                            <option value="">Unassigned</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex gap-2 sm:col-span-2">
                    <x-button type="submit">Save</x-button>
                    <x-button type="button" variant="secondary" wire:click="cancel">Cancel</x-button>
                </div>
            </form>
        </div>
    @endif

    @if ($showReject)
        <div class="rounded-xl border border-danger/20 bg-surface p-5">
            <h2 class="text-sm font-semibold text-danger">Reject link</h2>
            <textarea wire:model="rejection_reason" rows="3" class="mt-3 block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm" placeholder="Reason required…"></textarea>
            @error('rejection_reason') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            <div class="mt-3 flex gap-2">
                <x-button variant="danger" wire:click="reject">Confirm</x-button>
                <x-button variant="secondary" wire:click="cancel">Cancel</x-button>
            </div>
        </div>
    @endif

    @if ($links->isEmpty())
        <x-empty-state title="No links" description="Log a link with source URL — duplicate domains warn but do not block." />
    @else
        <div class="overflow-x-auto rounded-xl border border-line bg-surface">
            <table class="min-w-full text-sm">
                <thead class="border-b border-line text-left text-xs tracking-wide text-muted uppercase">
                    <tr>
                        <th class="px-4 py-3">Source</th>
                        <th class="px-4 py-3">Target</th>
                        <th class="px-4 py-3">Cost</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($links as $link)
                        <tr class="hover:bg-canvas/50">
                            <td class="px-4 py-3">
                                <p class="font-medium truncate max-w-[14rem]">{{ $link->source_domain }}</p>
                                <p class="text-xs text-muted truncate max-w-[14rem]">{{ $link->project?->domain }} · {{ $link->type->label() }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="truncate max-w-[12rem]">{{ $link->target_page }}</p>
                                <p class="text-xs text-muted truncate max-w-[12rem]">“{{ $link->anchor_text }}”</p>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">
                                {{ number_format($link->cost_paisa / 100, 2) }}
                                @if ($link->expense_id)
                                    <span class="block text-success">exp #{{ $link->expense_id }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-badge :tone="match($link->workflow_status->value) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'submitted' => 'warn',
                                    default => 'accent',
                                }">{{ $link->workflow_status->label() }}</x-badge>
                                <x-badge tone="{{ $link->live_status->value === 'removed' ? 'warn' : 'accent' }}" class="mt-1">{{ $link->live_status->label() }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-1">
                                    @can('update', $link)
                                        <x-button size="sm" variant="ghost" wire:click="edit({{ $link->id }})">Edit</x-button>
                                    @endcan
                                    @can('submit', $link)
                                        @if (in_array($link->workflow_status->value, ['pending', 'rejected'], true))
                                            <x-button size="sm" variant="secondary" wire:click="submit({{ $link->id }})">Submit</x-button>
                                        @endif
                                    @endcan
                                    @can('approve', $link)
                                        @if (in_array($link->workflow_status->value, ['submitted', 'pending'], true))
                                            <x-button size="sm" wire:click="approve({{ $link->id }})">Approve</x-button>
                                            <x-button size="sm" variant="danger" wire:click="openReject({{ $link->id }})">Reject</x-button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $links->links() }}</div>
    @endif
</div>
