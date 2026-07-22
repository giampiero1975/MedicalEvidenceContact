<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.users.index', [
            'users' => User::with('businessProfile')->latest()->paginate(15),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.users.create', [
            'user' => new User(['role' => 'professional', 'nationality' => 'Italiana', 'address_country' => 'Italia']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $this->validateUser($request);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                ...$this->userPayload($data),
                'password' => Hash::make($data['password']),
            ]);

            $this->syncRoleProfile($user, $data);

            return $user;
        });

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'Utente creato.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeAdmin($request);

        $user->load('businessProfile');

        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $this->validateUser($request, $user);

        DB::transaction(function () use ($user, $data) {
            $payload = $this->userPayload($data);

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);
            $this->syncRoleProfile($user, $data);
        });

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'Utente aggiornato.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);
        abort_if($request->user()->is($user), 422, 'Non puoi eliminare il tuo account admin mentre sei loggato.');

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Utente eliminato.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateUser(Request $request, ?User $user = null): array
    {
        $passwordRules = $user
            ? ['nullable', 'confirmed', Password::default()]
            : ['required', 'confirmed', Password::default()];

        return $request->validate([
            'role' => ['required', Rule::in(['professional', 'business', 'admin'])],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => $passwordRules,
            'nationality' => ['required_if:role,professional', 'nullable', Rule::in(config('nationalities.values'))],
            'address_city' => ['required_if:role,professional', 'nullable', 'string', 'max:150'],
            'address_country' => ['required_if:role,professional', 'nullable', 'string', 'max:150'],
            'address_province' => ['required_if:role,professional', 'nullable', 'string', 'max:100'],
            'postal_code' => ['required_if:role,professional', 'nullable', 'string', 'max:20'],
            'street_address' => ['required_if:role,professional', 'nullable', 'string', 'max:255'],
            'company_name' => ['required_if:role,business', 'nullable', 'string', 'max:180'],
            'company_type' => ['required_if:role,business', 'nullable', Rule::in(config('business-types.values'))],
            'location' => ['required_if:role,business', 'nullable', 'string', 'max:150'],
            'employee_count' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function userPayload(array $data): array
    {
        return [
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'residence' => $data['role'] === 'professional' ? ($data['address_city'] ?? null) : null,
            'nationality' => $data['role'] === 'professional' ? ($data['nationality'] ?? null) : null,
            'address_city' => $data['role'] === 'professional' ? ($data['address_city'] ?? null) : null,
            'address_country' => $data['role'] === 'professional' ? ($data['address_country'] ?? null) : null,
            'address_province' => $data['role'] === 'professional' ? ($data['address_province'] ?? null) : null,
            'postal_code' => $data['role'] === 'professional' ? ($data['postal_code'] ?? null) : null,
            'street_address' => $data['role'] === 'professional' ? ($data['street_address'] ?? null) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncRoleProfile(User $user, array $data): void
    {
        if ($data['role'] === 'professional') {
            DB::table('professional_profiles')->updateOrInsert(
                ['user_id' => $user->id],
                ['updated_at' => now(), 'created_at' => now()]
            );
            $user->businessProfile()->delete();

            return;
        }

        DB::table('professional_profiles')->where('user_id', $user->id)->delete();

        if ($data['role'] === 'business') {
            BusinessProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $data['company_name'],
                    'company_type' => $data['company_type'],
                    'location' => $data['location'],
                    'employee_count' => $data['employee_count'] ?? null,
                ]
            );

            return;
        }

        $user->businessProfile()->delete();
    }
}
