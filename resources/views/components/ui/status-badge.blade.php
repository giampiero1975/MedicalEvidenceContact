@props([
    'tone' => 'gray',
])

@php
    $tones = [
        'gray' => 'bg-gray-100 text-gray-700 ring-gray-200',
        'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'red' => 'bg-red-50 text-red-700 ring-red-200',
        'teal' => 'bg-teal-50 text-teal-700 ring-teal-200',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset', $tones[$tone] ?? $tones['gray']]) }}>
    {{ $slot }}
</span>
