<div class="{{ $compact ? '' : '' }}">
    @unless($compact)
        <div class="mb-8 max-w-3xl">
            <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Milestone 7</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-ink">AI assistant</h1>
            <p class="mt-2 text-muted">
                Ask plain-English questions about your scoped portfolio data. Answers map to fixed read-only reports — never generated SQL.
            </p>
        </div>
    @else
        <div class="mb-3 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold">Ask your data</h2>
                <p class="text-xs text-muted">AI-generated · scoped to your permissions</p>
            </div>
            <a href="{{ route('ai.ask') }}" wire:navigate class="text-sm font-medium text-accent hover:underline">Open full AI</a>
        </div>
    @endunless

    <div class="{{ $compact ? 'rounded-xl border border-line bg-surface p-5' : 'grid gap-6 lg:grid-cols-3' }}">
        <div class="{{ $compact ? '' : 'lg:col-span-2 space-y-4' }}">
            @unless($compact)
                <div class="flex flex-wrap gap-2 mb-4">
                    <button type="button" wire:click="$set('mode', 'ask')"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $mode === 'ask' ? 'bg-accent-soft text-accent' : 'border border-line text-muted' }}">
                        Ask
                    </button>
                    <button type="button" wire:click="$set('mode', 'meta')"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $mode === 'meta' ? 'bg-accent-soft text-accent' : 'border border-line text-muted' }}">
                        Meta title
                    </button>
                    <button type="button" wire:click="$set('mode', 'rejections')"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $mode === 'rejections' ? 'bg-accent-soft text-accent' : 'border border-line text-muted' }}">
                        Rejection themes
                    </button>
                    <button type="button" wire:click="$set('mode', 'brief')"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $mode === 'brief' ? 'bg-accent-soft text-accent' : 'border border-line text-muted' }}">
                        Task brief
                    </button>
                </div>
            @endunless

            @if ($mode === 'ask' || $compact)
                <form wire:submit="ask" class="space-y-3">
                    <label class="block text-sm font-medium">Question</label>
                    <textarea
                        wire:model="question"
                        rows="{{ $compact ? 2 : 3 }}"
                        placeholder="e.g. Which sites dropped revenue this month vs prior?"
                        class="block w-full rounded-lg border border-line bg-canvas px-3 py-2 text-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                    ></textarea>
                    @error('question') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                    <x-button type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="ask">Ask</span>
                        <span wire:loading wire:target="ask">Thinking…</span>
                    </x-button>
                </form>
            @elseif ($mode === 'meta' || $mode === 'brief')
                <form wire:submit="runHelper" class="space-y-3">
                    <x-input label="{{ $mode === 'meta' ? 'Title / topic' : 'Task title' }}" wire:model="helperTitle" error="{{ $errors->first('helperTitle') }}" />
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium">Notes (optional)</label>
                        <textarea wire:model="helperNotes" rows="3"
                                  class="block w-full rounded-lg border border-line bg-canvas px-3 py-2 text-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"></textarea>
                    </div>
                    <x-button type="submit" wire:loading.attr="disabled">Generate</x-button>
                </form>
            @else
                <form wire:submit="runHelper" class="space-y-3">
                    <p class="text-sm text-muted">Summarise recent rejection / revision reasons in your scope.</p>
                    <x-button type="submit" wire:loading.attr="disabled">Summarise</x-button>
                </form>
            @endif

            @if ($error)
                <div class="mt-4 rounded-lg border border-danger/20 bg-danger-soft px-4 py-3 text-sm text-danger">{{ $error }}</div>
            @endif

            @if ($answer !== '')
                <div class="mt-5 space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-badge tone="accent">AI-generated</x-badge>
                        @if ($cached)
                            <x-badge tone="warn">Cached</x-badge>
                        @endif
                        @if ($reportTitle)
                            <span class="text-xs text-muted">Report: {{ $reportTitle }}</span>
                        @endif
                    </div>
                    <div class="rounded-xl border border-line bg-canvas px-4 py-3 text-sm whitespace-pre-wrap text-ink">{{ $answer }}</div>

                    @if ($sourceFigures !== [])
                        <details class="rounded-xl border border-line bg-surface px-4 py-3 text-sm">
                            <summary class="cursor-pointer font-medium">Source figures</summary>
                            <pre class="mt-3 overflow-x-auto font-mono text-xs text-muted">{{ json_encode($sourceFigures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                    @endif
                </div>
            @endif
        </div>

        @unless($compact)
            <aside class="space-y-4">
                <div class="rounded-xl border border-line bg-surface p-5">
                    <h2 class="text-sm font-semibold">Spend this month</h2>
                    <p class="mt-2 font-mono text-2xl font-semibold tabular-nums">
                        ${{ number_format($spentCents / 100, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-muted">
                        of ${{ number_format($budgetCents / 100, 2) }} cap · ${{ number_format($remainingCents / 100, 2) }} left
                        (estimate)
                    </p>
                </div>
                <div class="rounded-xl border border-line bg-surface p-5">
                    <h2 class="text-sm font-semibold">Supported reports</h2>
                    <ul class="mt-3 space-y-2 text-sm text-muted">
                        @foreach ($supported as $key => $label)
                            <li class="flex gap-2">
                                <span class="font-mono text-[10px] text-accent uppercase shrink-0 mt-0.5">{{ str_replace('_', ' ', $key) }}</span>
                                <span>{{ $label }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @if(auth()->user()?->isAdmin() || auth()->user()?->hasPermission('settings.view'))
                    <a href="{{ route('ai.drafts') }}" wire:navigate class="block rounded-xl border border-line bg-surface p-4 text-sm font-medium text-accent hover:underline">
                        Review monthly draft notes →
                    </a>
                @endif
            </aside>
        @endunless
    </div>
</div>
