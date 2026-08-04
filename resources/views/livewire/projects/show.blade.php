<div>
    <div class="mb-8">
        <a href="{{ route('projects.index') }}" wire:navigate class="text-sm font-medium text-muted hover:text-ink">← Projects</a>
        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Project</p>
                <h1 class="mt-1 text-3xl font-semibold tracking-tight">{{ $project->domain }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <x-badge :tone="match($project->status->value) {
                        'monetized' => 'success',
                        'paused', 'sold' => 'warn',
                        default => 'accent',
                    }">{{ $project->status->label() }}</x-badge>
                    @if ($project->cms)
                        <span class="text-sm text-muted">{{ $project->cms }}</span>
                    @endif
                    @if ($project->niche)
                        <span class="text-sm text-muted">· {{ $project->niche }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Snapshot stubs for M3/M5 --}}
    <div class="mb-8 grid gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-line bg-surface px-4 py-3">
            <p class="text-xs text-muted">Month revenue</p>
            <p class="mt-1 font-semibold tabular-nums">{{ number_format($project->monthRevenuePaisa() / 100, 0) }} PKR</p>
        </div>
        <div class="rounded-xl border border-line bg-surface px-4 py-3">
            <p class="text-xs text-muted">Month cost</p>
            <p class="mt-1 font-semibold tabular-nums">{{ number_format($project->monthCostPaisa() / 100, 0) }} PKR</p>
        </div>
        <div class="rounded-xl border border-line bg-surface px-4 py-3">
            <p class="text-xs text-muted">Profit</p>
            <p class="mt-1 font-semibold tabular-nums">{{ number_format($project->monthProfitPaisa() / 100, 0) }} PKR</p>
        </div>
        <div class="rounded-xl border border-line bg-surface px-4 py-3">
            <p class="text-xs text-muted">Open tasks</p>
            <p class="mt-1 font-semibold tabular-nums">{{ $project->openTasksCount() }}</p>
        </div>
    </div>

    {{-- Details --}}
    <section class="mb-8 rounded-xl border border-line bg-surface p-5">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-base font-semibold">Details</h2>
            @can('update', $project)
                @if (! $editingDetails)
                    <x-button size="sm" variant="ghost" wire:click="startEditDetails">Edit</x-button>
                @endif
            @endcan
        </div>

        @if ($editingDetails)
            <form wire:submit="saveDetails" class="grid gap-4 sm:grid-cols-2">
                <x-input label="Domain" wire:model="domain" error="{{ $errors->first('domain') }}" />
                <x-input label="Niche" wire:model="niche" error="{{ $errors->first('niche') }}" />
                <x-input label="CMS" wire:model="cms" error="{{ $errors->first('cms') }}" />
                <x-input label="Start date" type="date" wire:model="start_date" error="{{ $errors->first('start_date') }}" />
                <x-input label="Acquisition cost (PKR)" type="number" step="0.01" min="0" wire:model="acquisition_cost" error="{{ $errors->first('acquisition_cost') }}" />
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium">Status</label>
                    <select wire:model="status" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-sm font-medium">Notes</label>
                    <textarea wire:model="notes" rows="4" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm"></textarea>
                </div>
                <div class="flex gap-2 sm:col-span-2">
                    <x-button type="submit">Save</x-button>
                    <x-button type="button" variant="secondary" wire:click="cancelEditDetails">Cancel</x-button>
                </div>
            </form>
        @else
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-muted">Start date</dt>
                    <dd class="mt-0.5 font-medium">{{ $project->start_date?->timezone(\App\Support\AppSettings::get('display_timezone', 'Asia/Karachi'))->format('Y-m-d') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Acquisition cost</dt>
                    <dd class="mt-0.5 font-medium font-mono text-xs">{{ number_format($project->acquisition_cost_paisa / 100, 2) }} PKR</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-muted">Notes</dt>
                    <dd class="mt-0.5 whitespace-pre-wrap">{{ $project->notes ?: '—' }}</dd>
                </div>
            </dl>
        @endif
    </section>

    {{-- Ownership --}}
    <section class="mb-8 rounded-xl border border-line bg-surface p-5">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-base font-semibold">Ownership</h2>
            @can('manageOwnership', $project)
                @if (! $editingOwnership)
                    <x-button size="sm" variant="ghost" wire:click="startEditOwnership">Edit shares</x-button>
                @endif
            @endcan
        </div>

        @if ($editingOwnership)
            <div class="space-y-2">
                @foreach ($owners as $index => $owner)
                    <div class="grid gap-2 sm:grid-cols-[1fr_8rem_auto]" wire:key="own-{{ $index }}">
                        <select wire:model="owners.{{ $index }}.user_id" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                            <option value="">Select user…</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.01" min="0" max="100" wire:model="owners.{{ $index }}.share_percent" class="rounded-lg border border-line bg-surface px-3 py-2 text-sm" placeholder="%">
                        <x-button type="button" size="sm" variant="ghost" wire:click="removeOwnerRow({{ $index }})">Remove</x-button>
                    </div>
                @endforeach
                @error('owners') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                <div class="flex flex-wrap gap-2 pt-2">
                    <x-button size="sm" variant="secondary" wire:click="addOwnerRow">Add owner</x-button>
                    <x-button size="sm" wire:click="saveOwnership">Save ownership</x-button>
                    <x-button size="sm" variant="ghost" wire:click="cancelOwnership">Cancel</x-button>
                </div>
            </div>
        @else
            <ul class="divide-y divide-line">
                @forelse ($project->owners as $owner)
                    <li class="flex items-center justify-between py-2 text-sm">
                        <span>{{ $owner->name }} <span class="text-muted font-mono text-xs">{{ $owner->email }}</span></span>
                        <span class="font-medium tabular-nums">{{ number_format($owner->pivot->share_bps / 100, 2) }}%</span>
                    </li>
                @empty
                    <li class="text-sm text-muted">No ownership recorded.</li>
                @endforelse
            </ul>
        @endif
    </section>

    {{-- Team --}}
    <section class="mb-8 rounded-xl border border-line bg-surface p-5">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-base font-semibold">Team</h2>
            @can('manageTeam', $project)
                @if (! $editingTeam)
                    <x-button size="sm" variant="ghost" wire:click="startEditTeam">Edit team</x-button>
                @endif
            @endcan
        </div>

        @if ($editingTeam)
            <div class="flex flex-wrap gap-3">
                @foreach ($users as $user)
                    <label class="flex items-center gap-2 rounded-lg border border-line px-3 py-2 text-sm">
                        <input type="checkbox" value="{{ $user->id }}" wire:model="teamMemberIds" class="rounded border-line text-accent">
                        {{ $user->name }}
                    </label>
                @endforeach
            </div>
            <div class="mt-4 flex gap-2">
                <x-button size="sm" wire:click="saveTeam">Save team</x-button>
                <x-button size="sm" variant="ghost" wire:click="cancelTeam">Cancel</x-button>
            </div>
        @else
            <div class="flex flex-wrap gap-2">
                @forelse ($project->teamMembers as $member)
                    <x-badge>{{ $member->name }}</x-badge>
                @empty
                    <p class="text-sm text-muted">No team members assigned.</p>
                @endforelse
            </div>
        @endif
    </section>

    {{-- Credentials vault --}}
    <section class="mb-8 rounded-xl border border-line bg-surface p-5">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold">Credentials vault</h2>
                <p class="mt-1 text-sm text-muted">Secrets encrypted at rest. Reveals are audit-logged.</p>
            </div>
            @can('create', [App\Models\Credential::class, $project])
                <x-button size="sm" wire:click="createCredential">Add credential</x-button>
            @endcan
        </div>

        @if ($showCredentialForm)
            <form wire:submit="saveCredential" class="mb-6 grid gap-4 rounded-lg border border-line bg-canvas/40 p-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium">Type</label>
                    <select wire:model="cred_type" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm">
                        @foreach ($credentialTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('cred_type') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <x-input label="Label" wire:model="cred_label" error="{{ $errors->first('cred_label') }}" />
                <x-input label="Username / login" wire:model="cred_username" error="{{ $errors->first('cred_username') }}" />
                <x-input
                    label="Secret / password"
                    type="password"
                    wire:model="cred_secret"
                    error="{{ $errors->first('cred_secret') }}"
                    hint="{{ $editingCredentialId ? 'Leave blank to keep existing secret.' : null }}"
                />
                <x-input label="URL" wire:model="cred_url" error="{{ $errors->first('cred_url') }}" />
                <x-input label="Expires on" type="date" wire:model="cred_expires_on" error="{{ $errors->first('cred_expires_on') }}" hint="Required for registrar, hosting, SSL renewals, etc." />
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-sm font-medium">Metadata (JSON)</label>
                    <textarea wire:model="cred_metadata_json" rows="4" class="block w-full rounded-lg border border-line bg-surface px-3 py-2 font-mono text-xs" placeholder='{"auto_renew": true, "transfer_lock": true}'></textarea>
                    @error('cred_metadata_json') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-2 sm:col-span-2">
                    <x-button type="submit">Save credential</x-button>
                    <x-button type="button" variant="secondary" wire:click="cancelCredentialForm">Cancel</x-button>
                </div>
            </form>
        @endif

        @if ($credentials->isEmpty())
            <p class="text-sm text-muted">No credentials stored for this project.</p>
        @else
            <div class="space-y-3">
                @foreach ($credentials as $credential)
                    @php
                        $isRevealed = isset($revealed[$credential->id]);
                    @endphp
                    <div class="rounded-lg border border-line px-4 py-3" wire:key="cred-{{ $credential->id }}">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="font-medium">{{ $credential->label }}</p>
                                <p class="font-mono text-xs text-muted">{{ $credential->type->label() }}</p>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                @can('reveal', $credential)
                                    @if ($isRevealed)
                                        <x-button size="sm" variant="ghost" wire:click="hideCredential({{ $credential->id }})">Hide</x-button>
                                    @else
                                        <x-button size="sm" variant="ghost" wire:click="revealCredential({{ $credential->id }})" wire:confirm="Reveal secret? This action is audited.">Reveal</x-button>
                                    @endif
                                @endcan
                                @can('update', $credential)
                                    <x-button size="sm" variant="ghost" wire:click="editCredential({{ $credential->id }})">Edit</x-button>
                                @endcan
                                @can('delete', $credential)
                                    <x-button size="sm" variant="ghost" wire:click="deleteCredential({{ $credential->id }})" wire:confirm="Soft-delete this credential?">Delete</x-button>
                                @endcan
                            </div>
                        </div>

                        <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="text-xs text-muted">Username</dt>
                                <dd class="font-mono text-xs">
                                    @if ($isRevealed)
                                        {{ $revealed[$credential->id]['username'] ?: '—' }}
                                    @else
                                        {{ $credential->maskedUsername() }}
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted">Secret</dt>
                                <dd class="font-mono text-xs">
                                    @if ($isRevealed)
                                        {{ $revealed[$credential->id]['secret'] ?: '—' }}
                                    @else
                                        {{ $credential->maskedSecret() }}
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted">URL</dt>
                                <dd class="truncate text-xs">
                                    @if ($credential->url)
                                        <a href="{{ $credential->url }}" target="_blank" rel="noopener" class="text-accent hover:underline">{{ $credential->url }}</a>
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted">Expires</dt>
                                <dd class="font-mono text-xs">
                                    {{ $credential->expires_on?->format('Y-m-d') ?? '—' }}
                                    @if ($credential->daysUntilExpiry() !== null)
                                        <span class="text-muted">({{ $credential->daysUntilExpiry() }}d)</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Files --}}
    <section class="rounded-xl border border-line bg-surface p-5">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-base font-semibold">Files</h2>
        </div>

        @can('update', $project)
            <form wire:submit="uploadFile" class="mb-4 flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[12rem]">
                    <input type="file" wire:model="upload" class="block w-full text-sm text-muted file:mr-3 file:rounded-lg file:border-0 file:bg-accent-soft file:px-3 file:py-2 file:text-sm file:font-medium file:text-accent">
                    @error('upload') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="upload" class="mt-1 text-xs text-muted">Uploading…</div>
                </div>
                <x-button type="submit" size="sm">Upload</x-button>
            </form>
        @endcan

        @if ($project->media->isEmpty())
            <p class="text-sm text-muted">No files attached.</p>
        @else
            <ul class="divide-y divide-line">
                @foreach ($project->media as $file)
                    <li class="flex items-center justify-between py-2 text-sm">
                        <div>
                            <p class="font-medium">{{ $file->original_name }}</p>
                            <p class="font-mono text-xs text-muted">{{ number_format($file->size / 1024, 1) }} KB</p>
                        </div>
                        @can('update', $project)
                            <x-button size="sm" variant="ghost" wire:click="deleteMedia({{ $file->id }})" wire:confirm="Delete this file?">Remove</x-button>
                        @endcan
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
