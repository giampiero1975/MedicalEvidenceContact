<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $jobPosting->title }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $jobPosting->contract_type }} - {{ $jobPosting->workplace_city ?: $jobPosting->workplace_address }}{{ $jobPosting->workplace_province ? ' ('.$jobPosting->workplace_province.')' : '' }}</p>
            </div>
            <a href="{{ route('job-postings.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50">Torna agli annunci</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @session('status')
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm font-medium text-green-700">{{ $value }}</div>
            @endsession
            @session('warning')
                <div class="mb-6 rounded-md bg-amber-50 p-4 text-sm font-medium text-amber-800">{{ $value }}</div>
            @endsession

            <article class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.badge :variant="$jobPosting->status === 'active' ? 'success' : 'warning'">{{ $jobPosting->status === 'active' ? 'Attivo' : 'Scaduto' }}</x-ui.badge>
                        @if ($jobPosting->professional_category)
                            <x-ui.badge variant="info">{{ $jobPosting->professional_category }}</x-ui.badge>
                        @endif
                    </div>
                    @if ($role === 'business')
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('job-postings.edit', $jobPosting) }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">Modifica</a>
                            <a href="{{ route('job-postings.applications', $jobPosting) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Candidature</a>
                            <form method="POST" action="{{ route('job-postings.destroy', $jobPosting) }}" onsubmit="return confirm('Eliminare questo annuncio?');">@csrf @method('DELETE')<button type="submit" class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500">Elimina</button></form>
                        </div>
                    @endif
                </div>

                <div class="mt-6 grid gap-4 text-sm text-gray-700 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-md bg-gray-50 p-4"><p class="font-semibold text-gray-900">Categoria</p><p class="mt-1">{{ $jobPosting->professional_category ?: 'Non specificata' }}</p></div>
                    <div class="rounded-md bg-gray-50 p-4"><p class="font-semibold text-gray-900">Posizioni</p><p class="mt-1">{{ $jobPosting->positions }}</p></div>
                    <div class="rounded-md bg-gray-50 p-4"><p class="font-semibold text-gray-900">Contratto</p><p class="mt-1">{{ $jobPosting->contract_type }}</p></div>
                    <div class="rounded-md bg-gray-50 p-4"><p class="font-semibold text-gray-900">Scadenza</p><p class="mt-1">{{ $jobPosting->expires_at->format('d/m/Y') }}</p></div>
                </div>

                <div class="mt-6 space-y-5 border-t border-gray-100 pt-6 text-sm leading-6 text-gray-700">
                    <div>
                        <p class="font-semibold text-gray-900">Descrizione</p>
                        <div class="mt-2 space-y-2 [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:list-disc [&_ul]:pl-5">{!! $jobPosting->description !!}</div>
                    </div>
                    <div><p class="font-semibold text-gray-900">Sede</p><p class="mt-2">{{ $jobPosting->workplace_address }}</p>@if ($jobPosting->workplace_city || $jobPosting->workplace_province)<p class="mt-1 text-xs text-gray-500">{{ $jobPosting->workplace_city }}{{ $jobPosting->workplace_province ? ' ('.$jobPosting->workplace_province.')' : '' }}</p>@endif</div>
                    <div><p class="font-semibold text-gray-900">Abilità richieste</p><p class="mt-2 whitespace-pre-line">{{ $jobPosting->required_skills ?: 'Non specificate' }}</p></div>
                    @if ($jobPosting->benefits)
                        <div><p class="font-semibold text-gray-900">Benefit</p><p class="mt-2 whitespace-pre-line">{{ $jobPosting->benefits }}</p></div>
                    @endif
                    @if ($jobPosting->preferred_requirements)
                        <div><p class="font-semibold text-gray-900">Requisiti preferenziali</p><p class="mt-2 whitespace-pre-line">{{ $jobPosting->preferred_requirements }}</p></div>
                    @endif
                    @if ($jobPosting->work_schedule)
                        <div><p class="font-semibold text-gray-900">Orario di lavoro</p><p class="mt-2">{{ $jobPosting->work_schedule }}</p></div>
                    @endif
                    <div>
                        <p class="font-semibold text-gray-900">Retribuzione</p>
                        <p class="mt-2">@if ($jobPosting->salary_min || $jobPosting->salary_max){{ $jobPosting->salary_min ? 'EUR '.number_format((float) $jobPosting->salary_min, 0, ',', '.') : 'Da definire' }} - {{ $jobPosting->salary_max ? 'EUR '.number_format((float) $jobPosting->salary_max, 0, ',', '.') : 'Da definire' }}@else Da definire @endif</p>
                    </div>
                </div>

                @if ($role === 'professional')
                    <div class="mt-6 flex justify-end border-t border-gray-100 pt-6">
                        @if ($jobPosting->applications->isNotEmpty())
                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700">Candidatura {{ str_replace('_', ' ', $jobPosting->applications->first()->status) }}</span>
                        @else
                            <div x-data="{ open: {{ $errors->has('application_confirmation') || $errors->has('presentation_message') ? 'true' : 'false' }} }">
                                <x-ui.button type="button" x-on:click="open = true">Candidati</x-ui.button>

                                <div x-cloak x-show="open" x-on:keydown.escape.window="open = false" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="application-modal-title">
                                    <div class="absolute inset-0 bg-slate-950/50" x-on:click="open = false"></div>
                                    <div class="relative z-10 w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl">
                                        <div class="flex items-start justify-between gap-4">
                                            <div><h3 id="application-modal-title" class="text-lg font-semibold text-slate-950">Conferma candidatura</h3><p class="mt-1 text-sm text-slate-600">Controlla il riepilogo prima dell'invio.</p></div>
                                            <button type="button" x-on:click="open = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Chiudi">✕</button>
                                        </div>

                                        <div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-700">
                                            <p class="font-semibold text-slate-950">{{ auth()->user()->first_name && auth()->user()->last_name ? auth()->user()->first_name.' '.auth()->user()->last_name : auth()->user()->name }}</p>
                                            <p class="mt-1">{{ auth()->user()->residence ?: 'Residenza non indicata' }}</p>
                                            <p class="mt-2 text-xs text-slate-500">I contatti personali restano protetti secondo il workflow della piattaforma.</p>
                                        </div>

                                        <form method="POST" action="{{ route('job-applications.store', $jobPosting) }}" class="mt-5 space-y-4">
                                            @csrf
                                            <div>
                                                <label for="presentation_message" class="block text-sm font-semibold text-slate-800">Messaggio di presentazione <span class="font-normal text-slate-500">(facoltativo)</span></label>
                                                <textarea id="presentation_message" name="presentation_message" rows="4" maxlength="500" class="mt-2 block w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="Aggiungi un breve messaggio per la struttura...">{{ old('presentation_message') }}</textarea>
                                                <div class="mt-1 flex justify-between gap-3 text-xs text-slate-500"><span>Massimo 500 caratteri.</span></div>
                                                @error('presentation_message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                            </div>
                                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4">
                                                <input type="checkbox" name="application_confirmation" value="1" @checked(old('application_confirmation')) class="mt-1 rounded border-slate-300 text-teal-600 focus:ring-teal-600">
                                                <span class="text-sm text-slate-700"><span class="font-semibold text-slate-900">Confermo l'invio della candidatura</span><span class="mt-1 block text-xs text-slate-500">La candidatura verrà registrata e notificata alla struttura.</span></span>
                                            </label>
                                            @error('application_confirmation')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                                            <div class="grid grid-cols-2 gap-3">
                                                <x-ui.button type="button" variant="secondary" x-on:click="open = false">Annulla</x-ui.button>
                                                <x-ui.button type="submit">Invia candidatura</x-ui.button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </article>
        </div>
    </div>
</x-app-layout>
