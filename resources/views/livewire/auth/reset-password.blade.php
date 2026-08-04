<div>
    <div class="mb-8">
        <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Account recovery</p>
        <h2 class="mt-1 text-xl font-semibold tracking-tight">Set a new password</h2>
    </div>

    <form wire:submit="resetPassword" class="space-y-4">
        <x-input
            label="Email"
            type="email"
            wire:model="email"
            autocomplete="username"
            error="{{ $errors->first('email') }}"
        />

        <x-input
            label="New password"
            type="password"
            wire:model="password"
            autocomplete="new-password"
            error="{{ $errors->first('password') }}"
        />

        <x-input
            label="Confirm password"
            type="password"
            wire:model="password_confirmation"
            autocomplete="new-password"
        />

        <x-button type="submit" class="w-full">Reset password</x-button>
    </form>
</div>
