@extends('layouts.admin')

@php($title = 'Dashboard admin')

@section('content')
    <div class="space-y-8">
        <x-ui.page-header
            title="Dashboard admin"
            subtitle="Vista operativa su utenti, annunci e attività della piattaforma."
        />

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Riepilogo piattaforma">
            <x-ui.stat-card
                label="Utenti"
                :value="$usersCount"
                description="Account registrati sulla piattaforma"
                :href="route('admin.users.index')"
            />

            <x-ui.stat-card
                label="Annunci"
                :value="$jobPostingsCount"
                description="Offerte di lavoro presenti"
                :href="route('admin.job-postings.index')"
            />
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-ui.card>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Ultimi utenti</h2>
                        <p class="mt-1 text-sm text-slate-500">Gli account creati più recentemente.</p>
                    </div>

                    <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">
                        Vedi tutti
                    </a>
                </div>

                @forelse ($recentUsers as $user)
                    <div class="mt-5 divide-y divide-slate-100 border-t border-slate-100">
                        @foreach ($recentUsers as $user)
                            <a href="{{ route('admin.users.edit', $user) }}" class="flex items-center justify-between gap-4 py-3 text-sm transition hover:text-teal-800">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900">{{ $user->name }}</p>
                                    <p class="truncate text-slate-500">{{ $user->email }}</p>
                                </div>
                                <x-ui.badge>{{ ucfirst($user->role) }}</x-ui.badge>
                            </a>
                        @endforeach
                    </div>
                @empty
                    <div class="mt-5">
                        <x-ui.empty-state
                            title="Nessun utente"
                            description="Non sono ancora presenti account sulla piattaforma."
                        />
                    </div>
                @endforelse
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">Ultimi annunci</h2>
                        <p class="mt-1 text-sm text-slate-500">Le offerte pubblicate più recentemente.</p>
                    </div>

                    <a href="{{ route('admin.job-postings.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">
                        Vedi tutti
                    </a>
                </div>

                @forelse ($recentJobPostings as $jobPosting)
                    <div class="mt-5 divide-y divide-slate-100 border-t border-slate-100">
                        @foreach ($recentJobPostings as $jobPosting)
                            <a href="{{ route('admin.job-postings.edit', $jobPosting) }}" class="block py-3 text-sm transition hover:text-teal-800">
                                <p class="font-semibold text-slate-900">{{ $jobPosting->title }}</p>
                                <p class="mt-1 text-slate-500">{{ $jobPosting->owner?->name ?? 'Azienda non disponibile' }}</p>
                            </a>
                        @endforeach
                    </div>
                @empty
                    <div class="mt-5">
                        <x-ui.empty-state
                            title="Nessun annuncio"
                            description="Non sono ancora state pubblicate offerte di lavoro."
                        />
                    </div>
                @endforelse
            </x-ui.card>
        </div>
    </div>
@endsection
