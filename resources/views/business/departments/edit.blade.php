<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Modifica reparto" subtitle="Aggiorna sede, responsabile, contatti e stato operativo." />
    </x-slot>

    <div class="mx-auto max-w-4xl">
        <x-ui.card>
            <form method="POST" action="{{ route('business.departments.update', $department) }}" class="space-y-5">
                @csrf
                @method('PUT')
                @include('business.departments._form')

                <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                    <x-ui.button :href="route('business.departments.index')" variant="ghost">Annulla</x-ui.button>
                    <x-ui.button type="submit">Salva modifiche</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
