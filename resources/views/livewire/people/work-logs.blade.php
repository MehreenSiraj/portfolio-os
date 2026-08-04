<div>
    <div class="mb-8">
        <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">People</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight">Work logs</h1>
        <p class="mt-1 text-sm text-muted">Short daily notes. Optional IDs of tasks / articles / links you touched.</p>
    </div>

    @if ($canWrite)
        <form wire:submit="save" class="mb-8 rounded-xl border border-line bg-surface p-5 space-y-4">
            <h2 class="text-sm font-semibold">Today’s log</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                <x-input type="date" label="Date" wire:model.live="logDate" error="{{ $errors->first('logDate') }}" />
            </div>
            <div class="space-y-1.5">
                <label class="block text-sm font-medium">What did you work on?</label>
                <textarea
                    wire:model="body"
                    rows="4"
                    class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                    placeholder="Brief summary of the day…"
                ></textarea>
                @error('body') <p class="text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <x-input label="Task IDs" wire:model="taskIdsInput" hint="Comma-separated" error="{{ $errors->first('taskIdsInput') }}" />
                <x-input label="Article IDs" wire:model="articleIdsInput" hint="Optional" error="{{ $errors->first('articleIdsInput') }}" />
                <x-input label="Link IDs" wire:model="linkIdsInput" hint="Optional" error="{{ $errors->first('linkIdsInput') }}" />
            </div>
            <x-button type="submit">Save log</x-button>
        </form>
    @endif

    @if ($canFilterUsers)
        <div class="mb-4 w-full sm:w-56">
            <label class="mb-1 block text-xs font-medium text-muted">Person</label>
            <select wire:model.live="userId" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                @foreach ($viewableUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if ($logs->isEmpty())
        <x-empty-state title="No work logs" description="Save a short note for the day above." />
    @else
        <ul class="space-y-3">
            @foreach ($logs as $log)
                <li class="rounded-xl border border-line bg-surface p-4">
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <p class="font-mono text-xs text-muted">{{ $log->local_date->format('Y-m-d') }} · {{ $log->user?->name }}</p>
                        @if (! empty($log->task_ids) || ! empty($log->article_ids) || ! empty($log->link_ids))
                            <p class="font-mono text-[10px] text-muted">
                                @if (! empty($log->task_ids)) T:{{ implode(',', $log->task_ids) }} @endif
                                @if (! empty($log->article_ids)) A:{{ implode(',', $log->article_ids) }} @endif
                                @if (! empty($log->link_ids)) L:{{ implode(',', $log->link_ids) }} @endif
                            </p>
                        @endif
                    </div>
                    <p class="whitespace-pre-wrap text-sm text-ink">{{ $log->body }}</p>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $logs->links() }}</div>
    @endif
</div>
