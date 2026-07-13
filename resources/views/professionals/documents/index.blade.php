<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Documenti professionali"
            subtitle="Controlla lo stato dei documenti richiesti e carica o sostituisci i file del tuo profilo."
        />
    </x-slot>

    <div class="space-y-6">
        <x-ui.alert variant="info" title="Archivio documentale riservato">
            I file caricati sono associati al tuo profilo professionale. Sono accettati PDF, JPG e PNG fino a 5 MB.
        </x-ui.alert>

        <div class="grid gap-6 xl:grid-cols-2">
            @foreach ($documents as $document)
                @php($hasFile = filled($document['file']))

                <x-ui.card>
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="text-xl font-semibold text-slate-950">{{ $document['label'] }}</h2>
                                <x-ui.badge :variant="$hasFile ? 'success' : 'warning'">
                                    {{ $hasFile ? 'Caricato' : 'Da caricare' }}
                                </x-ui.badge>
                            </div>

                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $document['description'] }}</p>

                            <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <dt class="font-semibold text-slate-900">Obbligatorietà</dt>
                                    <dd class="mt-1 text-slate-600">{{ $document['required'] ? 'Richiesto' : 'Facoltativo' }}</dd>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <dt class="font-semibold text-slate-900">Stato</dt>
                                    <dd class="mt-1 text-slate-600">{{ $hasFile ? 'Documento disponibile' : 'Nessun file presente' }}</dd>
                                </div>
                            </dl>

                            @if ($hasFile)
                                <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4 text-sm">
                                    <p class="truncate font-semibold text-slate-900">{{ $document['file']['original_name'] }}</p>
                                    @if ($document['file']['uploaded_at'])
                                        <p class="mt-1 text-xs text-slate-500">
                                            Caricato il {{ \Illuminate\Support\Carbon::parse($document['file']['uploaded_at'])->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($hasFile)
                        <div class="mt-6 flex flex-wrap gap-3 border-t border-slate-100 pt-6">
                            <x-ui.button
                                variant="secondary"
                                :href="route('professional-documents.view', $document['type'])"
                                target="_blank"
                                rel="noopener"
                            >
                                Visualizza
                            </x-ui.button>

                            <x-ui.button
                                variant="secondary"
                                :href="route('professional-documents.download', $document['type'])"
                            >
                                Scarica
                            </x-ui.button>

                            <form
                                method="POST"
                                action="{{ route('professional-documents.destroy', $document['type']) }}"
                                class="sm:ml-auto"
                                onsubmit="return confirm('Eliminare definitivamente questo documento?');"
                            >
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="danger">Elimina</x-ui.button>
                            </form>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('professional-documents.store') }}" enctype="multipart/form-data" class="mt-6 border-t border-slate-100 pt-6">
                        @csrf
                        <input type="hidden" name="redirect_to" value="documents">

                        <label for="{{ $document['key'] }}" class="block text-sm font-semibold text-slate-700">
                            {{ $hasFile ? 'Sostituisci documento' : 'Carica documento' }}
                        </label>
                        <input
                            id="{{ $document['key'] }}"
                            type="file"
                            name="{{ $document['key'] }}"
                            accept=".pdf,.jpg,.jpeg,.png"
                            required
                            class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-teal-800 hover:file:bg-teal-100"
                        >
                        @error($document['key'])
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror

                        <div class="mt-5 flex justify-end">
                            <x-ui.button type="submit">
                                {{ $hasFile ? 'Sostituisci file' : 'Carica file' }}
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            @endforeach
        </div>
    </div>
</x-app-layout>
