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

        <section>
            <x-ui.card>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Curriculum</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Esperienze e percorsi di studio</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Aggiungi le informazioni che saranno visibili ai business quando valuteranno una candidatura.</p>
                </div>

                <form method="POST" action="{{ route('professional-profile-items.store') }}" class="mt-6 grid gap-4 lg:grid-cols-[180px_1fr_180px]">
                    @csrf

                    <div>
                        <label for="profile_item_type" class="text-sm font-semibold text-slate-800">Tipo</label>
                        <select id="profile_item_type" name="type" class="mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600" required>
                            <option value="work_experience">Esperienza lavorativa</option>
                            <option value="education">Percorso di studio</option>
                        </select>
                    </div>

                    <x-ui.input id="profile_item_title" name="title" label="Titolo" :value="old('title')" required />
                    <x-ui.input id="profile_item_duration" name="duration" label="Durata" :value="old('duration')" placeholder="Es. 2021 - 2024" required />

                    <div class="lg:col-span-3">
                        <x-ui.textarea id="profile_item_description" name="description" label="Descrizione" rows="4">{{ old('description') }}</x-ui.textarea>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 pt-5 lg:col-span-3">
                        <x-ui.button type="submit">Aggiungi al profilo</x-ui.button>
                    </div>
                </form>

                @if ($profileItems->isNotEmpty())
                    <div class="mt-8 grid gap-4 md:grid-cols-2">
                        @foreach ($profileItems as $item)
                            <article class="rounded-xl border border-slate-200 p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <x-ui.badge>{{ $item->type === 'education' ? 'Percorso di studio' : 'Esperienza lavorativa' }}</x-ui.badge>
                                        <h3 class="mt-3 font-semibold text-slate-950">{{ $item->title }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ $item->duration }}</p>
                                    </div>
                                </div>
                                @if ($item->description)
                                    <p class="mt-4 text-sm leading-6 text-slate-600">{{ $item->description }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </section>
    </div>
</x-app-layout>
