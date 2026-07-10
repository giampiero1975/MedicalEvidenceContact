@php
    $role = old('role', $user->role ?? 'professional');
    $businessProfile = $user->businessProfile;
@endphp

<div x-data="{ role: @js($role) }" class="space-y-8">
    <section class="space-y-5">
        <div>
            <h2 class="text-base font-semibold text-slate-950">Account</h2>
            <p class="mt-1 text-sm text-slate-500">Dati di accesso e ruolo principale dell'utente.</p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-ui.select name="role" label="Ruolo" x-model="role" required>
                    @foreach (['professional' => 'Professional', 'business' => 'Business', 'admin' => 'Admin'] as $value => $label)
                        <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <x-ui.input name="first_name" label="Nome" :value="$user->first_name" required autocomplete="given-name" />
            <x-ui.input name="last_name" label="Cognome" :value="$user->last_name" required autocomplete="family-name" />
            <x-ui.input name="email" label="Email" type="email" :value="$user->email" required autocomplete="email" />
            <x-ui.input name="phone" label="Telefono" :value="$user->phone" autocomplete="tel" />
            <x-ui.input
                name="password"
                label="Password"
                type="password"
                :required="! $user->exists"
                :help="$user->exists ? 'Lascia vuoto per mantenere la password attuale.' : null"
                autocomplete="new-password"
            />
            <x-ui.input
                name="password_confirmation"
                label="Conferma password"
                type="password"
                :required="! $user->exists"
                autocomplete="new-password"
            />
        </div>
    </section>

    <section x-cloak x-show="role === 'professional'" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 sm:p-6">
        <div>
            <h2 class="text-base font-semibold text-slate-950">Profilo Professional</h2>
            <p class="mt-1 text-sm text-slate-500">Informazioni personali e indirizzo di residenza.</p>
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <x-ui.select name="nationality" label="Nazionalità" x-bind:required="role === 'professional'">
                @foreach (config('nationalities.values') as $nationality)
                    <option value="{{ $nationality }}" @selected(old('nationality', $user->nationality ?: 'Italiana') === $nationality)>{{ $nationality }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.input name="address_country" label="Paese" :value="$user->address_country ?: 'Italia'" x-bind:required="role === 'professional'" autocomplete="country-name" />
            <x-ui.input name="address_city" label="Città" :value="$user->address_city" x-bind:required="role === 'professional'" autocomplete="address-level2" />
            <x-ui.input name="address_province" label="Provincia" :value="$user->address_province" x-bind:required="role === 'professional'" autocomplete="address-level1" />
            <x-ui.input name="postal_code" label="CAP" :value="$user->postal_code" x-bind:required="role === 'professional'" autocomplete="postal-code" />
            <x-ui.input name="street_address" label="Indirizzo" :value="$user->street_address" x-bind:required="role === 'professional'" autocomplete="street-address" />
        </div>
    </section>

    <section x-cloak x-show="role === 'business'" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 sm:p-6">
        <div>
            <h2 class="text-base font-semibold text-slate-950">Profilo Business</h2>
            <p class="mt-1 text-sm text-slate-500">Dati identificativi della struttura sanitaria o dell'organizzazione.</p>
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-ui.input name="company_name" label="Nome azienda" :value="$businessProfile?->company_name" x-bind:required="role === 'business'" />
            </div>
            <x-ui.input name="company_type" label="Tipo azienda" :value="$businessProfile?->company_type" x-bind:required="role === 'business'" />
            <x-ui.input name="location" label="Località" :value="$businessProfile?->location" x-bind:required="role === 'business'" />
            <x-ui.input name="employee_count" label="Numero dipendenti" type="number" min="1" :value="$businessProfile?->employee_count" />
        </div>
    </section>

    <section x-cloak x-show="role === 'admin'" class="rounded-2xl border border-amber-200 bg-amber-50 p-5 sm:p-6">
        <h2 class="text-base font-semibold text-amber-950">Account amministrativo</h2>
        <p class="mt-1 text-sm leading-6 text-amber-800">
            Questo utente avrà accesso all'area di amministrazione e alla gestione degli account e degli annunci.
        </p>
    </section>
</div>
