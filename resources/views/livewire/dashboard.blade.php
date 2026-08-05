<div>
    <div class="mb-10 max-w-3xl">
        <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Overview</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-ink">Home</h1>
        <p class="mt-2 text-muted">
            Portfolio pulse, today’s work, and approvals. Welcome, {{ auth()->user()->name }}.
        </p>
    </div>

    @if (\App\Support\AiAvailability::enabled())
        <div class="mb-8">
            <livewire:ai.ask :compact="true" />
        </div>
    @endif

    @if ($canSeeApprovals || $canSeeTasks)
        <div class="mb-8 grid gap-4 lg:grid-cols-2">
            @if ($canSeeApprovals)
                <div class="rounded-xl border border-line bg-surface p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-base font-semibold">Awaiting my approval</h2>
                        <a href="{{ route('approvals.queue') }}" wire:navigate class="text-sm font-medium text-accent hover:underline">
                            Queue ({{ $awaitingCount }})
                        </a>
                    </div>
                    @if ($awaitingMyApproval->isEmpty())
                        <p class="text-sm text-muted">Nothing waiting on you.</p>
                    @else
                        <ul class="divide-y divide-line">
                            @foreach ($awaitingMyApproval as $row)
                                <li class="flex items-center justify-between gap-3 py-2.5 text-sm">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">{{ $row['label'] }}</p>
                                        <p class="text-xs text-muted">{{ $row['type'] }} · {{ $row['project'] }}</p>
                                    </div>
                                    <a href="{{ $row['url'] }}" wire:navigate class="shrink-0 text-accent hover:underline">Review</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @if ($canSeeTasks)
                <div class="rounded-xl border border-line bg-surface p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-base font-semibold">My tasks due today</h2>
                        <a href="{{ route('tasks.index', ['mineOnly' => '1']) }}" wire:navigate class="text-sm font-medium text-accent hover:underline">All mine</a>
                    </div>
                    @if ($myTasksDueToday->isEmpty())
                        <p class="text-sm text-muted">No tasks due today.</p>
                    @else
                        <ul class="divide-y divide-line">
                            @foreach ($myTasksDueToday as $task)
                                <li class="flex items-center justify-between gap-3 py-2.5 text-sm">
                                    <div class="min-w-0">
                                        <a href="{{ route('tasks.show', $task) }}" wire:navigate class="truncate font-medium hover:text-accent">{{ $task->title }}</a>
                                        <p class="text-xs text-muted">{{ $task->project?->domain }}</p>
                                    </div>
                                    <x-badge tone="warn">{{ $task->status->label() }}</x-badge>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </div>
    @endif

    @if ($canSeeTeamAttendance)
        <div class="mb-8 rounded-xl border border-line bg-surface p-5">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold">Team attendance today</h2>
                @if ($canSeePeople)
                    <a href="{{ route('people.attendance') }}" wire:navigate class="text-sm font-medium text-accent hover:underline">Sheet</a>
                @endif
            </div>
            <ul class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($teamAttendanceToday as $row)
                    <li class="flex items-center justify-between rounded-lg border border-line/70 px-3 py-2 text-sm">
                        <span class="font-medium">{{ $row['name'] }}</span>
                        @if ($row['status'] === 'present')
                            <x-badge tone="success">{{ $row['is_late'] ? 'Late' : 'Present' }}</x-badge>
                        @elseif ($row['status'] === 'leave')
                            <x-badge tone="warn">Leave</x-badge>
                        @elseif ($row['status'] === 'holiday')
                            <x-badge tone="accent">Holiday</x-badge>
                        @else
                            <x-badge tone="danger">{{ $row['status_label'] }}</x-badge>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @elseif ($canSeePeople)
        <div class="mb-8 flex flex-wrap gap-3 text-sm">
            <a href="{{ route('people.work-logs') }}" wire:navigate class="rounded-lg border border-line bg-surface px-3 py-2 text-accent hover:underline">Work log</a>
            <a href="{{ route('people.scorecard') }}" wire:navigate class="rounded-lg border border-line bg-surface px-3 py-2 text-accent hover:underline">My scorecard</a>
            <a href="{{ route('people.attendance') }}" wire:navigate class="rounded-lg border border-line bg-surface px-3 py-2 text-accent hover:underline">Attendance</a>
        </div>
    @endif

    @if ($canViewProjects)
        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-line bg-surface px-5 py-4">
                <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Projects</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $totalProjects }}</p>
            </div>
            <div class="rounded-xl border border-line bg-surface px-5 py-4">
                <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Open tasks</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $openTasksPortfolio }}</p>
            </div>
            <div class="rounded-xl border border-line bg-surface px-5 py-4">
                <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Month revenue</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight">{{ number_format($monthRevenuePlaceholder / 100, 0) }}</p>
                <p class="mt-1 text-xs text-muted">PKR · this month</p>
            </div>
            <div class="rounded-xl border border-line bg-surface px-5 py-4">
                <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Expiring vault</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $expiring->count() }}</p>
                <p class="mt-1 text-xs text-muted">Within {{ implode('/', $thresholds) }} days</p>
            </div>
        </div>

        <div class="mb-10 grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-line bg-surface p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-semibold">Status mix</h2>
                    <a href="{{ route('projects.index') }}" wire:navigate class="text-sm font-medium text-accent hover:underline">All projects</a>
                </div>
                <ul class="space-y-2">
                    @foreach ($statusOptions as $value => $label)
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-muted">{{ $label }}</span>
                            <span class="font-medium tabular-nums">{{ $byStatus[$value] ?? 0 }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-xl border border-line bg-surface p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-semibold">Recent projects</h2>
                </div>
                @if ($recentProjects->isEmpty())
                    <p class="text-sm text-muted">No projects yet.</p>
                @else
                    <ul class="divide-y divide-line">
                        @foreach ($recentProjects as $project)
                            <li class="flex items-center justify-between py-2.5 first:pt-0 last:pb-0">
                                <a href="{{ route('projects.show', $project) }}" wire:navigate class="font-medium text-ink hover:text-accent">
                                    {{ $project->domain }}
                                </a>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs text-muted">{{ $project->openTasksCount() }} open</span>
                                    <x-badge :tone="match($project->status->value) {
                                        'monetized' => 'success',
                                        'paused', 'sold' => 'warn',
                                        default => 'accent',
                                    }">
                                        {{ $project->status->label() }}
                                    </x-badge>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @else
        <x-empty-state
            title="No portfolio access"
            description="You do not have projects.view yet. Ask an admin for a role assignment."
        />
    @endif

    @if ($canViewCredentials)
        <div class="rounded-xl border border-line bg-surface p-5">
            <div class="mb-4">
                <h2 class="text-base font-semibold">Expiring soon</h2>
                <p class="mt-1 text-sm text-muted">Credentials within {{ implode(', ', $thresholds) }}-day alert windows (including recently expired).</p>
            </div>

            @if ($expiring->isEmpty())
                <p class="text-sm text-muted">Nothing expiring in the alert window.</p>
            @else
                <x-table :headers="['Credential', 'Project', 'Expires', 'Window']">
                    @foreach ($expiring as $credential)
                        @php
                            $days = $credential->daysUntilExpiry();
                            $urgency = $credential->expiryUrgency($thresholds);
                        @endphp
                        <tr class="hover:bg-canvas/50">
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $credential->label }}</p>
                                <p class="font-mono text-xs text-muted">{{ $credential->type->label() }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if ($credential->project)
                                    <a href="{{ route('projects.show', $credential->project) }}" wire:navigate class="text-accent hover:underline">
                                        {{ $credential->project->domain }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">
                                {{ $credential->expires_on?->timezone(\App\Support\AppSettings::get('display_timezone', 'Asia/Karachi'))->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($days !== null && $days < 0)
                                    <x-badge tone="danger">Expired {{ abs($days) }}d</x-badge>
                                @elseif ($urgency === '7' || ($days !== null && $days <= 7))
                                    <x-badge tone="danger">{{ $days }}d</x-badge>
                                @elseif ($urgency === '14' || ($days !== null && $days <= 14))
                                    <x-badge tone="warn">{{ $days }}d</x-badge>
                                @else
                                    <x-badge tone="accent">{{ $days }}d</x-badge>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </div>
    @endif
</div>
