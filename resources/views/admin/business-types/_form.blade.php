@php
    $isEditing = $businessType->exists;
@endphp

<div class="space-y-6">
    <x-ui.card>
        <div class="space-y-5">
            <div>
                <label for="name" class="mb-1.5 block text-sm font-semibold text-slate-700">Nome tipologia</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    maxlength="120"
                    required
                    value="{{ old('name', $businessType->name) }}"
                    class="block w-full rounded-xl border-slate-300 text-sm shadow-sm transition focus:border-teal-600 focus:ring-teal-600"
                    placeholder="Es. Clinica privata"
                >
                @error('name')
                    <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sort_order" class="mb-1.5 block text-sm font-semibold text-slate-700">Ordine</label>
                <input
                    id="sort_order"
                    name="sort_order"
                    type="number"
                    min="0"
                    required
                    value="{{ old('sort_order', $businessType->sort_order ?? 0) }}"
                    class="block w-full rounded-xl border-slate-300 text-sm shadow-sm transition focus:border-teal-600 focus:ring-teal-600 sm:max-w-xs"
                >
                <p class="mt-1.5 text-xs text-slate-500">I valori più bassi vengono mostrati per primi.</p>
                @error('sort_order')
                    <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input type="hidden" name="is_active" value="0">
                <label class="inline-flex items-center gap-3">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', $businessType->is_active ?? true))
                        class="rounded border-slate-300 text-teal-700 shadow-sm focus:ring-teal-600"
                    >
                    <span>
                        <span class="block text-sm font-semibold text-slate-800">Tipologia attiva</span>
                        <span class="block text-xs text-slate-500">Le tipologie disattivate non sono selezionabili nelle nuove registrazioni.</span>
                    </span>
                </label>
                @error('is_active')
                    <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </x-ui.card>

    @if ($errors->any())
        <x-ui.alert variant="danger">Controlla i campi evidenziati prima di salvare.</x-ui.alert>
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
            {{ $isEditing ? 'Salva modifiche' : 'Crea tipologia' }}
        </button>
        <a href="{{ route('admin.business-types.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">
            Annulla
        </a>
    </div>
</div>
