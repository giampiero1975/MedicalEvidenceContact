<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\BusinessType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminBusinessTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.business-types.index', [
            'businessTypes' => BusinessType::ordered()->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.business-types.create', [
            'businessType' => new BusinessType([
                'is_active' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $this->validatedData($request);

        $businessType = BusinessType::create($data);

        return redirect()
            ->route('admin.business-types.edit', $businessType)
            ->with('status', 'Tipologia aziendale creata.');
    }

    public function edit(Request $request, BusinessType $businessType): View
    {
        $this->authorizeAdmin($request);

        return view('admin.business-types.edit', compact('businessType'));
    }

    public function update(Request $request, BusinessType $businessType): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $this->validatedData($request, $businessType);
        $previousName = $businessType->name;

        DB::transaction(function () use ($businessType, $data, $previousName) {
            $businessType->update($data);

            if ($previousName !== $businessType->name) {
                BusinessProfile::query()
                    ->where('company_type', $previousName)
                    ->update(['company_type' => $businessType->name]);
            }
        });

        return redirect()
            ->route('admin.business-types.edit', $businessType)
            ->with('status', 'Tipologia aziendale aggiornata.');
    }

    public function toggle(Request $request, BusinessType $businessType): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $businessType->update([
            'is_active' => ! $businessType->is_active,
        ]);

        return redirect()
            ->route('admin.business-types.index')
            ->with('status', $businessType->is_active
                ? 'Tipologia aziendale attivata.'
                : 'Tipologia aziendale disattivata.');
    }

    public function destroy(Request $request, BusinessType $businessType): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $inUse = BusinessProfile::query()
            ->where('company_type', $businessType->name)
            ->exists();

        if ($inUse) {
            return redirect()
                ->route('admin.business-types.index')
                ->withErrors(['business_type' => 'La tipologia è utilizzata da almeno un profilo Business. Disattivala invece di eliminarla.']);
        }

        $businessType->delete();

        return redirect()
            ->route('admin.business-types.index')
            ->with('status', 'Tipologia aziendale eliminata.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }

    /** @return array{name:string,is_active:bool,sort_order:int} */
    private function validatedData(Request $request, ?BusinessType $businessType = null): array
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('business_types', 'name')->ignore($businessType?->id),
            ],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        return [
            'name' => $validated['name'],
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
        ];
    }
}
