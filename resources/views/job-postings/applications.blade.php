<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Candidature ricevute
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $jobPosting->title }}
                </p>
            </div>

            <a href="{{ route('job-postings.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50">
                Torna agli annunci
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <section class="mb-6 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <div class="grid gap-4 text-sm text-gray-700 sm:grid-cols-3">
                    <div>
                        <p class="font-semibold text-gray-900">Posizione</p>
                        <p class="mt-1">{{ $jobPosting->title }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Sede</p>
                        <p class="mt-1">{{ $jobPosting->workplace_address }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Candidature</p>
                        <p class="mt-1">{{ $applications->count() }}</p>
                    </div>
                </div>
            </section>

            @if ($applications->isEmpty())
                <section class="rounded-lg border border-dashed border-gray-300 bg-white p-10 text-center">
                    <h3 class="text-lg font-semibold text-gray-900">Nessuna candidatura ricevuta</h3>
                    <p class="mt-2 text-sm text-gray-600">Quando un professionista accettera questa posizione, comparira qui.</p>
                </section>
            @else
                <div class="grid gap-4">
                    @foreach ($applications as $application)
                        @php
                            $professional = $application->professional;
                        @endphp

                        <article class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $professional->first_name && $professional->last_name ? $professional->first_name.' '.$professional->last_name : $professional->name }}
                                    </h3>
                                    <dl class="mt-3 grid gap-3 text-sm text-gray-700 sm:grid-cols-2">
                                        <div>
                                            <dt class="font-semibold text-gray-900">Profilo</dt>
                                            <dd class="mt-1">Professionista</dd>
                                        </div>
                                        <div>
                                            <dt class="font-semibold text-gray-900">Residenza</dt>
                                            <dd class="mt-1">{{ $professional->residence ?: 'Non indicata' }}</dd>
                                        </div>
                                    </dl>

                                    @php
                                        $workExperiences = $professional->professionalProfileItems->where('type', 'work_experience');
                                        $educationItems = $professional->professionalProfileItems->where('type', 'education');
                                    @endphp

                                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-900">Esperienze lavorative</h4>
                                            @if ($workExperiences->isEmpty())
                                                <p class="mt-2 text-sm text-gray-600">Non indicate.</p>
                                            @else
                                                <div class="mt-2 grid gap-3">
                                                    @foreach ($workExperiences as $item)
                                                        <div class="rounded-md border border-gray-200 p-3">
                                                            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
                                                                <p class="font-semibold text-gray-900">{{ $item->title }}</p>
                                                                <span class="text-sm text-gray-600">{{ $item->duration }}</span>
                                                            </div>
                                                            @if ($item->description)
                                                                <p class="mt-2 text-sm leading-6 text-gray-600">{{ $item->description }}</p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-900">Percorsi di studio</h4>
                                            @if ($educationItems->isEmpty())
                                                <p class="mt-2 text-sm text-gray-600">Non indicati.</p>
                                            @else
                                                <div class="mt-2 grid gap-3">
                                                    @foreach ($educationItems as $item)
                                                        <div class="rounded-md border border-gray-200 p-3">
                                                            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
                                                                <p class="font-semibold text-gray-900">{{ $item->title }}</p>
                                                                <span class="text-sm text-gray-600">{{ $item->duration }}</span>
                                                            </div>
                                                            @if ($item->description)
                                                                <p class="mt-2 text-sm leading-6 text-gray-600">{{ $item->description }}</p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col items-start gap-2 sm:items-end">
                                    <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                        {{ str_replace('_', ' ', $application->status) }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        {{ $application->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-6 rounded-md border border-indigo-100 bg-indigo-50/60 p-4">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900">Fissa colloquio</h4>
                                        <p class="mt-1 text-sm leading-6 text-gray-600">
                                            Prepara gli slot da proporre al professionista. Nel prossimo step questa azione inviera l'invito e cambiera lo stato della candidatura.
                                        </p>
                                    </div>
                                    <span class="inline-flex w-fit rounded-full bg-white px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-100">
                                        Frontend preview
                                    </span>
                                </div>

                                <div class="mt-4 grid gap-3 lg:grid-cols-3">
                                    <label class="block">
                                        <span class="text-sm font-semibold text-gray-800">Data</span>
                                        <input type="date" class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-semibold text-gray-800">Ora inizio</span>
                                        <input type="time" class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-semibold text-gray-800">Ora fine</span>
                                        <input type="time" class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                                    </label>
                                    <label class="block lg:col-span-2">
                                        <span class="text-sm font-semibold text-gray-800">Modalita colloquio</span>
                                        <select class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                                            <option>Videochiamata</option>
                                            <option>In presenza</option>
                                            <option>Telefono</option>
                                        </select>
                                    </label>
                                    <div class="flex items-end">
                                        <button type="button" class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white opacity-60" disabled>
                                            Proponi slot
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-3 md:grid-cols-3">
                                    <div class="rounded-md bg-white p-3 ring-1 ring-indigo-100">
                                        <p class="text-xs font-semibold uppercase text-gray-500">Slot esempio</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900">Martedi 10:00 - 10:30</p>
                                        <p class="mt-1 text-sm text-gray-600">Videochiamata</p>
                                    </div>
                                    <div class="rounded-md bg-white p-3 ring-1 ring-indigo-100">
                                        <p class="text-xs font-semibold uppercase text-gray-500">Slot esempio</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900">Mercoledi 15:00 - 15:45</p>
                                        <p class="mt-1 text-sm text-gray-600">In presenza</p>
                                    </div>
                                    <div class="rounded-md bg-white p-3 ring-1 ring-indigo-100">
                                        <p class="text-xs font-semibold uppercase text-gray-500">Stato previsto</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900">Invito a colloquio inviato</p>
                                        <p class="mt-1 text-sm text-gray-600">Contatti ancora nascosti.</p>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
