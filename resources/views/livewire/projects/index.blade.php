<div>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Portfolio</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Projects</h1>
            <p class="mt-1 text-sm text-muted">Domains, ownership, status, and operational snapshot.</p>
        </div>

        @can('create', App\Models\Project::class)
            <x-button wire:click="create">New project</x-button>
        @endcan
    </div>

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="max-w-sm flex-1">
            <x-input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Search domain, niche, CMS…"
            />
        </div>
        <div class="w-full sm:w-48">
            <select wire:model.live="statusFilter" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                <option value="">All statuses</option>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if(auth()->user()->hasPermission('projects.update') && $projects->isNotEmpty())
        <div class="mb-4 flex flex-wrap items-center gap-2 rounded-xl border border-line bg-surface px-4 py-3">
            <span class="text-sm text-muted">Bulk status</span>
            <select wire:model="bulkStatus" class="rounded-lg border border-line bg-surface px-3 py-1.5 text-sm">
                <option value="">Choose…</option>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <x-button size="sm" variant="secondary" wire:click="applyBulkStatus" wire:confirm="Apply status to selected projects?">Apply</x-button>
            <span class="text-xs text-muted">{{ count($selectedIds) }} selected</span>
        </div>
    @endif

    @if ($showForm)
        <div class="mb-8 rounded-xl border border-line bg-surface p-6">
            <h2 class="text-base font-semibold">{{ $editingProjectId ? 'Edit project' : 'Create project' }}</h2>
            <form wire:submit="save" class="mt-5 grid gap-4 sm:grid-cols-2">
                <x-input label="Domain" wire:model="domain" error="{{ $errors->first('domain') }}" placeholder="example.com" />
                <x-input label="Niche" wire:model="niche" error="{{ $errors->first('niche') }}" />
                <x-input label="CMS" wire:model="cms" error="{{ $errors->first('cms') }}" placeholder="WordPress" />
                <x-input label="Start date" type="date" wire:model="start_date" error="{{ $errors->first('start_date') }}" />
                <x-input
                    label="Acquisition cost (PKR)"
                    type="number"
                    step="0.01"
                    min="0"
                    wire:model="acquisition_cost"
                    error="{{ $errors->first('acquisition_cost') }}"
                    hint="Stored as integer paisa."
                />
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium">Status</label>
                    <select wire:model="status" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-sm font-medium">Notes</label>
                    <textarea wire:model="notes" rows="3" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm"></textarea>
                    @error('notes') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                @if ($canManageOwnership || ! $editingProjectId)
                    <div class="sm:col-span-2">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-medium">Ownership (must total 100%)</span>
                            <x-button type="button" size="sm" variant="ghost" wire:click="addOwnerRow">Add owner</x-button>
                        </div>
                        <div class="space-y-2">
                            @foreach ($owners as $index => $owner)
                                <div class="grid gap-2 sm:grid-cols-[1fr_8rem_auto]" wire:key="owner-{{ $index }}">
                                    <select wire:model="owners.{{ $index }}.user_id" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                                        <option value="">Select user…</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        wire:model="owners.{{ $index }}.share_percent"
                                        placeholder="%"
                                        class="rounded-lg border border-line bg-surface px-3 py-2 text-sm"
                                    >
                                    <x-button type="button" size="sm" variant="ghost" wire:click="removeOwnerRow({{ $index }})">Remove</x-button>
                                </div>
                            @endforeach
                        </div>
                        @error('owners') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        @error('owners.*') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="flex gap-2 sm:col-span-2">
                    <x-button type="submit">Save project</x-button>
                    <x-button type="button" variant="secondary" wire:click="cancel">Cancel</x-button>
                </div>
            </form>
        </div>
    @endif

    @if ($projects->isEmpty())
        <x-empty-state
            title="No projects yet"
            description="Add your first domain to start the portfolio vault."
        >
            @can('create', App\Models\Project::class)
                <x-button wire:click="create" size="sm">New project</x-button>
            @endcan
        </x-empty-state>
    @else
        <x-table :headers="['', 'Domain', 'Status', 'Month rev.', 'Month cost', 'Profit', 'Open tasks', 'Acquisition', '']">
            @foreach ($projects as $project)
                <tr class="hover:bg-canvas/50" wire:key="project-{{ $project->id }}">
                    <td class="px-4 py-3">
                        @if(auth()->user()->hasPermission('projects.update'))
                            <input type="checkbox" value="{{ $project->id }}" wire:model.live="selectedIds" class="rounded border-line text-accent">
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('projects.show', $project) }}" wire:navigate class="font-medium text-ink hover:text-accent">
                            {{ $project->domain }}
                        </a>
                        @if ($project->niche)
                            <p class="text-xs text-muted">{{ $project->niche }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :tone="match($project->status->value) {
                            'monetized' => 'success',
                            'paused', 'sold' => 'warn',
                            default => 'accent',
                        }">
                            {{ $project->status->label() }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-muted">{{ number_format($project->monthRevenuePaisa() / 100, 0) }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-muted">{{ number_format($project->monthCostPaisa() / 100, 0) }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-muted">{{ number_format($project->monthProfitPaisa() / 100, 0) }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-muted">{{ $project->openTasksCount() }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ number_format($project->acquisition_cost_paisa / 100, 0) }} PKR</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('projects.show', $project) }}" wire:navigate class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-muted hover:text-ink">Open</a>
                            @can('update', $project)
                                <x-button size="sm" variant="ghost" wire:click="edit({{ $project->id }})">Edit</x-button>
                            @endcan
                            @can('delete', $project)
                                <x-button size="sm" variant="ghost" wire:click="delete({{ $project->id }})" wire:confirm="Archive this project?">Archive</x-button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-4">
            {{ $projects->links() }}
        </div>
    @endif
</div>
