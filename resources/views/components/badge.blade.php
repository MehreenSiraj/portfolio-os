@props([
    'tone' => 'neutral',
])

@php
    $tones = [
        'neutral' => 'bg-canvas text-muted ring-line',
        'accent' => 'bg-accent-soft text-accent ring-accent/20',
        'success' => 'bg-success-soft text-success ring-success/20',
        'danger' => 'bg-danger-soft text-danger ring-danger/20',
        'warn' => 'bg-warn-soft text-warn ring-warn/20',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset '.($tones[$tone] ?? $tones['neutral'])]) }}>
    {{ $slot }}
</span>
