<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Moodle e attestati"
            subtitle="Collega i tuoi account Moodle e prepara la sincronizzazione di corsi, certificazioni e attestati."
        />
    </x-slot>

    <div class="space-y-8">
        @if ($errors->any())
            <x-ui.alert variant="danger" title="Controlla i dati inseriti">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            <x-ui.stat-card
                label="Siti disponibili"
                :value="$moodleSites->count()"
                hint="Piattaforme Moodle attive"
            />
            <x-ui.stat-card
                label="Account collegati"
                :value="$moodleUserLinks->where('status', 'active')->count()"
                hint="Collegamenti verificati"
            />
            <x-ui.stat-card
                label="Sincronizzazione"
                :value="$moodleUserLinks->where('status', 'active')->isNotEmpty() ? 'Pronta' : 'Da configurare'"
                hint="Stato integrazione formazione"
            />
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <x-ui.card>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Nuovo collegamento</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-950">Collega un account Moodle</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Cerca il tuo account tramite email o username. Se i dati corrispondono, riceverai un codice all'indirizzo email registrato su Moodle.
                </p>

                @if ($moodleSites->isEmpty())
                    <div class="mt-6">
                        <x-ui.empty-state
                            title="Nessun sito Moodle disponibile"
                            description="Al momento non ci sono piattaforme abilitate al collegamento."
                        />
                    </div>
                @else
                    <form method="POST" action="{{ route('professional.moodle.start') }}" class="mt-6 space-y-5">
                        @csrf

                        <x-ui.select name="moodle_site_id" label="Sito Moodle" required>
                            @foreach ($moodleSites as $moodleSite)
                                <option value="{{ $moodleSite->id }}" @selected(old('moodle_site_id') == $moodleSite->id)>
                                    {{ $moodleSite->name }}
                                </option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select name="lookup_type" label="Come vuoi cercare l'account?" required>
                            <option value="email" @selected(old('lookup_type', 'email') === 'email')>Email Moodle</option>
                            <option value="username" @selected(old('lookup_type') === 'username')>Username Moodle</option>
                        </x-ui.select>

                        <x-ui.input
                            name="lookup_value"
                            label="Email o username Moodle"
                            :value="old('lookup_value')"
                            placeholder="Inserisci il dato usato su Moodle"
                            help="Il dato viene usato solo per individuare l'account e avviare la verifica."
                            required
                        />

                        <div class="flex justify-end border-t border-slate-100 pt-5">
                            <x-ui.button type="submit">Collega account</x-ui.button>
                        </div>
                    </form>
                @endif
            </x-ui.card>

            <x-ui.card>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Formazione collegata</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">I tuoi account Moodle</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            I collegamenti attivi saranno usati per sincronizzare corsi completati e attestati disponibili.
                        </p>
                    </div>
                    <x-ui.badge>{{ $moodleUserLinks->count() }} collegamenti</x-ui.badge>
                </div>

                @if ($moodleUserLinks->isEmpty())
                    <div class="mt-6">
                        <x-ui.empty-state
                            title="Nessun account Moodle collegato"
                            description="Usa il modulo accanto per collegare il tuo primo account."
                        />
                    </div>
                @else
                    <div class="mt-6 space-y-4">
                        @foreach ($moodleUserLinks as $link)
                            <article class="rounded-2xl border border-slate-200 p-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h3 class="font-semibold text-slate-950">{{ $link->moodleSite->name }}</h3>
                                            <x-ui.badge :variant="$link->status === 'active' ? 'success' : 'warning'">
                                                {{ $link->status === 'active' ? 'Attivo' : ucfirst($link->status) }}
                                            </x-ui.badge>
                                        </div>

                                        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                            <div class="rounded-xl bg-slate-50 p-4">
                                                <dt class="font-semibold text-slate-900">Username</dt>
                                                <dd class="mt-1 break-all text-slate-600">{{ $link->moodle_username ?: 'Non disponibile' }}</dd>
                                            </div>
                                            <div class="rounded-xl bg-slate-50 p-4">
                                                <dt class="font-semibold text-slate-900">ID Moodle</dt>
                                                <dd class="mt-1 text-slate-600">{{ $link->moodle_user_id }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="text-left text-xs text-slate-500 sm:text-right">
                                        <p class="font-semibold uppercase tracking-wide text-slate-600">Collegato il</p>
                                        <p class="mt-1">{{ $link->linked_at?->format('d/m/Y H:i') ?: 'Data non disponibile' }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </section>

        <x-ui.card>
            <div class="grid gap-6 lg:grid-cols-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Come funziona</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Collegamento sicuro in tre passaggi</h2>
                </div>

                <div class="rounded-2xl bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-950">1. Individua l'account</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Seleziona il sito Moodle e inserisci email o username.</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-950">2. Verifica via email</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Ricevi un codice temporaneo all'email associata all'account Moodle.</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-5 lg:col-start-2">
                    <p class="text-sm font-semibold text-slate-950">3. Attiva la sincronizzazione</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Dopo la verifica, il collegamento risulta attivo e pronto per gli attestati.</p>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
