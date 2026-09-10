@php
    $contractTypes = ['Tempo determinato', 'Tempo indeterminato', 'A chiamata', 'Stage', 'Altro'];
    $professionalCategories = ['OSS', 'Infermiere', 'Anestesista', 'Fisioterapista', 'Altra'];
    $selectedContractType = old('contract_type', $jobPosting->contract_type ?? '');
    $selectedProfessionalCategory = old('professional_category', $jobPosting->professional_category ?? '');
    $selectedLocationId = old('business_location_id', $jobPosting->business_location_id ?? '');
    $selectedDepartmentId = old('business_department_id', $jobPosting->business_department_id ?? '');
    $locationAddresses = ($businessLocations ?? collect())
        ->mapWithKeys(fn ($location) => [(string) $location->id => $location->formattedAddress()]);
    $locationCities = ($businessLocations ?? collect())
        ->mapWithKeys(fn ($location) => [(string) $location->id => $location->city]);
    $locationProvinces = ($businessLocations ?? collect())
        ->mapWithKeys(fn ($location) => [(string) $location->id => $location->province]);
    $departmentLocations = ($businessDepartments ?? collect())
        ->mapWithKeys(fn ($department) => [(string) $department->id => (string) $department->business_location_id]);
    $minimumExpiryDate = today()->addDays(7)->toDateString();
    $initialDescription = old('description', $jobPosting->description ?? '');
@endphp

<div
    class="space-y-6"
    x-data='{
        selectedLocation: @json((string) $selectedLocationId),
        selectedDepartment: @json((string) $selectedDepartmentId),
        workplaceAddress: @json(old("workplace_address", $jobPosting->workplace_address ?? "")),
        workplaceCity: @json(old("workplace_city", $jobPosting->workplace_city ?? "")),
        workplaceProvince: @json(old("workplace_province", $jobPosting->workplace_province ?? "")),
        locationAddresses: @json($locationAddresses),
        locationCities: @json($locationCities),
        locationProvinces: @json($locationProvinces),
        departmentLocations: @json($departmentLocations),
        applyLocation() {
            if (this.selectedLocation && this.locationAddresses[this.selectedLocation]) {
                this.workplaceAddress = this.locationAddresses[this.selectedLocation] || "";
                this.workplaceCity = this.locationCities[this.selectedLocation] || "";
                this.workplaceProvince = this.locationProvinces[this.selectedLocation] || "";
            }
            if (this.selectedDepartment && this.departmentLocations[this.selectedDepartment] !== this.selectedLocation) {
                this.selectedDepartment = "";
            }
        },
        departmentVisible(id) {
            return this.selectedLocation !== "" && this.departmentLocations[String(id)] === this.selectedLocation;
        }
    }'
    x-init="applyLocation()"
