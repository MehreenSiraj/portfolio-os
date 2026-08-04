<div>
    <div class="mb-8">
        <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Organization</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight">Settings</h1>
        <p class="mt-1 text-sm text-muted">Defaults used across money, display time, and security.</p>
    </div>

    <form wire:submit="save" class="max-w-xl space-y-6">
        <div class="rounded-xl border border-line bg-surface p-6 space-y-4">
            <h2 class="text-sm font-semibold tracking-tight">General</h2>

            <x-input label="Organization name" wire:model="org_name" error="{{ $errors->first('org_name') }}" />
            <x-input
                label="Base currency"
                wire:model="base_currency"
                error="{{ $errors->first('base_currency') }}"
                hint="Reporting currency is PKR (paisa as integer minor units from Milestone 5)."
            />
            <x-input
                label="Display timezone"
                wire:model="display_timezone"
                error="{{ $errors->first('display_timezone') }}"
                hint="Stored timestamps remain UTC."
            />
        </div>

        <div class="rounded-xl border border-line bg-surface p-6 space-y-4">
            <h2 class="text-sm font-semibold tracking-tight">Security scaffold</h2>
            <label class="flex items-start gap-3 text-sm">
                <input type="checkbox" wire:model="two_factor_required" class="mt-0.5 rounded border-line text-accent focus:ring-accent/30">
                <span>
                    <span class="font-medium text-ink">Require two-factor authentication</span>
                    <span class="mt-0.5 block text-muted">Off by default. Column scaffold exists on users; full challenge flow is optional later.</span>
                </span>
            </label>
        </div>

        <div class="rounded-xl border border-line bg-surface p-6 space-y-4">
            <h2 class="text-sm font-semibold tracking-tight">FX defaults (placeholder)</h2>
            <x-input
                label="USD → PKR rate (placeholder)"
                wire:model="fx_usd_to_pkr"
                error="{{ $errors->first('fx_usd_to_pkr') }}"
                hint="Stored as string/JSON until money module uses integer rates."
            />
            <div class="space-y-1.5">
                <label class="block text-sm font-medium">Note</label>
                <textarea
                    wire:model="fx_note"
                    rows="3"
                    class="block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                ></textarea>
                @error('fx_note') <p class="text-xs text-danger">{{ $message }}</p> @enderror
            </div>
        </div>

        @if(auth()->user()->hasPermission('settings.update'))
            <x-button type="submit">Save settings</x-button>
        @endif
    </form>
</div>
