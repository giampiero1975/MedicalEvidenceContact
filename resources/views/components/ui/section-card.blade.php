@props([
    'title' => null,
    'description' => null,
    'padding' => 'p-6',
])

<section {{ $attributes->class(["rounded-xl bg-white shadow-sm ring-1 ring-gray-200", $padding]) }}>
    @if ($title || $description || isset($actions))
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                @if ($title)
                    <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="mt-1 text-sm leading-6 text-gray-600">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="shrink-0">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div @class(['mt-5' => $title || $description || isset($actions)])>
        {{ $slot }}
    </div>
</section>
