@php
    $user = auth()->user();
    $isProfessional = $user?->role === 'professional';
    $isBusiness = $user?->role === 'business';
@endphp

<div class="flex h-full flex-col bg-white">
    <div class="flex h-20 items-center border-b border-slate-200 px-6">
        <a href="{{ route('dashboard') }}" class="text-base font-semibold tracking-tight text-slate-950">
            Medical Evidence Contact
            <span class="mt-0.5 block text-xs font-medium uppercase tracking-[0.18em] text-teal-700">
                {{ $isProfessional ? 'Professionista' : ($isBusiness ? 'Business' : 'Area riservata') }}
            </span>
        </a>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto p-4" aria-label="Navigazione principale">
        <x-ui.sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            Dashboard
        </x-ui.sidebar-link>

        @if ($isProfessional)
            <x-ui.sidebar-link :href="route('professional.experiences.index')" :active="request()->routeIs('professional.experiences.*')">
                Esperienze e studi
            </x-ui.sidebar-link>

            <x-ui.sidebar-link :href="route('professional.documents.index')" :active="request()->routeIs('professional.documents.*')">
                Documenti
            </x-ui.sidebar-link>

            <x-ui.sidebar-link :href="route('job-postings.index')" :active="request()->routeIs('job-postings.*')">
                Offerte di lavoro
            </x-ui.sidebar-link>

            <x-ui.sidebar-link :href="route('interviews.index')" :active="request()->routeIs('interviews.*')">
                Colloqui
            </x-ui.sidebar-link>

            <x-ui.sidebar-link :href="route('professional.moodle.index')" :active="request()->routeIs('professional.moodle.*')">
                Moodle e attestati
            </x-ui.sidebar-link>
        @elseif ($isBusiness)
            <x-ui.sidebar-link :href="route('job-postings.index')" :active="request()->routeIs('job-postings.index', 'job-postings.show', 'job-postings.edit', 'job-postings.applications')">
                I miei annunci
            </x-ui.sidebar-link>

            <x-ui.sidebar-link :href="route('job-postings.create')" :active="request()->routeIs('job-postings.create')">
                Pubblica annuncio
            </x-ui.sidebar-link>

            <x-ui.sidebar-link :href="route('interviews.index')" :active="request()->routeIs('interviews.*')">
                Colloqui
            </x-ui.sidebar-link>

            <x-ui.sidebar-link :href="route('business-points-of-contact.index')" :active="request()->routeIs('business-points-of-contact.*')">
                Referenti aziendali
            </x-ui.sidebar-link>
        @endif

        @if (Route::has('profile.show'))
            <div class="my-3 border-t border-slate-200"></div>
            <x-ui.sidebar-link :href="route('profile.show')" :active="request()->routeIs('profile.show')">
                Profilo e sicurezza
            </x-ui.sidebar-link>
        @endif
    </nav>

    <div class="border-t border-slate-200 p-4">
        <div class="rounded-xl bg-slate-50 p-3">
            <p class="truncate text-sm font-semibold text-slate-900">{{ $user?->name }}</p>
            <p class="truncate text-xs text-slate-500">{{ $user?->email }}</p>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">
                Esci
            </button>
        </form>
    </div>
</div>
