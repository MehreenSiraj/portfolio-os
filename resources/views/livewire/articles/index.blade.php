<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Work</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Articles</h1>
            <p class="mt-1 text-sm text-muted">Briefs, drafts, keyword lock, and writer cost → expense on approve.</p>
        </div>
        @if ($canCreate)
            <x-button wire:click="create">New article</x-button>
        @endif
    </div>

    <div class="flex flex-col gap-3 lg:flex-row">
        <div class="max-w-sm flex-1">
            <x-input type="search" wire:model.live.debounce.300ms="search" placeholder="Search title or keyword…" />
        </div>
        <select wire:model.live="projectFilter" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm sm:w-48">
            <option value="">All projects</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->domain }}</option>
            @endforeach
        </select>
        <select wire:model.live="statusFilter" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm sm:w-44">
            <option value="">All statuses</option>
            @foreach ($statusOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    @if ($showForm)
        <div class="rounded-xl border border-line bg-surface p-5">
            <h2 class="text-base font-semibold">{{ $editingId ? 'Edit article' : 'New article' }}</h2>
            <form wire:submit="save" class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="block text-sm font-medium">Project</label>
                    <select wire:model="project_id" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        <option value="">Select…</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->domain }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <x-input label="Title" wire:model="title" error="{{ $errors->first('title') }}" />
                <x-input label="Target keyword" wire:model="target_keyword" error="{{ $errors->first('target_keyword') }}" hint="Unique per project." />
                <x-input label="Word count target" type="number" min="0" wire:model="word_count_target" />
                <x-input label="Word count actual" type="number" min="0" wire:model="word_count_actual" />
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium">Writer</label>
                    <select wire:model="writer_id" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        <option value="">Unassigned</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input label="Writer cost (PKR)" type="number" step="0.01" min="0" wire:model="cost" error="{{ $errors->first('cost') }}" />

                <div class="sm:col-span-2">
                    <button type="button" class="text-sm font-medium text-accent hover:underline" wire:click="$toggle('showMore')">
                        {{ $showMore ? 'Hide' : 'More' }} details
                    </button>
                </div>

                @if ($showMore)
                    <x-input label="Meta title" wire:model="meta_title" />
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="block text-sm font-medium">Meta description</label>
                        <textarea wire:model="meta_description" rows="2" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm"></textarea>
                    </div>
                    <x-input label="Published URL" wire:model="published_url" />
                    <x-input label="Publish date" type="date" wire:model="publish_date" />
                @endif

                <div class="flex gap-2 sm:col-span-2">
                    <x-button type="submit">Save</x-button>
                    <x-button type="button" variant="secondary" wire:click="cancel">Cancel</x-button>
                </div>
            </form>
        </div>
    @endif

    @if ($showRevision)
        <div class="rounded-xl border border-warn/30 bg-warn-soft/40 p-5">
            <h2 class="text-sm font-semibold">Request revision</h2>
            <textarea wire:model="revision_notes" rows="3" class="mt-3 block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm" placeholder="Reason required…"></textarea>
            @error('revision_notes') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            <div class="mt-3 flex gap-2">
                <x-button wire:click="requestRevision">Send</x-button>
                <x-button variant="secondary" wire:click="cancel">Cancel</x-button>
            </div>
        </div>
    @endif

    @if ($articles->isEmpty())
        <x-empty-state title="No articles" description="Add a brief with a unique keyword for the project." />
    @else
        <div class="space-y-3">
            @foreach ($articles as $article)
                <div class="rounded-xl border border-line bg-surface p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-medium">{{ $article->title }}</p>
                            <p class="mt-0.5 text-sm text-muted">
                                <span class="font-mono text-xs">{{ $article->target_keyword }}</span>
                                · {{ $article->project?->domain }}
                                · {{ $article->writer?->name ?? 'No writer' }}
                            </p>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <x-badge :tone="match($article->status->value) {
                                    'approved' => 'success',
                                    'revision_requested' => 'danger',
                                    'draft_submitted' => 'warn',
                                    default => 'accent',
                                }">{{ $article->status->label() }}</x-badge>
                                <span class="font-mono text-xs text-muted">{{ number_format($article->cost_paisa / 100, 2) }} PKR</span>
                                @if ($article->expense_id)
                                    <x-badge tone="success">Expense #{{ $article->expense_id }}</x-badge>
                                @endif
                            </div>
                            @if ($article->revision_notes)
                                <p class="mt-2 text-sm text-danger">Revision: {{ $article->revision_notes }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2 shrink-0">
                            @can('update', $article)
                                <x-button size="sm" variant="ghost" wire:click="edit({{ $article->id }})">Edit</x-button>
                            @endcan
                            @can('submit', $article)
                                @if (in_array($article->status->value, ['assigned', 'revision_requested', 'brief'], true))
                                    <x-button size="sm" variant="secondary" wire:click="submitDraft({{ $article->id }})">Submit draft</x-button>
                                @endif
                            @endcan
                            @can('approve', $article)
                                @if ($article->status->value === 'draft_submitted')
                                    <x-button size="sm" wire:click="approve({{ $article->id }})">Approve</x-button>
                                    <x-button size="sm" variant="danger" wire:click="openRevision({{ $article->id }})">Revise</x-button>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div>{{ $articles->links() }}</div>
    @endif
</div>
