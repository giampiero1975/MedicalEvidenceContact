<?php

namespace App\Http\Controllers;

use App\Models\BusinessDepartment;
use App\Models\BusinessLocation;
use App\Models\BusinessProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessDepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $this->profile($request);

        return view('business.departments.index', [
            'departments' => $profile->departments()
                ->with('location')
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'locations' => $profile->locations()
                ->where('is_active', true)
                ->orderByDesc('is_primary')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $this->profile($request);
        $data = $this->validated($request, $profile);

        $profile->departments()->create($data);

        return redirect()
            ->route('business.departments.index')
            ->with('status', 'Reparto aggiunto.')
            ->with('status_variant', 'success');
    }

    public function edit(Request $request, BusinessDepartment $department): View
    {
        $profile = $this->profile($request);
        $this->authorizeOwner($department, $profile);

        return view('business.departments.edit', [
            'department' => $department,
            'locations' => $profile->locations()
                ->where('is_active', true)
                ->orderByDesc('is_primary')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, BusinessDepartment $department): RedirectResponse
    {
        $profile = $this->profile($request);
        $this->authorizeOwner($department, $profile);

        $department->update($this->validated($request, $profile, $department));

        return redirect()
            ->route('business.departments.index')
            ->with('status', 'Reparto aggiornato.')
            ->with('status_variant', 'success');
    }

    public function destroy(Request $request, BusinessDepartment $department): RedirectResponse
    {
        $profile = $this->profile($request);
        $this->authorizeOwner($department, $profile);
        $department->delete();

        return redirect()
            ->route('business.departments.index')
            ->with('status', 'Reparto eliminato.')
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
            'location' => 'Da completare',
        ]);
    }

    private function authorizeOwner(BusinessDepartment $department, BusinessProfile $profile): void
    {
        abort_unless($department->business_profile_id === $profile->id, 403);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, BusinessProfile $profile, ?BusinessDepartment $department = null): array
    {
        $data = $request->validate([
            'business_location_id' => [
                'required',
                Rule::exists('business_locations', 'id')->where(
                    fn ($query) => $query
                        ->where('business_profile_id', $profile->id)
                        ->where('is_active', true)
                ),
            ],
            'name' => [
                'required',
                'string',
                'max:180',
                Rule::unique('business_departments', 'name')
                    ->where(fn ($query) => $query->where('business_location_id', $request->input('business_location_id')))
                    ->ignore($department?->id),
            ],
            'code' => ['nullable', 'string', 'max:60'],
            'manager_name' => ['nullable', 'string', 'max:180'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
