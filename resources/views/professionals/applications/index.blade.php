<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Le mie candidature"
            subtitle="Segui lo stato delle candidature inviate e torna rapidamente agli annunci ancora disponibili."
        >
            <x-slot name="actions">
                <x-ui.button :href="route('job-postings.index')">Cerca opportunità</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card
                label="Totale candidature"
                :value="$statusCounts->sum()"
                hint="Tutte le candidature inviate"
            />
            <x-ui.stat-card
                label="In valutazione"
                :value="($statusCounts[\App\Models\JobApplication::STATUS_RECEIVED] ?? 0) + ($statusCounts[\App\Models\JobApplication::STATUS_REVIEW] ?? 0)"
                hint="Ricevute o in revisione"
            />
            <x-ui.stat-card
                label="Colloqui"
                :value="($statusCounts[\App\Models\JobApplication::STATUS_INTERVIEW_SCHEDULED] ?? 0) + ($statusCounts[\App\Models\JobApplication::STATUS_INTERVIEW_COMPLETED] ?? 0)"
                hint="Programmato o effettuato"
            />
            <x-ui.stat-card
                label="Esiti positivi"
                :value="($statusCounts[\App\Models\JobApplication::STATUS_SUITABLE] ?? 0) + ($statusCounts[\App\Models\JobApplication::STATUS_HIRED] ?? 0)"
                hint="Idoneità o assunzione"
            />
        </section>

        <x-ui.card>
            <form method="GET" action="{{ route('professional.applications.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="min-w-0 flex-1">
                    <label for="status" class="block text-sm font-semibold text-slate-800">Filtra per stato</label>
                    <select
                        id="status"
                        name="status"
                        class="mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600"
                    >
                        <option value="">Tutte le candidature</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($selectedStatus === $value)>
                                {{ $label }} ({{ $statusCounts[$value] ?? 0 }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <x-ui.button type="submit" variant="secondary">Applica filtro</x-ui.button>
                    @if ($selectedStatus)
                        <x-ui.button variant="ghost" :href="route('professional.applications.index')">Azzera</x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        @if ($applications->count())
            <div class="space-y-4">
                @foreach ($applications as $application)
                    @php
                        $jobPosting = $application->jobPosting;
                        $isAvailable = $jobPosting
                            && $jobPosting->status === 'active'
                            && $jobPosting->expires_at
                            && $jobPosting->expires_at->toDateString() >= now()->toDateString();

                        $statusVariant = match ($application->status) {
                            \App\Models\JobApplication::STATUS_HIRED,
                            \App\Models\JobApplication::STATUS_SUITABLE => 'success',
                            \App\Models\JobApplication::STATUS_REJECTED => 'danger',
                            \App\Models\JobApplication::STATUS_WITHDRAWN => 'neutral',
                            \App\Models\JobApplication::STATUS_INTERVIEW_SCHEDULED,
                            \App\Models\JobApplication::STATUS_INTERVIEW_COMPLETED => 'primary',
                            default => 'warning',
                        };
                    @endphp

                    <x-ui.card>
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h2 class="text-xl font-semibold text-slate-950">
                                        {{ $jobPosting?->title ?? 'Annuncio non disponibile' }}
                                    </h2>
                                    <x-ui.badge :variant="$statusVariant">{{ $application->statusLabel() }}</x-ui.badge>
                                </div>

                                <p class="mt-2 text-sm text-slate-500">
                                    Candidatura inviata il {{ $application->created_at?->format('d/m/Y H:i') }}
                                </p>

                                @if ($jobPosting)
                                    <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2 xl:grid-cols-4">
                                        <div class="rounded-xl bg-slate-50 p-4">
                                            <dt class="font-semibold text-slate-900">Struttura</dt>
                                            <dd class="mt-1 text-slate-600">{{ $jobPosting->businessProfile?->company_name ?? 'Non disponibile' }}</dd>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 p-4">
                                            <dt class="font-semibold text-slate-900">Sede</dt>
                                            <dd class="mt-1 text-slate-600">
                                                {{ $jobPosting->businessLocation?->name ?? $jobPosting->workplace_address ?? 'Non disponibile' }}
                                            </dd>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 p-4">
                                            <dt class="font-semibold text-slate-900">Contratto</dt>
                                            <dd class="mt-1 text-slate-600">{{ $jobPosting->contract_type ?: 'Non specificato' }}</dd>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 p-4">
                                            <dt class="font-semibold text-slate-900">Annuncio</dt>
                                            <dd class="mt-1 text-slate-600">{{ $isAvailable ? 'Ancora disponibile' : 'Non più disponibile' }}</dd>
                                        </div>
                                    </dl>
                                @endif
                            </div>

                            <div class="flex shrink-0 flex-wrap gap-2 lg:justify-end">
                                @if ($isAvailable)
                                    <x-ui.button variant="secondary" :href="route('job-postings.show', $jobPosting)">
                                        Vedi annuncio
                                    </x-ui.button>
                                @endif

                                @if (in_array($application->status, [\App\Models\JobApplication::STATUS_INTERVIEW_SCHEDULED, \App\Models\JobApplication::STATUS_INTERVIEW_COMPLETED], true))
                                    <x-ui.button variant="ghost" :href="route('interviews.index')">Vedi colloqui</x-ui.button>
                                @endif
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            @if ($applications->hasPages())
                <div>{{ $applications->links() }}</div>
            @endif
        @else
            <x-ui.empty-state
                title="Nessuna candidatura trovata"
                description="Non ci sono candidature compatibili con il filtro selezionato."
            >
                <x-slot name="actions">
                    @if ($selectedStatus)
                        <x-ui.button variant="secondary" :href="route('professional.applications.index')">Mostra tutte</x-ui.button>
                    @else
                        <x-ui.button :href="route('job-postings.index')">Esplora gli annunci</x-ui.button>
                    @endif
                </x-slot>
            </x-ui.empty-state>
        @endif
    </div>
</x-app-layout>
