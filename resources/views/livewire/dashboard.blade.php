<div>
    <div class="mb-10 max-w-2xl">
        <p class="font-mono text-[11px] tracking-[0.16em] text-muted uppercase">Overview</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-ink">Home</h1>
        <p class="mt-2 text-muted">
            Welcome, {{ auth()->user()->name }}. Projects, work, people, and money modules arrive in later milestones.
        </p>
    </div>

    <x-empty-state
        title="Your workspace is ready"
        description="This is the foundation shell. Next you’ll add projects, credential vault, tasks, attendance, and financials — without leaving this home open as the hub."
    />
</div>
