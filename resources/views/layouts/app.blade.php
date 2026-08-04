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

                @if(auth()->user()?->hasPermission('projects.view'))
                    <a href="{{ route('projects.index') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('projects.*') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Projects
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('tasks.view'))
                    <a href="{{ route('tasks.index') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('tasks.*') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Tasks
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('articles.view'))
                    <a href="{{ route('articles.index') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('articles.*') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Articles
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('links.view'))
                    <a href="{{ route('links.index') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('links.*') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Links
                    </a>
                @endif

                @if(auth()->user()?->hasAnyPermission('tasks.approve', 'articles.approve', 'links.approve'))
                    <a href="{{ route('approvals.queue') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('approvals.*') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Approvals
                    </a>
                @endif

                @if(auth()->user()?->hasAnyPermission('attendance.view', 'login_history.view', 'work_logs.view', 'scorecards.view'))
                    <p class="mt-4 px-3 font-mono text-[10px] tracking-[0.14em] text-muted uppercase">People</p>
                @endif

                @if(auth()->user()?->hasPermission('attendance.view'))
                    <a href="{{ route('people.attendance') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('people.attendance') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Attendance
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('work_logs.view'))
                    <a href="{{ route('people.work-logs') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('people.work-logs') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Work logs
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('scorecards.view'))
                    <a href="{{ route('people.scorecard') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('people.scorecard') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Scorecard
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('login_history.view'))
                    <a href="{{ route('people.login-history') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('people.login-history') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Login history
                    </a>
                @endif

                @if(auth()->user()?->hasAnyPermission('revenue.view', 'expenses.view', 'pnl.view', 'distributions.view', 'partners.view', 'partners.statement'))
                    <p class="mt-4 px-3 font-mono text-[10px] tracking-[0.14em] text-muted uppercase">Money</p>
                @endif

                @if(auth()->user()?->hasPermission('revenue.view'))
                    <a href="{{ route('money.revenues') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('money.revenues') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Revenue
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('expenses.view'))
                    <a href="{{ route('money.expenses') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('money.expenses') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Expenses
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('pnl.view'))
                    <a href="{{ route('money.pnl') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('money.pnl') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        P&amp;L
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('distributions.view'))
                    <a href="{{ route('money.distributions') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('money.distributions*') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Distributions
                    </a>
                @endif

                @if(auth()->user()?->hasAnyPermission('partners.view', 'partners.statement'))
                    <a href="{{ auth()->user()->hasPermission('partners.view') ? route('money.partners') : route('money.partners.statement') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('money.partners*') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Partners
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('users.view'))
                    <a href="{{ route('users.index') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('users.*') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Users
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('settings.view'))
                    <a href="{{ route('settings.index') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('settings.index') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Settings
                    </a>
                @endif

                @if(auth()->user()?->hasPermission('task_templates.manage'))
                    <a href="{{ route('settings.task-templates') }}" wire:navigate
                       class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('settings.task-templates') ? 'bg-accent-soft text-accent' : 'text-muted hover:bg-canvas hover:text-ink' }}">
                        Task templates
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
                <a href="{{ route('dashboard') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('dashboard') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Home</a>
                @if(auth()->user()?->hasPermission('projects.view'))
                    <a href="{{ route('projects.index') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('projects.*') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Projects</a>
                @endif
                @if(auth()->user()?->hasPermission('tasks.view'))
                    <a href="{{ route('tasks.index') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('tasks.*') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Tasks</a>
                @endif
                @if(auth()->user()?->hasPermission('articles.view'))
                    <a href="{{ route('articles.index') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('articles.*') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Articles</a>
                @endif
                @if(auth()->user()?->hasPermission('links.view'))
                    <a href="{{ route('links.index') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('links.*') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Links</a>
                @endif
                @if(auth()->user()?->hasAnyPermission('tasks.approve', 'articles.approve', 'links.approve'))
                    <a href="{{ route('approvals.queue') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('approvals.*') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Approvals</a>
                @endif
                @if(auth()->user()?->hasPermission('attendance.view'))
                    <a href="{{ route('people.attendance') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('people.attendance') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Attendance</a>
                @endif
                @if(auth()->user()?->hasPermission('work_logs.view'))
                    <a href="{{ route('people.work-logs') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('people.work-logs') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Logs</a>
                @endif
                @if(auth()->user()?->hasPermission('scorecards.view'))
                    <a href="{{ route('people.scorecard') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('people.scorecard') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Scorecard</a>
                @endif
                @if(auth()->user()?->hasPermission('login_history.view'))
                    <a href="{{ route('people.login-history') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('people.login-history') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Logins</a>
                @endif
                @if(auth()->user()?->hasPermission('revenue.view'))
                    <a href="{{ route('money.revenues') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('money.revenues') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Revenue</a>
                @endif
                @if(auth()->user()?->hasPermission('expenses.view'))
                    <a href="{{ route('money.expenses') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('money.expenses') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Expenses</a>
                @endif
                @if(auth()->user()?->hasPermission('pnl.view'))
                    <a href="{{ route('money.pnl') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('money.pnl') ? 'bg-accent-soft text-accent' : 'text-muted' }}">P&amp;L</a>
                @endif
                @if(auth()->user()?->hasPermission('distributions.view'))
                    <a href="{{ route('money.distributions') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('money.distributions*') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Distributions</a>
                @endif
                @if(auth()->user()?->hasAnyPermission('partners.view', 'partners.statement'))
                    <a href="{{ auth()->user()->hasPermission('partners.view') ? route('money.partners') : route('money.partners.statement') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('money.partners*') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Partners</a>
                @endif
                @if(auth()->user()?->hasPermission('users.view'))
                    <a href="{{ route('users.index') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('users.*') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Users</a>
                @endif
                @if(auth()->user()?->hasPermission('settings.view'))
                    <a href="{{ route('settings.index') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('settings.index') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Settings</a>
                @endif
                @if(auth()->user()?->hasPermission('task_templates.manage'))
                    <a href="{{ route('settings.task-templates') }}" wire:navigate class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('settings.task-templates') ? 'bg-accent-soft text-accent' : 'text-muted' }}">Templates</a>
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
