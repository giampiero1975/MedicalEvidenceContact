@props([
    'label',
    'value',
    'detail' => null,
    'icon' => 'chart',
    'tone' => 'teal',
    'href' => null,
])

@php
    $tones = [
        'teal' => ['icon' => 'bg-teal-50 text-teal-700 ring-teal-100', 'accent' => 'text-teal-700'],
        'blue' => ['icon' => 'bg-blue-50 text-blue-700 ring-blue-100', 'accent' => 'text-blue-700'],
        'amber' => ['icon' => 'bg-amber-50 text-amber-700 ring-amber-100', 'accent' => 'text-amber-700'],
        'green' => ['icon' => 'bg-emerald-50 text-emerald-700 ring-emerald-100', 'accent' => 'text-emerald-700'],
        'slate' => ['icon' => 'bg-slate-100 text-slate-700 ring-slate-200', 'accent' => 'text-slate-700'],
    ];
    $palette = $tones[$tone] ?? $tones['teal'];
    $classes = $attributes->class([
        'group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200',
        'hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md' => $href,
    ]);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $classes }}>
@else
    <div {{ $classes }}>
@endif
    <div class="flex items-start justify-between gap-4">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl ring-1 {{ $palette['icon'] }}">
            @switch($icon)
                @case('briefcase')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6V4.75A1.75 1.75 0 0110.75 3h2.5A1.75 1.75 0 0115 4.75V6m-11 5.25h16M5.75 6h12.5A1.75 1.75 0 0120 7.75v10.5A1.75 1.75 0 0118.25 20H5.75A1.75 1.75 0 014 18.25V7.75A1.75 1.75 0 015.75 6z" /></svg>
                    @break
                @case('users')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 19.25v-1.5A3.75 3.75 0 0011.75 14h-4.5a3.75 3.75 0 00-3.75 3.75v1.5M9.5 10.5a3.25 3.25 0 100-6.5 3.25 3.25 0 000 6.5zm7.25.25a2.75 2.75 0 100-5.5m1.5 8.75a3.5 3.5 0 012.25 3.27v1.23" /></svg>
                    @break
                @case('calendar')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3v3m10-3v3M4.75 8.5h14.5M6.5 5h11A1.75 1.75 0 0119.25 6.75v11.5A1.75 1.75 0 0117.5 20h-11a1.75 1.75 0 01-1.75-1.75V6.75A1.75 1.75 0 016.5 5z" /></svg>
                    @break
                @case('check')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 12.25l2.5 2.5 5-5m4.5 2.25a8.25 8.25 0 11-16.5 0 8.25 8.25 0 0116.5 0z" /></svg>
                    @break
                @default
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 19V9m7 10V5m7 14v-7" /></svg>
            @endswitch
        </div>
        @if($href)
            <svg class="h-4 w-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
        @endif
    </div>

    <p class="mt-5 text-sm font-medium text-slate-500">{{ $label }}</p>
    <p class="mt-1 text-3xl font-semibold tracking-tight text-slate-950">{{ $value }}</p>
    @if($detail)
        <p class="mt-2 text-xs font-medium {{ $palette['accent'] }}">{{ $detail }}</p>
    @endif
@if($href)
    </a>
@else
    </div>
@endif
