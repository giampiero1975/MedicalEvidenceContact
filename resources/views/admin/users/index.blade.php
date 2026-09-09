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

        <x-ui.card>
            <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <x-ui.select name="role" label="Tipo account">
                    <option value="">Tutti</option>
                    <option value="professional" @selected(($filters['role'] ?? '') === 'professional')>Professional</option>
                    <option value="business" @selected(($filters['role'] ?? '') === 'business')>Business</option>
                    <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>Admin</option>
                </x-ui.select>

                <x-ui.select name="professional_category" label="Categoria professionale">
                    <option value="">Tutte</option>
                    @foreach ($professionalCategories as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['professional_category'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select name="business_type" label="Tipo azienda">
                    <option value="">Tutti</option>
                    @foreach ($businessTypes as $businessType)
                        <option value="{{ $businessType->name }}" @selected(($filters['business_type'] ?? '') === $businessType->name)>{{ $businessType->name }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.input name="location" label="Località" :value="$filters['location'] ?? ''" placeholder="Città o località" />
                <x-ui.input type="date" name="registered_from" label="Registrati dal" :value="$filters['registered_from'] ?? ''" />
                <x-ui.input type="date" name="registered_to" label="Registrati al" :value="$filters['registered_to'] ?? ''" />

                <div class="flex items-end gap-2 md:col-span-2">
                    <x-ui.button type="submit">Filtra</x-ui.button>
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Azzera</a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padding="false">
            @if ($users->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-slate-700">Nome</th>
                                <th class="px-6 py-3 font-semibold text-slate-700">Email</th>
                                <th class="px-6 py-3 font-semibold text-slate-700">Ruolo</th>
                                <th class="px-6 py-3 font-semibold text-slate-700">Profilo</th>
                                <th class="px-6 py-3 font-semibold text-slate-700">Stato</th>
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
                                        <div class="mt-0.5 text-xs text-slate-500">ID {{ $user->id }} · {{ optional($user->created_at)->format('d/m/Y') }}</div>
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
                                    <td class="px-6 py-4 text-slate-600">
                                        @if ($user->role === 'professional')
                                            {{ data_get($professionalCategories, $user->professionalProfession?->profession, 'Categoria non indicata') }}
                                            <div class="mt-0.5 text-xs text-slate-500">{{ $user->address_city ?: $user->residence ?: 'Località non indicata' }}</div>
                                        @elseif ($user->role === 'business')
                                            {{ $user->businessProfile?->company_type ?: 'Tipo non indicato' }}
                                            <div class="mt-0.5 text-xs text-slate-500">{{ $user->businessProfile?->location ?: 'Località non indicata' }}</div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($user->suspended_at)
                                            <x-ui.badge variant="danger">Sospeso</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="success">Attivo</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-teal-700 transition hover:bg-teal-50 hover:text-teal-900">
                                                Modifica
                                            </a>

                                            @if (in_array($user->role, ['professional', 'business'], true))
                                                <form method="POST" action="{{ route('admin.users.suspension', $user) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold {{ $user->suspended_at ? 'text-emerald-700 hover:bg-emerald-50' : 'text-amber-700 hover:bg-amber-50' }}">
                                                        {{ $user->suspended_at ? 'Riattiva' : 'Sospendi' }}
                                                    </button>
                                                </form>
                                            @endif

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
                        description="Nessun profilo corrisponde ai filtri selezionati."
                    />
                </div>
            @endif
        </x-ui.card>

        @if ($users->hasPages())
            <div>{{ $users->links() }}</div>
        @endif
    </div>
@endsection
