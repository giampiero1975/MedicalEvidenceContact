@props([
    'label',
    'value',
    'description' => null,
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->class([
    'block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm',
    'transition hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-md' => $href,
]) }}>
    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-teal-700">{{ $label }}</p>
    <p class="mt-3 text-3xl font-bold tracking-tight text-slate-950">{{ $value }}</p>

    @if ($description)
        <p class="mt-2 text-sm text-slate-500">{{ $description }}</p>
    @endif
</{{ $tag }}>
