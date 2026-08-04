<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Work</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Tasks</h1>
            <p class="mt-1 text-sm text-muted">Setup checklists, recurring work, and ad-hoc assignments.</p>
        </div>
        @if ($canCreate)
            <x-button wire:click="create">New task</x-button>
        @endif
    </div>

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <div class="max-w-sm flex-1">
            <x-input type="search" wire:model.live.debounce.300ms="search" placeholder="Search tasks…" />
        </div>
        <select wire:model.live="projectFilter" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm sm:w-48">
            <option value="">All projects</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->domain }}</option>
            @endforeach
        </select>
        <select wire:model.live="statusFilter" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm sm:w-40">
            <option value="">All statuses</option>
            @foreach ($statusOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-muted">
            <input type="checkbox" wire:model.live="mineOnly" value="1" class="rounded border-line">
            Mine only
        </label>
    </div>

    @if (($canAssign || $canApprove) && $tasks->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2 rounded-xl border border-line bg-surface px-4 py-3">
            <span class="text-sm text-muted">Bulk</span>
            @if ($canAssign)
                <select wire:model="bulkAssignTo" class="rounded-lg border border-line bg-surface px-3 py-1.5 text-sm">
                    <option value="">Assign to…</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <x-button size="sm" variant="secondary" wire:click="applyBulkAssign">Assign</x-button>
            @endif
            @if ($canApprove || $canAssign)
                <select wire:model="bulkStatus" class="rounded-lg border border-line bg-surface px-3 py-1.5 text-sm">
                    <option value="">Status…</option>
                    @foreach ($statusOptions as $value => $label)
                        @if (! in_array($value, ['rejected', 'approved'], true) || $canApprove)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endif
                    @endforeach
                </select>
                <x-button size="sm" variant="secondary" wire:click="applyBulkStatus">Apply</x-button>
            @endif
            <span class="text-xs text-muted">{{ count($selectedIds) }} selected</span>
        </div>
    @endif

    @if ($showForm)
        <div class="rounded-xl border border-line bg-surface p-5">
            <h2 class="text-base font-semibold">{{ $editingId ? 'Edit task' : 'New task' }}</h2>
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
                <x-input label="Title" wire:model="title" error="{{ $errors->first('title') }}" class="sm:col-span-2" />
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-sm font-medium">Description</label>
                    <textarea wire:model="description" rows="3" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm"></textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium">Type</label>
                    <select wire:model="type" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        @foreach ($typeOptions as $value => $label)
                            @if ($value !== 'setup')
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium">Recurrence</label>
                    <select wire:model="recurrence_frequency" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        <option value="">None</option>
                        @foreach ($frequencyOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium">Assignee</label>
                    <select wire:model="assigned_to" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        <option value="">Unassigned</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input label="Due date" type="date" wire:model="due_date" error="{{ $errors->first('due_date') }}" />
                <x-input label="Time spent (minutes)" type="number" min="0" wire:model="time_spent_minutes" />
                <div class="flex gap-2 sm:col-span-2">
                    <x-button type="submit">Save</x-button>
                    <x-button type="button" variant="secondary" wire:click="cancel">Cancel</x-button>
                </div>
            </form>
        </div>
    @endif

    @if ($tasks->isEmpty())
        <x-empty-state title="No tasks" description="Create a project to auto-generate setup SEO checklists, or add ad-hoc work." />
    @else
        <div class="overflow-x-auto rounded-xl border border-line bg-surface">
            <table class="min-w-full text-sm">
                <thead class="border-b border-line text-left text-xs tracking-wide text-muted uppercase">
                    <tr>
                        @if ($canAssign || $canApprove)
                            <th class="px-3 py-3 w-10"></th>
                        @endif
                        <th class="px-4 py-3">Task</th>
                        <th class="px-4 py-3">Project</th>
                        <th class="px-4 py-3">Assignee</th>
                        <th class="px-4 py-3">Due</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($tasks as $task)
                        <tr class="hover:bg-canvas/50">
                            @if ($canAssign || $canApprove)
                                <td class="px-3 py-3">
                                    <input type="checkbox" value="{{ $task->id }}" wire:model.live="selectedIds" class="rounded border-line">
                                </td>
                            @endif
                            <td class="px-4 py-3">
                                <a href="{{ route('tasks.show', $task) }}" wire:navigate class="font-medium text-ink hover:text-accent">
                                    {{ $task->title }}
                                </a>
                                <p class="mt-0.5 font-mono text-[11px] text-muted">{{ $task->type->label() }}</p>
                            </td>
                            <td class="px-4 py-3 text-muted">{{ $task->project?->domain }}</td>
                            <td class="px-4 py-3">{{ $task->assignee?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs">
                                {{ $task->due_date?->timezone(\App\Support\AppSettings::get('display_timezone', 'Asia/Karachi'))->format('Y-m-d') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <x-badge :tone="match($task->status->value) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'submitted' => 'warn',
                                    default => 'accent',
                                }">{{ $task->status->label() }}</x-badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $tasks->links() }}</div>
    @endif
</div>
