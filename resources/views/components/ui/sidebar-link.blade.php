@props([
    'href',
    'active' => false,
])

<a href="{{ $href }}"
   {{ $attributes->class([
       'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
       'bg-teal-50 text-teal-800' => $active,
       'text-slate-600 hover:bg-slate-100 hover:text-slate-950' => ! $active,
   ]) }}>
    @isset($icon)
        <span class="flex h-5 w-5 shrink-0 items-center justify-center" aria-hidden="true">
            {{ $icon }}
        </span>
    @endisset

    <span class="min-w-0 flex-1 truncate">{{ $slot }}</span>

    @isset($suffix)
        <span class="shrink-0">{{ $suffix }}</span>
    @endisset
</a>
