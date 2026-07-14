<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Sedi della struttura"
            subtitle="Gestisci sede legale, sedi operative e recapiti collegati alla struttura."
        />
    </x-slot>

    <div class="space-y-6">
        <x-ui.card>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Nuova sede</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Aggiungi una sede</h2>
                    <p class="mt-2 text-sm text-slate-600">Inserisci indirizzo e contatti. Una sola sede può essere indicata come principale.</p>
                </div>
                <x-ui.badge>{{ $locations->count() }} sedi</x-ui.badge>
            </div>

            <form method="POST" action="{{ route('business.locations.store') }}" class="mt-6">
                @csrf
                @include('business.locations._form')

                <div class="mt-5 flex justify-end border-t border-slate-100 pt-5">
                    <x-ui.button type="submit">Aggiungi sede</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Struttura</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Sedi registrate</h2>
                </div>
                <x-ui.button variant="ghost" size="sm" :href="route('business.profile.edit')">Profilo struttura</x-ui.button>
            </div>

            @if ($locations->isEmpty())
                <div class="mt-6">
                    <x-ui.empty-state title="Nessuna sede registrata" description="Aggiungi la sede principale per iniziare a collegare reparti e annunci." />
                </div>
            @else
                <div class="mt-6 divide-y divide-slate-100 border-y border-slate-100">
                    @foreach ($locations as $location)
                        <article class="grid gap-4 py-4 xl:grid-cols-[1.1fr_1.6fr_1fr_auto] xl:items-center">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold text-slate-950">{{ $location->name }}</h3>
                                    @if ($location->is_primary)
                                        <x-ui.badge variant="success">Principale</x-ui.badge>
                                    @endif
                                    <x-ui.badge :variant="$location->is_active ? 'neutral' : 'warning'">
                                        {{ $location->is_active ? 'Attiva' : 'Non attiva' }}
                                    </x-ui.badge>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">{{ $location->type === 'legal' ? 'Sede legale' : 'Sede operativa' }}</p>
                            </div>

                            <div class="min-w-0 text-sm text-slate-700">
                                <p class="font-medium text-slate-900">{{ $location->street_address }}</p>
                                <p class="mt-1">{{ collect([$location->postal_code, $location->city, $location->province])->filter()->join(' · ') }} · {{ $location->country }}</p>
                            </div>

                            <div class="min-w-0 text-sm text-slate-600">
                                <p class="truncate">{{ $location->email ?: 'Email non indicata' }}</p>
                                <p class="mt-1">{{ $location->phone ?: 'Telefono non indicato' }}</p>
                            </div>

                            <div class="flex flex-wrap justify-end gap-2">
                                <x-ui.button variant="secondary" size="sm" :href="route('business.locations.edit', $location)">Modifica</x-ui.button>
                                <form method="POST" action="{{ route('business.locations.destroy', $location) }}" onsubmit="return confirm('Eliminare questa sede?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" size="sm">Elimina</x-ui.button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>
