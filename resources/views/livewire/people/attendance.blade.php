<div>
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">People</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Attendance</h1>
            <p class="mt-1 text-sm text-muted">
                First login of each {{ $tz }} day is check-in. Late after {{ sprintf('%02d:00', $lateHour) }} local.
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

    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-xl border border-line bg-surface px-4 py-3">
            <p class="font-mono text-[10px] uppercase tracking-wide text-muted">Present</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $sheet['days_present'] }}</p>
        </div>
        <div class="rounded-xl border border-line bg-surface px-4 py-3">
            <p class="font-mono text-[10px] uppercase tracking-wide text-muted">Late</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $sheet['days_late'] }}</p>
        </div>
        <div class="rounded-xl border border-line bg-surface px-4 py-3">
            <p class="font-mono text-[10px] uppercase tracking-wide text-muted">Leave</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $sheet['days_leave'] }}</p>
        </div>
        <div class="rounded-xl border border-line bg-surface px-4 py-3">
            <p class="font-mono text-[10px] uppercase tracking-wide text-muted">Holiday</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $sheet['days_holiday'] }}</p>
        </div>
        <div class="rounded-xl border border-line bg-surface px-4 py-3">
            <p class="font-mono text-[10px] uppercase tracking-wide text-muted">Absent</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $sheet['days_absent'] }}</p>
        </div>
    </div>

    @if ($canManage)
        <form wire:submit="markLeave" class="mb-8 rounded-xl border border-line bg-surface p-5 space-y-4">
            <h2 class="text-sm font-semibold">Mark leave / holiday</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <x-input type="date" label="Date" wire:model="markDate" error="{{ $errors->first('markDate') }}" />
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium">Type</label>
                    <select wire:model="markStatus" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        <option value="leave">Leave</option>
                        <option value="holiday">Holiday</option>
                    </select>
                    @error('markStatus') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <x-input label="Notes" wire:model="markNotes" error="{{ $errors->first('markNotes') }}" />
                <div class="flex items-end">
                    <x-button type="submit">Save mark</x-button>
                </div>
            </div>
            <p class="text-xs text-muted">Applies to {{ $subject->name }} (selected person above).</p>
        </form>
    @endif

    <div class="overflow-x-auto rounded-xl border border-line bg-surface">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line bg-canvas/60 text-left text-xs font-medium uppercase tracking-wide text-muted">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">First login</th>
                    <th class="px-4 py-3">Notes</th>
                    @if ($canManage)
                        <th class="px-4 py-3"></th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($sheet['rows'] as $row)
                    <tr class="{{ $row['is_weekend'] ? 'bg-canvas/30' : '' }} hover:bg-canvas/50">
                        <td class="px-4 py-2.5 font-mono text-xs">
                            {{ $row['date'] }}
                            <span class="text-muted">{{ $row['weekday'] }}</span>
                        </td>
                        <td class="px-4 py-2.5">
                            @if ($row['status'] === 'present')
                                <x-badge tone="success">Present @if($row['is_late']) · late @endif</x-badge>
                            @elseif ($row['status'] === 'leave')
                                <x-badge tone="warn">Leave</x-badge>
                            @elseif ($row['status'] === 'holiday')
                                <x-badge tone="accent">Holiday</x-badge>
                            @elseif ($row['status'] === 'absent')
                                <x-badge tone="danger">Absent</x-badge>
                            @else
                                <span class="text-xs text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 font-mono text-xs text-muted">
                            @if ($row['first_login_at'])
                                {{ $row['first_login_at']->timezone($tz)->format('H:i') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-xs text-muted">{{ $row['notes'] ?? '' }}</td>
                        @if ($canManage)
                            <td class="px-4 py-2.5 text-right">
                                @if (in_array($row['status'], ['leave', 'holiday'], true))
                                    <button type="button" wire:click="clearMark('{{ $row['date'] }}')" class="text-xs text-accent hover:underline">
                                        Clear
                                    </button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
