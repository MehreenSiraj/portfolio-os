<div
    class="mx-auto max-w-3xl space-y-6"
    x-data
    x-on:keydown.window="
        if (['INPUT','TEXTAREA','SELECT'].includes($event.target.tagName)) return;
        if ($event.key === 'j' || $event.key === 'J') { $wire.next(); }
        if ($event.key === 'k' || $event.key === 'K') { $wire.prev(); }
        if ($event.key === 'a' || $event.key === 'A') { $event.preventDefault(); $wire.approve(); }
        if ($event.key === 'r' || $event.key === 'R') { $event.preventDefault(); $wire.openReject(); }
    "
>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Work</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Approval queue</h1>
            <p class="mt-1 text-sm text-muted">
                {{ $queueCount === 0 ? 'Queue empty' : "Item {$position} of {$queueCount}" }}
                · Shortcuts: <span class="font-mono text-xs">j/k</span> navigate · <span class="font-mono text-xs">a</span> approve · <span class="font-mono text-xs">r</span> reject
            </p>
        </div>
        <x-button size="sm" variant="secondary" wire:click="refreshQueue">Refresh</x-button>
    </div>

    @if (! $current)
        <x-empty-state title="Nothing awaiting approval" description="Submitted tasks, article drafts, and links appear here." />
    @else
        @php $item = $current['model']; @endphp
        <div class="rounded-xl border border-line bg-surface p-6">
            <div class="flex flex-wrap items-center gap-2">
                <x-badge tone="warn">{{ strtoupper($current['type']) }}</x-badge>
                <span class="text-sm text-muted">{{ $item->project?->domain }}</span>
            </div>

            @if ($current['type'] === 'task')
                <h2 class="mt-3 text-xl font-semibold">{{ $item->title }}</h2>
                <p class="mt-2 whitespace-pre-wrap text-sm text-muted">{{ $item->description ?: 'No description.' }}</p>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted">Assignee</dt>
                        <dd class="font-medium">{{ $item->assignee?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Time spent</dt>
                        <dd class="font-medium">{{ $item->time_spent_minutes }} min</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-muted">Evidence</dt>
                        <dd class="mt-1">
                            @forelse ($item->media as $file)
                                <span class="mr-2 inline-block rounded bg-canvas px-2 py-0.5 text-xs">{{ $file->original_name }}</span>
                            @empty
                                <span class="text-muted">None</span>
                            @endforelse
                        </dd>
                    </div>
                </dl>
                <a href="{{ route('tasks.show', $item) }}" wire:navigate class="mt-4 inline-block text-sm font-medium text-accent hover:underline">Open full task →</a>
            @elseif ($current['type'] === 'article')
                <h2 class="mt-3 text-xl font-semibold">{{ $item->title }}</h2>
                <p class="mt-1 font-mono text-sm text-muted">{{ $item->target_keyword }}</p>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted">Writer</dt>
                        <dd class="font-medium">{{ $item->writer?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Cost</dt>
                        <dd class="font-medium font-mono text-xs">{{ number_format($item->cost_paisa / 100, 2) }} PKR</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Words</dt>
                        <dd class="font-medium">{{ $item->word_count_actual ?? '—' }} / {{ $item->word_count_target ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">URL</dt>
                        <dd class="font-medium truncate">{{ $item->published_url ?: '—' }}</dd>
                    </div>
                </dl>
            @else
                <h2 class="mt-3 text-xl font-semibold">{{ $item->source_domain }}</h2>
                <p class="mt-1 truncate text-sm text-muted">{{ $item->source_url }}</p>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted">Target</dt>
                        <dd class="font-medium break-all">{{ $item->target_page }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Anchor</dt>
                        <dd class="font-medium">{{ $item->anchor_text }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">DR / DA</dt>
                        <dd class="font-medium">{{ $item->dr ?? '—' }} / {{ $item->da ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Cost</dt>
                        <dd class="font-medium font-mono text-xs">{{ number_format($item->cost_paisa / 100, 2) }} PKR</dd>
                    </div>
                </dl>
            @endif

            <div class="mt-6 flex flex-wrap gap-2">
                <x-button wire:click="approve">Approve <span class="opacity-60 font-mono text-xs">(a)</span></x-button>
                <x-button variant="danger" wire:click="openReject">Reject <span class="opacity-60 font-mono text-xs">(r)</span></x-button>
                <x-button variant="secondary" wire:click="prev">Prev (k)</x-button>
                <x-button variant="secondary" wire:click="next">Next (j)</x-button>
            </div>
        </div>

        @if ($showReject)
            <div class="rounded-xl border border-danger/20 bg-surface p-5">
                <h2 class="text-sm font-semibold text-danger">Rejection reason (required)</h2>
                <textarea wire:model="rejection_reason" rows="3" class="mt-3 block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm" placeholder="Explain what needs to change…"></textarea>
                @error('rejection_reason') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                <div class="mt-3 flex gap-2">
                    <x-button variant="danger" wire:click="reject">Confirm reject</x-button>
                    <x-button variant="secondary" wire:click="$set('showReject', false)">Cancel</x-button>
                </div>
            </div>
        @endif
    @endif
</div>
