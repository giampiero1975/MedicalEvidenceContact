@props([
    'name',
    'src' => null,
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-14 w-14 text-base',
    ];

    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

@if ($src)
    <img
        src="{{ $src }}"
        alt="{{ $name }}"
        {{ $attributes->class(['rounded-full object-cover ring-1 ring-slate-200', $sizes[$size] ?? $sizes['md']]) }}
    >
@else
    <span
        role="img"
        aria-label="{{ $name }}"
        {{ $attributes->class(['inline-flex items-center justify-center rounded-full bg-teal-100 font-semibold text-teal-800 ring-1 ring-teal-200', $sizes[$size] ?? $sizes['md']]) }}
    >
        {{ $initials ?: '?' }}
    </span>
@endif
