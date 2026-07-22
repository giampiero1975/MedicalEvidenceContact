@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class('rounded-xl border border-dashed border-gray-300 bg-gray-50/50 px-6 py-10 text-center') }}>
    @isset($icon)
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm ring-1 ring-gray-200">
            {{ $icon }}
        </div>
    @endisset

    <h4 class="mt-4 text-base font-semibold text-gray-900">{{ $title }}</h4>
    @if ($description)
        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-gray-600">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
