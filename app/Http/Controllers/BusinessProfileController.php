<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\BusinessType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BusinessProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $this->authorizeBusiness($request);

        return view('business.profile.edit', [
            'businessProfile' => $request->user()->businessContextProfile(),
            'businessTypes' => BusinessType::query()->active()->ordered()->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeBusiness($request);

        $profile = $request->user()->businessContextProfile();
        abort_unless($profile, 404);

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:180'],
            'legal_name' => ['nullable', 'string', 'max:180'],
            'company_type' => [
                'required',
                Rule::exists('business_types', 'name')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'vat_number' => ['nullable', 'regex:/^[0-9]{11}$/', Rule::unique('business_profiles', 'vat_number')->ignore($profile->id)],
            'tax_code' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'pec' => ['nullable', 'email:rfc', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'employee_count' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:150'],
            'address_province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address_country' => ['nullable', 'string', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        unset($data['logo']);

        if ($request->hasFile('logo')) {
            if ($profile->logo_path) {
                Storage::disk('local')->delete($profile->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store(
                'business-logos/'.$profile->user_id,
                'local'
            );
        }

        $profile->update($data);

        return redirect()
            ->route('business.profile.edit')
            ->with('status', 'Profilo struttura aggiornato.')
            ->with('status_variant', 'success');
    }

    public function logo(Request $request): StreamedResponse
    {
        $this->authorizeBusiness($request);

        $profile = $request->user()->businessContextProfile();
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
