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

        @if ($jobPostings->isEmpty())
            <x-ui.empty-state
                title="Nessun annuncio"
                description="Non sono ancora presenti annunci sulla piattaforma."
            >
                <a href="{{ route('admin.job-postings.create') }}" class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">
                    Crea il primo annuncio
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
                                        {{ $jobPosting->owner?->businessProfile?->company_name ?: $jobPosting->owner?->name ?: 'Non assegnato' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <x-ui.badge :variant="$jobPosting->status === 'active' ? 'success' : 'neutral'">
                                            {{ $jobPosting->status === 'active' ? 'Attivo' : 'Scaduto' }}
                                        </x-ui.badge>
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
