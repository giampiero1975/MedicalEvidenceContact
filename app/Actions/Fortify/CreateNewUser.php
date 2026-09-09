<?php

namespace App\Actions\Fortify;

use App\Models\BusinessProfile;
use App\Models\BusinessType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'account_type' => ['required', Rule::in(['professional', 'business'])],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:40'],
            'nationality' => ['required_if:account_type,professional', 'nullable', Rule::in(config('nationalities.values'))],
            'profession' => ['required_if:account_type,professional', 'nullable', Rule::in(array_keys(config('professional-professions.values')))],
            'address_city' => ['required_if:account_type,professional', 'nullable', 'string', 'max:150'],
            'address_country' => ['required_if:account_type,professional', 'nullable', 'string', 'max:150'],
            'address_province' => ['required_if:account_type,professional', 'nullable', 'string', 'max:100'],
            'postal_code' => ['required_if:account_type,professional', 'nullable', 'string', 'max:20'],
            'street_address' => ['required_if:account_type,professional', 'nullable', 'string', 'max:255'],
            'company_name' => ['required_if:account_type,business', 'nullable', 'string', 'max:180'],
            'company_type' => [
                'required_if:account_type,business',
                'nullable',
                Rule::exists('business_types', 'name')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'vat_number' => ['required_if:account_type,business', 'nullable', 'regex:/^[0-9]{11}$/', 'unique:business_profiles,vat_number'],
            'company_street_address' => ['required_if:account_type,business', 'nullable', 'string', 'max:255'],
            'company_city' => ['required_if:account_type,business', 'nullable', 'string', 'max:150'],
            'company_province' => ['required_if:account_type,business', 'nullable', 'string', 'max:100'],
            'company_postal_code' => ['required_if:account_type,business', 'nullable', 'string', 'max:20'],
            'company_country' => ['required_if:account_type,business', 'nullable', 'string', 'max:150'],
            'employee_count' => ['required_if:account_type,business', 'nullable', 'integer', Rule::in([10, 50, 200, 500, 501])],
            'poc_role' => ['required_if:account_type,business', 'nullable', 'string', 'max:150'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => trim($input['first_name'].' '.$input['last_name']),
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'role' => $input['account_type'],
                'phone' => $input['phone'],
                'residence' => $input['account_type'] === 'professional' ? ($input['address_city'] ?? null) : null,
                'nationality' => $input['account_type'] === 'professional' ? ($input['nationality'] ?? null) : null,
                'address_city' => $input['account_type'] === 'professional' ? ($input['address_city'] ?? null) : null,
                'address_country' => $input['account_type'] === 'professional' ? ($input['address_country'] ?? null) : null,
                'address_province' => $input['account_type'] === 'professional' ? ($input['address_province'] ?? null) : null,
                'postal_code' => $input['account_type'] === 'professional' ? ($input['postal_code'] ?? null) : null,
                'street_address' => $input['account_type'] === 'professional' ? ($input['street_address'] ?? null) : null,
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            if ($input['account_type'] === 'professional') {
                DB::table('professional_profiles')->insert([
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('professional_professions')->insert([
                    'user_id' => $user->id,
                    'profession' => $input['profession'] ?? 'oss',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return $user;
            }

            $businessProfile = BusinessProfile::create([
                'user_id' => $user->id,
                'company_name' => $input['company_name'],
                'company_type' => $input['company_type'],
                'vat_number' => $input['vat_number'],
                'location' => $input['company_city'],
                'employee_count' => $input['employee_count'],
                'address_street' => $input['company_street_address'],
                'address_city' => $input['company_city'],
                'address_province' => $input['company_province'],
                'postal_code' => $input['company_postal_code'],
                'address_country' => $input['company_country'],
            ]);

            $businessProfile->addPointOfContact([
                'user_id' => $user->id,
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'role' => $input['poc_role'],
            ]);

            return $user;
        });
    }
}
