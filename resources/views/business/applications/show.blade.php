<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Scheda candidatura"
            :description="$application->jobPosting->title"
        >
            <x-slot name="actions">
                <x-ui.button href="{{ route('job-postings.applications', $application->jobPosting) }}" variant="secondary" size="sm">
                    Torna alle candidature
                </x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    @php
        $workExperiences = $professional->professionalProfileItems->where('type', 'work_experience');
        $educationItems = $professional->professionalProfileItems->where('type', 'education');
        $document = $professional->professionalDocument;
        $fullName = $professional->first_name && $professional->last_name
            ? $professional->first_name.' '.$professional->last_name
            : $professional->name;
    @endphp

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <x-ui.card>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex min-w-0 items-center gap-4">
                        <img class="h-14 w-14 rounded-full object-cover" src="{{ $professional->profile_photo_url }}" alt="{{ $fullName }}">
                        <div class="min-w-0">
                            <h2 class="truncate text-xl font-semibold text-slate-950">{{ $fullName }}</h2>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ $professional->residence ?: 'Residenza non indicata' }}
                                <span class="mx-1 text-slate-300">·</span>
                                {{ $professional->email }}
                            </p>
                            @if ($professional->phone)
                                <p class="mt-1 text-sm text-slate-600">{{ $professional->phone }}</p>
                            @endif
                        </div>
                    </div>
                    <x-ui.badge variant="info">{{ $application->statusLabel() }}</x-ui.badge>
                </div>

                <dl class="mt-5 grid gap-4 border-t border-slate-200 pt-5 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Candidatura</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $application->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sede</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $application->jobPosting->workplace_address }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reparto</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $application->jobPosting->businessDepartment?->name ?: 'Non specificato' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-base font-semibold text-slate-950">Esperienze lavorative</h3>
                    <span class="text-xs font-medium text-slate-500">{{ $workExperiences->count() }} elementi</span>
                </div>
                @if ($workExperiences->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">Nessuna esperienza indicata.</p>
                @else
                    <div class="mt-4 divide-y divide-slate-200">
                        @foreach ($workExperiences as $item)
                            <div class="grid gap-1 py-3 first:pt-0 last:pb-0 sm:grid-cols-[minmax(0,1fr)_10rem]">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $item->title }}</p>
                                    @if ($item->description)
                                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $item->description }}</p>
                                    @endif
                                </div>
                                <p class="text-sm text-slate-500 sm:text-right">{{ $item->duration ?: 'Durata non indicata' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-base font-semibold text-slate-950">Formazione</h3>
                    <span class="text-xs font-medium text-slate-500">{{ $educationItems->count() }} elementi</span>
                </div>
                @if ($educationItems->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">Nessun percorso di studio indicato.</p>
                @else
                    <div class="mt-4 divide-y divide-slate-200">
                        @foreach ($educationItems as $item)
                            <div class="grid gap-1 py-3 first:pt-0 last:pb-0 sm:grid-cols-[minmax(0,1fr)_10rem]">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $item->title }}</p>
                                    @if ($item->description)
                                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $item->description }}</p>
                                    @endif
                                </div>
                                <p class="text-sm text-slate-500 sm:text-right">{{ $item->duration ?: 'Durata non indicata' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-base font-semibold text-slate-950">Attestati Moodle</h3>
                    <span class="text-xs font-medium text-slate-500">{{ $professional->certificates->count() }} attestati</span>
                </div>
                @if ($professional->certificates->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">Nessun attestato sincronizzato.</p>
                @else
                    <div class="mt-4 divide-y divide-slate-200">
                        @foreach ($professional->certificates as $certificate)
                            <div class="grid gap-2 py-3 first:pt-0 last:pb-0 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_8rem] lg:items-center">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $certificate->certificate_name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $certificate->course_fullname ?: 'Corso non indicato' }}</p>
                                </div>
                                <p class="text-sm text-slate-600">Codice {{ $certificate->certificate_code ?: 'non disponibile' }}</p>
                                <p class="text-sm text-slate-500 lg:text-right">{{ $certificate->issued_at?->format('d/m/Y') ?: 'Data assente' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </div>

        <aside class="space-y-6">
            <x-ui.card>
                <h3 class="text-base font-semibold text-slate-950">Workflow HR</h3>
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
                <h3 class="text-base font-semibold text-slate-950">Documenti professionali</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-600">Attestato ATA</dt>
                        <dd class="font-semibold text-slate-900">{{ $document?->ata_certificate_path ? 'Disponibile' : 'Non caricato' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-slate-200 pt-3">
                        <dt class="text-slate-600">Permesso di soggiorno</dt>
                        <dd class="font-semibold text-slate-900">{{ $document?->residence_permit_path ? 'Disponibile' : 'Non caricato' }}</dd>
                    </div>
                </dl>
                <p class="mt-4 text-xs leading-5 text-slate-500">La scheda mostra la disponibilità dei documenti. L’accesso ai file sarà gestito nel prossimo blocco con autorizzazioni dedicate.</p>
            </x-ui.card>

            <x-ui.card>
                <h3 class="text-base font-semibold text-slate-950">Dati personali</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nazionalità</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $professional->nationality ?: 'Non indicata' }}</dd>
                    </div>
                    <div class="border-t border-slate-200 pt-3">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Indirizzo</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            {{ collect([$professional->street_address, $professional->postal_code, $professional->address_city, $professional->address_province, $professional->address_country])->filter()->implode(', ') ?: 'Non indicato' }}
                        </dd>
                    </div>
                </dl>
            </x-ui.card>
        </aside>
    </div>
</x-app-layout>
