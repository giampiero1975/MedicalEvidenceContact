<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-600">Control center</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">Dashboard amministrazione</h2>
                <p class="mt-1 text-sm text-gray-600">Monitora utenti, aziende, professionisti e annunci della piattaforma.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2">
                <x-ui.kpi-card
                    label="Utenti"
                    :value="$usersCount"
                    detail="Account registrati sulla piattaforma"
                    tone="indigo"
                    :href="route('admin.users.index')"
                >
                    <x-slot name="icon">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.203-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.94-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.06 2.772m0 0a3 3 0 0 0-4.681 2.72 9.1 9.1 0 0 0 3.741.477m.94-3.197A5.971 5.971 0 0 0 6 18.719M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                    </x-slot>
                </x-ui.kpi-card>

                <x-ui.kpi-card
                    label="Annunci"
                    :value="$jobPostingsCount"
                    detail="Opportunità pubblicate dalle aziende"
                    tone="teal"
                    :href="route('admin.job-postings.index')"
                >
                    <x-slot name="icon">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.073a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V8.25A2.25 2.25 0 0 1 6 6h2.25m7.5 0H18a2.25 2.25 0 0 1 2.25 2.25v1.5M8.25 6V4.875A1.125 1.125 0 0 1 9.375 3.75h5.25a1.125 1.125 0 0 1 1.125 1.125V6m-7.5 0h7.5" />
                        </svg>
                    </x-slot>
                </x-ui.kpi-card>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-ui.section-card title="Ultimi utenti" description="Account creati più recentemente.">
                    @if ($recentUsers->isEmpty())
                        <x-ui.empty-state title="Nessun utente disponibile" description="I nuovi account compariranno qui." />
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($recentUsers as $user)
                                <a href="{{ route('admin.users.edit', $user) }}" class="flex items-center justify-between gap-4 py-3 text-sm transition hover:text-indigo-700">
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-gray-900">{{ $user->name }}</p>
                                        <p class="truncate text-gray-500">{{ $user->email }}</p>
                                    </div>
                                    <x-ui.status-badge tone="indigo">{{ $user->role }}</x-ui.status-badge>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </x-ui.section-card>

                <x-ui.section-card title="Ultimi annunci" description="Opportunità pubblicate più recentemente.">
                    @if ($recentJobPostings->isEmpty())
                        <x-ui.empty-state title="Nessun annuncio disponibile" description="Gli annunci pubblicati compariranno qui." />
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($recentJobPostings as $jobPosting)
                                <a href="{{ route('admin.job-postings.edit', $jobPosting) }}" class="block py-3 text-sm transition hover:text-indigo-700">
                                    <p class="font-semibold text-gray-900">{{ $jobPosting->title }}</p>
                                    <p class="mt-1 text-gray-500">{{ $jobPosting->owner?->name ?: 'Azienda non disponibile' }}</p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </x-ui.section-card>
            </div>
        </div>
    </div>
</x-app-layout>
