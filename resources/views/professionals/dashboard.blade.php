<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-600">Workspace professionista</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">
                    Buongiorno, {{ auth()->user()->first_name ?: auth()->user()->name }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Monitora il profilo, le candidature e i collegamenti formativi da un unico spazio.
                </p>
            </div>

            <a href="{{ route('job-postings.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.073a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6.75A2.25 2.25 0 0 1 6 4.5h4.073M14.25 3.75H20.25V9.75M10.5 13.5 20.25 3.75" />
                </svg>
                Esplora annunci
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @session('status')
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                    {{ $value }}
                </div>
            @endsession

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @php
                    $kpis = [
                        ['label' => 'Profilo', 'value' => $profileCompletion.'%', 'detail' => $profileChecks->where('complete', true)->count().' di '.$profileChecks->count().' sezioni complete', 'tone' => 'teal'],
                        ['label' => 'Candidature', 'value' => $applications->count(), 'detail' => $applications->where('status', 'pending')->count().' in valutazione', 'tone' => 'indigo'],
                        ['label' => 'Colloqui', 'value' => $interviewApplications->count(), 'detail' => 'Candidature in fase colloquio', 'tone' => 'amber'],
                        ['label' => 'Attestati Moodle', 'value' => $certificateCount, 'detail' => $moodleUserLinks->count().' account collegati', 'tone' => 'emerald'],
                    ];
                    $toneClasses = [
                        'teal' => 'bg-teal-50 text-teal-700 ring-teal-100',
                        'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
                        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
                        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                    ];
                @endphp

                @foreach ($kpis as $kpi)
                    <article class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ $kpi['label'] }}</p>
                                <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900">{{ $kpi['value'] }}</p>
                                <p class="mt-2 text-xs leading-5 text-gray-500">{{ $kpi['detail'] }}</p>
                            </div>
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg ring-1 {{ $toneClasses[$kpi['tone']] }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    @if ($kpi['label'] === 'Profilo')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.5-1.632Z" />
                                    @elseif ($kpi['label'] === 'Candidature')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.073a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V8.25A2.25 2.25 0 0 1 6 6h2.25m7.5 0H18a2.25 2.25 0 0 1 2.25 2.25v1.5M8.25 6V4.875A1.125 1.125 0 0 1 9.375 3.75h5.25a1.125 1.125 0 0 1 1.125 1.125V6m-7.5 0h7.5" />
                                    @elseif ($kpi['label'] === 'Colloqui')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M5.25 4.5h13.5A1.5 1.5 0 0 1 20.25 6v12A1.5 1.5 0 0 1 18.75 19.5H5.25A1.5 1.5 0 0 1 3.75 18V6A1.5 1.5 0 0 1 5.25 4.5Z" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84 51.827 51.827 0 0 0-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                                    @endif
                                </svg>
                            </span>
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 xl:grid-cols-3">
                <article class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 xl:col-span-2">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Completamento profilo</h3>
                            <p class="mt-1 text-sm text-gray-600">Un profilo completo rende la candidatura piu chiara alle aziende.</p>
                        </div>
                        <span class="text-2xl font-semibold text-gray-900">{{ $profileCompletion }}%</span>
                    </div>

                    <div class="mt-5 h-2.5 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-teal-600 transition-all" style="width: {{ $profileCompletion }}%"></div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach ($profileChecks as $check)
                            <div class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 px-3 py-3">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full {{ $check['complete'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-500' }}">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        @if ($check['complete'])
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        @endif
                                    </svg>
                                </span>
                                <span class="text-sm font-medium text-gray-700">{{ $check['label'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <a href="#profilo-professionale" class="mt-6 inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700">
                        Completa profilo
                    </a>
                </article>

                <aside class="rounded-xl bg-slate-900 p-6 text-white shadow-sm">
                    <p class="text-sm font-semibold text-teal-300">Suggerimenti profilo</p>
                    <h3 class="mt-2 text-lg font-semibold">Aumenta la qualita della candidatura</h3>
                    <div class="mt-5 space-y-4">
                        @forelse ($profileChecks->where('complete', false)->take(4) as $check)
                            <div class="flex gap-3 text-sm text-slate-200">
                                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/10 text-xs">{{ $loop->iteration }}</span>
                                <span>Completa la sezione: {{ $check['label'] }}.</span>
                            </div>
                        @empty
                            <p class="text-sm leading-6 text-slate-200">Il profilo e completo. Mantieni aggiornate esperienze, documenti e attestati.</p>
                        @endforelse
                    </div>
                </aside>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Le mie candidature</h3>
                            <p class="mt-1 text-sm text-gray-600">Le opportunita seguite piu di recente.</p>
                        </div>
                        <a href="{{ route('interviews.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Vedi tutte</a>
                    </div>

                    <div class="mt-5 divide-y divide-gray-100">
                        @forelse ($applications->take(5) as $application)
                            <a href="{{ route('job-postings.show', $application->jobPosting) }}" class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-900">{{ $application->jobPosting?->title }}</p>
                                    <p class="mt-1 truncate text-sm text-gray-500">{{ $application->jobPosting?->owner?->businessProfile?->company_name ?: $application->jobPosting?->owner?->name }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                    {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                </span>
                            </a>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 p-6 text-center">
                                <p class="font-semibold text-gray-900">Nessuna candidatura</p>
                                <p class="mt-1 text-sm text-gray-600">Esplora gli annunci e invia la tua prima candidatura.</p>
                            </div>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Attivita recenti</h3>
                        <p class="mt-1 text-sm text-gray-600">Gli ultimi aggiornamenti del workspace.</p>
                    </div>

                    <div class="mt-5 space-y-4">
                        @forelse ($recentActivity as $activity)
                            <div class="flex gap-3">
                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-indigo-500 ring-4 ring-indigo-50"></span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $activity['title'] }}</p>
                                    <p class="mt-0.5 text-sm text-gray-600">{{ $activity['description'] }}</p>
                                    <p class="mt-1 text-xs text-gray-400">{{ $activity['date']?->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-600">Le attivita compariranno qui appena inizierai a usare il workspace.</p>
                        @endforelse
                    </div>
                </article>
            </section>

            <section id="profilo-professionale" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Collegamento Moodle</h3>
                        <p class="mt-1 text-sm text-gray-600">Collega un account formativo e mantieni aggiornati gli attestati.</p>
                    </div>
                    <a href="{{ route('professional.moodle.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Gestisci collegamenti</a>
                </div>

                @if ($moodleSites->isNotEmpty())
                    <form method="POST" action="{{ route('professional.moodle.start') }}" class="mt-5 grid gap-4 lg:grid-cols-4">
                        @csrf
                        <div>
                            <x-label for="moodle_site_id" value="Sito Moodle" />
                            <select id="moodle_site_id" name="moodle_site_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach ($moodleSites as $moodleSite)
                                    <option value="{{ $moodleSite->id }}">{{ $moodleSite->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-label for="lookup_type" value="Tipo dato" />
                            <select id="lookup_type" name="lookup_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="email">Email Moodle</option>
                                <option value="username">Username Moodle</option>
                            </select>
                        </div>
                        <div>
                            <x-label for="lookup_value" value="Email o username" />
                            <x-input id="lookup_value" name="lookup_value" type="text" class="mt-1 block w-full" required />
                        </div>
                        <div class="flex items-end">
                            <x-button class="w-full justify-center">Collega account</x-button>
                        </div>
                    </form>
                @else
                    <p class="mt-5 rounded-lg border border-dashed border-gray-300 p-5 text-sm text-gray-600">Nessun sito Moodle disponibile.</p>
                @endif
            </section>

            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Documenti professionali</h3>
                    <p class="mt-1 text-sm text-gray-600">Carica i documenti richiesti per completare la candidatura.</p>
                </div>

                <x-validation-errors class="mt-5" />

                <form method="POST" action="{{ route('professional-documents.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-5 lg:grid-cols-2">
                    @csrf
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <x-label for="ata_certificate_document" value="Attestato ATA" />
                                <p class="mt-1 text-sm text-gray-600">PDF, JPG o PNG fino a 5 MB.</p>
                            </div>
                            @if (auth()->user()->ata_certificate_path)
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Caricato</span>
                            @endif
                        </div>
                        <input id="ata_certificate_document" type="file" name="ata_certificate_document" accept=".pdf,.jpg,.jpeg,.png" class="mt-4 block w-full text-sm text-gray-700" />
                    </div>

                    @if (! $isItalian)
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <x-label for="residence_permit_document" value="Permesso di soggiorno" />
                                    <p class="mt-1 text-sm text-gray-600">Richiesto per nazionalita diversa da italiana.</p>
                                </div>
                                @if (auth()->user()->residence_permit_path)
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Caricato</span>
                                @endif
                            </div>
                            <input id="residence_permit_document" type="file" name="residence_permit_document" accept=".pdf,.jpg,.jpeg,.png" class="mt-4 block w-full text-sm text-gray-700" />
                        </div>
                    @endif

                    <div class="flex justify-end border-t border-gray-100 pt-5 lg:col-span-2">
                        <x-button>Salva documenti</x-button>
                    </div>
                </form>
            </section>

            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Esperienze e formazione</h3>
                    <p class="mt-1 text-sm text-gray-600">Aggiungi le informazioni che saranno visibili alle aziende.</p>
                </div>

                <form method="POST" action="{{ route('professional-profile-items.store') }}" class="mt-5 grid gap-4 lg:grid-cols-[180px_1fr_180px]">
                    @csrf
                    <div>
                        <x-label for="profile_item_type" value="Tipo" />
                        <select id="profile_item_type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="work_experience">Esperienza lavorativa</option>
                            <option value="education">Percorso di studio</option>
                        </select>
                    </div>
                    <div>
                        <x-label for="profile_item_title" value="Titolo" />
                        <x-input id="profile_item_title" class="mt-1 block w-full" type="text" name="title" :value="old('title')" required />
                    </div>
                    <div>
                        <x-label for="profile_item_duration" value="Durata" />
                        <x-input id="profile_item_duration" class="mt-1 block w-full" type="text" name="duration" :value="old('duration')" placeholder="Es. 2021 - 2024" required />
                    </div>
                    <div class="lg:col-span-3">
                        <x-label for="profile_item_description" value="Descrizione" />
                        <textarea id="profile_item_description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                    </div>
                    <div class="flex justify-end border-t border-gray-100 pt-5 lg:col-span-3">
                        <x-button>Aggiungi al profilo</x-button>
                    </div>
                </form>

                @if ($profileItems->isNotEmpty())
                    <div class="mt-6 grid gap-3 md:grid-cols-2">
                        @foreach ($profileItems as $item)
                            <article class="rounded-lg border border-gray-200 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $item->type === 'education' ? 'Formazione' : 'Esperienza' }}</p>
                                        <h4 class="mt-1 font-semibold text-gray-900">{{ $item->title }}</h4>
                                        <p class="mt-1 text-sm text-gray-500">{{ $item->duration }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('professional-profile-items.destroy', $item) }}" onsubmit="return confirm('Eliminare questo elemento dal profilo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-500">Elimina</button>
                                    </form>
                                </div>
                                @if ($item->description)
                                    <p class="mt-3 text-sm leading-6 text-gray-600">{{ $item->description }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
