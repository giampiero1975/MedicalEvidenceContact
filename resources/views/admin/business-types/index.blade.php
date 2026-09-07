@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            title="Tipologie aziendali"
            subtitle="Gestisci le tipologie disponibili per registrazione e profili Business."
        >
            <x-slot name="actions">
                <a href="{{ route('admin.business-types.create') }}" class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                    Crea tipologia
                </a>
            </x-slot>
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        @error('business_type')
            <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
        @enderror

        <x-ui.card :padding="false">
            @if ($businessTypes->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-slate-700">Nome</th>
                                <th class="px-6 py-3 font-semibold text-slate-700">Ordine</th>
                                <th class="px-6 py-3 font-semibold text-slate-700">Stato</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-700">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($businessTypes as $businessType)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-950">{{ $businessType->name }}</div>
                                        <div class="mt-0.5 text-xs text-slate-500">ID {{ $businessType->id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $businessType->sort_order }}</td>
                                    <td class="px-6 py-4">
                                        <x-ui.badge :variant="$businessType->is_active ? 'success' : 'neutral'">
                                            {{ $businessType->is_active ? 'Attiva' : 'Disattivata' }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <a href="{{ route('admin.business-types.edit', $businessType) }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-teal-700 transition hover:bg-teal-50 hover:text-teal-900">
                                                Modifica
                                            </a>
                                            <form method="POST" action="{{ route('admin.business-types.toggle', $businessType) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold {{ $businessType->is_active ? 'text-amber-700 hover:bg-amber-50 hover:text-amber-900' : 'text-emerald-700 hover:bg-emerald-50 hover:text-emerald-900' }} transition">
                                                    {{ $businessType->is_active ? 'Disattiva' : 'Attiva' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.business-types.destroy', $businessType) }}" onsubmit="return confirm('Eliminare definitivamente questa tipologia? Se è già utilizzata da un profilo Business l’operazione verrà bloccata.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 hover:text-rose-800">
                                                    Elimina
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6">
                    <x-ui.empty-state
                        title="Nessuna tipologia aziendale"
                        description="Crea la prima tipologia disponibile per i profili Business."
                    >
                        <x-slot name="actions">
                            <a href="{{ route('admin.business-types.create') }}" class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">
                                Crea tipologia
                            </a>
                        </x-slot>
                    </x-ui.empty-state>
                </div>
            @endif
        </x-ui.card>

        @if ($businessTypes->hasPages())
            <div>{{ $businessTypes->links() }}</div>
        @endif
    </div>
@endsection
