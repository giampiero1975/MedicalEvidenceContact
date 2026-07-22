<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Candidate Workspace" :description="$application->jobPosting->title">
            <x-slot name="actions">
                <x-ui.button href="{{ route('job-postings.applications', $application->jobPosting) }}" variant="secondary" size="sm">Torna alle candidature</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    @php
        $workExperiences = $professional->professionalProfileItems->where('type', 'work_experience');
        $educationItems = $professional->professionalProfileItems->where('type', 'education');
        $document = $professional->professionalDocument;
        $fullName = $professional->first_name && $professional->last_name ? $professional->first_name.' '.$professional->last_name : $professional->name;
        $statusKeys = array_keys($statusOptions);
        $currentStatusIndex = array_search($application->status, $statusKeys, true);
        $pipelineProgress = $currentStatusIndex === false || count($statusKeys) <= 1
            ? 0
            : (int) round(($currentStatusIndex / (count($statusKeys) - 1)) * 100);
        $statusVariant = match ($application->status) {
            'assunto', 'idoneo' => 'success',
            'non_idoneo', 'ritirata' => 'danger',
            'colloquio_programmato', 'colloquio_effettuato' => 'info',
            default => 'warning',
        };
        $nextInterview = $application->interviews
            ->where('status', 'scheduled')
            ->sortBy('scheduled_at')
            ->first();
    @endphp

    <div class="space-y-6">
        <x-ui.card>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <img class="h-16 w-16 rounded-2xl object-cover ring-1 ring-slate-200" src="{{ $professional->profile_photo_url }}" alt="{{ $fullName }}">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="truncate text-2xl font-semibold text-slate-950">{{ $fullName }}</h2>
                            <x-ui.badge :variant="$statusVariant">{{ $application->statusLabel() }}</x-ui.badge>
                        </div>
                        <p class="mt-1 text-sm text-slate-600">{{ $professional->residence ?: 'Residenza non indicata' }}</p>
                        @if ($canViewContacts)
                            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm font-medium text-slate-700">
                                <span>{{ $professional->email }}</span>
                                @if($professional->phone)<span>{{ $professional->phone }}</span>@endif
                            </div>
                        @else
                            <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-amber-800">Contatti protetti</p>
                                <p class="mt-1 text-sm text-amber-900">Saranno visibili dopo l’accettazione del colloquio e il consenso del professionista.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid min-w-full gap-3 sm:grid-cols-3 lg:min-w-[31rem]">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Candidatura</p>
                        <p class="mt-2 font-semibold text-slate-950">{{ $application->created_at->format('d/m/Y') }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $application->created_at->format('H:i') }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Esperienze</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $workExperiences->count() }}</p>
                        <p class="mt-1 text-xs text-slate-500">registrate</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Attestati</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $professional->certificates->count() }}</p>
                        <p class="mt-1 text-xs text-slate-500">sincronizzati</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 border-t border-slate-200 pt-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Avanzamento selezione</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->statusLabel() }}</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">{{ $pipelineProgress }}%</span>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-teal-600 transition-all" style="width: {{ $pipelineProgress }}%"></div>
                </div>
                <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-3">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Posizione</dt><dd class="mt-1 font-medium text-slate-900">{{ $application->jobPosting->title }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sede</dt><dd class="mt-1 font-medium text-slate-900">{{ $application->jobPosting->workplace_address }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reparto</dt><dd class="mt-1 font-medium text-slate-900">{{ $application->jobPosting->businessDepartment?->name ?: 'Non specificato' }}</dd></div>
                </dl>
            </div>
        </x-ui.card>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_23rem]">
            <main class="space-y-6">
                @foreach ([['Esperienze lavorative', $workExperiences, 'Nessuna esperienza indicata.'], ['Formazione', $educationItems, 'Nessun percorso di studio indicato.']] as [$sectionTitle, $items, $emptyText])
                    <x-ui.card>
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="text-base font-semibold text-slate-950">{{ $sectionTitle }}</h3>
                            <x-ui.badge variant="secondary">{{ $items->count() }} elementi</x-ui.badge>
                        </div>
                        @if ($items->isEmpty())
                            <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">{{ $emptyText }}</div>
                        @else
                            <div class="mt-4 divide-y divide-slate-200">
                                @foreach ($items as $item)
                                    <article class="grid gap-2 py-4 first:pt-0 last:pb-0 sm:grid-cols-[minmax(0,1fr)_11rem]">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $item->title }}</p>
                                            @if ($item->description)<p class="mt-1 text-sm leading-6 text-slate-600">{{ $item->description }}</p>@endif
                                        </div>
                                        <p class="text-sm font-medium text-slate-500 sm:text-right">{{ $item->duration ?: 'Durata non indicata' }}</p>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </x-ui.card>
                @endforeach

                <x-ui.card>
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-slate-950">Attestati Moodle</h3>
                            <p class="mt-1 text-sm text-slate-500">Certificazioni sincronizzate dal profilo professionale.</p>
                        </div>
                        <x-ui.badge :variant="$professional->certificates->isNotEmpty() ? 'success' : 'warning'">{{ $professional->certificates->count() }} attestati</x-ui.badge>
                    </div>
                    @if ($professional->certificates->isEmpty())
                        <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Nessun attestato sincronizzato.</div>
                    @else
                        <div class="mt-4 divide-y divide-slate-200">
                            @foreach ($professional->certificates as $certificate)
                                <article class="grid gap-3 py-4 first:pt-0 last:pb-0 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_8rem] lg:items-center">
                                    <div><p class="font-semibold text-slate-900">{{ $certificate->certificate_name }}</p><p class="mt-1 text-sm text-slate-500">{{ $certificate->course_fullname ?: 'Corso non indicato' }}</p></div>
                                    <p class="text-sm text-slate-600">Codice {{ $certificate->certificate_code ?: 'non disponibile' }}</p>
                                    <p class="text-sm text-slate-500 lg:text-right">{{ $certificate->issued_at?->format('d/m/Y') ?: 'Data assente' }}</p>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </x-ui.card>

                <x-ui.card>
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-slate-950">Timeline candidatura</h3>
                            <p class="mt-1 text-sm text-slate-500">Cronologia delle attività e delle decisioni HR.</p>
                        </div>
                        <x-ui.badge variant="secondary">{{ max(1, $application->events->count()) }} eventi</x-ui.badge>
                    </div>
                    <div class="mt-6 space-y-0">
                        @forelse ($application->events as $event)
                            <div class="relative border-l-2 border-slate-200 pb-6 pl-6 last:pb-0">
                                <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full border-2 border-white bg-teal-600 ring-1 ring-slate-200"></span>
                                <p class="text-sm font-semibold text-slate-900">{{ $event->label }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $event->created_at->format('d/m/Y H:i') }}@if($event->actor) · {{ $event->actor->name }}@endif</p>
                            </div>
                        @empty
                            <div class="relative border-l-2 border-slate-200 pl-6">
                                <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full border-2 border-white bg-teal-600 ring-1 ring-slate-200"></span>
                                <p class="text-sm font-semibold text-slate-900">Candidatura ricevuta</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $application->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>
            </main>

            <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
                <x-ui.card>
                    <h3 class="text-base font-semibold text-slate-950">Workflow HR</h3>
                    <p class="mt-1 text-sm text-slate-500">Aggiorna la posizione del candidato nella pipeline.</p>
                    <form method="POST" action="{{ route('job-applications.status.update', $application) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')
                        <x-ui.select name="status" label="Stato candidatura">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.button type="submit" class="w-full">Aggiorna stato</x-ui.button>
                    </form>
                </x-ui.card>

                <x-ui.card>
                    <div class="flex items-center justify-between gap-3">
                        <div><h3 class="text-base font-semibold text-slate-950">Colloqui</h3><p class="mt-1 text-sm text-slate-500">Pianificazione e risposte.</p></div>
                        <x-ui.badge variant="secondary">{{ $application->interviews->count() }}</x-ui.badge>
                    </div>
                    @if ($nextInterview)
                        <div class="mt-4 rounded-xl border border-teal-200 bg-teal-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-teal-800">Prossimo colloquio</p>
                            <p class="mt-2 font-semibold text-slate-950">{{ $nextInterview->scheduled_at->format('d/m/Y H:i') }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $nextInterview->modeLabel() }} · {{ $nextInterview->duration_minutes }} minuti</p>
                        </div>
                    @endif
                    <div class="mt-4 divide-y divide-slate-200">
                        @forelse ($application->interviews as $interview)
                            <div class="py-3 first:pt-0">
                                <div class="flex items-center justify-between gap-3"><p class="font-semibold text-slate-900">{{ $interview->scheduled_at->format('d/m/Y H:i') }}</p><x-ui.badge variant="secondary">{{ $interview->statusLabel() }}</x-ui.badge></div>
                                <p class="mt-1 text-sm text-slate-600">{{ $interview->modeLabel() }} · {{ $interview->duration_minutes }} minuti</p>
                                @if($interview->location)<p class="mt-1 break-all text-xs text-slate-500">{{ $interview->location }}</p>@endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Nessun colloquio programmato.</p>
                        @endforelse
                    </div>
                    <form method="POST" action="{{ route('business.applications.interviews.store', $application) }}" class="mt-4 space-y-3 border-t border-slate-200 pt-4">
                        @csrf
                        <x-ui.input name="scheduled_at" type="datetime-local" label="Data e ora" required />
                        <div class="grid grid-cols-2 gap-3">
                            <x-ui.select name="duration_minutes" label="Durata"><option value="30">30 minuti</option><option value="45">45 minuti</option><option value="60">60 minuti</option><option value="90">90 minuti</option></x-ui.select>
                            <x-ui.select name="mode" label="Modalità"><option value="in_person">In presenza</option><option value="video">Videochiamata</option><option value="phone">Telefonico</option></x-ui.select>
                        </div>
                        <x-ui.input name="location" label="Sede o link" placeholder="Indirizzo, Teams, Meet..." />
                        <textarea name="notes" rows="2" maxlength="2000" placeholder="Note per il colloquio" class="block w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600"></textarea>
                        <x-ui.button type="submit" size="sm" class="w-full">Programma colloquio</x-ui.button>
                    </form>
                </x-ui.card>

                <x-ui.card>
                    <h3 class="text-base font-semibold text-slate-950">Note interne HR</h3>
                    <p class="mt-1 text-sm text-slate-500">Visibili esclusivamente alla struttura.</p>
                    <form method="POST" action="{{ route('business.applications.notes.store', $application) }}" class="mt-4 space-y-3">
                        @csrf
                        <textarea name="body" rows="4" required maxlength="5000" placeholder="Aggiungi una nota sul candidato..." class="block w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600"></textarea>
                        <x-ui.button type="submit" size="sm" class="w-full">Aggiungi nota</x-ui.button>
                    </form>
                    <div class="mt-4 max-h-80 divide-y divide-slate-200 overflow-y-auto pr-1">
                        @forelse ($application->notes as $note)
                            <div class="py-3 first:pt-0 last:pb-0"><p class="text-sm leading-6 text-slate-700">{{ $note->body }}</p><p class="mt-1 text-xs text-slate-500">{{ $note->author?->name }} · {{ $note->created_at->format('d/m/Y H:i') }}</p></div>
                        @empty
                            <p class="text-sm text-slate-500">Nessuna nota interna.</p>
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <h3 class="text-base font-semibold text-slate-950">Documenti professionali</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3"><dt class="text-slate-600">Attestato ATA</dt><dd><x-ui.badge :variant="$document?->ata_certificate_path ? 'success' : 'warning'">{{ $document?->ata_certificate_path ? 'Disponibile' : 'Non caricato' }}</x-ui.badge></dd></div>
                        <div class="flex items-center justify-between gap-3 border-t border-slate-200 pt-3"><dt class="text-slate-600">Permesso di soggiorno</dt><dd><x-ui.badge :variant="$document?->residence_permit_path ? 'success' : 'warning'">{{ $document?->residence_permit_path ? 'Disponibile' : 'Non caricato' }}</x-ui.badge></dd></div>
                    </dl>
                </x-ui.card>
            </aside>
        </div>
    </div>
</x-app-layout>
