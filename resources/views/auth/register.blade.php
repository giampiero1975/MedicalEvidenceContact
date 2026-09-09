<x-guest-layout>
    <div x-data="{ accountType: @js(old('account_type', 'professional')) }" class="min-h-screen bg-gray-100 px-4 py-8 sm:py-12">
        <div class="mx-auto w-full max-w-5xl">
            <div class="mb-8 flex justify-center">
                <x-authentication-card-logo />
            </div>

            <div class="grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
                <section class="rounded-lg bg-white p-6 shadow-md">
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Medical Evidence Contact</p>
                    <h1 class="mt-3 text-3xl font-semibold text-gray-900">Crea il tuo account</h1>
                    <p class="mt-4 text-sm leading-6 text-gray-600">
                        La registrazione raccoglie solo i dati necessari per avviare il matching tra professionisti socio-sanitari e aziende. I contatti non saranno mostrati pubblicamente.
                    </p>
                    <div class="mt-6 space-y-3 text-sm text-gray-700">
                        <div class="rounded-md border border-gray-200 p-4">
                            <p class="font-semibold text-gray-900">Professionista</p>
                            <p class="mt-1 text-gray-600">Dati personali, nazionalità e indirizzo completo per creare il profilo professionale.</p>
                        </div>
                        <div class="rounded-md border border-gray-200 p-4">
                            <p class="font-semibold text-gray-900">Business</p>
                            <p class="mt-1 text-gray-600">Dati azienda e contatto principale per gestire annunci, candidature e colloqui.</p>
                        </div>
                        <div class="rounded-md border border-gray-200 p-4">
                            <p class="font-semibold text-gray-900">Staff</p>
                            <p class="mt-1 text-gray-600">Gli admin non si registrano da qui: usano l'ingresso riservato dello staff.</p>
                            <a class="mt-2 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-800" href="{{ route('admin.login') }}">Vai all'accesso admin</a>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg bg-white p-6 shadow-md">
                    <x-validation-errors class="mb-4" />

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <fieldset>
                            <legend class="text-base font-semibold text-gray-900">Tipo di account</legend>
                            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <label class="cursor-pointer rounded-md border p-4" :class="accountType === 'professional' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300'">
                                    <input class="sr-only" type="radio" name="account_type" value="professional" x-model="accountType">
                                    <span class="block font-semibold text-gray-900">Professionista</span>
                                    <span class="mt-1 block text-sm text-gray-600">OSS, infermiere, anestesista, fisioterapista.</span>
                                </label>
                                <label class="cursor-pointer rounded-md border p-4" :class="accountType === 'business' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300'">
                                    <input class="sr-only" type="radio" name="account_type" value="business" x-model="accountType">
                                    <span class="block font-semibold text-gray-900">Business</span>
                                    <span class="mt-1 block text-sm text-gray-600">Cooperative, RSA, cliniche, farmacie.</span>
                                </label>
                            </div>
                        </fieldset>

                        <fieldset class="mt-6">
                            <legend class="text-base font-semibold text-gray-900" x-text="accountType === 'business' ? 'Point of Contact principale' : 'Dati account'"></legend>
                            <p x-cloak x-show="accountType === 'business'" class="mt-1 text-sm text-gray-500">Questo contatto sarà creato come POC principale dell'azienda.</p>
                            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div><x-label for="first_name" value="Nome" /><x-input id="first_name" class="mt-1 block w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" /></div>
                                <div><x-label for="last_name" value="Cognome" /><x-input id="last_name" class="mt-1 block w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" /></div>
                                <div><x-label for="email" value="Email" /><x-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" /></div>
                                <div><x-label for="phone" value="Telefono" /><x-input id="phone" class="mt-1 block w-full" type="text" name="phone" :value="old('phone')" required autocomplete="tel" /></div>
                                <div x-cloak x-show="accountType === 'business'" class="sm:col-span-2"><x-label for="poc_role" value="Ruolo in azienda" /><x-input id="poc_role" class="mt-1 block w-full" type="text" name="poc_role" :value="old('poc_role')" /></div>
                                <div><x-label for="password" value="Password" /><x-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" /></div>
                                <div><x-label for="password_confirmation" value="Conferma password" /><x-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" /></div>
                            </div>
                        </fieldset>

                        <fieldset x-cloak x-show="accountType === 'professional'" class="mt-6">
                            <legend class="text-base font-semibold text-gray-900">Profilo professionista</legend>
                            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <x-label for="profession" value="Professione" />
                                    <select id="profession" name="profession" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach (config('professional-professions.values') as $value => $label)
                                            <option value="{{ $value }}" @selected(old('profession', 'oss') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-label for="nationality" value="Nazionalità" />
                                    <select id="nationality" name="nationality" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach (config('nationalities.values') as $nationality)
                                            <option value="{{ $nationality }}" @selected(old('nationality', 'Italiana') === $nationality)>{{ $nationality }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div><x-label for="address_country" value="Paese" /><x-input id="address_country" class="mt-1 block w-full" type="text" name="address_country" :value="old('address_country', 'Italia')" /></div>
                                <div><x-label for="address_city" value="Città" /><x-input id="address_city" class="mt-1 block w-full" type="text" name="address_city" :value="old('address_city')" /></div>
                                <div><x-label for="address_province" value="Provincia" /><x-input id="address_province" class="mt-1 block w-full" type="text" name="address_province" :value="old('address_province')" /></div>
                                <div><x-label for="postal_code" value="CAP" /><x-input id="postal_code" class="mt-1 block w-full" type="text" name="postal_code" :value="old('postal_code')" /></div>
                                <div><x-label for="street_address" value="Indirizzo" /><x-input id="street_address" class="mt-1 block w-full" type="text" name="street_address" :value="old('street_address')" /></div>
                            </div>
                        </fieldset>

                        <fieldset x-cloak x-show="accountType === 'business'" class="mt-6">
                            <legend class="text-base font-semibold text-gray-900">Profilo azienda</legend>
                            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2"><x-label for="company_name" value="Nome azienda / struttura" /><x-input id="company_name" class="mt-1 block w-full" type="text" name="company_name" :value="old('company_name')" /></div>
                                <div>
                                    <x-label for="company_type" value="Tipo azienda" />
                                    <select id="company_type" name="company_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seleziona</option>
                                        @foreach ($businessTypes as $type)
                                            <option value="{{ $type->name }}" @selected(old('company_type') === $type->name)>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div><x-label for="vat_number" value="Partita IVA" /><x-input id="vat_number" class="mt-1 block w-full" type="text" inputmode="numeric" maxlength="11" name="vat_number" :value="old('vat_number')" /></div>
                                <div>
                                    <x-label for="employee_count" value="Numero dipendenti" />
                                    <select id="employee_count" name="employee_count" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seleziona</option>
                                        <option value="10" @selected((string) old('employee_count') === '10')>1-10</option>
                                        <option value="50" @selected((string) old('employee_count') === '50')>11-50</option>
                                        <option value="200" @selected((string) old('employee_count') === '200')>51-200</option>
                                        <option value="500" @selected((string) old('employee_count') === '500')>201-500</option>
                                        <option value="501" @selected((string) old('employee_count') === '501')>500+</option>
                                    </select>
                                </div>
                                <div><x-label for="company_country" value="Paese" /><x-input id="company_country" class="mt-1 block w-full" type="text" name="company_country" :value="old('company_country', 'Italia')" /></div>
                                <div class="sm:col-span-2"><x-label for="company_street_address" value="Via / indirizzo" /><x-input id="company_street_address" class="mt-1 block w-full" type="text" name="company_street_address" :value="old('company_street_address')" /></div>
                                <div><x-label for="company_city" value="Città" /><x-input id="company_city" class="mt-1 block w-full" type="text" name="company_city" :value="old('company_city')" /></div>
                                <div><x-label for="company_province" value="Provincia" /><x-input id="company_province" class="mt-1 block w-full" type="text" name="company_province" :value="old('company_province')" /></div>
                                <div><x-label for="company_postal_code" value="CAP" /><x-input id="company_postal_code" class="mt-1 block w-full" type="text" name="company_postal_code" :value="old('company_postal_code')" /></div>
                            </div>
                        </fieldset>

                        @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                            <div class="mt-4"><x-label for="terms"><div class="flex items-center"><x-checkbox name="terms" id="terms" required /><div class="ms-2">{!! __('I agree to the :terms_of_service and :privacy_policy', ['terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline">'.__('Terms of Service').'</a>', 'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline">'.__('Privacy Policy').'</a>']) !!}</div></div></x-label></div>
                        @endif

                        <div class="mt-6 flex items-center justify-end">
                            <a class="rounded-md text-sm text-gray-600 underline hover:text-gray-900" href="{{ route('login') }}">Hai già un account?</a>
                            <x-button class="ms-4">Crea account</x-button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</x-guest-layout>
