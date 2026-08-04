@props([
    'headers' => [],
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-line bg-surface']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            @if (count($headers))
                <thead class="border-b border-line bg-canvas/70">
                    <tr>
                        @foreach ($headers as $header)
                            <th class="px-4 py-3 font-medium text-muted">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-line">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
