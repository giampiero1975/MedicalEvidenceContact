<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Colloqui" :description="$role === 'business' ? 'Pianifica e controlla i colloqui con i candidati.' : 'Gestisci inviti, conferme e consenso alla condivisione dei contatti.'">
            <x-slot name="actions"><x-ui.button href="{{ route('dashboard') }}" variant="secondary" size="sm">Torna alla dashboard</x-ui.button></x-slot>
        </x-ui.page-header>
    </x-slot>

    @if ($role === 'business')
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-4">
                <x-ui.card>
                    <div class="flex items-center justify-between gap-4"><div><h3 class="text-base font-semibold text-slate-950">Candidature da pianificare</h3><p class="mt-1 text-sm text-slate-600">Apri la scheda candidatura per programmare un colloquio.</p></div><span class="text-sm font-semibold text-slate-700">{{ $businessJobPostings->sum('applications_count') }} candidature</span></div>
                    <div class="mt-4 divide-y divide-slate-200">
                        @forelse ($businessJobPostings as $jobPosting)
                            <div class="py-4 first:pt-0 last:pb-0">
                                <p class="font-semibold text-slate-900">{{ $jobPosting->title }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $jobPosting->workplace_address }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($jobPosting->applications as $application)
                                        @php($professional = $application->professional)
                                        <x-ui.button href="{{ route('business.applications.show', $application) }}" variant="secondary" size="sm">{{ $professional->first_name && $professional->last_name ? $professional->first_name.' '.$professional->last_name : $professional->name }}</x-ui.button>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Nessuna candidatura disponibile.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            <aside class="space-y-4">
                <x-ui.card>
                    <h3 class="text-base font-semibold text-slate-950">Nuovo invito a colloquio</h3>
                    <p class="mt-2 text-sm text-slate-600">La programmazione avviene dalla scheda del candidato.</p>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">Slot proposti</p>
                    <x-ui.button href="{{ route('job-postings.index') }}" class="mt-3 w-full">Invia invito</x-ui.button>
                </x-ui.card>
                <x-ui.card>
                    <h3 class="text-base font-semibold text-slate-950">Colloqui programmati</h3>
                    <div class="mt-4 divide-y divide-slate-200">
                        @forelse ($interviews as $interview)
                            <div class="py-3 first:pt-0 last:pb-0"><p class="font-semibold text-slate-900">{{ $interview->jobApplication->professional->name }}</p><p class="mt-1 text-sm text-slate-600">{{ $interview->scheduled_at->format('d/m/Y H:i') }} · {{ $interview->statusLabel() }}</p></div>
                        @empty
                            <p class="text-sm text-slate-500">Nessun colloquio programmato.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </aside>
        </div>
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-4">
                <x-ui.card>
                    <h3 class="text-base font-semibold text-slate-950">Inviti e candidature</h3>
                    <div class="mt-4 divide-y divide-slate-200">
                        @forelse ($professionalApplications as $application)
                            <div class="py-3 first:pt-0 last:pb-0"><p class="font-semibold text-slate-900">{{ $application->jobPosting->title }}</p><p class="mt-1 text-sm text-slate-500">{{ $application->jobPosting->workplace_address }}</p></div>
                        @empty
                            <p class="text-sm text-slate-500">Non hai ancora candidature.</p>
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <h3 class="text-base font-semibold text-slate-950">Rispondi a un invito</h3>
                    <div class="mt-4 space-y-4">
                        @forelse ($interviews as $interview)
                            <article class="rounded-xl border border-slate-200 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div><p class="font-semibold text-slate-950">{{ $interview->jobApplication->jobPosting->title }}</p><p class="mt-1 text-sm text-slate-600">{{ $interview->scheduled_at->format('d/m/Y H:i') }} · {{ $interview->modeLabel() }}</p>@if($interview->location)<p class="mt-1 text-xs text-slate-500">{{ $interview->location }}</p>@endif</div>
                                    <x-ui.badge variant="info">{{ $interview->statusLabel() }}</x-ui.badge>
                                </div>
                                @if ($interview->status === 'scheduled')
                                    <form method="POST" action="{{ route('professional.interviews.respond', $interview) }}" class="mt-4 space-y-3">@csrf @method('PATCH')
                                        <label class="flex items-start gap-3 rounded-xl bg-slate-50 p-3"><input type="checkbox" name="contact_sharing_consent" value="1" class="mt-1 rounded border-slate-300 text-teal-600 focus:ring-teal-600"><span><span class="block text-sm font-semibold text-slate-900">Consenso sblocco contatti</span><span class="mt-1 block text-xs leading-5 text-slate-600">Email e telefono saranno condivisi con questa struttura soltanto dopo la conferma.</span></span></label>
                                        <div class="grid grid-cols-2 gap-3"><x-ui.button type="submit" name="response" value="accepted">Conferma slot</x-ui.button><x-ui.button type="submit" name="response" value="declined" variant="danger">Rifiuta</x-ui.button></div>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-slate-500">Nessun invito reale da confermare.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            <aside>
                <x-ui.card>
                    <h3 class="text-base font-semibold text-slate-950">Consenso sblocco contatti</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">I contatti restano protetti finché non accetti un colloquio e autorizzi espressamente la condivisione.</p>
                    <x-ui.button type="button" class="mt-4 w-full" disabled>Conferma slot</x-ui.button>
                </x-ui.card>
            </aside>
        </div>
    @endif
</x-app-layout>