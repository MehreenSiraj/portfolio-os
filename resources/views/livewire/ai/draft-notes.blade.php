<div>
    <div class="mb-8 max-w-3xl">
        <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">AI drafts</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight">Monthly notes for review</h1>
        <p class="mt-2 text-muted">
            Auto-drafted portfolio and performance notes plus revenue/cost anomaly flags. Nothing is auto-emailed.
        </p>
    </div>

    @if ($notes->isEmpty())
        <x-empty-state
            title="No draft notes yet"
            description="Run php artisan ai:draft-monthly-summaries after AI is configured."
        />
    @else
        <ul class="space-y-4">
            @foreach ($notes as $note)
                <li class="rounded-xl border border-line bg-surface p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-badge tone="accent">AI-generated draft</x-badge>
                                <x-badge :tone="match($note->status) {
                                    'reviewed' => 'success',
                                    'dismissed' => 'warn',
                                    default => 'accent',
                                }">{{ $note->status }}</x-badge>
                                <span class="font-mono text-xs text-muted">{{ $note->type }} · {{ $note->period }}</span>
                            </div>
                            <h2 class="mt-2 text-base font-semibold">{{ $note->title }}</h2>
                        </div>
                        @if ($note->status === 'draft' && (auth()->user()->isAdmin() || auth()->user()->hasPermission('settings.update')))
                            <div class="flex gap-2">
                                <button type="button" wire:click="markReviewed({{ $note->id }})" class="text-sm font-medium text-accent hover:underline">Mark reviewed</button>
                                <button type="button" wire:click="dismiss({{ $note->id }})" class="text-sm font-medium text-muted hover:text-ink">Dismiss</button>
                            </div>
                        @endif
                    </div>
                    <div class="mt-3 whitespace-pre-wrap text-sm text-ink">{{ $note->body }}</div>
                    @if ($note->source_figures)
                        <details class="mt-3 text-sm">
                            <summary class="cursor-pointer text-muted">Source figures</summary>
                            <pre class="mt-2 overflow-x-auto font-mono text-xs text-muted">{{ json_encode($note->source_figures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
