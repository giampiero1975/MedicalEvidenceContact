<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard professionista
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Gestisci documenti, profilo professionale e candidature.
                </p>
            </div>

            <a href="{{ route('job-postings.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50">
                Vai agli annunci
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @session('status')
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm font-medium text-green-700">
                    {{ $value }}
                </div>
            @endsession
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
                            <h3 class="text-lg font-semibold text-gray-900">Esperienze e percorsi di studio</h3>
                            <p class="mt-1 text-sm text-gray-600">Aggiungi le informazioni che saranno visibili ai business quando valuteranno una tua candidatura.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('professional-profile-items.store') }}" class="mt-5 grid gap-4 lg:grid-cols-[180px_1fr_180px]">
                        @csrf

                        <div>
                            <x-label for="profile_item_type" value="Tipo" />
                            <select id="profile_item_type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="work_experience" @selected(old('type') === 'work_experience')>Esperienza lavorativa</option>
                                <option value="education" @selected(old('type') === 'education')>Percorso di studio</option>
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
                            <x-label for="profile_item_description" value="Testo" />
                            <textarea id="profile_item_description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        </div>

                        <div class="lg:col-span-3 flex justify-end border-t border-gray-100 pt-5">
                            <x-button>
                                Aggiungi al profilo
                            </x-button>
                        </div>
                    </form>

                    @php
                    $profileItems = auth()->user()->professionalProfileItems()->latest()->get();
                    @endphp

                    @if ($profileItems->isNotEmpty())
                        <div class="mt-6 grid gap-3">
                            @foreach ($profileItems as $item)
                                <article class="rounded-md border border-gray-200 p-4">
                                    <form method="POST" action="{{ route('professional-profile-items.update', $item) }}" class="grid gap-4 lg:grid-cols-[180px_1fr_180px]">
                                        @csrf
                                        @method('PUT')

                                        <div>
                                            <x-label for="profile_item_type_{{ $item->id }}" value="Tipo" />
                                            <select id="profile_item_type_{{ $item->id }}" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                <option value="work_experience" @selected(old('type', $item->type) === 'work_experience')>Esperienza lavorativa</option>
                                                <option value="education" @selected(old('type', $item->type) === 'education')>Percorso di studio</option>
                                            </select>
                                        </div>

                                        <div>
                                            <x-label for="profile_item_title_{{ $item->id }}" value="Titolo" />
                                            <x-input id="profile_item_title_{{ $item->id }}" class="mt-1 block w-full" type="text" name="title" :value="old('title', $item->title)" required />
                                        </div>

                                        <div>
                                            <x-label for="profile_item_duration_{{ $item->id }}" value="Durata" />
                                            <x-input id="profile_item_duration_{{ $item->id }}" class="mt-1 block w-full" type="text" name="duration" :value="old('duration', $item->duration)" required />
                                        </div>

                                        <div class="lg:col-span-3">
                                            <x-label for="profile_item_description_{{ $item->id }}" value="Testo" />
                                            <textarea id="profile_item_description_{{ $item->id }}" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $item->description) }}</textarea>
                                        </div>

                                        <div class="lg:col-span-3 flex justify-end border-t border-gray-100 pt-4">
                                            <x-button>
                                                Salva modifiche
                                            </x-button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('professional-profile-items.destroy', $item) }}" class="mt-3 flex justify-end" onsubmit="return confirm('Eliminare questo elemento dal profilo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500">
                                            Elimina
                                        </button>
                                    </form>
                                </article>
                            @endforeach
                        </div>
                    @endif
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
                </section>        </div>
    </div>
</x-app-layout>