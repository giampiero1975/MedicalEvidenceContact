<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $this->authorizeBusiness($request);

        return view('business.profile.edit', [
            'businessProfile' => $request->user()->businessProfile,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeBusiness($request);

        $profile = $request->user()->businessProfile;

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:180'],
            'legal_name' => ['nullable', 'string', 'max:180'],
            'company_type' => ['required', Rule::in([
                'RSA',
                'Casa di cura',
                'Ospedale',
                'Cooperativa',
                'Agenzia per il lavoro',
                'Assistenza domiciliare',
                'Poliambulatorio',
                'Altro',
            ])],
            'vat_number' => ['nullable', 'string', 'max:32'],
            'tax_code' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string', 'max:5000'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'pec' => ['nullable', 'email:rfc', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'employee_count' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        unset($data['logo']);

        if ($request->hasFile('logo')) {
            if ($profile?->logo_path) {
                Storage::disk('local')->delete($profile->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store(
                'business-logos/'.$request->user()->id,
                'local'
            );
        }

        BusinessProfile::query()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return redirect()
            ->route('business.profile.edit')
            ->with('status', 'Profilo struttura aggiornato.')
            ->with('status_variant', 'success');
    }

    public function logo(Request $request): Response
    {
        $this->authorizeBusiness($request);

        $profile = $request->user()->businessProfile;
        abort_unless($profile?->logo_path && Storage::disk('local')->exists($profile->logo_path), 404);

        return Storage::disk('local')->response(
            $profile->logo_path,
            basename($profile->logo_path),
            ['Content-Disposition' => 'inline']
        );
    }

    private function authorizeBusiness(Request $request): void
    {
        abort_unless($request->user()->role === 'business', 403);
    }
}
