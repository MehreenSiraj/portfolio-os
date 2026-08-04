<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Settings</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Task templates</h1>
            <p class="mt-1 text-sm text-muted">On-page + technical SEO checklist items auto-copied onto new projects.</p>
        </div>
        <x-button wire:click="create">Add template</x-button>
    </div>

    @if ($showForm)
        <div class="rounded-xl border border-line bg-surface p-5">
            <h2 class="text-base font-semibold">{{ $editingId ? 'Edit template' : 'New template' }}</h2>
            <form wire:submit="save" class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-input label="Title" wire:model="title" error="{{ $errors->first('title') }}" class="sm:col-span-2" />
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-sm font-medium">Description</label>
                    <textarea wire:model="description" rows="2" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm"></textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium">Category</label>
                    <select wire:model="category" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        @foreach ($categoryOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input label="Sort order" type="number" min="0" wire:model="sort_order" />
                <label class="flex items-center gap-2 text-sm sm:col-span-2">
                    <input type="checkbox" wire:model="is_active" class="rounded border-line">
                    Active (included on new projects)
                </label>
                <div class="flex gap-2 sm:col-span-2">
                    <x-button type="submit">Save</x-button>
                    <x-button type="button" variant="secondary" wire:click="cancel">Cancel</x-button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-line bg-surface">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line text-left text-xs tracking-wide text-muted uppercase">
                <tr>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Active</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($templates as $template)
                    <tr class="hover:bg-canvas/50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $template->sort_order }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $template->title }}</p>
                            @if ($template->description)
                                <p class="mt-0.5 text-xs text-muted line-clamp-1">{{ $template->description }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-muted">{{ $template->category->label() }}</td>
                        <td class="px-4 py-3">
                            <x-badge :tone="$template->is_active ? 'success' : 'warn'">
                                {{ $template->is_active ? 'Yes' : 'No' }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <x-button size="sm" variant="ghost" wire:click="edit({{ $template->id }})">Edit</x-button>
                            <x-button size="sm" variant="secondary" wire:click="toggleActive({{ $template->id }})">Toggle</x-button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
