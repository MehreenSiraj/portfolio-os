<div>
    <div class="mb-8">
        <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Account recovery</p>
        <h2 class="mt-1 text-xl font-semibold tracking-tight">Forgot password</h2>
        <p class="mt-1 text-sm text-muted">We’ll email a reset link if the account exists.</p>
    </div>

    @if ($status)
        <div class="mb-4 rounded-lg border border-success/20 bg-success-soft px-3 py-2 text-sm text-success">
            {{ $status }}
        </div>
    @endif

    <form wire:submit="sendResetLink" class="space-y-4">
        <x-input
            label="Email"
            type="email"
            wire:model="email"
            autocomplete="username"
            error="{{ $errors->first('email') }}"
        />

        <x-button type="submit" class="w-full">Send reset link</x-button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        <a href="{{ route('login') }}" wire:navigate class="font-medium text-accent hover:underline">Back to sign in</a>
    </p>
</div>
