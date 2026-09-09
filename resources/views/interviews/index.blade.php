<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Colloqui" :description="$role === 'business' ? 'Proponi slot e conferma le richieste dei candidati.' : 'Scegli uno slot proposto e attendi la conferma della struttura.'">
            <x-slot name="actions"><x-ui.button href="{{ route('dashboard') }}" variant="secondary" size="sm">Torna alla dashboard</x-ui.button></x-slot>
        </x-ui.page-header>
    </x-slot>

    @if ($role === 'business')
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-4">
                <x-ui.card>
                    <div class="flex items-center justify-between gap-4">
                        <div><h3 class="text-base font-semibold text-slate-950">Candidature da pianificare</h3><p class="mt-1 text-sm text-slate-600">Apri una candidatura per proporre uno o più slot di disponibilità.</p></div>
                        <span class="text-sm font-semibold text-slate-700">{{ $businessJobPostings->sum('applications_to_schedule_count') }} candidature</span>
                    </div>
                    <div class="mt-4 divide-y divide-slate-200">
                        @forelse ($businessJobPostings as $jobPosting)
                            <div class="py-4 first:pt-0 last:pb-0">
                                <p class="font-semibold text-slate-900">{{ $jobPosting->title }}</p><p class="mt-1 text-sm text-slate-500">{{ $jobPosting->workplace_address }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">@foreach ($jobPosting->applications as $application) @php($professional = $application->professional) <x-ui.button href="{{ route('business.applications.show', $application) }}" variant="secondary" size="sm">{{ $professional->first_name && $professional->last_name ? $professional->first_name.' '.$professional->last_name : $professional->name }}</x-ui.button> @endforeach</div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Nessuna candidatura in attesa di pianificazione.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            <aside class="space-y-4">
                <x-ui.card><h3 class="text-base font-semibold text-slate-950">Nuovo invito a colloquio</h3><p class="mt-2 text-sm text-slate-600">Dalla scheda candidatura puoi proporre più slot. Quando il professionista ne sceglie uno, dovrai confermarlo o rifiutarlo.</p><x-ui.button href="{{ route('job-postings.index') }}" variant="secondary" class="mt-4 w-full">Vai agli annunci</x-ui.button></x-ui.card>
                <x-ui.card>
                    <h3 class="text-base font-semibold text-slate-950">Slot e colloqui</h3>
                    <div class="mt-4 divide-y divide-slate-200">
                        @forelse ($interviews as $interview)
                            <div id="interview-{{ $interview->id }}" class="py-3 first:pt-0 last:pb-0 scroll-mt-6">
                                <a href="{{ route('business.applications.show', $interview->jobApplication) }}" class="block"><div class="flex items-center justify-between gap-3"><p class="font-semibold text-slate-900">{{ $interview->jobApplication->professional->name }}</p><x-ui.badge variant="secondary">{{ $interview->statusLabel() }}</x-ui.badge></div><p class="mt-1 text-sm text-slate-600">{{ $interview->scheduled_at->format('d/m/Y H:i') }} · {{ $interview->modeLabel() }}</p></a>
                                @if ($interview->status === \App\Models\Interview::STATUS_REQUESTED)
                                    <p class="mt-2 text-xs font-semibold text-amber-700">Richiesta da confermare</p>
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <form method="POST" action="{{ route('business.interviews.confirm', $interview) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="accepted"><x-ui.button type="submit" size="sm" class="w-full">Conferma</x-ui.button></form>
                                        <form method="POST" action="{{ route('business.interviews.confirm', $interview) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="declined"><x-ui.button type="submit" size="sm" variant="danger" class="w-full">Rifiuta</x-ui.button></form>
                                    </div>
                                @elseif ($interview->status === \App\Models\Interview::STATUS_ACCEPTED)
                                    <form method="POST" action="{{ route('interviews.reschedule', $interview) }}" class="mt-3 space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="reschedule_reason" maxlength="500" placeholder="Motivo riprogrammazione (facoltativo)" class="block w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                                        <x-ui.button type="submit" size="sm" variant="secondary" class="w-full">Riprogramma colloquio</x-ui.button>
                                    </form>
                                    <form method="POST" action="{{ route('interviews.cancel', $interview) }}" class="mt-2 space-y-2" onsubmit="return confirm('Annullare questo colloquio confermato?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="cancellation_reason" maxlength="500" placeholder="Motivo annullamento (facoltativo)" class="block w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                                        <x-ui.button type="submit" size="sm" variant="danger" class="w-full">Annulla colloquio</x-ui.button>
                                    </form>
                                @elseif ($interview->status === \App\Models\Interview::STATUS_CANCELLED)
                                    <form method="POST" action="{{ route('interviews.reschedule', $interview) }}" class="mt-3 space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="reschedule_reason" maxlength="500" placeholder="Motivo riprogrammazione (facoltativo)" class="block w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                                        <x-ui.button type="submit" size="sm" variant="secondary" class="w-full">Riprogramma colloquio</x-ui.button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Nessuno slot proposto.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </aside>
        </div>
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-4">
                <x-ui.card><h3 class="text-base font-semibold text-slate-950">Inviti e candidature</h3><div class="mt-4 divide-y divide-slate-200">@forelse ($professionalApplications as $application)<div class="py-3 first:pt-0 last:pb-0"><p class="font-semibold text-slate-900">{{ $application->jobPosting->title }}</p><p class="mt-1 text-sm text-slate-500">{{ $application->jobPosting->workplace_address }}</p></div>@empty<p class="text-sm text-slate-500">Non hai ancora candidature.</p>@endforelse</div></x-ui.card>
                <x-ui.card>
                    <h3 class="text-base font-semibold text-slate-950">Rispondi a un invito</h3>
                    <p class="mt-1 text-sm text-slate-500">Slot disponibili e colloqui.</p>
                    <div class="mt-4 space-y-4">
                        @forelse ($interviews as $interview)
                            <article id="interview-{{ $interview->id }}" class="rounded-xl border border-slate-200 p-4 scroll-mt-6">
                                <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="font-semibold text-slate-950">{{ $interview->jobApplication->jobPosting->title }}</p><p class="mt-1 text-sm text-slate-600">{{ $interview->scheduled_at->format('d/m/Y H:i') }} · {{ $interview->modeLabel() }}</p>@if($interview->location)<p class="mt-1 text-xs text-slate-500">{{ $interview->location }}</p>@endif</div><x-ui.badge variant="info">{{ $interview->statusLabel() }}</x-ui.badge></div>
                                @if ($interview->isAvailableProposal())
                                    <form method="POST" action="{{ route('professional.interviews.respond', $interview) }}" class="mt-4 space-y-3">@csrf @method('PATCH')
                                        <label class="flex items-start gap-3 rounded-xl bg-slate-50 p-3"><input type="checkbox" name="contact_sharing_consent" value="1" class="mt-1 rounded border-slate-300 text-teal-600 focus:ring-teal-600"><span><span class="block text-sm font-semibold text-slate-900">Consenso sblocco contatti</span><span class="mt-1 block text-xs leading-5 text-slate-600">Autorizzi la condivisione di email e telefono solo se la struttura confermerà definitivamente questo colloquio.</span></span></label>
                                        <x-ui.button type="submit" class="w-full">Seleziona slot</x-ui.button>
                                    </form>
                                @elseif ($interview->status === \App\Models\Interview::STATUS_REQUESTED)
                                    <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900">Hai selezionato questo slot. La struttura deve ancora confermare il colloquio.</p>
                                @elseif ($interview->status === \App\Models\Interview::STATUS_ACCEPTED)
                                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3"><p class="text-sm font-semibold text-emerald-900">Colloquio confermato</p>@if ($interview->unlocksContacts()) @php($business = $interview->jobApplication->jobPosting->owner) <p class="mt-2 text-sm text-emerald-900">Contatti sbloccati: {{ $business->email }}@if($business->phone) · {{ $business->phone }}@endif</p> @endif</div>
                                    <form method="POST" action="{{ route('interviews.reschedule', $interview) }}" class="mt-3 space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="reschedule_reason" maxlength="500" placeholder="Motivo riprogrammazione (facoltativo)" class="block w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                                        <x-ui.button type="submit" variant="secondary" class="w-full">Riprogramma colloquio</x-ui.button>
                                    </form>
                                    <form method="POST" action="{{ route('interviews.cancel', $interview) }}" class="mt-2 space-y-2" onsubmit="return confirm('Annullare questo colloquio confermato?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="cancellation_reason" maxlength="500" placeholder="Motivo annullamento (facoltativo)" class="block w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                                        <x-ui.button type="submit" variant="danger" class="w-full">Annulla colloquio</x-ui.button>
                                    </form>
                                @elseif ($interview->status === \App\Models\Interview::STATUS_CANCELLED)
                                    <p class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">Il colloquio è stato annullato. Puoi richiedere una nuova pianificazione.</p>
                                    <form method="POST" action="{{ route('interviews.reschedule', $interview) }}" class="mt-3 space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="reschedule_reason" maxlength="500" placeholder="Motivo riprogrammazione (facoltativo)" class="block w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                                        <x-ui.button type="submit" variant="secondary" class="w-full">Riprogramma colloquio</x-ui.button>
                                    </form>
                                @elseif ($interview->status === \App\Models\Interview::STATUS_DECLINED)
                                    <p class="mt-4 text-sm text-slate-600">Questo slot non è stato confermato dalla struttura. Puoi scegliere un altro slot disponibile.</p>
                                @else
                                    <p class="mt-4 text-sm text-slate-600">{{ $interview->statusLabel() }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-slate-500">Nessun invito reale da gestire.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>
            <aside><x-ui.card><h3 class="text-base font-semibold text-slate-950">Consenso sblocco contatti</h3><p class="mt-3 text-sm leading-6 text-slate-600">Scegli uno degli slot proposti. La struttura dovrà confermarlo. I contatti restano protetti fino alla conferma finale e vengono sbloccati solo con il tuo consenso.</p><x-ui.button type="button" class="mt-4 w-full" disabled>Conferma slot</x-ui.button></x-ui.card></aside>
        </div>
    @endif
</x-app-layout>