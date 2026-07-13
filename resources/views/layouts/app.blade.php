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
        <x-banner />

        <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:grid lg:grid-cols-[17rem_minmax(0,1fr)]">
            <aside class="hidden border-r border-slate-200 lg:block">
                <x-layout.app-sidebar />
            </aside>

            <div
                x-cloak
                x-show="sidebarOpen"
                class="fixed inset-0 z-50 lg:hidden"
                role="dialog"
                aria-modal="true"
                @keydown.escape.window="sidebarOpen = false"
            >
                <div class="absolute inset-0 bg-slate-950/40" @click="sidebarOpen = false"></div>
                <aside class="relative h-full w-[min(19rem,85vw)] border-r border-slate-200 shadow-xl">
                    <x-layout.app-sidebar />
                </aside>
            </div>

            <div class="min-w-0">
                <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 lg:hidden"
                            @click="sidebarOpen = true"
                            aria-label="Apri navigazione"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </button>

                        <div>
                            <p class="text-sm font-medium text-slate-500">
                                {{ auth()->user()->role === 'professional' ? 'Area professionista' : 'Area business' }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('profile.show') }}" class="flex items-center gap-3 rounded-xl px-2 py-1.5 transition hover:bg-slate-100">
                        <img class="h-8 w-8 rounded-full object-cover" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}">
                        <span class="hidden max-w-48 truncate text-sm font-semibold text-slate-800 sm:block">{{ auth()->user()->name }}</span>
                    </a>
                </header>

                @if (isset($header))
                    <div class="border-b border-slate-200 bg-white px-4 py-5 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                @endif

                <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    @if (session('status'))
                        <x-ui.alert variant="success" class="mb-6">
                            {{ session('status') }}
                        </x-ui.alert>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('modals')
        @livewireScripts
        @stack('scripts')
    </body>
</html>
