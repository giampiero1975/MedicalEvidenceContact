@php
    $role = old('role', $user->role ?? 'professional');
    $businessProfile = $user->businessProfile;
@endphp

<div x-data="{ role: @js($role) }" class="space-y-6">
<div>
    <x-label for="role" value="Ruolo" />
    <select id="role" name="role" x-model="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach (['professional' => 'Professionista', 'business' => 'Business', 'admin' => 'Admin'] as $value => $label)
            <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-label for="first_name" value="Nome" />
        <x-input id="first_name" class="mt-1 block w-full" name="first_name" :value="old('first_name', $user->first_name)" required />
    </div>

    <div>
        <x-label for="last_name" value="Cognome" />
        <x-input id="last_name" class="mt-1 block w-full" name="last_name" :value="old('last_name', $user->last_name)" required />
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-label for="email" value="Email" />
        <x-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email', $user->email)" required />
    </div>

    <div>
        <x-label for="phone" value="Telefono" />
        <x-input id="phone" class="mt-1 block w-full" name="phone" :value="old('phone', $user->phone)" />
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-label for="password" value="Password" />
        <x-input id="password" class="mt-1 block w-full" type="password" name="password" :required="! $user->exists" />
    </div>

    <div>
        <x-label for="password_confirmation" value="Conferma password" />
        <x-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" :required="! $user->exists" />
    </div>
</div>

<section x-show="role === 'professional'" class="rounded-md border border-gray-200 p-4">
    <h3 class="font-semibold text-gray-900">Campi professionista</h3>
    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <x-label for="nationality" value="Nazionalita" />
            <select id="nationality" name="nationality" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (config('nationalities.values') as $nationality)
                    <option value="{{ $nationality }}" @selected(old('nationality', $user->nationality ?: 'Italiana') === $nationality)>{{ $nationality }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-label for="address_country" value="Paese" />
            <x-input id="address_country" class="mt-1 block w-full" name="address_country" :value="old('address_country', $user->address_country ?: 'Italia')" x-bind:required="role === 'professional'" />
        </div>
        <div>
            <x-label for="address_city" value="Citta" />
            <x-input id="address_city" class="mt-1 block w-full" name="address_city" :value="old('address_city', $user->address_city)" x-bind:required="role === 'professional'" />
        </div>
        <div>
            <x-label for="address_province" value="Provincia" />
            <x-input id="address_province" class="mt-1 block w-full" name="address_province" :value="old('address_province', $user->address_province)" x-bind:required="role === 'professional'" />
        </div>
        <div>
            <x-label for="postal_code" value="CAP" />
            <x-input id="postal_code" class="mt-1 block w-full" name="postal_code" :value="old('postal_code', $user->postal_code)" x-bind:required="role === 'professional'" />
        </div>
        <div>
            <x-label for="street_address" value="Indirizzo" />
            <x-input id="street_address" class="mt-1 block w-full" name="street_address" :value="old('street_address', $user->street_address)" x-bind:required="role === 'professional'" />
        </div>
    </div>
</section>

<section x-show="role === 'business'" class="rounded-md border border-gray-200 p-4">
    <h3 class="font-semibold text-gray-900">Campi business</h3>
    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-label for="company_name" value="Nome azienda" />
            <x-input id="company_name" class="mt-1 block w-full" name="company_name" :value="old('company_name', $businessProfile?->company_name)" x-bind:required="role === 'business'" />
        </div>
        <div>
            <x-label for="company_type" value="Tipo azienda" />
            <select id="company_type" name="company_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-bind:required="role === 'business'">
                <option value="">Seleziona</option>
                @foreach (config('business-types.values') as $type)
                    <option value="{{ $type }}" @selected(old('company_type', $businessProfile?->company_type) === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-label for="location" value="Localita" />
            <x-input id="location" class="mt-1 block w-full" name="location" :value="old('location', $businessProfile?->location)" x-bind:required="role === 'business'" />
        </div>
        <div>
            <x-label for="employee_count" value="Numero dipendenti" />
            <x-input id="employee_count" class="mt-1 block w-full" type="number" min="1" name="employee_count" :value="old('employee_count', $businessProfile?->employee_count)" />
        </div>
    </div>
</section>
</div>
