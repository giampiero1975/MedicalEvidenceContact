<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Esperienze e percorsi di studio"
            subtitle="Raccogli in un unico punto le esperienze lavorative e la formazione visibili alle strutture."
        />
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
        <x-ui.card>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Nuovo elemento</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-950">Aggiungi al profilo</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Inserisci un'esperienza lavorativa oppure un percorso di studio.</p>

            <form method="POST" action="{{ route('professional-profile-items.store') }}" class="mt-6 space-y-5">
                @csrf

                <x-ui.select name="type" id="new_profile_item_type" label="Tipo" required>
                    <option value="work_experience" @selected(old('type') === 'work_experience')>Esperienza lavorativa</option>
                    <option value="education" @selected(old('type') === 'education')>Percorso di studio</option>
                </x-ui.select>

                <x-ui.input name="title" id="new_profile_item_title" label="Titolo" :value="old('title')" required />
                <x-ui.input name="duration" id="new_profile_item_duration" label="Durata" :value="old('duration')" placeholder="Es. 2021 - 2024" required />
                <x-ui.textarea name="description" id="new_profile_item_description" label="Descrizione" rows="5">{{ old('description') }}</x-ui.textarea>

                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <x-ui.button type="submit">Aggiungi al profilo</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Profilo professionale</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Elementi inseriti</h2>
                </div>
                <x-ui.badge>{{ $profileItems->count() }} elementi</x-ui.badge>
            </div>

            @if ($profileItems->isEmpty())
                <div class="mt-6">
                    <x-ui.empty-state
                        title="Nessuna esperienza inserita"
                        description="Aggiungi il primo elemento per completare il profilo professionale."
                    />
                </div>
            @else
                <div class="mt-6 space-y-4">
                    @foreach ($profileItems as $item)
                        <article class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-semibold text-slate-950">{{ $item->title }}</h3>
                                        <x-ui.badge variant="neutral">
                                            {{ $item->type === 'education' ? 'Percorso di studio' : 'Esperienza lavorativa' }}
                                        </x-ui.badge>
                                    </div>
                                    <p class="mt-2 text-sm font-medium text-slate-600">{{ $item->duration }}</p>
                                    @if ($item->description)
                                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $item->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <details class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <summary class="cursor-pointer select-none text-sm font-semibold text-teal-700">
                                    Modifica elemento
                                </summary>

                                <form method="POST" action="{{ route('professional-profile-items.update', $item) }}" class="mt-5 space-y-5">
                                    @csrf
                                    @method('PUT')

                                    <div class="grid gap-4 md:grid-cols-[180px_1fr_180px]">
                                        <x-ui.select name="type" id="profile_item_type_{{ $item->id }}" label="Tipo" required>
                                            <option value="work_experience" @selected($item->type === 'work_experience')>Esperienza lavorativa</option>
                                            <option value="education" @selected($item->type === 'education')>Percorso di studio</option>
                                        </x-ui.select>

                                        <x-ui.input name="title" id="profile_item_title_{{ $item->id }}" label="Titolo" :value="$item->title" required />
                                        <x-ui.input name="duration" id="profile_item_duration_{{ $item->id }}" label="Durata" :value="$item->duration" required />
                                    </div>

                                    <x-ui.textarea name="description" id="profile_item_description_{{ $item->id }}" label="Descrizione" rows="4">{{ $item->description }}</x-ui.textarea>

                                    <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-4">
                                        <x-ui.button type="submit">Salva modifiche</x-ui.button>
                                    </div>
                                </form>
                            </details>

                            <form method="POST" action="{{ route('professional-profile-items.destroy', $item) }}" class="mt-3 flex justify-end" onsubmit="return confirm('Eliminare questo elemento dal profilo?');">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="danger" size="sm">Elimina</x-ui.button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>
