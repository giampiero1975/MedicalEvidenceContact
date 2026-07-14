<?php

namespace App\Http\Controllers;

use App\Models\BusinessLocation;
use App\Models\BusinessProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessLocationController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $this->profile($request);

        return view('business.locations.index', [
            'businessProfile' => $profile,
            'locations' => $profile->locations()
                ->orderByDesc('is_primary')
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $this->profile($request);
        $data = $this->validated($request);

        DB::transaction(function () use ($profile, $data): void {
            if ($data['is_primary']) {
                $profile->locations()->update(['is_primary' => false]);
            }

            $profile->locations()->create($data);
        });

        return redirect()
            ->route('business.locations.index')
            ->with('status', 'Sede aggiunta.')
            ->with('status_variant', 'success');
    }

    public function edit(Request $request, BusinessLocation $location): View
    {
        $this->authorizeOwner($request, $location);

        return view('business.locations.edit', [
            'location' => $location,
        ]);
    }

    public function update(Request $request, BusinessLocation $location): RedirectResponse
    {
        $this->authorizeOwner($request, $location);
        $data = $this->validated($request);

        DB::transaction(function () use ($location, $data): void {
            if ($data['is_primary']) {
                $location->businessProfile->locations()
                    ->whereKeyNot($location->id)
                    ->update(['is_primary' => false]);
            }

            $location->update($data);
        });

        return redirect()
            ->route('business.locations.index')
            ->with('status', 'Sede aggiornata.')
            ->with('status_variant', 'success');
    }

    public function destroy(Request $request, BusinessLocation $location): RedirectResponse
    {
        $this->authorizeOwner($request, $location);

        if ($location->is_primary && $location->businessProfile->locations()->count() > 1) {
            return back()
                ->with('status', 'Imposta prima un’altra sede come principale.')
                ->with('status_variant', 'danger');
        }

        $location->delete();

        return redirect()
            ->route('business.locations.index')
            ->with('status', 'Sede eliminata.')
            ->with('status_variant', 'success');
    }

    private function profile(Request $request): BusinessProfile
    {
        abort_unless($request->user()->role === 'business', 403);

        return $request->user()->businessProfile()->firstOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'company_name' => $request->user()->name,
            'company_type' => 'Altro',
        ]);
    }

    private function authorizeOwner(Request $request, BusinessLocation $location): void
    {
        $profile = $this->profile($request);
        abort_unless($location->business_profile_id === $profile->id, 403);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'type' => ['required', Rule::in(['legal', 'operational'])],
            'street_address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:10'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'is_primary' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_primary'] = $request->boolean('is_primary');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
