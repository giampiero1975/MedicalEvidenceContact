@php
    $location = $location ?? null;
@endphp

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <x-ui.input name="name" label="Nome sede" :value="$location?->name" placeholder="Es. Sede Milano" required />

    <x-ui.select name="type" label="Tipologia" required>
        <option value="operational" @selected(old('type', $location?->type ?? 'operational') === 'operational')>Sede operativa</option>
        <option value="legal" @selected(old('type', $location?->type) === 'legal')>Sede legale</option>
    </x-ui.select>

    <div class="md:col-span-2">
        <x-ui.input name="street_address" label="Indirizzo" :value="$location?->street_address" placeholder="Via e numero civico" required />
    </div>

    <x-ui.input name="city" label="Città" :value="$location?->city" required />
    <x-ui.input name="province" label="Provincia" :value="$location?->province" placeholder="MI" maxlength="10" />
    <x-ui.input name="postal_code" label="CAP" :value="$location?->postal_code" />
    <x-ui.input name="country" label="Paese" :value="$location?->country ?? 'Italia'" required />

    <x-ui.input name="email" type="email" label="Email sede" :value="$location?->email" />
    <x-ui.input name="phone" label="Telefono sede" :value="$location?->phone" />

    <div class="flex flex-wrap items-center gap-6 md:col-span-2 xl:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
            <input type="checkbox" name="is_primary" value="1" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600" @checked(old('is_primary', $location?->is_primary ?? false))>
            Sede principale
        </label>
        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600" @checked(old('is_active', $location?->is_active ?? true))>
            Sede attiva
        </label>
    </div>
</div>
