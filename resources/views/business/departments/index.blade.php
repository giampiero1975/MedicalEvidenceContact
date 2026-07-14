<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Reparti e unità operative" subtitle="Organizza i reparti collegandoli alle sedi della struttura." />
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.card>
            <h3 class="text-base font-semibold text-slate-950">Nuovo reparto</h3>
            <p class="mt-1 text-sm text-slate-500">Crea un reparto o un'unità operativa e associa la sede di riferimento.</p>

            @if ($locations->isEmpty())
                <x-ui.alert variant="warning" class="mt-5">
                    Prima di creare un reparto devi registrare almeno una sede attiva.
                </x-ui.alert>
                <div class="mt-4">
                    <x-ui.button :href="route('business.locations.index')" variant="secondary">Gestisci sedi</x-ui.button>
                </div>
            @else
                <form method="POST" action="{{ route('business.departments.store') }}" class="mt-5 space-y-5">
                    @csrf
                    @include('business.departments._form', ['department' => null])
                    <div class="flex justify-end border-t border-slate-100 pt-4">
                        <x-ui.button type="submit">Aggiungi reparto</x-ui.button>
                    </div>
                </form>
            @endif
        </x-ui.card>

        @if ($departments->isEmpty())
            <x-ui.empty-state title="Nessun reparto registrato" description="I reparti creati compariranno qui e potranno essere collegati agli annunci." />
        @else
            <div class="space-y-3">
                @foreach ($departments as $department)
                    <x-ui.card class="px-5 py-4">
                        <div class="grid gap-4 lg:grid-cols-[minmax(220px,1.3fr)_minmax(180px,1fr)_minmax(180px,1fr)_auto] lg:items-center">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="truncate font-semibold text-slate-950">{{ $department->name }}</h3>
                                    <x-ui.badge :variant="$department->is_active ? 'success' : 'warning'">
                                        {{ $department->is_active ? 'Attivo' : 'Non attivo' }}
                                    </x-ui.badge>
                                </div>
                                @if ($department->code)
                                    <p class="mt-1 text-xs text-slate-500">Codice {{ $department->code }}</p>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sede</p>
                                <p class="mt-1 truncate text-sm font-medium text-slate-900">{{ $department->location->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $department->location->city }}</p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Responsabile e contatti</p>
                                <p class="mt-1 truncate text-sm font-medium text-slate-900">{{ $department->manager_name ?: 'Non indicato' }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $department->email ?: ($department->phone ?: 'Nessun contatto') }}</p>
                            </div>

                            <div class="flex flex-wrap justify-end gap-2">
                                <x-ui.button :href="route('business.departments.edit', $department)" variant="secondary" size="sm">Modifica</x-ui.button>
                                <form method="POST" action="{{ route('business.departments.destroy', $department) }}" onsubmit="return confirm('Eliminare questo reparto?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" size="sm">Elimina</x-ui.button>
                                </form>
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
