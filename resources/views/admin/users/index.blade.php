@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            title="Utenti"
            subtitle="Gestisci account Professional, Business e Admin."
        >
            <x-slot name="actions">
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                    Crea utente
                </a>
            </x-slot>
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        <x-ui.card :padding="false">
            @if ($users->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-slate-700">Nome</th>
                                <th class="px-6 py-3 font-semibold text-slate-700">Email</th>
                                <th class="px-6 py-3 font-semibold text-slate-700">Ruolo</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-700">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($users as $user)
                                @php
                                    $roleVariant = match ($user->role) {
                                        'admin' => 'danger',
                                        'business' => 'warning',
                                        default => 'info',
                                    };
                                @endphp
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="font-semibold text-slate-950">{{ $user->name }}</div>
                                        <div class="mt-0.5 text-xs text-slate-500">ID {{ $user->id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        <x-ui.badge :variant="$roleVariant">
                                            {{ match ($user->role) {
                                                'professional' => 'Professional',
                                                'business' => 'Business',
                                                'admin' => 'Admin',
                                                default => ucfirst($user->role),
                                            } }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-teal-700 transition hover:bg-teal-50 hover:text-teal-900">
                                                Modifica
                                            </a>

                                            @unless (auth()->user()->is($user))
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Eliminare definitivamente questo utente?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 hover:text-rose-800">
                                                        Elimina
                                                    </button>
                                                </form>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6">
                    <x-ui.empty-state
                        title="Nessun utente"
                        description="Crea il primo account della piattaforma."
                    >
                        <x-slot name="actions">
                            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">
                                Crea utente
                            </a>
                        </x-slot>
                    </x-ui.empty-state>
                </div>
            @endif
        </x-ui.card>

        @if ($users->hasPages())
            <div>{{ $users->links() }}</div>
        @endif
    </div>
@endsection
