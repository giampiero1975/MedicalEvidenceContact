<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Candidature ricevute"
            :description="$jobPosting->title"
        >
            <x-slot name="actions">
                <x-ui.button href="{{ route('job-postings.index') }}" variant="secondary" size="sm">
                    Torna agli annunci
                </x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-6">
        <x-ui.card>
            <dl class="grid gap-4 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Posizione</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $jobPosting->title }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sede</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $jobPosting->workplace_address }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Candidature</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $applications->count() }}</dd>
                </div>
            </dl>
        </x-ui.card>

        @if ($applications->isEmpty())
            <x-ui.empty-state
                title="Nessuna candidatura ricevuta"
                description="Quando un professionista invierà la candidatura, comparirà qui."
            />
        @else
            <div class="space-y-3">
                @foreach ($applications as $application)
                    @php
                        $professional = $application->professional;
                        $workExperiences = $professional->professionalProfileItems->where('type', 'work_experience');
                        $educationItems = $professional->professionalProfileItems->where('type', 'education');
                        $latestWork = $workExperiences->first();
                        $latestEducation = $educationItems->first();
                    @endphp

                    <article class="rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_minmax(520px,1fr)] xl:items-center">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="truncate text-base font-semibold text-slate-950">
                                        {{ $professional->first_name && $professional->last_name ? $professional->first_name.' '.$professional->last_name : $professional->name }}
                                    </h3>
                                    <x-ui.badge variant="info">{{ $application->statusLabel() }}</x-ui.badge>
                                </div>

                                <p class="mt-1 text-sm text-slate-600">
                                    {{ $professional->residence ?: 'Residenza non indicata' }}
                                    <span class="mx-1 text-slate-300">·</span>
                                    Candidatura del {{ $application->created_at->format('d/m/Y') }}
                                </p>

                                <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500">
                                    <span><strong class="font-semibold text-slate-700">Esperienza:</strong> {{ $latestWork?->title ?: 'Non indicata' }}</span>
                                    <span><strong class="font-semibold text-slate-700">Formazione:</strong> {{ $latestEducation?->title ?: 'Non indicata' }}</span>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('job-applications.status.update', $application) }}" class="grid gap-3 sm:grid-cols-[minmax(250px,1fr)_auto] sm:items-end">
                                @csrf
                                @method('PATCH')

                                <x-ui.select name="status" label="Stato candidatura">
                                    @foreach (\App\Models\JobApplication::statusOptions() as $value => $label)
                                        <option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>

                                <x-ui.button type="submit" size="sm">Aggiorna stato</x-ui.button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
