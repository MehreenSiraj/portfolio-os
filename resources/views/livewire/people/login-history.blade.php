<div>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">People</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Login history</h1>
            <p class="mt-1 text-sm text-muted">Successful sign-ins (UTC stored, shown in {{ $tz }}).</p>
        </div>
        @if ($canFilterUsers)
            <div class="w-full sm:w-56">
                <label class="mb-1 block text-xs font-medium text-muted">Person</label>
                <select wire:model.live="userId" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                    <option value="">Everyone I can see</option>
                    @foreach ($viewableUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @if ($histories->isEmpty())
        <x-empty-state title="No logins yet" description="History appears after a successful sign-in." />
    @else
        <x-table :headers="['When', 'Person', 'Device', 'IP']">
            @foreach ($histories as $row)
                <tr class="hover:bg-canvas/50">
                    <td class="px-4 py-3 font-mono text-xs">
                        {{ $row->logged_in_at->timezone($tz)->format('Y-m-d H:i') }}
                        <span class="block text-muted">{{ $tz }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm font-medium">{{ $row->user?->name }}</td>
                    <td class="px-4 py-3 text-sm text-muted">{{ $row->device ?? '—' }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-muted">{{ $row->ip_address ?? '—' }}</td>
                </tr>
            @endforeach
        </x-table>
        <div class="mt-4">{{ $histories->links() }}</div>
    @endif
</div>
