@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class('rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center') }}>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
        {{ $icon ?? '—' }}
    </div>

    <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $title }}</h3>

    @if ($description)
        <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">{{ $description }}</p>
    @endif

    @isset($actions)
        <div class="mt-5 flex justify-center gap-3">
            {{ $actions }}
        </div>
    @endisset
</div>
