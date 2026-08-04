<div>
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">People</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Scorecard</h1>
            <p class="mt-1 text-sm text-muted">
                Derived from tasks, articles, and links for {{ $subject->name }} · {{ $tz }}.
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-muted">Month</label>
                <input type="month" wire:model.live="month" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm" />
            </div>
            @if ($canFilterUsers)
                <div>
                    <label class="mb-1 block text-xs font-medium text-muted">Person</label>
                    <select wire:model.live="userId" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        @foreach ($viewableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-line bg-surface px-5 py-4">
            <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Tasks done</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums">{{ $card['tasks']['completed'] }}</p>
            <p class="mt-1 text-xs text-muted">of {{ $card['tasks']['assigned'] }} in scope</p>
        </div>
        <div class="rounded-xl border border-line bg-surface px-5 py-4">
            <p class="font-mono text-[11px] tracking-wide text-muted uppercase">On-time %</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums">
                {{ $card['tasks']['on_time_pct'] !== null ? $card['tasks']['on_time_pct'].'%' : '—' }}
            </p>
            <p class="mt-1 text-xs text-muted">{{ $card['tasks']['on_time'] }} on-time approvals</p>
        </div>
        <div class="rounded-xl border border-line bg-surface px-5 py-4">
            <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Rejection rate</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums">
                {{ $card['tasks']['rejection_rate_pct'] !== null ? $card['tasks']['rejection_rate_pct'].'%' : '—' }}
            </p>
            <p class="mt-1 text-xs text-muted">{{ $card['tasks']['rejected'] }} rejected</p>
        </div>
        <div class="rounded-xl border border-line bg-surface px-5 py-4">
            <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Avg turnaround</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums">
                {{ $card['tasks']['avg_turnaround_hours'] !== null ? $card['tasks']['avg_turnaround_hours'].'h' : '—' }}
            </p>
            <p class="mt-1 text-xs text-muted">submit → approve</p>
        </div>
    </div>

    <div class="mb-8 grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-line bg-surface p-5">
            <h2 class="text-sm font-semibold">Articles</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted">In scope</dt><dd class="font-medium tabular-nums">{{ $card['articles']['count'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted">Approved</dt><dd class="font-medium tabular-nums">{{ $card['articles']['approved'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted">Words</dt><dd class="font-medium tabular-nums">{{ number_format($card['articles']['words']) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted">Approved cost</dt><dd class="font-medium tabular-nums">{{ number_format($card['articles']['cost_paisa'] / 100, 2) }} PKR</dd></div>
            </dl>
        </div>
        <div class="rounded-xl border border-line bg-surface p-5">
            <h2 class="text-sm font-semibold">Links</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted">Approved</dt><dd class="font-medium tabular-nums">{{ $card['links']['approved'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted">Cost</dt><dd class="font-medium tabular-nums">{{ number_format($card['links']['cost_paisa'] / 100, 2) }} PKR</dd></div>
            </dl>
        </div>
        <div class="rounded-xl border border-line bg-surface p-5">
            <h2 class="text-sm font-semibold">Output cost</h2>
            <p class="mt-3 text-2xl font-semibold tabular-nums">{{ $card['output_cost_formatted'] }}</p>
            <p class="mt-1 text-xs text-muted">Articles + links approved cost in this month</p>

            @if (! empty($card['pay_rates']))
                <h3 class="mt-5 text-xs font-semibold uppercase tracking-wide text-muted">Your pay rates</h3>
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach ($card['pay_rates'] as $rate)
                        <li class="flex justify-between gap-2">
                            <span class="text-muted">{{ $rate['type_label'] }}</span>
                            <span class="font-medium tabular-nums">{{ $rate['amount_formatted'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
