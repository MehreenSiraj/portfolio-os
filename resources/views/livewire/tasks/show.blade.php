<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <a href="{{ route('tasks.index') }}" wire:navigate class="text-sm font-medium text-muted hover:text-ink">← Tasks</a>
        <div class="mt-3 flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">{{ $task->project?->domain }}</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $task->title }}</h1>
                <div class="mt-2 flex flex-wrap gap-2">
                    <x-badge :tone="match($task->status->value) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'submitted' => 'warn',
                        default => 'accent',
                    }">{{ $task->status->label() }}</x-badge>
                    <x-badge tone="accent">{{ $task->type->label() }}</x-badge>
                </div>
            </div>
        </div>
    </div>

    @if ($task->description)
        <div class="rounded-xl border border-line bg-surface p-5">
            <h2 class="text-sm font-semibold">Description</h2>
            <p class="mt-2 whitespace-pre-wrap text-sm text-muted">{{ $task->description }}</p>
        </div>
    @endif

    @if ($task->rejection_reason)
        <div class="rounded-xl border border-danger/20 bg-danger-soft px-4 py-3 text-sm text-danger">
            <p class="font-medium">Rejected</p>
            <p class="mt-1">{{ $task->rejection_reason }}</p>
        </div>
    @endif

    @if ($canUpdate)
        <div class="rounded-xl border border-line bg-surface p-5">
            <h2 class="text-sm font-semibold">Details</h2>
            <form wire:submit="saveMeta" class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-input label="Time spent (min)" type="number" min="0" wire:model="time_spent_minutes" />
                <x-input label="Due date" type="date" wire:model="due_date" />
                @if ($canAssign)
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
                <div class="sm:col-span-2">
                    <x-button type="submit" size="sm">Save details</x-button>
                </div>
            </form>
        </div>
    @else
        <div class="rounded-xl border border-line bg-surface p-5 text-sm">
            <dl class="grid gap-3 sm:grid-cols-3">
                <div>
                    <dt class="text-muted">Assignee</dt>
                    <dd class="font-medium">{{ $task->assignee?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Due</dt>
                    <dd class="font-medium font-mono text-xs">{{ $task->due_date?->format('Y-m-d') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Time spent</dt>
                    <dd class="font-medium">{{ $task->time_spent_minutes }} min</dd>
                </div>
            </dl>
        </div>
    @endif

    <div class="flex flex-wrap gap-2">
        @if ($canUpdate && in_array($task->status->value, ['assigned', 'rejected'], true))
            <x-button wire:click="start">Start</x-button>
        @endif
        @if ($canSubmit && in_array($task->status->value, ['assigned', 'in_progress', 'rejected'], true))
            <x-button wire:click="submit">Submit for approval</x-button>
        @endif
        @if ($canApprove && $task->status->value === 'submitted')
            <x-button wire:click="approve">Approve</x-button>
            <x-button variant="danger" wire:click="openReject">Reject</x-button>
        @endif
    </div>

    @if ($showReject)
        <div class="rounded-xl border border-danger/20 bg-surface p-5">
            <h2 class="text-sm font-semibold text-danger">Reject task</h2>
            <div class="mt-3 space-y-3">
                <textarea wire:model="rejection_reason" rows="3" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm" placeholder="Reason required…"></textarea>
                @error('rejection_reason') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                <div class="flex gap-2">
                    <x-button variant="danger" wire:click="reject">Confirm reject</x-button>
                    <x-button variant="secondary" wire:click="$set('showReject', false)">Cancel</x-button>
                </div>
            </div>
        </div>
    @endif

    @if ($canUpdate)
        <div class="rounded-xl border border-line bg-surface p-5 sm:p-6">
            <h2 class="text-sm font-semibold">Evidence</h2>
            <p class="mt-1 text-xs text-muted">Attach screenshots, docs, or other proof for this task.</p>
            <form wire:submit="uploadEvidence" class="mt-4 space-y-3">
                <x-file-input
                    wire:model="evidence"
                    :filename="$evidence?->getClientOriginalName()"
                    :error="$errors->first('evidence')"
                    hint="Max 10 MB"
                />
                <div class="flex items-center justify-end gap-2">
                    <x-button type="submit" size="sm">Upload</x-button>
                </div>
            </form>
            <ul class="mt-5 divide-y divide-line border-t border-line pt-1">
                @forelse ($task->media as $file)
                    <li class="flex items-center justify-between gap-3 py-2.5 text-sm">
                        <span class="min-w-0 truncate font-medium">{{ $file->original_name }}</span>
                        <x-button size="sm" variant="ghost" wire:click="deleteEvidence({{ $file->id }})">Remove</x-button>
                    </li>
                @empty
                    <li class="py-4 text-center text-sm text-muted">No evidence yet.</li>
                @endforelse
            </ul>
        </div>
    @elseif ($task->media->isNotEmpty())
        <div class="rounded-xl border border-line bg-surface p-5 sm:p-6">
            <h2 class="text-sm font-semibold">Evidence</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($task->media as $file)
                    <li class="truncate font-medium">{{ $file->original_name }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-xl border border-line bg-surface p-5">
        <h2 class="text-sm font-semibold">Comments</h2>
        <ul class="mt-4 space-y-3">
            @forelse ($task->comments as $comment)
                <li class="rounded-lg bg-canvas/70 px-3 py-2 text-sm">
                    <p class="font-medium">{{ $comment->user?->name }}
                        <span class="font-normal text-muted">· {{ $comment->created_at?->timezone(\App\Support\AppSettings::get('display_timezone', 'Asia/Karachi'))->format('Y-m-d H:i') }}</span>
                    </p>
                    <p class="mt-1 whitespace-pre-wrap text-muted">{{ $comment->body }}</p>
                </li>
            @empty
                <li class="text-sm text-muted">No comments yet.</li>
            @endforelse
        </ul>
        <form wire:submit="addComment" class="mt-4 space-y-2">
            <textarea wire:model="commentBody" rows="2" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm" placeholder="Add a comment…"></textarea>
            @error('commentBody') <p class="text-xs text-danger">{{ $message }}</p> @enderror
            <x-button type="submit" size="sm">Comment</x-button>
        </form>
    </div>
</div>
