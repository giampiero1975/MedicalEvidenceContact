@props([
    'label',
    'value',
    'detail' => null,
    'tone' => 'indigo',
    'href' => null,
])

@php
    $tones = [
        'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'teal' => 'bg-teal-50 text-teal-700 ring-teal-100',
        'gray' => 'bg-gray-50 text-gray-700 ring-gray-200',
    ];
    $tag = $href ? 'a' : 'article';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->class('block rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md') }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900">{{ $value }}</p>
            @if ($detail)
                <p class="mt-2 text-xs leading-5 text-gray-500">{{ $detail }}</p>
            @endif
        </div>

        @isset($icon)
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg ring-1 {{ $tones[$tone] ?? $tones['indigo'] }}">
                {{ $icon }}
            </span>
        @endisset
    </div>
</{{ $tag }}>
