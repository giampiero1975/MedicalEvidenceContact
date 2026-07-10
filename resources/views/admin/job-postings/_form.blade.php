@php
    $selectedBusiness = old('user_id', $jobPosting->user_id);
@endphp

<div class="space-y-8">
    <section>
        <div class="mb-4">
            <h2 class="text-base font-semibold text-slate-900">Proprietario dell’annuncio</h2>
            <p class="mt-1 text-sm text-slate-500">Seleziona il business per conto del quale viene pubblicato.</p>
        </div>

        <x-ui.select name="user_id" label="Business proprietario" required>
            <option value="">Seleziona business</option>
            @foreach ($businessUsers as $businessUser)
                <option value="{{ $businessUser->id }}" @selected((int) $selectedBusiness === $businessUser->id)>
                    {{ $businessUser->businessProfile?->company_name ?: $businessUser->name }} · {{ $businessUser->email }}
                </option>
            @endforeach
        </x-ui.select>
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
