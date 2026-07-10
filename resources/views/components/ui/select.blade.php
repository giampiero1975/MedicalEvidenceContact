@props([
    'label' => null,
    'name',
    'help' => null,
])

@php
    $selectId = $attributes->get('id', $name);
@endphp

<div>
    @if ($label)
        <label for="{{ $selectId }}" class="block text-sm font-semibold text-slate-700">
            {{ $label }}
        </label>
    @endif

    <select
        id="{{ $selectId }}"
        name="{{ $name }}"
        {{ $attributes->except('id')->class([
            'mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-600 focus:ring-teal-600',
            'border-rose-300 focus:border-rose-500 focus:ring-rose-500' => $errors->has($name),
        ]) }}
    >
        {{ $slot }}
    </select>

    @if ($help && ! $errors->has($name))
        <p class="mt-1 text-xs text-slate-500">{{ $help }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
    @enderror
</div>