>
    <div class="grid gap-5 lg:grid-cols-2">
        <x-ui.input
            name="title"
            label="Titolo"
            :value="$jobPosting->title ?? ''"
            placeholder="Es. OSS per RSA a Milano"
            maxlength="100"
            help="Massimo 100 caratteri."
            required
        />

        <x-ui.select name="professional_category" label="Categoria professionale ricercata" required>
            <option value="">Seleziona categoria</option>
            @foreach ($professionalCategories as $professionalCategory)
                <option value="{{ $professionalCategory }}" @selected($selectedProfessionalCategory === $professionalCategory)>{{ $professionalCategory }}</option>
            @endforeach
        </x-ui.select>
    </div>

    <div
        x-data='{
            description: @json($initialDescription),
            count: 0,
            sync() {
                this.description = this.$refs.editor.innerHTML;
                this.count = (this.$refs.editor.innerText || "").trim().length;
            },
            command(command, value = null) {
                this.$refs.editor.focus();
                document.execCommand(command, false, value);
                this.sync();
            }
        }'
        x-init="$refs.editor.innerHTML = description; sync()"
    >
        <label class="block text-sm font-semibold text-slate-700">Descrizione</label>
        <div class="mt-1 overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm @error('description') border-rose-300 @enderror">
            <div class="flex flex-wrap gap-1 border-b border-slate-200 bg-slate-50 px-2 py-2" aria-label="Formattazione descrizione">
                <button type="button" class="rounded-md px-2.5 py-1 text-sm font-bold text-slate-700 hover:bg-white" x-on:click="command('bold')" title="Grassetto">B</button>
                <button type="button" class="rounded-md px-2.5 py-1 text-sm italic text-slate-700 hover:bg-white" x-on:click="command('italic')" title="Corsivo">I</button>
                <button type="button" class="rounded-md px-2.5 py-1 text-sm underline text-slate-700 hover:bg-white" x-on:click="command('underline')" title="Sottolineato">U</button>
                <button type="button" class="rounded-md px-2.5 py-1 text-sm text-slate-700 hover:bg-white" x-on:click="command('insertUnorderedList')">• Elenco</button>
                <button type="button" class="rounded-md px-2.5 py-1 text-sm text-slate-700 hover:bg-white" x-on:click="command('insertOrderedList')">1. Elenco</button>
            </div>
            <div
                x-ref="editor"
                contenteditable="true"
                role="textbox"
                aria-multiline="true"
                class="min-h-44 px-4 py-3 text-sm leading-6 text-slate-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-teal-600"
                x-on:input="sync()"
                x-on:blur="sync()"
            ></div>
        </div>
        <textarea name="description" class="hidden" x-model="description" required></textarea>
        <div class="mt-1 flex justify-between gap-3 text-xs text-slate-500">
            <span>Puoi usare grassetto, corsivo, sottolineato ed elenchi.</span>
            <span :class="count > 3000 ? 'font-semibold text-rose-600' : ''"><span x-text="count"></span>/3000</span>
        </div>
        @error('description')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-ui.input
            name="positions"
            type="number"
            label="Numero posti disponibili"
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

    <div class="grid gap-5 lg:grid-cols-2">
        <x-ui.select
            name="business_location_id"
            label="Sede della struttura"
            x-model="selectedLocation"
            @change="applyLocation()"
            help="Puoi usare una sede censita oppure indicare manualmente una sede di lavoro diversa."
        >
            <option value="">Sede di lavoro diversa / inserimento manuale</option>
            @foreach (($businessLocations ?? collect()) as $location)
                <option value="{{ $location->id }}" @selected((string) $selectedLocationId === (string) $location->id)>
                    {{ $location->name }} — {{ $location->formattedAddress() }}
                </option>
            @endforeach
        </x-ui.select>

        <x-ui.select
            name="business_department_id"
            label="Reparto / unità operativa"
            x-model="selectedDepartment"
            x-bind:disabled="selectedLocation === ''"
            help="Facoltativo; disponibile per le sedi già censite."
        >
            <option value="">Nessun reparto specifico</option>
            @foreach (($businessDepartments ?? collect()) as $department)
                <option
                    value="{{ $department->id }}"
                    x-show="departmentVisible({{ $department->id }})"
                    @selected((string) $selectedDepartmentId === (string) $department->id)
                >
                    {{ $department->name }}{{ $department->code ? ' · '.$department->code : '' }}
                </option>
            @endforeach
        </x-ui.select>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <div>
            <label for="workplace_address" class="block text-sm font-semibold text-slate-700">Indirizzo sede di lavoro</label>
            <input
                id="workplace_address"
                name="workplace_address"
                type="text"
                x-model="workplaceAddress"
                :readonly="selectedLocation !== ''"
                placeholder="Via e numero civico"
                class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm placeholder:text-slate-400 read-only:bg-slate-50 read-only:text-slate-600 focus:border-teal-600 focus:ring-teal-600 @error('workplace_address') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror"
            >
            @error('workplace_address')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="workplace_city" class="block text-sm font-semibold text-slate-700">Città</label>
            <input
                id="workplace_city"
                name="workplace_city"
                type="text"
                x-model="workplaceCity"
                :readonly="selectedLocation !== ''"
                placeholder="Es. Milano"
                class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm read-only:bg-slate-50 read-only:text-slate-600 focus:border-teal-600 focus:ring-teal-600 @error('workplace_city') border-rose-300 @enderror"
            >
            @error('workplace_city')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="workplace_province" class="block text-sm font-semibold text-slate-700">Provincia</label>
            <input
                id="workplace_province"
                name="workplace_province"
                type="text"
                x-model="workplaceProvince"
                :readonly="selectedLocation !== ''"
                placeholder="Es. MI"
                class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm read-only:bg-slate-50 read-only:text-slate-600 focus:border-teal-600 focus:ring-teal-600 @error('workplace_province') border-rose-300 @enderror"
            >
            @error('workplace_province')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    @error('business_department_id')
        <p class="-mt-4 text-sm text-rose-600">{{ $message }}</p>
    @enderror

    @if (($businessLocations ?? collect())->isEmpty())
        <x-ui.alert variant="info">
            Non hai sedi censite: indica manualmente indirizzo, città e provincia della sede di lavoro.
        </x-ui.alert>
    @endif

    <div>
        <label for="required_skills" class="block text-sm font-semibold text-slate-700">Abilità richieste <span class="font-normal text-slate-500">(facoltativo)</span></label>
        <textarea
            id="required_skills"
            name="required_skills"
            rows="3"
            class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-600 focus:ring-teal-600 @error('required_skills') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror"
            placeholder="Es. Qualifica OSS, BLSD, esperienza geriatrica"
        >{{ old('required_skills', $jobPosting->required_skills ?? '') }}</textarea>
        @error('required_skills')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @else
            <p class="mt-1 text-xs text-slate-500">Inserisci fino a 10 tag separati da virgola o andando a capo.</p>
        @enderror
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div>
            <label for="benefits" class="block text-sm font-semibold text-slate-700">Benefit <span class="font-normal text-slate-500">(facoltativo)</span></label>
            <textarea id="benefits" name="benefits" rows="4" class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-600 focus:ring-teal-600 @error('benefits') border-rose-300 @enderror" placeholder="Benefit previsti dalla posizione">{{ old('benefits', $jobPosting->benefits ?? '') }}</textarea>
            @error('benefits')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="preferred_requirements" class="block text-sm font-semibold text-slate-700">Requisiti preferenziali <span class="font-normal text-slate-500">(facoltativo)</span></label>
            <textarea id="preferred_requirements" name="preferred_requirements" rows="4" class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-600 focus:ring-teal-600 @error('preferred_requirements') border-rose-300 @enderror" placeholder="Esperienze o requisiti preferenziali">{{ old('preferred_requirements', $jobPosting->preferred_requirements ?? '') }}</textarea>
            @error('preferred_requirements')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <x-ui.input
        name="work_schedule"
        label="Orario di lavoro"
        :value="$jobPosting->work_schedule ?? ''"
        placeholder="Es. Full time 40h / Part time 20h"
        maxlength="255"
        help="Facoltativo."
    />

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
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm font-semibold text-slate-500">EUR</span>
            </div>
            @error('salary_min')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
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
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm font-semibold text-slate-500">EUR</span>
            </div>
            @error('salary_max')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.input
                name="expires_at"
                type="date"
                label="Data scadenza"
                :value="isset($jobPosting) && $jobPosting->expires_at ? $jobPosting->expires_at->format('Y-m-d') : ''"
                :min="$minimumExpiryDate"
                required
            />
            <p class="mt-1 text-xs text-slate-500">La scadenza deve essere almeno 7 giorni da oggi.</p>
        </div>
    </div>

    @if (isset($jobPosting) && $jobPosting->exists)
        <x-ui.select name="status" label="Stato annuncio" required>
            <option value="active" @selected(old('status', $jobPosting->status) === 'active')>Attivo</option>
            <option value="expired" @selected(old('status', $jobPosting->status) === 'expired')>Scaduto / chiuso</option>
        </x-ui.select>
    @endif
</div>
