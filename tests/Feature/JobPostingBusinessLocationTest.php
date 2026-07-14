<?php

namespace Tests\Feature;

use App\Models\BusinessLocation;
use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingBusinessLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_can_publish_job_posting_using_its_location(): void
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
            'province' => 'MI',
            'postal_code' => '20100',
            'country' => 'Italia',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->actingAs($business)
            ->post(route('job-postings.store'), [
                'business_location_id' => $location->id,
                'title' => 'OSS per RSA',
                'description' => 'Ricerca operatore socio sanitario.',
                'positions' => 2,
                'required_skills' => 'Qualifica OSS',
                'contract_type' => 'Tempo determinato',
                'salary_min' => '1.200,00',
                'salary_max' => '1.500,00',
                'expires_at' => now()->addMonth()->toDateString(),
            ])
            ->assertRedirect(route('job-postings.index', absolute: false));

        $this->assertDatabaseHas('job_postings', [
            'business_profile_id' => $profile->id,
            'business_location_id' => $location->id,
            'workplace_address' => 'Via Roma 10, 20100 Milano (MI), Italia',
            'salary_min' => 1200,
            'salary_max' => 1500,
        ]);
    }

    public function test_business_cannot_use_a_location_owned_by_another_business(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'RSA Aurora',
            'company_type' => 'RSA',
            'location' => 'Milano',
        ]);

        $otherBusiness = User::factory()->create(['role' => 'business']);
        $otherProfile = BusinessProfile::create([
            'user_id' => $otherBusiness->id,
            'company_name' => 'Clinica Delta',
            'company_type' => 'Casa di cura',
            'location' => 'Roma',
        ]);

        $otherLocation = BusinessLocation::create([
            'business_profile_id' => $otherProfile->id,
            'name' => 'Sede Roma',
            'type' => 'operational',
            'street_address' => 'Via Appia 20',
            'city' => 'Roma',
            'country' => 'Italia',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->actingAs($business)
            ->post(route('job-postings.store'), [
                'business_location_id' => $otherLocation->id,
                'title' => 'OSS per RSA',
                'description' => 'Ricerca operatore socio sanitario.',
                'positions' => 1,
                'contract_type' => 'Tempo determinato',
                'expires_at' => now()->addMonth()->toDateString(),
            ])
            ->assertSessionHasErrors('business_location_id');

        $this->assertDatabaseCount('job_postings', 0);
    }
}
