<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'PinSA') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen text-[15px] leading-relaxed text-ink">
    <div class="mx-auto flex min-h-screen max-w-[1400px]">
        <aside class="hidden w-60 shrink-0 border-r border-line/80 bg-surface/70 px-4 py-6 backdrop-blur md:flex md:flex-col">
            <div class="mb-10 px-2">
                <p class="font-mono text-[11px] font-medium tracking-[0.18em] text-muted uppercase">Portfolio</p>
                <p class="mt-1 text-lg font-semibold tracking-tight text-ink">{{ \App\Support\AppSettings::get('org_name', config('app.name')) }}</p>
            </div>

            <nav class="flex flex-1 flex-col gap-1">
                <a href="{{ route('dashboard') }}" wire:navigate
                   class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                    Home
                </a>

                @if(auth()->user()?->hasPermission('users.view'))
                    <a href="{{ route('users.index') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('users.*') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Users
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('settings.view'))
                    <a href="{{ route('settings.index') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('settings.*') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Settings
                    </a>
                @endif
            </nav>

            <div class="mt-auto border-t border-line pt-4 px-2">
                <p class="truncate text-sm font-medium text-ink">{{ auth()->user()->name }}</p>
                <p class="truncate font-mono text-xs text-muted">{{ auth()->user()->email }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-muted transition hover:text-ink">
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between border-b border-line/80 bg-surface/60 px-5 py-4 backdrop-blur md:hidden">
                <div>
                    <p class="font-mono text-[10px] tracking-[0.16em] text-muted uppercase">Portfolio</p>
                    <p class="text-sm font-semibold">{{ \App\Support\AppSettings::get('org_name', config('app.name')) }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-muted">Log out</button>
                </form>
            </header>

            <nav class="flex gap-1 overflow-x-auto border-b border-line bg-surface/50 px-3 py-2 md:hidden">
                <a href="{{ route('dashboard') }}" wire:navigate class="rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('dashboard') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Home</a>
                @if(auth()->user()?->hasPermission('users.view'))
                    <a href="{{ route('users.index') }}" wire:navigate class="rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('users.*') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Users</a>
                @endif
                @if(auth()->user()?->hasPermission('settings.view'))
                    <a href="{{ route('settings.index') }}" wire:navigate class="rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('settings.*') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Settings</a>
                @endif
            </nav>

            <main class="flex-1 px-5 py-8 sm:px-8 lg:px-10">
                @if (session('status'))
                    <div class="mb-6 rounded-lg border border-success/20 bg-success-soft px-4 py-3 text-sm text-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-lg border border-danger/20 bg-danger-soft px-4 py-3 text-sm text-danger">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
