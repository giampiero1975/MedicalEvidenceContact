@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <x-ui.page-header
            title="Modifica annuncio"
            subtitle="Aggiorna contenuti, proprietario, stato e scadenza dell’annuncio."
        />

        @if ($errors->any())
            <x-ui.alert variant="danger" title="Controlla i dati inseriti">
                Sono presenti campi mancanti o non validi.
            </x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('admin.job-postings.update', $jobPosting) }}" class="space-y-8">
                @csrf
                @method('PUT')
                @include('admin.job-postings._form')

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('admin.job-postings.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">
                        Torna agli annunci
                    </a>
                    <x-ui.button type="submit">Salva modifiche</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
