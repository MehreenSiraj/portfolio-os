<div>
    <div class="mb-10 max-w-3xl">
        <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Overview</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-ink">Home</h1>
        <p class="mt-2 text-muted">
            Portfolio pulse across projects and vault expiry. Welcome, {{ auth()->user()->name }}.
        </p>
    </div>

    @if ($canViewProjects)
        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-line bg-surface px-5 py-4">
                <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Projects</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $totalProjects }}</p>
            </div>
            <div class="rounded-xl border border-line bg-surface px-5 py-4">
                <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Month revenue</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight">{{ number_format($monthRevenuePlaceholder / 100, 0) }}</p>
                <p class="mt-1 text-xs text-muted">PKR · wired for M5</p>
            </div>
            <div class="rounded-xl border border-line bg-surface px-5 py-4">
                <p class="font-mono text-[11px] tracking-wide text-muted uppercase">Month cost</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight">{{ number_format($monthCostPlaceholder / 100, 0) }}</p>
                <p class="mt-1 text-xs text-muted">PKR · wired for M5</p>
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
                                <x-badge :tone="match($project->status->value) {
                                    'monetized' => 'success',
                                    'paused', 'sold' => 'warn',
                                    default => 'accent',
                                }">
                                    {{ $project->status->label() }}
                                </x-badge>
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
