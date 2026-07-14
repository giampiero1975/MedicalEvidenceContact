<?php

namespace Tests\Feature;

use App\Models\BusinessDepartment;
use App\Models\BusinessLocation;
use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingBusinessDepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_can_publish_job_posting_for_a_department_of_selected_location(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'RSA Aurora',
            'company_type' => 'RSA',
            'location' => 'Milano',
        ]);

        $location = BusinessLocation::create([
            'business_profile_id' => $profile->id,
            'name' => 'Sede Milano',
            'type' => 'operational',
            'street_address' => 'Via Roma 10',
            'city' => 'Milano',
            'country' => 'Italia',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $department = BusinessDepartment::create([
            'business_profile_id' => $profile->id,
            'business_location_id' => $location->id,
            'name' => 'Nucleo Alzheimer',
            'is_active' => true,
        ]);

        $this->actingAs($business)
            ->post(route('job-postings.store'), [
                'business_location_id' => $location->id,
                'business_department_id' => $department->id,
                'title' => 'OSS per Nucleo Alzheimer',
                'description' => 'Ricerca operatore socio sanitario.',
                'positions' => 2,
                'contract_type' => 'Tempo determinato',
                'expires_at' => now()->addMonth()->toDateString(),
            ])
            ->assertRedirect(route('job-postings.index', absolute: false));

        $this->assertDatabaseHas('job_postings', [
            'business_profile_id' => $profile->id,
            'business_location_id' => $location->id,
            'business_department_id' => $department->id,
        ]);
    }

    public function test_business_cannot_use_department_owned_by_another_business(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'RSA Aurora',
            'company_type' => 'RSA',
            'location' => 'Milano',
        ]);
        $location = BusinessLocation::create([
            'business_profile_id' => $profile->id,
            'name' => 'Sede Milano',
            'type' => 'operational',
            'street_address' => 'Via Roma 10',
            'city' => 'Milano',
            'country' => 'Italia',
            'is_active' => true,
        ]);

        $otherBusiness = User::factory()->create(['role' => 'business']);
        $otherProfile = BusinessProfile::create([
            'user_id' => $otherBusiness->id,
            'company_name' => 'Clinica Delta',
            'company_type' => 'Clinica',
            'location' => 'Roma',
        ]);
        $otherLocation = BusinessLocation::create([
            'business_profile_id' => $otherProfile->id,
            'name' => 'Sede Roma',
            'type' => 'operational',
            'street_address' => 'Via Appia 20',
            'city' => 'Roma',
            'country' => 'Italia',
            'is_active' => true,
        ]);
        $otherDepartment = BusinessDepartment::create([
            'business_profile_id' => $otherProfile->id,
            'business_location_id' => $otherLocation->id,
            'name' => 'Chirurgia',
            'is_active' => true,
        ]);

        $this->actingAs($business)
            ->post(route('job-postings.store'), [
                'business_location_id' => $location->id,
                'business_department_id' => $otherDepartment->id,
                'title' => 'OSS',
                'description' => 'Ricerca operatore socio sanitario.',
                'positions' => 1,
                'contract_type' => 'Tempo determinato',
                'expires_at' => now()->addMonth()->toDateString(),
            ])
            ->assertSessionHasErrors('business_department_id');

        $this->assertDatabaseCount('job_postings', 0);
    }
}
