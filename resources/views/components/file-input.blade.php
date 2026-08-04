@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'filename' => null,
])

@php
    $id = $attributes->get('id')
        ?? str_replace(['.', '[', ']'], ['-', '-', ''], (string) ($attributes->get('wire:model') ?? 'file-input'));
    $wireModel = $attributes->get('wire:model') ?? $attributes->get('wire:model.live') ?? null;
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <span class="block text-sm font-medium text-ink">{{ $label }}</span>
    @endif

    <label
        for="{{ $id }}"
        class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-line bg-canvas/50 px-4 py-6 text-center transition hover:border-accent/50 hover:bg-accent-soft/30 has-[:focus-visible]:border-accent has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-accent/20"
    >
        <input
            type="file"
            id="{{ $id }}"
            {{ $attributes->except('class')->merge([
                'class' => 'sr-only',
            ]) }}
        >

        <span class="inline-flex items-center justify-center rounded-lg border border-line bg-surface px-3 py-1.5 text-xs font-medium text-ink shadow-sm transition group-hover:border-accent/30 group-hover:text-accent">
            Choose file
        </span>

        @if ($wireModel)
            <span wire:loading wire:target="{{ $wireModel }}" class="text-sm text-muted">
                Preparing file…
            </span>
            <span wire:loading.remove wire:target="{{ $wireModel }}" class="max-w-full truncate px-2 text-sm {{ $filename ? 'font-medium text-ink' : 'text-muted' }}">
                {{ $filename ?: 'No file chosen' }}
            </span>
        @else
            <span class="max-w-full truncate px-2 text-sm {{ $filename ? 'font-medium text-ink' : 'text-muted' }}">
                {{ $filename ?: 'No file chosen' }}
            </span>
        @endif

        @if ($hint)
            <span class="text-xs text-muted">{{ $hint }}</span>
        @endif
    </label>

    @if ($error)
        <p class="text-xs text-danger">{{ $error }}</p>
    @endif
</div>
