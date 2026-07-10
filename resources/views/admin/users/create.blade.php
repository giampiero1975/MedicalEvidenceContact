@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <x-ui.page-header
            title="Crea utente"
            subtitle="Crea un account Professional, Business o Admin."
        />

        <x-ui.card>
            <x-validation-errors class="mb-6" />

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                @csrf
                @include('admin.users._form')

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">
                        Annulla
                    </a>
                    <x-ui.button type="submit">Crea utente</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
