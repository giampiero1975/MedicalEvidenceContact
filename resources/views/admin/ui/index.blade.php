@extends('layouts.admin')

@section('content')
    <x-ui.page-header
        title="UI Playground"
        subtitle="Riferimento visivo interno per componenti, colori, tipografia e pattern dell'interfaccia."
    />

    <div class="mt-8 space-y-8">
        <x-ui.card>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Colori fondamentali</h2>
                    <p class="mt-1 text-sm text-slate-600">Palette iniziale del Medical UI Kit.</p>
                </div>
                <x-ui.badge variant="success">UI Framework v1</x-ui.badge>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['name' => 'Primary', 'class' => 'bg-teal-700', 'token' => 'teal-700'],
                    ['name' => 'Neutral', 'class' => 'bg-slate-700', 'token' => 'slate-700'],
                    ['name' => 'Success', 'class' => 'bg-emerald-600', 'token' => 'emerald-600'],
                    ['name' => 'Danger', 'class' => 'bg-rose-600', 'token' => 'rose-600'],
                ] as $color)
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                        <div class="h-20 {{ $color['class'] }}"></div>
                        <div class="p-4">
                            <p class="font-semibold text-slate-900">{{ $color['name'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $color['token'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-lg font-semibold text-slate-950">Tipografia</h2>
            <div class="mt-6 space-y-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Eyebrow</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Titolo pagina principale</h1>
                </div>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-950">Titolo sezione</h2>
                <h3 class="text-lg font-semibold text-slate-950">Titolo card</h3>
                <p class="max-w-3xl text-sm leading-6 text-slate-600">Testo descrittivo standard. Deve rimanere leggibile, sobrio e con un contrasto adeguato anche nelle interfacce dense.</p>
            </div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-lg font-semibold text-slate-950">Azioni</h2>
            <div class="mt-6 flex flex-wrap gap-3">
                <x-ui.button>Primario</x-ui.button>
                <x-ui.button variant="secondary">Secondario</x-ui.button>
                <x-ui.button variant="ghost">Ghost</x-ui.button>
                <x-ui.button variant="danger">Elimina</x-ui.button>
                <x-ui.button size="sm">Piccolo</x-ui.button>
                <x-ui.button size="lg">Grande</x-ui.button>
            </div>
        </x-ui.card>

        <div class="grid gap-8 xl:grid-cols-2">
            <x-ui.card>
                <h2 class="text-lg font-semibold text-slate-950">Form</h2>
                <div class="mt-6 space-y-5">
                    <x-ui.input name="playground_name" label="Nome completo" placeholder="Mario Rossi" />
                    <x-ui.input name="playground_email" type="email" label="Email" placeholder="mario@example.com" help="Il testo di supporto usa slate-500." />
                    <x-ui.select name="playground_role" label="Profilo">
                        <option>Professionista</option>
                        <option>Business</option>
                        <option>Admin</option>
                    </x-ui.select>
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-lg font-semibold text-slate-950">Stati e feedback</h2>
                <div class="mt-6 space-y-4">
                    <x-ui.alert variant="success">Operazione completata correttamente.</x-ui.alert>
                    <x-ui.alert variant="warning">Controlla i dati prima di continuare.</x-ui.alert>
                    <x-ui.alert variant="danger">Si è verificato un errore.</x-ui.alert>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.badge variant="success">Attivo</x-ui.badge>
                        <x-ui.badge variant="warning">In verifica</x-ui.badge>
                        <x-ui.badge variant="danger">Scaduto</x-ui.badge>
                        <x-ui.badge>Neutro</x-ui.badge>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <x-ui.stat-card label="Professionisti" value="1.248" hint="Totale registrati" />
            <x-ui.stat-card label="Business" value="84" hint="Strutture attive" />
            <x-ui.stat-card label="Annunci" value="126" hint="Attualmente pubblicati" />
        </div>

        <x-ui.empty-state
            title="Nessun risultato"
            description="Questo è il pattern standard per liste e ricerche senza contenuti."
        />
    </div>
@endsection
