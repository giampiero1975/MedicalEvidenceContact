@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $variants = [
        'info' => 'border-sky-200 bg-sky-50 text-sky-900',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-900',
    ];
@endphp

<div role="alert" {{ $attributes->class(['rounded-xl border px-4 py-3 text-sm', $variants[$variant] ?? $variants['info']]) }}>
    @if ($title)
        <p class="font-semibold">{{ $title }}</p>
    @endif
    <div @class(['mt-1' => $title])>
        {{ $slot }}
    </div>
</div>
