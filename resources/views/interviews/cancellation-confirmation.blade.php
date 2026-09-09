<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Conferma annullamento" :description="$posting->title">
            <x-slot name="actions">
                <x-ui.button href="{{ route('interviews.index') }}#interview-{{ $interview->id }}" variant="secondary" size="sm">Torna ai colloqui</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="mx-auto max-w-2xl">
        <x-ui.card>
            <h2 class="text-lg font-semibold text-slate-950">Confermare l'annullamento definitivo?</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Il colloquio del {{ $interview->scheduled_at->format('d/m/Y H:i') }} risulta annullato. Puoi confermare definitivamente l'annullamento oppure tornare alla sezione colloqui e richiedere una riprogrammazione.</p>

            <dl class="mt-6 grid gap-4 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2">
                <div><dt class="font-medium text-slate-500">Annuncio</dt><dd class="mt-1 font-semibold text-slate-900">{{ $posting->title }}</dd></div>
                <div><dt class="font-medium text-slate-500">Stato</dt><dd class="mt-1 font-semibold text-slate-900">{{ $interview->statusLabel() }}</dd></div>
            </dl>

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <x-ui.button href="{{ route('interviews.index') }}#interview-{{ $interview->id }}" variant="secondary" class="w-full">Riprogramma</x-ui.button>
                <form method="POST" action="{{ route('interviews.cancellation.confirm', $interview) }}">
                    @csrf
                    @method('PATCH')
                    <x-ui.button type="submit" variant="danger" class="w-full">Conferma annullamento definitivo</x-ui.button>
                </form>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
