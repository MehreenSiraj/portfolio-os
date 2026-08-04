@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

    $variants = [
        'primary' => 'bg-accent text-white hover:bg-accent/90 focus-visible:outline-accent',
        'secondary' => 'border border-line bg-surface text-ink hover:bg-canvas focus-visible:outline-ink/30',
        'ghost' => 'text-muted hover:bg-canvas hover:text-ink focus-visible:outline-ink/20',
        'danger' => 'bg-danger text-white hover:bg-danger/90 focus-visible:outline-danger',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-sm',
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md'])]) }}>
    {{ $slot }}
</button>
