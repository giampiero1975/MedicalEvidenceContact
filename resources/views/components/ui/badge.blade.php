@props([
    'variant' => 'neutral',
])

@php
    $variants = [
        'neutral' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'primary' => 'bg-teal-50 text-teal-700 ring-teal-200',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'danger' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'info' => 'bg-sky-50 text-sky-700 ring-sky-200',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset',
    $variants[$variant] ?? $variants['neutral'],
]) }}>
    {{ $slot }}
</span>
