<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Profilo e sicurezza"
            subtitle="Aggiorna i dati del tuo account, la password e le impostazioni di sicurezza."
        >
            <x-slot name="actions">
                <x-ui.button href="{{ route('dashboard') }}" variant="secondary" size="sm">Torna alla dashboard</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-8">
        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Account" :value="ucfirst(auth()->user()->role)" hint="Ruolo corrente" />
            <x-ui.stat-card label="Email" :value="auth()->user()->email" hint="Indirizzo di accesso" />
            <x-ui.stat-card
                label="Autenticazione a due fattori"
                :value="auth()->user()->two_factor_secret ? 'Attiva' : 'Non attiva'"
                hint="Protezione aggiuntiva dell'account"
            />
            <x-ui.stat-card label="Password" value="Configurata" hint="Puoi aggiornarla in qualsiasi momento" />
        </section>

        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            @livewire('profile.update-profile-information-form')
        @endif

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            <div>
                @livewire('profile.update-password-form')
            </div>
        @endif

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <div>
                @livewire('profile.two-factor-authentication-form')
            </div>
        @endif

        <div>
            @livewire('profile.logout-other-browser-sessions-form')
        </div>

        @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures() && auth()->user()->role !== 'admin')
            <div>
                @livewire('profile.delete-user-form')
            </div>
        @endif
    </div>
</x-app-layout>
