<?php

namespace Tests\Feature;

use App\Models\BusinessLocation;
use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessDepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_can_create_and_view_a_department_for_its_location(): void
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

        $this->actingAs($business)
            ->post(route('business.departments.store'), [
                'business_location_id' => $location->id,
                'name' => 'Nucleo Alzheimer',
                'code' => 'NA-01',
                'manager_name' => 'Laura Neri',
                'email' => 'laura.neri@example.com',
                'is_active' => '1',
            ])
            ->assertRedirect(route('business.departments.index', absolute: false));

        $this->assertDatabaseHas('business_departments', [
            'business_profile_id' => $profile->id,
            'business_location_id' => $location->id,
            'name' => 'Nucleo Alzheimer',
            'is_active' => true,
        ]);

        $this->actingAs($business)
            ->get(route('business.departments.index'))
            ->assertOk()
            ->assertSee('Nucleo Alzheimer')
            ->assertSee('Sede Milano');
    }

    public function test_business_cannot_use_another_company_location(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'RSA Aurora',
            'company_type' => 'RSA',
            'location' => 'Milano',
        ]);

        $other = User::factory()->create(['role' => 'business']);
        $otherProfile = BusinessProfile::create([
            'user_id' => $other->id,
            'company_name' => 'Clinica Delta',
            'company_type' => 'Casa di cura',
            'location' => 'Roma',
        ]);
        $otherLocation = BusinessLocation::create([
            'business_profile_id' => $otherProfile->id,
            'name' => 'Sede Roma',
            'type' => 'operational',
            'street_address' => 'Via Uno 1',
            'city' => 'Roma',
            'country' => 'Italia',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->actingAs($business)
            ->post(route('business.departments.store'), [
                'business_location_id' => $otherLocation->id,
                'name' => 'Reparto improprio',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('business_location_id');

        $this->assertDatabaseMissing('business_departments', ['name' => 'Reparto improprio']);
    }

    public function test_professional_cannot_access_business_departments(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($professional)
            ->get(route('business.departments.index'))
            ->assertForbidden();
    }
}
