@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            title="Modifica tipologia aziendale"
            subtitle="Aggiorna nome, ordine e disponibilità della tipologia."
        />

        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('admin.business-types.update', $businessType) }}">
            @csrf
            @method('PUT')
            @include('admin.business-types._form')
        </form>
    </div>
@endsection
