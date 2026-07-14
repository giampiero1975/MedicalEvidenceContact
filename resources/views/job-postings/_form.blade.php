@php
    $contractTypes = ['Tempo indeterminato', 'Tempo determinato', 'Part-time', 'Collaborazione', 'Libero professionista', 'Somministrazione'];
    $selectedContractType = old('contract_type', $jobPosting->contract_type ?? '');
@endphp

<div class="space-y-6">
    <x-ui.input
        name="title"
        label="Titolo"
        :value="$jobPosting->title ?? ''"
        placeholder="Es. OSS per RSA a Milano"
        required
    />

    <div>
        <label for="description" class="block text-sm font-semibold text-slate-700">Descrizione</label>
        <textarea
            id="description"
            name="description"
            rows="7"
            required
            class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-600 focus:ring-teal-600 @error('description') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror"
            placeholder="Descrivi attività, requisiti, orari e contesto di lavoro."
        >{{ old('description', $jobPosting->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-ui.input
            name="positions"
            type="number"
            label="Numero posizioni"
            :value="$jobPosting->positions ?? 1"
            min="1"
            required
        />

        <x-ui.select name="contract_type" label="Tipo contratto" required>
            <option value="">Seleziona</option>
            @foreach ($contractTypes as $contractType)
                <option value="{{ $contractType }}" @selected($selectedContractType === $contractType)>{{ $contractType }}</option>
            @endforeach
        </x-ui.select>
    </div>

    <x-ui.input
        name="workplace_address"
        label="Indirizzo sede di lavoro"
        :value="$jobPosting->workplace_address ?? ''"
        placeholder="Via, città e provincia"
        required
    />

    <div>
        <label for="required_skills" class="block text-sm font-semibold text-slate-700">Abilità richieste</label>
        <textarea
            id="required_skills"
            name="required_skills"
            rows="4"
            class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-600 focus:ring-teal-600 @error('required_skills') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror"
            placeholder="Competenze, attestati o esperienza richiesti (opzionale)."
        >{{ old('required_skills', $jobPosting->required_skills ?? '') }}</textarea>
        @error('required_skills')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-3">
        <div>
            <label for="salary_min" class="block text-sm font-semibold text-slate-700">Retribuzione minima</label>
            <div class="relative mt-1">
                <input
                    id="salary_min"
                    name="salary_min"
                    type="text"
                    inputmode="decimal"
                    autocomplete="off"
                    value="{{ old('salary_min', isset($jobPosting) && $jobPosting->salary_min !== null ? number_format((float) $jobPosting->salary_min, 2, ',', '.') : '') }}"
                    placeholder="Es. 1.200,00"
                    class="block w-full rounded-xl border-slate-300 bg-white pr-10 text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-600 focus:ring-teal-600 @error('salary_min') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror"
                >
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm font-semibold text-slate-500">€</span>
            </div>
            @error('salary_min')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @else
                <p class="mt-1 text-xs text-slate-500">Inserisci liberamente l'importo lordo, ad esempio 1200 oppure 1.200,00.</p>
            @enderror
        </div>

        <div>
            <label for="salary_max" class="block text-sm font-semibold text-slate-700">Retribuzione massima</label>
            <div class="relative mt-1">
                <input
                    id="salary_max"
                    name="salary_max"
                    type="text"
                    inputmode="decimal"
                    autocomplete="off"
                    value="{{ old('salary_max', isset($jobPosting) && $jobPosting->salary_max !== null ? number_format((float) $jobPosting->salary_max, 2, ',', '.') : '') }}"
                    placeholder="Es. 1.500,00"
                    class="block w-full rounded-xl border-slate-300 bg-white pr-10 text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-600 focus:ring-teal-600 @error('salary_max') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror"
                >
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm font-semibold text-slate-500">€</span>
            </div>
            @error('salary_max')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @else
                <p class="mt-1 text-xs text-slate-500">Deve essere uguale o superiore alla retribuzione minima.</p>
            @enderror
        </div>

        <x-ui.input
            name="expires_at"
            type="date"
            label="Data scadenza"
            :value="isset($jobPosting) && $jobPosting->expires_at ? $jobPosting->expires_at->format('Y-m-d') : ''"
            required
        />
    </div>

    @if (isset($jobPosting) && $jobPosting->exists)
        <x-ui.select name="status" label="Stato annuncio" required>
            <option value="active" @selected(old('status', $jobPosting->status) === 'active')>Attivo</option>
            <option value="expired" @selected(old('status', $jobPosting->status) === 'expired')>Scaduto</option>
        </x-ui.select>
    @endif
</div>