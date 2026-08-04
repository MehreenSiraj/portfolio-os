<div>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Admin</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Users</h1>
            <p class="mt-1 text-sm text-muted">Manage accounts, roles, and access.</p>
        </div>

        @if(auth()->user()->hasPermission('users.create'))
            <x-button wire:click="create">New user</x-button>
        @endif
    </div>

    <div class="mb-5 max-w-sm">
        <x-input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Search name or email…"
        />
    </div>

    @if ($showForm)
        <div class="mb-8 rounded-xl border border-line bg-surface p-6">
            <h2 class="text-base font-semibold">{{ $editingUserId ? 'Edit user' : 'Create user' }}</h2>
            <form wire:submit="save" class="mt-5 grid gap-4 sm:grid-cols-2">
                <x-input label="Name" wire:model="name" error="{{ $errors->first('name') }}" />
                <x-input label="Email" type="email" wire:model="email" error="{{ $errors->first('email') }}" />
                <x-input
                    label="Password"
                    type="password"
                    wire:model="password"
                    error="{{ $errors->first('password') }}"
                    hint="{{ $editingUserId ? 'Leave blank to keep the current password.' : null }}"
                />

                <div class="space-y-1.5">
                    <span class="block text-sm font-medium">Status</span>
                    <label class="flex items-center gap-2 text-sm text-muted">
                        <input type="checkbox" wire:model="is_active" class="rounded border-line text-accent focus:ring-accent/30">
                        Active (can sign in)
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <span class="mb-2 block text-sm font-medium">Global roles</span>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($roles as $role)
                            <label class="flex items-center gap-2 rounded-lg border border-line px-3 py-2 text-sm">
                                <input
                                    type="checkbox"
                                    value="{{ $role->id }}"
                                    wire:model="selectedRoles"
                                    class="rounded border-line text-accent focus:ring-accent/30"
                                >
                                {{ $role->label }}
                            </label>
                        @endforeach
                    </div>
                    @error('selectedRoles') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-2 sm:col-span-2">
                    <x-button type="submit">Save user</x-button>
                    <x-button type="button" variant="secondary" wire:click="cancel">Cancel</x-button>
                </div>
            </form>
        </div>
    @endif

    @if ($users->isEmpty())
        <x-empty-state
            title="No users found"
            description="Create a teammate account or adjust your search."
        >
            @if(auth()->user()->hasPermission('users.create'))
                <x-button wire:click="create" size="sm">New user</x-button>
            @endif
        </x-empty-state>
    @else
        <x-table :headers="['Name', 'Email', 'Roles', 'Status', '']">
            @foreach ($users as $user)
                <tr class="hover:bg-canvas/50">
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-muted">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            @forelse ($user->roles as $role)
                                <x-badge tone="accent">{{ $role->label }}</x-badge>
                            @empty
                                <span class="text-xs text-muted">No roles</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if ($user->is_active)
                            <x-badge tone="success">Active</x-badge>
                        @else
                            <x-badge tone="danger">Inactive</x-badge>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-2">
                            @if(auth()->user()->hasPermission('users.update'))
                                <x-button size="sm" variant="ghost" wire:click="edit({{ $user->id }})">Edit</x-button>
                            @endif
                            @if(auth()->user()->hasPermission('users.deactivate') && $user->id !== auth()->id())
                                <x-button
                                    size="sm"
                                    variant="ghost"
                                    wire:click="toggleActive({{ $user->id }})"
                                    wire:confirm="{{ $user->is_active ? 'Deactivate this user? They will be blocked from signing in immediately.' : 'Reactivate this user?' }}"
                                >
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </x-button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>
