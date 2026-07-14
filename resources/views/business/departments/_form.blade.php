<div class="grid gap-5 sm:grid-cols-2">
    <x-ui.select name="business_location_id" label="Sede" required>
        <option value="">Seleziona sede</option>
        @foreach ($locations as $locationOption)
            <option value="{{ $locationOption->id }}" @selected((string) old('business_location_id', $department->business_location_id ?? '') === (string) $locationOption->id)>
                {{ $locationOption->name }} · {{ $locationOption->city }}
            </option>
        @endforeach
    </x-ui.select>

    <x-ui.input name="name" label="Nome reparto / unità operativa" :value="$department->name ?? ''" required />
    <x-ui.input name="code" label="Codice interno" :value="$department->code ?? ''" />
    <x-ui.input name="manager_name" label="Responsabile" :value="$department->manager_name ?? ''" />
    <x-ui.input name="email" type="email" label="Email" :value="$department->email ?? ''" />
    <x-ui.input name="phone" label="Telefono" :value="$department->phone ?? ''" />

    <div class="sm:col-span-2">
        <label for="description" class="block text-sm font-semibold text-slate-700">Descrizione</label>
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-600 focus:ring-teal-600">{{ old('description', $department->description ?? '') }}</textarea>
        @error('description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <label class="inline-flex items-center gap-3 text-sm font-medium text-slate-700 sm:col-span-2">
        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600" @checked(old('is_active', $department->is_active ?? true))>
        Reparto attivo
    </label>
</div>
