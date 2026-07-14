<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Profilo struttura"
            subtitle="Completa l'identità della struttura che verrà utilizzata negli annunci e nelle candidature."
        />
    </x-slot>

    <div class="space-y-8">
        <section class="grid gap-6 xl:grid-cols-[0.75fr_1.25fr]">
            <x-ui.card>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Identità</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-950">Immagine della struttura</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Il logo sarà usato nelle aree Business e, in seguito, negli annunci pubblici.</p>

                <div class="mt-6 flex min-h-56 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6">
                    @if ($businessProfile?->logo_path)
                        <img
                            src="{{ route('business.profile.logo') }}"
                            alt="Logo {{ $businessProfile->company_name }}"
                            class="max-h-40 max-w-full object-contain"
                        >
                    @else
                        <div class="text-center">
                            <p class="font-semibold text-slate-900">Logo non caricato</p>
                            <p class="mt-1 text-sm text-slate-500">PNG, JPG o WebP fino a 4 MB</p>
                        </div>
                    @endif
                </div>

                <div class="mt-6 rounded-2xl bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-900">Profilo attuale</p>
                    <p class="mt-2 text-sm text-slate-600">{{ $businessProfile?->company_name ?: 'Struttura da configurare' }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $businessProfile?->company_type ?: 'Tipologia non impostata' }}</p>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Dati struttura</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Informazioni principali</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Questi dati costituiscono la base per sedi, reparti e operatori Business.</p>
                </div>

                <form method="POST" action="{{ route('business.profile.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-5 md:grid-cols-2">
                    @csrf
                    @method('PUT')

                    <x-ui.input name="company_name" label="Nome struttura" :value="old('company_name', $businessProfile?->company_name)" required />
                    <x-ui.input name="legal_name" label="Ragione sociale" :value="old('legal_name', $businessProfile?->legal_name)" />

                    <x-ui.select name="company_type" label="Tipologia struttura" required>
                        @foreach (['RSA', 'Casa di cura', 'Ospedale', 'Cooperativa', 'Agenzia per il lavoro', 'Assistenza domiciliare', 'Poliambulatorio', 'Altro'] as $type)
                            <option value="{{ $type }}" @selected(old('company_type', $businessProfile?->company_type) === $type)>{{ $type }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input name="location" label="Località principale" :value="old('location', $businessProfile?->location)" />

                    <x-ui.input name="vat_number" label="Partita IVA" :value="old('vat_number', $businessProfile?->vat_number)" />
                    <x-ui.input name="tax_code" label="Codice fiscale" :value="old('tax_code', $businessProfile?->tax_code)" />

                    <x-ui.input type="email" name="email" label="Email struttura" :value="old('email', $businessProfile?->email)" />
                    <x-ui.input name="phone" label="Telefono" :value="old('phone', $businessProfile?->phone)" />

                    <x-ui.input type="email" name="pec" label="PEC" :value="old('pec', $businessProfile?->pec)" />
                    <x-ui.input type="url" name="website" label="Sito web" :value="old('website', $businessProfile?->website)" placeholder="https://" />

                    <x-ui.input type="number" name="employee_count" label="Numero dipendenti" :value="old('employee_count', $businessProfile?->employee_count)" min="0" />
                    <x-ui.input type="file" name="logo" label="Logo" accept="image/jpeg,image/png,image/webp" help="PNG, JPG o WebP. Massimo 4 MB." />

                    <div class="md:col-span-2">
                        <x-ui.textarea name="description" label="Descrizione struttura" rows="6">{{ old('description', $businessProfile?->description) }}</x-ui.textarea>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 pt-5 md:col-span-2">
                        <x-ui.button type="submit">Salva profilo struttura</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </section>
    </div>
</x-app-layout>
