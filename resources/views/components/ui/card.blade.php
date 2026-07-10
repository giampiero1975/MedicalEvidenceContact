@props([
    'padding' => 'p-6',
])

<section {{ $attributes->class(['rounded-2xl border border-slate-200 bg-white shadow-sm', $padding]) }}>
    {{ $slot }}
</section>
