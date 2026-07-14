<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="$role === 'business' ? 'I tuoi annunci' : 'Annunci disponibili'"
            :subtitle="$role === 'business' ? 'Gestisci le opportunità pubblicate dalla tua azienda.' : 'Consulta le opportunità attive pubblicate dalle aziende.'"
        >
            @if ($role === 'business')
                <x-slot name="actions">
                    <x-ui.button :href="route('job-postings.create')">Crea annuncio</x-ui.button>
                </x-slot>
            @endif
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-6">
        <x-ui.card>
            <form method="GET" action="{{ route('job-postings.index') }}" class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <x-label for="keyword" value="Keyword" />
                    <x-input id="keyword" class="mt-1 block w-full" type="search" name="keyword" :value="$filters['keyword'] ?? ''" placeholder="Titolo, descrizione, competenze" />
                </div>
                <div class="lg:col-span-3">
                    <x-label for="location" value="Località" />
                    <x-input id="location" class="mt-1 block w-full" type="search" name="location" :value="$filters['location'] ?? ''" placeholder="Città, provincia, sede" />
                </div>
                <div class="lg:col-span-3">
                    <x-label for="contract_type" value="Tipo contratto" />
                    <select id="contract_type" name="contract_type" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
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
                        <select id="status" name="status" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                            <option value="">Tutti</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Attivo</option>
                            <option value="expired" @selected(($filters['status'] ?? '') === 'expired')>Scaduto</option>
                        </select>
                    </div>
                @endif
                <div class="flex items-end gap-2 lg:col-span-12">
                    <x-ui.button type="submit" size="sm">Filtra</x-ui.button>
                    <x-ui.button variant="secondary" size="sm" :href="route('job-postings.index')">Pulisci</x-ui.button>
                    <span class="ml-auto text-sm font-semibold text-slate-700">{{ $jobPostings->total() }} risultati</span>
                </div>
            </form>
        </x-ui.card>

        @if ($jobPostings->isEmpty())
            <x-ui.card>
                <x-ui.empty-state
                    :title="$role === 'business' ? 'Nessun annuncio pubblicato' : 'Nessun annuncio attivo'"
                    :description="$role === 'business' ? 'Crea il primo annuncio per iniziare a ricevere candidature.' : 'Torna più tardi: qui compariranno gli annunci attivi delle aziende.'"
                >
                    @if ($role === 'business')
                        <x-ui.button :href="route('job-postings.create')">Pubblica annuncio</x-ui.button>
                    @endif
                </x-ui.empty-state>
            </x-ui.card>
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
                                    <x-ui.badge :variant="$jobPosting->status === 'active' ? 'success' : 'warning'">{{ $jobPosting->status === 'active' ? 'Attivo' : 'Scaduto' }}</x-ui.badge>
                                </div>
                                <p class="mt-1 line-clamp-2 text-sm leading-5 text-slate-600">{{ $jobPosting->description }}</p>
                                @if ($jobPosting->required_skills)
                                    <p class="mt-2 line-clamp-1 text-xs text-slate-500"><span class="font-semibold text-slate-700">Abilità:</span> {{ $jobPosting->required_skills }}</p>
                                @endif
                            </div>

                            <dl class="grid grid-cols-2 gap-x-5 gap-y-3 text-sm sm:grid-cols-3 xl:grid-cols-5">
                                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Posizioni</dt><dd class="mt-1 font-medium text-slate-900">{{ $jobPosting->positions }}</dd></div>
                                <div class="min-w-0"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Contratto</dt><dd class="mt-1 truncate font-medium text-slate-900">{{ $jobPosting->contract_type }}</dd></div>
                                <div class="min-w-0">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Retribuzione</dt>
                                    <dd class="mt-1 whitespace-nowrap font-medium text-slate-900">
                                        @if ($jobPosting->salary_min || $jobPosting->salary_max)
                                            {{ $jobPosting->salary_min ? '€ '.number_format((float) $jobPosting->salary_min, 0, ',', '.') : 'Da definire' }} – {{ $jobPosting->salary_max ? '€ '.number_format((float) $jobPosting->salary_max, 0, ',', '.') : 'Da definire' }}
                                        @else
                                            Da definire
                                        @endif
                                    </dd>
                                </div>
                                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Scadenza</dt><dd class="mt-1 whitespace-nowrap font-medium text-slate-900">{{ $jobPosting->expires_at->format('d/m/Y') }}</dd></div>
                                <div class="min-w-0 sm:col-span-2 xl:col-span-1"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sede</dt><dd class="mt-1 truncate font-medium text-slate-900" title="{{ $jobPosting->workplace_address }}">{{ $jobPosting->workplace_address }}</dd></div>
                            </dl>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                            <x-ui.button variant="secondary" size="sm" :href="route('job-postings.show', $jobPosting)">Dettaglio</x-ui.button>
                            @if ($role === 'professional')
                                @if ($jobPosting->applications->isNotEmpty())
                                    <x-ui.badge>Candidatura {{ str_replace('_', ' ', $jobPosting->applications->first()->status) }}</x-ui.badge>
                                @else
                                    <form method="POST" action="{{ route('job-applications.store', $jobPosting) }}">
                                        @csrf
                                        <x-ui.button type="submit" size="sm">Candidati</x-ui.button>
                                    </form>
                                @endif
                            @else
                                <x-ui.button size="sm" :href="route('job-postings.edit', $jobPosting)">Modifica</x-ui.button>
                                <x-ui.button variant="secondary" size="sm" :href="route('job-postings.applications', $jobPosting)">Vedi candidature</x-ui.button>
                                <form method="POST" action="{{ route('job-postings.destroy', $jobPosting) }}" onsubmit="return confirm('Eliminare questo annuncio?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" size="sm">Elimina</x-ui.button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div>{{ $jobPostings->links() }}</div>
        @endif
    </div>
</x-app-layout>
