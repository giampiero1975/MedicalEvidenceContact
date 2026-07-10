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
        <x-ui.input
            name="salary_min"
            type="number"
            label="Retribuzione minima"
            :value="$jobPosting->salary_min ?? ''"
            min="0"
            step="0.01"
            help="Importo lordo indicativo."
        />

        <x-ui.input
            name="salary_max"
            type="number"
            label="Retribuzione massima"
            :value="$jobPosting->salary_max ?? ''"
            min="0"
            step="0.01"
            help="Importo lordo indicativo."
        />

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
