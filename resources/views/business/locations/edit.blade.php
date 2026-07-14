<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Modifica sede"
            subtitle="Aggiorna indirizzo, recapiti e stato operativo della sede."
        />
    </x-slot>

    <x-ui.card>
        <form method="POST" action="{{ route('business.locations.update', $location) }}">
            @csrf
            @method('PUT')

            @include('business.locations._form', ['location' => $location])

            <div class="mt-5 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
                <x-ui.button variant="secondary" :href="route('business.locations.index')">Annulla</x-ui.button>
                <x-ui.button type="submit">Salva modifiche</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-app-layout>
