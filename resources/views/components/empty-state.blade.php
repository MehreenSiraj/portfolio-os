@props([
    'title' => 'Nothing here yet',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-start gap-3 rounded-xl border border-dashed border-line bg-surface/60 px-6 py-12']) }}>
    <div>
        <h3 class="text-base font-semibold tracking-tight text-ink">{{ $title }}</h3>
        @if ($description)
            <p class="mt-1 max-w-md text-sm text-muted">{{ $description }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="mt-2">
            {{ $slot }}
        </div>
    @endif
</div>
