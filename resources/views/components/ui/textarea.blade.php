@props([
    'name',
    'label' => null,
    'help' => null,
    'rows' => 4,
])

@php
    $hasError = $errors->has($name);
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->class([
            'mt-1 block w-full rounded-xl border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-2',
            'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $hasError,
            'border-slate-300 focus:border-teal-600 focus:ring-teal-100' => ! $hasError,
        ]) }}
    >{{ old($name, $slot) }}</textarea>

    @error($name)
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
    @elseif ($help)
        <p class="mt-1 text-sm text-slate-500">{{ $help }}</p>
    @enderror
</div>
