@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->class(['flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">
            {{ $title }}
        </h1>

        @if ($subtitle)
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-3">
            {{ $actions }}
        </div>
    @endisset
</div>
