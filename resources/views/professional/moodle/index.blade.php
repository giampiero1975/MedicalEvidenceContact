<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Moodle e attestati"
            subtitle="Collega il tuo account Moodle e sincronizza corsi, certificazioni e attestati."
        />
    </x-slot>

    @php
        $activeMoodleLink = $moodleUserLinks->firstWhere('status', 'active');
        $certificates = $moodleUserLinks
            ->flatMap(fn ($link) => $link->certificates()->latest('issued_at')->get())
            ->sortByDesc(fn ($certificate) => $certificate->issued_at ?? $certificate->created_at)
            ->values();
        $coursesCount = $certificates->pluck('course_id')->filter()->unique()->count();
    @endphp

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

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Siti disponibili" :value="$moodleSites->count()" hint="Piattaforme Moodle attive" />
            <x-ui.stat-card label="Account collegati" :value="$moodleUserLinks->where('status', 'active')->count()" hint="Collegamenti verificati" />
            <x-ui.stat-card label="Corsi rilevati" :value="$coursesCount" hint="Corsi con attestati sincronizzati" />
            <x-ui.stat-card label="Attestati" :value="$certificates->count()" hint="Certificazioni disponibili" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <x-ui.card>
                @if ($activeMoodleLink)
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Collegamento completato</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Account Moodle già collegato</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Il tuo account per <strong>{{ $activeMoodleLink->moodleSite->name }}</strong> è stato verificato ed è attivo.
                    </p>
                    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                        <div class="flex flex-wrap items-center gap-3">
                            <x-ui.badge variant="success">Attivo</x-ui.badge>
                            <p class="font-semibold text-slate-950">{{ $activeMoodleLink->moodle_username ?: 'Account verificato' }}</p>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Un professionista può mantenere un solo collegamento Moodle attivo. Per aggiornare corsi e attestati usa la sincronizzazione disponibile accanto.
                        </p>
                    </div>
                @else
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Nuovo collegamento</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Collega un account Moodle</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Cerca il tuo account tramite email o username. Riceverai un codice all'indirizzo email registrato su Moodle.
                    </p>

                    @if ($moodleSites->isEmpty())
                        <div class="mt-6">
                            <x-ui.empty-state title="Nessun sito Moodle disponibile" description="Al momento non ci sono piattaforme abilitate al collegamento." />
                        </div>
                    @else
                        <form method="POST" action="{{ route('professional.moodle.start') }}" class="mt-6 space-y-5">
                            @csrf
                            <x-ui.select name="moodle_site_id" label="Sito Moodle" required>
                                @foreach ($moodleSites as $moodleSite)
                                    <option value="{{ $moodleSite->id }}" @selected(old('moodle_site_id') == $moodleSite->id)>{{ $moodleSite->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.select name="lookup_type" label="Come vuoi cercare l'account?" required>
                                <option value="email" @selected(old('lookup_type', 'email') === 'email')>Email Moodle</option>
                                <option value="username" @selected(old('lookup_type') === 'username')>Username Moodle</option>
                            </x-ui.select>
                            <x-ui.input name="lookup_value" label="Email o username Moodle" :value="old('lookup_value')" placeholder="Inserisci il dato usato su Moodle" help="Il dato viene usato solo per individuare l'account e avviare la verifica." required />
                            <div class="flex justify-end border-t border-slate-100 pt-5">
                                <x-ui.button type="submit">Collega account</x-ui.button>
                            </div>
                        </form>
                    @endif
                @endif
            </x-ui.card>

            <x-ui.card>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Formazione collegata</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">Il tuo account Moodle</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Sincronizza manualmente gli attestati del collegamento attivo.</p>
                    </div>
                    <x-ui.badge>{{ $moodleUserLinks->where('status', 'active')->count() }} collegamenti</x-ui.badge>
                </div>

                @if ($moodleUserLinks->isEmpty())
                    <div class="mt-6">
                        <x-ui.empty-state title="Nessun account Moodle collegato" description="Usa il modulo accanto per collegare il tuo account." />
                    </div>
                @else
                    <div class="mt-6 space-y-4">
                        @foreach ($moodleUserLinks as $link)
                            <article class="rounded-2xl border border-slate-200 p-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h3 class="font-semibold text-slate-950">{{ $link->moodleSite->name }}</h3>
                                            <x-ui.badge :variant="$link->status === 'active' ? 'success' : 'warning'">{{ $link->status === 'active' ? 'Attivo' : ucfirst($link->status) }}</x-ui.badge>
                                        </div>
                                        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                            <div class="rounded-xl bg-slate-50 p-4">
                                                <dt class="font-semibold text-slate-900">Username</dt>
                                                <dd class="mt-1 break-all text-slate-600">{{ $link->moodle_username ?: 'Non disponibile' }}</dd>
                                            </div>
                                            <div class="rounded-xl bg-slate-50 p-4">
                                                <dt class="font-semibold text-slate-900">Ultimo sync</dt>
                                                <dd class="mt-1 text-slate-600">{{ $link->last_certificate_sync_at?->format('d/m/Y H:i') ?: 'Mai eseguito' }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                    @if ($link->status === 'active')
                                        <form method="POST" action="{{ route('professional.moodle.certificates.sync', $link) }}">
                                            @csrf
                                            <x-ui.button type="submit" variant="secondary">Sincronizza attestati</x-ui.button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </section>

        <x-ui.card>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Archivio formazione</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Corsi e attestati sincronizzati</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Gli attestati disponibili vengono salvati nel portale e collegati al tuo profilo.</p>
                </div>
                <x-ui.badge :variant="$certificates->isNotEmpty() ? 'success' : 'warning'">{{ $certificates->count() }} attestati</x-ui.badge>
            </div>

            @if ($certificates->isEmpty())
                <div class="mt-6">
                    <x-ui.empty-state title="Nessun attestato sincronizzato" description="Esegui la sincronizzazione sull'account Moodle attivo. Se l'utente non possiede attestati, questa sezione resterà vuota." />
                </div>
            @else
                <div class="mt-6 space-y-3">
                    @foreach ($certificates as $certificate)
                        <article class="grid gap-5 rounded-2xl border border-slate-200 p-5 md:grid-cols-[minmax(0,1.5fr)_minmax(240px,0.8fr)_auto] md:items-center">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-3">
                                    <p class="truncate text-xs font-semibold uppercase tracking-[0.16em] text-teal-700">{{ $certificate->course_shortname ?: 'Formazione Moodle' }}</p>
                                    <x-ui.badge :variant="$certificate->pdf_stored_path ? 'success' : 'warning'">{{ $certificate->pdf_stored_path ? 'PDF disponibile' : 'Dati sincronizzati' }}</x-ui.badge>
                                </div>
                                <h3 class="mt-2 text-lg font-semibold text-slate-950">{{ $certificate->certificate_name ?: $certificate->course_fullname ?: 'Attestato' }}</h3>
                                @if ($certificate->course_fullname)
                                    <p class="mt-1 truncate text-sm text-slate-600">{{ $certificate->course_fullname }}</p>
                                @endif
                            </div>

                            <dl class="grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                    <dt class="font-semibold text-slate-900">Rilasciato il</dt>
                                    <dd class="mt-1 whitespace-nowrap text-slate-600">{{ $certificate->issued_at?->format('d/m/Y') ?: 'Non disponibile' }}</dd>
                                </div>
                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                    <dt class="font-semibold text-slate-900">Codice</dt>
                                    <dd class="mt-1 truncate text-slate-600" title="{{ $certificate->certificate_code }}">{{ $certificate->certificate_code ?: 'Non disponibile' }}</dd>
                                </div>
                            </dl>

                            @if ($certificate->pdf_stored_path || $certificate->verification_url || $certificate->download_url)
                                <div class="flex flex-wrap gap-3 md:justify-end">
                                    @if ($certificate->pdf_stored_path)
                                        <x-ui.button variant="secondary" :href="route('professional.moodle.certificates.view', $certificate)" target="_blank" rel="noopener">Visualizza</x-ui.button>
                                        <x-ui.button :href="route('professional.moodle.certificates.download', $certificate)">Scarica PDF</x-ui.button>
                                    @endif
                                    @if ($certificate->verification_url)
                                        <x-ui.button variant="secondary" :href="$certificate->verification_url" target="_blank" rel="noopener">Verifica</x-ui.button>
                                    @endif
                                    @if (! $certificate->pdf_stored_path && $certificate->download_url)
                                        <x-ui.button :href="$certificate->download_url" target="_blank" rel="noopener">Apri attestato</x-ui.button>
                                    @endif
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>