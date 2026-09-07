@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            title="Nuova tipologia aziendale"
            subtitle="Aggiungi una nuova opzione disponibile per gli account Business."
        />

        <form method="POST" action="{{ route('admin.business-types.store') }}">
            @csrf
            @include('admin.business-types._form')
        </form>
    </div>
@endsection
