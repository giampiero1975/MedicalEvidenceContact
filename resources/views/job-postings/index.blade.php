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

            <section class="mb-6 rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <form method="GET" action="{{ route('job-postings.index') }}" class="grid gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-3">
                        <x-label for="keyword" value="Keyword" />
                        <x-input id="keyword" class="mt-1 block w-full" type="search" name="keyword" :value="$filters['keyword'] ?? ''" placeholder="Titolo, descrizione, competenze" />
                    </div>

                    <div class="lg:col-span-3">
                        <x-label for="location" value="Localita" />
                        <x-input id="location" class="mt-1 block w-full" type="search" name="location" :value="$filters['location'] ?? ''" placeholder="Citta, provincia, sede" />
                    </div>

                    <div class="lg:col-span-3">
                        <x-label for="contract_type" value="Tipo contratto" />
                        <select id="contract_type" name="contract_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Tutti</option>
                            @foreach ($contractTypes as $contractType)
                                <option value="{{ $contractType }}" @selected(($filters['contract_type'] ?? '') === $contractType)>{{ $contractType }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <x-label for="professional_category" value="Categoria professionale" />
                        <x-input id="professional_category" class="mt-1 block w-full" type="search" name="professional_category" :value="$filters['professional_category'] ?? ''" placeholder="OSS, Infermiere, Fisioterapista" />
                    </div>

                    <div class="lg:col-span-3">
                        <x-label for="company_category" value="Categoria azienda" />
                        <x-input id="company_category" class="mt-1 block w-full" type="search" name="company_category" :value="$filters['company_category'] ?? ''" placeholder="RSA, clinica, farmacia" />
                    </div>

                    <div class="lg:col-span-2">
                        <x-label for="salary_min" value="Retribuzione da" />
                        <x-input id="salary_min" class="mt-1 block w-full" type="number" min="0" step="100" name="salary_min" :value="$filters['salary_min'] ?? ''" />
                    </div>

                    <div class="lg:col-span-2">
                        <x-label for="salary_max" value="Retribuzione a" />
                        <x-input id="salary_max" class="mt-1 block w-full" type="number" min="0" step="100" name="salary_max" :value="$filters['salary_max'] ?? ''" />
                    </div>

                    <div class="lg:col-span-2">
                        <x-label for="published_from" value="Pubblicato da" />
                        <x-input id="published_from" class="mt-1 block w-full" type="date" name="published_from" :value="$filters['published_from'] ?? ''" />
                    </div>

                    <div class="lg:col-span-2">
                        <x-label for="published_to" value="Pubblicato a" />
                        <x-input id="published_to" class="mt-1 block w-full" type="date" name="published_to" :value="$filters['published_to'] ?? ''" />
                    </div>

                    @if ($role === 'business')
                        <div class="lg:col-span-1">
                            <x-label for="status" value="Stato" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Tutti</option>
                                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Attivo</option>
                                <option value="expired" @selected(($filters['status'] ?? '') === 'expired')>Scaduto</option>
                            </select>
                        </div>
                    @endif

                    <div class="flex items-end gap-2 lg:col-span-12">
                        <x-button>Filtra</x-button>
                        <a href="{{ route('job-postings.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50">Pulisci</a>
                        <span class="ml-auto text-sm font-semibold text-gray-700">{{ $jobPostings->total() }} risultati</span>
                    </div>
                </form>
            </section>

            @if ($jobPostings->isEmpty())
                <section class="rounded-lg border border-dashed border-gray-300 bg-white p-10 text-center">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $role === 'business' ? 'Nessun annuncio pubblicato' : 'Nessun annuncio attivo' }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ $role === 'business' ? 'Crea il primo annuncio per iniziare a ricevere candidature.' : 'Torna piu tardi: qui compariranno gli annunci attivi delle aziende.' }}</p>
                    @if ($role === 'business')
                        <a href="{{ route('job-postings.create') }}" class="mt-5 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">Pubblica annuncio</a>
                    @endif
                </section>
            @else
                <div class="space-y-3">
                    @foreach ($jobPostings as $jobPosting)
                        <article class="rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                            <div class="grid gap-4 xl:grid-cols-[minmax(0,1.65fr)_minmax(520px,1fr)] xl:items-center">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-base font-semibold text-slate-950">
                                            <a href="{{ route('job-postings.show', $jobPosting) }}" class="hover:text-teal-700">{{ $jobPosting->title }}</a>
                                        </h3>
                                        <x-ui.badge :variant="$jobPosting->status === 'active' ? 'success' : 'warning'">
                                            {{ $jobPosting->status === 'active' ? 'Attivo' : 'Scaduto' }}
                                        </x-ui.badge>
                                    </div>

                                    <p class="mt-1 line-clamp-2 text-sm leading-5 text-slate-600">{{ $jobPosting->description }}</p>

                                    @if ($jobPosting->required_skills)
                                        <p class="mt-2 line-clamp-1 text-xs text-slate-500">
                                            <span class="font-semibold text-slate-700">Abilita:</span> {{ $jobPosting->required_skills }}
                                        </p>
                                    @endif
                                </div>

                                <dl class="grid grid-cols-2 gap-x-5 gap-y-3 text-sm sm:grid-cols-3 xl:grid-cols-5">
                                    <div class="min-w-0">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Posizioni</dt>
                                        <dd class="mt-1 font-medium text-slate-900">{{ $jobPosting->positions }}</dd>
                                    </div>
                                    <div class="min-w-0">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Contratto</dt>
                                        <dd class="mt-1 truncate font-medium text-slate-900">{{ $jobPosting->contract_type }}</dd>
                                    </div>
                                    <div class="min-w-0">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Retribuzione</dt>
                                        <dd class="mt-1 whitespace-nowrap font-medium text-slate-900">
                                            @if ($jobPosting->salary_min || $jobPosting->salary_max)
                                                {{ $jobPosting->salary_min ? '€ '.number_format((float) $jobPosting->salary_min, 0, ',', '.') : 'Da definire' }}
                                                –
                                                {{ $jobPosting->salary_max ? '€ '.number_format((float) $jobPosting->salary_max, 0, ',', '.') : 'Da definire' }}
                                            @else
                                                Da definire
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="min-w-0">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Scadenza</dt>
                                        <dd class="mt-1 whitespace-nowrap font-medium text-slate-900">{{ $jobPosting->expires_at->format('d/m/Y') }}</dd>
                                    </div>
                                    <div class="min-w-0 sm:col-span-2 xl:col-span-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sede</dt>
                                        <dd class="mt-1 truncate font-medium text-slate-900" title="{{ $jobPosting->workplace_address }}">{{ $jobPosting->workplace_address }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                                @if ($role === 'professional')
                                    <a href="{{ route('job-postings.show', $jobPosting) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Dettaglio</a>
                                    @if ($jobPosting->applications->isNotEmpty())
                                        <x-ui.badge variant="info">Candidatura {{ str_replace('_', ' ', $jobPosting->applications->first()->status) }}</x-ui.badge>
                                    @else
                                        <form method="POST" action="{{ route('job-applications.store', $jobPosting) }}">
                                            @csrf
                                            <x-ui.button type="submit" size="sm">Candidati</x-ui.button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('job-postings.show', $jobPosting) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Dettaglio</a>
                                    <a href="{{ route('job-postings.edit', $jobPosting) }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">Modifica</a>
                                    <a href="{{ route('job-postings.applications', $jobPosting) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Vedi candidature</a>
                                    <form method="POST" action="{{ route('job-postings.destroy', $jobPosting) }}" onsubmit="return confirm('Eliminare questo annuncio?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-500">Elimina</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">{{ $jobPostings->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
