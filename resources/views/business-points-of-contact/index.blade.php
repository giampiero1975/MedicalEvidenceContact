<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Point of Contact</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $businessProfile->company_name }}</p>
            </div>

            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50">
                Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            @session('status')
                <div class="rounded-md bg-green-50 p-4 text-sm font-medium text-green-700">{{ $value }}</div>
            @endsession

            @session('warning')
                <div class="rounded-md bg-amber-50 p-4 text-sm font-medium text-amber-800">{{ $value }}</div>
            @endsession

            <x-validation-errors />

            <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <section class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Referenti aziendali</h3>
                            <p class="mt-1 text-sm text-gray-600">Aggiungi, modifica, elimina o imposta il Point of Contact principale.</p>
                        </div>
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $pointsOfContact->count() }}</span>
                    </div>

                    @if ($pointsOfContact->isEmpty())
                        <div class="mt-6 rounded-md border border-dashed border-gray-300 p-5 text-sm text-gray-600">Non hai ancora aggiunto Point of Contact.</div>
                    @else
                        <div class="mt-6 grid gap-4">
                            @foreach ($pointsOfContact as $pointOfContact)
                                <article class="rounded-lg border border-gray-200 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-semibold text-gray-900">{{ $pointOfContact->fullName() }}</p>
                                            @if ($pointOfContact->is_primary)
                                                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">POC principale</span>
                                            @endif
                                            @if ($pointOfContact->user)
                                                <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">Account attivo</span>
                                            @endif
                                        </div>

                                        @unless ($pointOfContact->is_primary)
                                            <form method="POST" action="{{ route('business-points-of-contact.primary', $pointOfContact) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-xs font-semibold text-indigo-700 hover:text-indigo-900">Imposta principale</button>
                                            </form>
                                        @endunless
                                    </div>

                                    <form method="POST" action="{{ route('business-points-of-contact.update', $pointOfContact) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                                        @csrf
                                        @method('PUT')

                                        <div><x-label for="first_name_{{ $pointOfContact->id }}" value="Nome" /><x-input id="first_name_{{ $pointOfContact->id }}" class="mt-1 block w-full" type="text" name="first_name" :value="$pointOfContact->first_name" required /></div>
                                        <div><x-label for="last_name_{{ $pointOfContact->id }}" value="Cognome" /><x-input id="last_name_{{ $pointOfContact->id }}" class="mt-1 block w-full" type="text" name="last_name" :value="$pointOfContact->last_name" required /></div>
                                        <div><x-label for="role_{{ $pointOfContact->id }}" value="Ruolo in azienda" /><x-input id="role_{{ $pointOfContact->id }}" class="mt-1 block w-full" type="text" name="role" :value="$pointOfContact->role" required /></div>
                                        <div><x-label for="email_{{ $pointOfContact->id }}" value="Email" /><x-input id="email_{{ $pointOfContact->id }}" class="mt-1 block w-full" type="email" name="email" :value="$pointOfContact->email" required /></div>
                                        <div class="sm:col-span-2"><x-label for="phone_{{ $pointOfContact->id }}" value="Telefono" /><x-input id="phone_{{ $pointOfContact->id }}" class="mt-1 block w-full" type="text" name="phone" :value="$pointOfContact->phone" /></div>

                                        <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-4 sm:col-span-2">
                                            <x-button type="submit">Salva modifiche</x-button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('business-points-of-contact.destroy', $pointOfContact) }}" class="mt-3 flex justify-end" onsubmit="return confirm('Eliminare questo Point of Contact?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800" @disabled($pointsOfContact->count() <= 1)>
                                            Elimina Point of Contact
                                        </button>
                                    </form>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Aggiungi Point of Contact</h3>
                    <p class="mt-1 text-sm text-gray-600">Il nuovo referente riceverà credenziali di accesso e verifica email.</p>

                    <form method="POST" action="{{ route('business-points-of-contact.store') }}" class="mt-6 space-y-5">
                        @csrf

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><x-label for="first_name" value="Nome" /><x-input id="first_name" class="mt-1 block w-full" type="text" name="first_name" :value="old('first_name')" required /></div>
                            <div><x-label for="last_name" value="Cognome" /><x-input id="last_name" class="mt-1 block w-full" type="text" name="last_name" :value="old('last_name')" required /></div>
                        </div>

                        <div><x-label for="role" value="Ruolo in azienda" /><x-input id="role" class="mt-1 block w-full" type="text" name="role" :value="old('role')" required /></div>
                        <div><x-label for="email" value="Email" /><x-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required /></div>
                        <div><x-label for="phone" value="Telefono" /><x-input id="phone" class="mt-1 block w-full" type="text" name="phone" :value="old('phone')" /></div>

                        <div class="flex justify-end border-t border-gray-100 pt-5">
                            <x-button>Aggiungi Point of Contact</x-button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
