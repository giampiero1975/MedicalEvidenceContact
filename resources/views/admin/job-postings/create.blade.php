@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <x-ui.page-header
            title="Crea annuncio"
            subtitle="Pubblica un nuovo annuncio per conto di un business registrato."
        />

        @if ($errors->any())
            <x-ui.alert variant="danger" title="Controlla i dati inseriti">
                Sono presenti campi mancanti o non validi.
            </x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('admin.job-postings.store') }}" class="space-y-8">
                @csrf
                @include('admin.job-postings._form')

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('admin.job-postings.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">
                        Annulla
                    </a>
                    <x-ui.button type="submit">Crea annuncio</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
