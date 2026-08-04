<div>
    <div class="mb-8">
        <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Sign in</p>
        <h2 class="mt-1 text-xl font-semibold tracking-tight">Welcome back</h2>
        <p class="mt-1 text-sm text-muted">Use your work email and password.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-success/20 bg-success-soft px-3 py-2 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="space-y-4">
        <x-input
            label="Email"
            type="email"
            wire:model="email"
            autocomplete="username"
            error="{{ $errors->first('email') }}"
        />

        <x-input
            label="Password"
            type="password"
            wire:model="password"
            autocomplete="current-password"
            error="{{ $errors->first('password') }}"
        />

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-muted">
                <input type="checkbox" wire:model="remember" class="rounded border-line text-accent focus:ring-accent/30">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" wire:navigate class="text-sm font-medium text-accent hover:underline">
                Forgot password?
            </a>
        </div>

        <x-button type="submit" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login">Signing in…</span>
        </x-button>
    </form>
</div>
