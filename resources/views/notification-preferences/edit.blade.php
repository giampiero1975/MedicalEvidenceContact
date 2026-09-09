<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Preferenze notifiche" description="Scegli quali comunicazioni ricevere e con quale frequenza." />
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('notification-preferences.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    @foreach ($preferences as $category => $preference)
                        <div class="grid gap-4 rounded-xl border border-slate-200 p-4 sm:grid-cols-[1fr_220px] sm:items-center">
                            <div>
                                <label class="flex items-center gap-3">
                                    <input
                                        type="checkbox"
                                        name="preferences[{{ $category }}][enabled]"
                                        value="1"
                                        @checked(old("preferences.$category.enabled", $preference['enabled']))
                                        class="rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                                    >
                                    <span class="font-semibold text-slate-950">{{ $preference['label'] }}</span>
                                </label>
                                <p class="mt-1 pl-7 text-sm text-slate-500">Puoi disattivare questa categoria in qualsiasi momento.</p>
                            </div>

                            <div>
                                <label for="frequency-{{ $category }}" class="sr-only">Frequenza {{ $preference['label'] }}</label>
                                <select
                                    id="frequency-{{ $category }}"
                                    name="preferences[{{ $category }}][frequency]"
                                    class="w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600"
                                >
                                    @foreach ($frequencyOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old("preferences.$category.frequency", $preference['frequency']) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        Controlla le preferenze inserite e riprova.
                    </div>
                @endif

                <div class="flex justify-end">
                    <x-ui.button type="submit">Salva preferenze</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
