<x-guest-layout>
    <div class="min-h-screen bg-gray-950 px-4 py-8 text-white sm:py-12">
        <div class="mx-auto grid min-h-[calc(100vh-4rem)] w-full max-w-5xl items-center gap-8 lg:grid-cols-[0.9fr_1.1fr]">
            <section class="hidden lg:block">
                <div class="mb-8">
                    <x-authentication-card-logo />
                </div>

                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-300">Area riservata</p>
                <h1 class="mt-4 text-4xl font-semibold leading-tight">Accesso amministrazione</h1>
                <p class="mt-5 max-w-md text-sm leading-6 text-gray-300">
                    Ingresso dedicato allo staff interno per la gestione di profili, aziende, annunci, candidature e controlli operativi della piattaforma.
                </p>

                <div class="mt-8 grid gap-3 text-sm text-gray-300">
                    <div class="rounded-md border border-white/10 bg-white/5 p-4">
                        <p class="font-semibold text-white">URL non pubblico</p>
                        <p class="mt-1">Questa pagina e separata dal login di professionisti e aziende.</p>
                    </div>
                    <div class="rounded-md border border-white/10 bg-white/5 p-4">
                        <p class="font-semibold text-white">Solo account admin</p>
                        <p class="mt-1">Gli account business e professionista vengono respinti da questo ingresso.</p>
                    </div>
                </div>
            </section>

            <section class="mx-auto w-full max-w-md rounded-lg bg-white p-6 text-gray-900 shadow-xl">
                <div class="mb-6 lg:hidden">
                    <x-authentication-card-logo />
                </div>

                <div class="mb-6">
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Admin</p>
                    <h2 class="mt-2 text-2xl font-semibold text-gray-900">Accedi all'area staff</h2>
                    <p class="mt-2 text-sm text-gray-600">Usa solo credenziali amministrative autorizzate.</p>
                </div>

                <x-validation-errors class="mb-4" />

                @session('status')
                    <div class="mb-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-700">
                        {{ $value }}
                    </div>
                @endsession

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="staff_login" value="1">

                    <div>
                        <x-label for="admin-email" value="Email admin" />
                        <x-input id="admin-email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    </div>

                    <div class="mt-4">
                        <x-label for="admin-password" value="Password" />
                        <x-input id="admin-password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <label for="admin-remember" class="flex items-center">
                            <x-checkbox id="admin-remember" name="remember" />
                            <span class="ms-2 text-sm text-gray-600">Ricordami</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-gray-600 underline hover:text-gray-900" href="{{ route('password.request') }}">
                                Password dimenticata?
                            </a>
                        @endif
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                        <a class="text-sm text-gray-600 underline hover:text-gray-900" href="{{ route('login') }}">
                            Login utenti
                        </a>

                        <x-button>
                            Accedi come admin
                        </x-button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-guest-layout>