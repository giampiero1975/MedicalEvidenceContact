<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name', 'Medical Evidence Contact') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @stack('styles')
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_minmax(0,1fr)]">
            <aside class="hidden border-r border-slate-200 bg-white lg:flex lg:flex-col">
                <div class="flex h-20 items-center border-b border-slate-200 px-6">
                    <a href="{{ route('admin.dashboard') }}" class="text-base font-semibold tracking-tight text-slate-950">
                        Medical Evidence Contact
                        <span class="mt-0.5 block text-xs font-medium uppercase tracking-[0.18em] text-teal-700">Amministrazione</span>
                    </a>
                </div>

                <nav class="flex-1 space-y-1 p-4" aria-label="Navigazione amministrativa">
                    <x-ui.sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        Dashboard
                    </x-ui.sidebar-link>
                    <x-ui.sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                        Utenti
                    </x-ui.sidebar-link>
                    <x-ui.sidebar-link :href="route('admin.job-postings.index')" :active="request()->routeIs('admin.job-postings.*')">
                        Annunci
                    </x-ui.sidebar-link>
                </nav>

                <div class="border-t border-slate-200 p-4">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </aside>

            <div class="min-w-0">
                <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Area riservata</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">
                            Esci
                        </button>
                    </form>
                </header>

                <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    @if (session('status'))
                        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
