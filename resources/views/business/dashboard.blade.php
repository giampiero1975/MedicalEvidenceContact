<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Dashboard Business"
            subtitle="Monitora recruiting, candidature e colloqui della tua struttura."
        >
            <x-slot name="actions">
                <x-ui.button :href="route('job-postings.create')">Pubblica annuncio</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicatori recruiting">
            <x-ui.kpi-card
                label="Annunci attivi"
                :value="$metrics['active_postings']"
                detail="Opportunità attualmente pubblicate"
                icon="briefcase"
                tone="teal"
                :href="route('job-postings.index', ['status' => 'active'])"
            />
            <x-ui.kpi-card
                label="Candidature"
                :value="$metrics['applications']"
                detail="Totale candidature ricevute"
                icon="users"
                tone="blue"
                :href="route('job-postings.index')"
            />
            <x-ui.kpi-card
                label="Colloqui programmati"
                :value="$metrics['interviews']"
                :detail="$alerts['interviews_today'].' previsti oggi'"
                icon="calendar"
                tone="amber"
                :href="route('interviews.index')"
            />
            <x-ui.kpi-card
                label="Assunti"
                :value="$metrics['hired']"
                detail="Candidati arrivati a fine pipeline"
                icon="check"
                tone="green"
            />
        </section>

        @if(collect($alerts)->sum() > 0)
            <section class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 ring-1 ring-amber-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.5v4m0 3h.01M10.3 4.8L3.5 17a1.5 1.5 0 001.31 2.25h14.38A1.5 1.5 0 0020.5 17L13.7 4.8a1.95 1.95 0 00-3.4 0z" /></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="font-semibold text-amber-950">Attività che richiedono attenzione</h2>
                        <div class="mt-3 grid gap-2 text-sm text-amber-900 md:grid-cols-3">
                            <p><span class="font-semibold">{{ $alerts['stale_applications'] }}</span> candidature ferme da più di 7 giorni</p>
                            <p><span class="font-semibold">{{ $alerts['interviews_today'] }}</span> colloqui previsti oggi</p>
                            <p><span class="font-semibold">{{ $alerts['expiring_postings'] }}</span> annunci in scadenza entro 7 giorni</p>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,0.65fr)]">
            <div class="space-y-6">
                <x-ui.card>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Pipeline HR</h2>
                            <p class="mt-1 text-sm text-slate-500">Distribuzione aggiornata delle candidature per fase.</p>
                        </div>
                        <x-ui.button variant="secondary" size="sm" :href="route('job-postings.index')">Gestisci annunci</x-ui.button>
                    </div>

                    @php
                        $pipelineMax = max(1, (int) $pipeline->max('count'));
                    @endphp

                    <div class="mt-6 space-y-4">
                        @foreach($pipeline as $status => $step)
                            @php
                                $width = (int) round(($step['count'] / $pipelineMax) * 100);
                                $isPositive = in_array($status, ['idoneo', 'assunto'], true);
                                $isClosed = in_array($status, ['non_idoneo', 'ritirata'], true);
                            @endphp
                            <div>
                                <div class="flex items-center justify-between gap-4 text-sm">
                                    <span class="font-medium text-slate-700">{{ $step['label'] }}</span>
                                    <span class="font-semibold text-slate-950">{{ $step['count'] }}</span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $isPositive ? 'bg-emerald-600' : ($isClosed ? 'bg-slate-400' : 'bg-teal-600') }}" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Candidature recenti</h2>
                            <p class="mt-1 text-sm text-slate-500">Gli ultimi profili entrati nel processo di selezione.</p>
                        </div>
                    </div>

                    <div class="mt-5 divide-y divide-slate-200">
                        @forelse($recentApplications as $application)
                            @php
                                $professional = $application->professional;
                                $fullName = $professional->first_name && $professional->last_name
                                    ? $professional->first_name.' '.$professional->last_name
                                    : $professional->name;
                            @endphp
                            <a href="{{ route('business.applications.show', $application) }}" class="group flex items-center gap-4 py-4 first:pt-0 last:pb-0">
                                <img class="h-11 w-11 rounded-xl object-cover ring-1 ring-slate-200" src="{{ $professional->profile_photo_url }}" alt="{{ $fullName }}">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="truncate font-semibold text-slate-900 group-hover:text-teal-700">{{ $fullName }}</p>
                                        <x-ui.badge variant="secondary">{{ $application->statusLabel() }}</x-ui.badge>
                                    </div>
                                    <p class="mt-1 truncate text-sm text-slate-500">{{ $application->jobPosting->title }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-xs font-medium text-slate-500">{{ $application->created_at->format('d/m/Y') }}</p>
                                    <svg class="ml-auto mt-2 h-4 w-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                </div>
                            </a>
                        @empty
                            <x-ui.empty-state title="Nessuna candidatura ricevuta" description="Le nuove candidature compariranno qui appena un professionista risponderà a un annuncio." />
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            <aside class="space-y-6">
                <x-ui.card>
                    <h2 class="text-lg font-semibold text-slate-950">Azioni rapide</h2>
                    <p class="mt-1 text-sm text-slate-500">Le operazioni più frequenti della tua struttura.</p>
                    <div class="mt-5 grid gap-3">
                        <x-ui.button :href="route('job-postings.create')">Pubblica un nuovo annuncio</x-ui.button>
                        <x-ui.button variant="secondary" :href="route('job-postings.index')">Gestisci gli annunci</x-ui.button>
                        <x-ui.button variant="secondary" :href="route('interviews.index')">Apri calendario colloqui</x-ui.button>
                        <x-ui.button variant="secondary" :href="route('business.locations.index')">Gestisci sedi e reparti</x-ui.button>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Prossimi colloqui</h2>
                            <p class="mt-1 text-sm text-slate-500">Agenda recruiting in ordine cronologico.</p>
                        </div>
                        <x-ui.badge variant="secondary">{{ $upcomingInterviews->count() }}</x-ui.badge>
                    </div>

                    <div class="mt-5 divide-y divide-slate-200">
                        @forelse($upcomingInterviews as $interview)
                            @php
                                $professional = $interview->jobApplication->professional;
                                $fullName = $professional->first_name && $professional->last_name
                                    ? $professional->first_name.' '.$professional->last_name
                                    : $professional->name;
                            @endphp
                            <a href="{{ route('business.applications.show', $interview->jobApplication) }}" class="group block py-4 first:pt-0 last:pb-0">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-xl bg-teal-50 text-teal-800 ring-1 ring-teal-100">
                                        <span class="text-[10px] font-semibold uppercase">{{ $interview->scheduled_at->translatedFormat('M') }}</span>
                                        <span class="text-sm font-bold leading-none">{{ $interview->scheduled_at->format('d') }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-semibold text-slate-900 group-hover:text-teal-700">{{ $fullName }}</p>
                                        <p class="mt-1 truncate text-sm text-slate-500">{{ $interview->jobApplication->jobPosting->title }}</p>
                                        <p class="mt-2 text-xs font-medium text-slate-600">{{ $interview->scheduled_at->format('H:i') }} · {{ $interview->modeLabel() }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-slate-500">Nessun colloquio futuro programmato.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </aside>
        </div>
    </div>
</x-app-layout>
