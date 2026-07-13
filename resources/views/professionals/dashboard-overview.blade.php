<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Dashboard professionista"
            subtitle="Tieni sotto controllo profilo, documenti, candidature e formazione."
        >
            <x-slot name="actions">
                <x-ui.button :href="route('job-postings.index')">Cerca opportunità</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-8">
        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Profilo completato" :value="$profileCompletion.'%'" hint="Dati anagrafici e professionali" />
            <x-ui.stat-card label="Candidature attive" :value="$activeApplicationsCount" hint="In valutazione o colloquio" />
            <x-ui.stat-card label="Candidature accettate" :value="$acceptedApplicationsCount" hint="Esiti positivi" />
            <x-ui.stat-card label="Opportunità disponibili" :value="$availableJobsCount" hint="Annunci attivi" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <x-ui.card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Profilo</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">Completa la tua presenza professionale</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Un profilo completo rende più semplice alle strutture valutare candidatura, esperienza e disponibilità.</p>
                    </div>
                    <x-ui.badge :variant="$profileCompletion === 100 ? 'success' : 'warning'">{{ $profileCompletion }}%</x-ui.badge>
                </div>

                <div class="mt-6 h-2 overflow-hidden rounded-full bg-slate-100" aria-label="Completamento profilo {{ $profileCompletion }}%">
                    <div class="h-full rounded-full bg-teal-700" style="width: {{ $profileCompletion }}%"></div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button :href="route('profile.show')">Aggiorna profilo</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('professional.moodle.index')">Gestisci formazione</x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Documenti</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">Documenti professionali</h2>
                    </div>
                    <x-ui.button variant="ghost" size="sm" :href="route('profile.show')">Gestisci</x-ui.button>
                </div>

                <div class="mt-6 space-y-3">
                    @foreach ($documents as $document)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $document['label'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $document['required'] ? 'Richiesto per il profilo' : 'Non richiesto per il profilo attuale' }}</p>
                            </div>
                            <x-ui.badge :variant="$document['uploaded'] ? 'success' : ($document['required'] ? 'warning' : 'neutral')">
                                {{ $document['uploaded'] ? 'Caricato' : ($document['required'] ? 'Da caricare' : 'Facoltativo') }}
                            </x-ui.badge>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <x-ui.card>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Candidature</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">Le tue candidature</h2>
                    </div>
                    <x-ui.button variant="secondary" size="sm" :href="route('job-postings.index')">Vedi annunci</x-ui.button>
                </div>

                @if ($jobApplications->isEmpty())
                    <div class="mt-6">
                        <x-ui.empty-state title="Nessuna candidatura" description="Esplora gli annunci disponibili e invia la tua prima candidatura." />
                    </div>
                @else
                    <div class="mt-6 divide-y divide-slate-100">
                        @foreach ($jobApplications->take(5) as $application)
                            <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $application->jobPosting?->title ?? 'Annuncio non disponibile' }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Inviata il {{ $application->created_at?->format('d/m/Y') }}</p>
                                </div>
                                <x-ui.badge :variant="in_array($application->status, ['accettata', 'colloquio'], true) ? 'success' : ($application->status === 'rifiutata' ? 'danger' : 'warning')">
                                    {{ $application->status === 'inviata' ? 'Candidatura inviata' : ucfirst(str_replace('_', ' ', $application->status)) }}
                                </x-ui.badge>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            <div class="space-y-6">
                <x-ui.card>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Moodle</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Formazione collegata</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ $moodleUserLinks->isNotEmpty() ? 'Il tuo account Moodle è collegato e pronto per la sincronizzazione.' : 'Collega il tuo account Moodle per importare attestati e corsi completati.' }}
                    </p>
                    <div class="mt-5">
                        <x-ui.button variant="secondary" :href="route('professional.moodle.index')">
                            {{ $moodleUserLinks->isNotEmpty() ? 'Gestisci collegamenti' : 'Collega Moodle' }}
                        </x-ui.button>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Prossimo passo</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Trova una nuova opportunità</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Consulta gli annunci attivi e filtra per ruolo, località, contratto e retribuzione.</p>
                    <div class="mt-5">
                        <x-ui.button :href="route('job-postings.index')">Esplora gli annunci</x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </section>
    </div>
</x-app-layout>
