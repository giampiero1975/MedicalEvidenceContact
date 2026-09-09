@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            title="Annunci"
            subtitle="Gestisci tutti gli annunci pubblicati sulla piattaforma."
        >
            <a href="{{ route('admin.job-postings.create') }}" class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                Crea annuncio
            </a>
        </x-ui.page-header>

        <x-ui.card>
            <form method="GET" action="{{ route('admin.job-postings.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <x-ui.select name="status" label="Stato">
                    <option value="">Tutti</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Attivo</option>
                    <option value="expired" @selected(($filters['status'] ?? '') === 'expired')>Scaduto</option>
                </x-ui.select>
                <x-ui.input type="date" name="published_from" label="Pubblicati dal" :value="$filters['published_from'] ?? ''" />
                <x-ui.input type="date" name="published_to" label="Pubblicati al" :value="$filters['published_to'] ?? ''" />
                <x-ui.input type="date" name="expires_from" label="Scadenza dal" :value="$filters['expires_from'] ?? ''" />
                <x-ui.input type="date" name="expires_to" label="Scadenza al" :value="$filters['expires_to'] ?? ''" />

                <div class="flex items-end gap-2 md:col-span-2 xl:col-span-5">
                    <x-ui.button type="submit">Filtra</x-ui.button>
                    <a href="{{ route('admin.job-postings.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Azzera</a>
                </div>
            </form>
        </x-ui.card>

        @if ($jobPostings->isEmpty())
            <x-ui.empty-state
                title="Nessun annuncio"
                description="Nessun annuncio corrisponde ai filtri selezionati."
            >
                <a href="{{ route('admin.job-postings.create') }}" class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">
                    Crea annuncio
                </a>
            </x-ui.empty-state>
        @else
            <x-ui.card :padding="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Annuncio</th>
                                <th class="px-5 py-3">Business</th>
                                <th class="px-5 py-3">Stato</th>
                                <th class="px-5 py-3">Pubblicato</th>
                                <th class="px-5 py-3">Scadenza</th>
                                <th class="px-5 py-3 text-right">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($jobPostings as $jobPosting)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-900">{{ $jobPosting->title }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $jobPosting->positions }} {{ $jobPosting->positions === 1 ? 'posizione' : 'posizioni' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        {{ $jobPosting->businessProfile?->company_name ?: $jobPosting->owner?->businessProfile?->company_name ?: $jobPosting->owner?->name ?: 'Non assegnato' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <x-ui.badge :variant="$jobPosting->status === 'active' ? 'success' : 'neutral'">
                                            {{ $jobPosting->status === 'active' ? 'Attivo' : 'Scaduto' }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        {{ optional($jobPosting->created_at)->format('d/m/Y') ?: '—' }}
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        {{ optional($jobPosting->expires_at)->format('d/m/Y') ?: '—' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('admin.job-postings.edit', $jobPosting) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">
                                                Modifica
                                            </a>
                                            <form method="POST" action="{{ route('admin.job-postings.destroy', $jobPosting) }}" onsubmit="return confirm('Eliminare definitivamente questo annuncio?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-semibold text-rose-600 hover:text-rose-800">
                                                    Elimina
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <div>{{ $jobPostings->links() }}</div>
        @endif
    </div>
@endsection
