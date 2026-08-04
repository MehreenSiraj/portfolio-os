@props([
    'label' => null,
    'type' => 'text',
    'error' => null,
    'hint' => null,
])

@php
    $id = $attributes->get('id') ?? $attributes->get('wire:model') ?? $attributes->get('name');
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label @if($id) for="{{ $id }}" @endif class="block text-sm font-medium text-ink">
            {{ $label }}
        </label>
    @endif

    <input
        type="{{ $type }}"
        @if($id) id="{{ $id }}" @endif
        {{ $attributes->except('class')->merge([
            'class' => 'block w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm text-ink shadow-sm placeholder:text-muted/70 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20 disabled:bg-canvas disabled:text-muted '.($error ? 'border-danger focus:border-danger focus:ring-danger/20' : ''),
        ]) }}
    >

    @if ($hint && ! $error)
        <p class="text-xs text-muted">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="text-xs text-danger">{{ $error }}</p>
    @endif
</div>
