<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ $role === 'business' ? 'I tuoi annunci' : 'Annunci disponibili' }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $role === 'business' ? 'Gestisci le opportunita pubblicate dalla tua azienda.' : 'Consulta le opportunita attive pubblicate dalle aziende.' }}
                </p>
            </div>

            @if ($role === 'business')
                <a href="{{ route('job-postings.create') }}" class="inline-flex items-center justify-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Crea annuncio
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @session('status')
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm font-medium text-green-700">
                    {{ $value }}
                </div>
            @endsession

            @if ($role === 'professional')
                @php
                    $nationality = strtolower(trim(auth()->user()->nationality ?? ''));
                    $isItalian = in_array($nationality, ['italiana', 'italiano', 'italia', 'italian'], true);
                @endphp

                <section class="mb-8 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Documenti professionali</h3>
                            <p class="mt-1 text-sm text-gray-600">Carica attestato ATA e, se richiesto dalla nazionalita, il permesso di soggiorno.</p>
                        </div>
                    </div>

                    <x-validation-errors class="mt-5" />

                    <form method="POST" action="{{ route('professional-documents.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-5 lg:grid-cols-2">
                        @csrf

                        <div class="rounded-md border border-gray-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <x-label for="ata_certificate_document" value="Attestato ATA" />
                                    <p class="mt-1 text-sm text-gray-600">PDF, JPG o PNG fino a 5 MB.</p>
                                </div>
                                @if (auth()->user()->ata_certificate_path)
                                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">Caricato</span>
                                @endif
                            </div>
                            <input id="ata_certificate_document" type="file" name="ata_certificate_document" accept=".pdf,.jpg,.jpeg,.png" class="mt-4 block w-full text-sm text-gray-700" />
                        </div>

                        @if (! $isItalian)
                            <div class="rounded-md border border-gray-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <x-label for="residence_permit_document" value="Permesso di soggiorno" />
                                        <p class="mt-1 text-sm text-gray-600">Richiesto per nazionalita diversa da italiana.</p>
                                    </div>
                                    @if (auth()->user()->residence_permit_path)
                                        <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">Caricato</span>
                                    @endif
                                </div>
                                <input id="residence_permit_document" type="file" name="residence_permit_document" accept=".pdf,.jpg,.jpeg,.png" class="mt-4 block w-full text-sm text-gray-700" />
                            </div>
                        @endif

                        <div class="lg:col-span-2 flex justify-end border-t border-gray-100 pt-5">
                            <x-button>
                                Salva documenti
                            </x-button>
                        </div>
                    </form>
                </section>

                <section class="mb-8 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Le tue candidature</h3>
                            <p class="mt-1 text-sm text-gray-600">Gli annunci a cui hai scelto di candidarti restano qui in evidenza.</p>
                        </div>
                        <span class="text-sm font-semibold text-gray-700">{{ $acceptedJobApplications->count() }} candidature</span>
                    </div>

                    @if ($acceptedJobApplications->isEmpty())
                        <div class="mt-5 rounded-md border border-dashed border-gray-300 p-5 text-sm text-gray-600">
                            Non hai ancora accettato nessun annuncio.
                        </div>
                    @else
                        <div class="mt-5 grid gap-3">
                            @foreach ($acceptedJobApplications as $application)
                                <div class="flex flex-col gap-3 rounded-md border border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $application->jobPosting->title }}</p>
                                        <p class="mt-1 text-sm text-gray-600">{{ $application->jobPosting->contract_type }} · {{ $application->jobPosting->workplace_address }}</p>
                                    </div>
                                    <span class="inline-flex w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                        {{ str_replace('_', ' ', $application->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            @if ($jobPostings->isEmpty())
                <section class="rounded-lg border border-dashed border-gray-300 bg-white p-10 text-center">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $role === 'business' ? 'Nessun annuncio pubblicato' : 'Nessun annuncio attivo' }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ $role === 'business' ? 'Crea il primo annuncio per iniziare a ricevere candidature.' : 'Torna piu tardi: qui compariranno gli annunci attivi delle aziende.' }}
                    </p>
                    @if ($role === 'business')
                        <a href="{{ route('job-postings.create') }}" class="mt-5 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                            Pubblica annuncio
                        </a>
                    @endif
                </section>
            @else
                <div class="grid gap-5">
                    @foreach ($jobPostings as $jobPosting)
                        <article class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <a href="{{ route('job-postings.show', $jobPosting) }}" class="hover:text-indigo-700">
                                                {{ $jobPosting->title }}
                                            </a>
                                        </h3>
                                        <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                                            {{ $jobPosting->status === 'active' ? 'Attivo' : 'Scaduto' }}
                                        </span>
                                    </div>
                                    <p class="mt-2 line-clamp-3 text-sm leading-6 text-gray-600">{{ $jobPosting->description }}</p>
                                </div>

                                <dl class="grid min-w-72 grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-md bg-gray-50 p-3">
                                        <dt class="font-semibold text-gray-900">Posizioni</dt>
                                        <dd class="mt-1 text-gray-600">{{ $jobPosting->positions }}</dd>
                                    </div>
                                    <div class="rounded-md bg-gray-50 p-3">
                                        <dt class="font-semibold text-gray-900">Contratto</dt>
                                        <dd class="mt-1 text-gray-600">{{ $jobPosting->contract_type }}</dd>
                                    </div>
                                    <div class="col-span-2 rounded-md bg-gray-50 p-3">
                                        <dt class="font-semibold text-gray-900">Sede</dt>
                                        <dd class="mt-1 text-gray-600">{{ $jobPosting->workplace_address }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="mt-5 grid gap-4 border-t border-gray-100 pt-5 text-sm text-gray-700 sm:grid-cols-3">
                                <div>
                                    <p class="font-semibold text-gray-900">Retribuzione</p>
                                    <p class="mt-1">
                                        @if ($jobPosting->salary_min || $jobPosting->salary_max)
                                            {{ $jobPosting->salary_min ? '€ '.number_format((float) $jobPosting->salary_min, 0, ',', '.') : 'Da definire' }}
                                            -
                                            {{ $jobPosting->salary_max ? '€ '.number_format((float) $jobPosting->salary_max, 0, ',', '.') : 'Da definire' }}
                                        @else
                                            Da definire
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Scadenza</p>
                                    <p class="mt-1">{{ $jobPosting->expires_at->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Abilita richieste</p>
                                    <p class="mt-1">{{ $jobPosting->required_skills ?: 'Non specificate' }}</p>
                                </div>
                            </div>

                            @if ($role === 'professional')
                                <div class="mt-5 flex justify-end border-t border-gray-100 pt-5">
                                    @if ($jobPosting->applications->isNotEmpty())
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('job-postings.show', $jobPosting) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                                Dettaglio
                                            </a>
                                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700">
                                                Candidatura {{ str_replace('_', ' ', $jobPosting->applications->first()->status) }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('job-postings.show', $jobPosting) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                                Dettaglio
                                            </a>
                                            <form method="POST" action="{{ route('job-applications.store', $jobPosting) }}">
                                                @csrf
                                                <x-button>
                                                    Candidati
                                                </x-button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="mt-5 flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-5">
                                    <a href="{{ route('job-postings.show', $jobPosting) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                        Dettaglio
                                    </a>
                                    <a href="{{ route('job-postings.edit', $jobPosting) }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                                        Modifica
                                    </a>
                                    <a href="{{ route('job-postings.applications', $jobPosting) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                        Vedi candidature
                                    </a>
                                    <form method="POST" action="{{ route('job-postings.destroy', $jobPosting) }}" onsubmit="return confirm('Eliminare questo annuncio?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500">
                                            Elimina
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $jobPostings->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
