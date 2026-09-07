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
        @php
            $adminNavigation = [
                ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'active' => 'admin.dashboard'],
                ['route' => 'admin.users.index', 'label' => 'Utenti', 'active' => 'admin.users.*'],
                ['route' => 'admin.job-postings.index', 'label' => 'Annunci', 'active' => 'admin.job-postings.*'],
                ['route' => 'admin.business-types.index', 'label' => 'Tipologie aziendali', 'active' => 'admin.business-types.*'],
            ];
        @endphp

        <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:grid lg:grid-cols-[17rem_minmax(0,1fr)]">
            <aside class="hidden border-r border-slate-200 bg-white lg:flex lg:flex-col">
                <div class="flex h-20 items-center border-b border-slate-200 px-6">
                    <a href="{{ route('admin.dashboard') }}" class="text-base font-semibold tracking-tight text-slate-950">
                        Medical Evidence Contact
                        <span class="mt-0.5 block text-xs font-medium uppercase tracking-[0.18em] text-teal-700">Amministrazione</span>
                    </a>
                </div>

                <nav class="flex-1 space-y-1 p-4" aria-label="Navigazione amministrativa">
                    @foreach ($adminNavigation as $item)
                        <x-ui.sidebar-link :href="route($item['route'])" :active="request()->routeIs($item['active'])">
                            {{ $item['label'] }}
                        </x-ui.sidebar-link>
                    @endforeach
                    <div class="my-3 border-t border-slate-200"></div>
                    <x-ui.sidebar-link :href="route('admin.ui.index')" :active="request()->routeIs('admin.ui.*')">
                        UI Playground
                    </x-ui.sidebar-link>
                </nav>

                <div class="border-t border-slate-200 p-4">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </aside>

            <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" @keydown.escape.window="sidebarOpen = false">
                <div class="absolute inset-0 bg-slate-950/40" @click="sidebarOpen = false"></div>
                <aside class="relative flex h-full w-[min(19rem,85vw)] flex-col border-r border-slate-200 bg-white shadow-xl">
                    <div class="flex h-20 items-center justify-between border-b border-slate-200 px-6">
                        <a href="{{ route('admin.dashboard') }}" class="text-base font-semibold tracking-tight text-slate-950">
                            Medical Evidence Contact
                            <span class="mt-0.5 block text-xs font-medium uppercase tracking-[0.18em] text-teal-700">Amministrazione</span>
                        </a>
                        <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" @click="sidebarOpen = false" aria-label="Chiudi navigazione amministrativa">×</button>
                    </div>
                    <nav class="flex-1 space-y-1 overflow-y-auto p-4" aria-label="Navigazione amministrativa mobile">
                        @foreach ($adminNavigation as $item)
                            <x-ui.sidebar-link :href="route($item['route'])" :active="request()->routeIs($item['active'])">
                                {{ $item['label'] }}
                            </x-ui.sidebar-link>
                        @endforeach
                        <div class="my-3 border-t border-slate-200"></div>
                        <x-ui.sidebar-link :href="route('admin.ui.index')" :active="request()->routeIs('admin.ui.*')">UI Playground</x-ui.sidebar-link>
                    </nav>
                </aside>
            </div>

            <div class="min-w-0">
                <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button type="button" class="inline-flex rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 lg:hidden" @click="sidebarOpen = true" aria-label="Apri navigazione amministrativa">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </button>
                        <p class="text-sm font-medium text-slate-500">Area amministrazione</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">Esci</button>
                    </form>
                </header>

                <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    @if (session('status'))
                        <x-ui.alert :variant="session('status_variant', 'success')" class="mb-6">
                            {{ session('status') }}
                        </x-ui.alert>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
