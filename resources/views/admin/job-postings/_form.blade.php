@php
    $selectedSource = old('company_source', $jobPosting->external_company_name ? 'external' : 'registered');
    $selectedBusiness = old('user_id', $jobPosting->external_company_name ? null : $jobPosting->user_id);
@endphp

<div class="space-y-8">
    <section x-data="{ companySource: @js($selectedSource) }">
        <div class="mb-4">
            <h2 class="text-base font-semibold text-slate-900">Azienda dell’annuncio</h2>
            <p class="mt-1 text-sm text-slate-500">Pubblica per conto di un business registrato oppure di un’azienda non ancora registrata.</p>
        </div>

        <div class="space-y-5">
            <x-ui.select name="company_source" label="Tipo azienda" x-model="companySource" required>
                <option value="registered" @selected($selectedSource === 'registered')>Azienda registrata</option>
                <option value="external" @selected($selectedSource === 'external')>Azienda non registrata</option>
            </x-ui.select>

            <div x-show="companySource === 'registered'">
                <x-ui.select name="user_id" label="Business registrato">
                    <option value="">Seleziona business</option>
                    @foreach ($businessUsers as $businessUser)
                        <option value="{{ $businessUser->id }}" @selected((int) $selectedBusiness === $businessUser->id)>
                            {{ $businessUser->businessProfile?->company_name ?: $businessUser->name }} · {{ $businessUser->email }}
                        </option>
                    @endforeach
                </x-ui.select>
            </div>

            <div x-show="companySource === 'external'">
                <x-ui.input
                    name="external_company_name"
                    label="Nome azienda non registrata"
                    :value="$jobPosting->external_company_name ?? ''"
                    maxlength="180"
                    placeholder="Es. Fondazione Sanitaria Example"
                />
            </div>
        </div>
    </section>

    <div class="border-t border-slate-200"></div>

    <section>
        <div class="mb-5">
            <h2 class="text-base font-semibold text-slate-900">Dettagli dell’annuncio</h2>
            <p class="mt-1 text-sm text-slate-500">Inserisci le informazioni mostrate ai professionisti.</p>
        </div>

        @include('job-postings._form', ['jobPosting' => $jobPosting])
    </section>
</div>
