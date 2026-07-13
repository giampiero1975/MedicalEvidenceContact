@props([
    'name',
    'label',
    'value' => 1,
    'help' => null,
    'checked' => false,
])

<label class="flex items-start gap-3">
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="checkbox"
        value="{{ $value }}"
        @checked(old($name, $checked))
        {{ $attributes->class('mt-0.5 rounded border-slate-300 text-teal-700 shadow-sm focus:ring-teal-200') }}
    >

    <span>
        <span class="block text-sm font-medium text-slate-800">{{ $label }}</span>
        @if ($help)
            <span class="mt-0.5 block text-sm text-slate-500">{{ $help }}</span>
        @endif
    </span>
</label>
