<?php

namespace Tests\Feature;

use App\Models\BusinessLocation;
use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_can_create_and_view_a_location(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'RSA Aurora',
            'company_type' => 'RSA',
            'location' => 'Milano',
        ]);

        $this->actingAs($business)
            ->post(route('business.locations.store'), [
                'name' => 'Sede Milano',
                'type' => 'operational',
                'street_address' => 'Via Roma 10',
                'city' => 'Milano',
                'province' => 'MI',
                'postal_code' => '20100',
                'country' => 'Italia',
                'email' => 'milano@example.com',
                'phone' => '021234567',
                'is_primary' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect(route('business.locations.index', absolute: false));

        $this->assertDatabaseHas('business_locations', [
            'business_profile_id' => $profile->id,
            'name' => 'Sede Milano',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->actingAs($business)
            ->get(route('business.locations.index'))
            ->assertOk()
            ->assertSee('Sede Milano')
            ->assertSee('Via Roma 10');
    }

    public function test_only_one_location_can_be_primary(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'Clinica Delta',
            'company_type' => 'Casa di cura',
            'location' => 'Milano',
        ]);

        $first = BusinessLocation::create([
            'business_profile_id' => $profile->id,
            'name' => 'Sede Legale',
            'type' => 'legal',
            'street_address' => 'Via Uno 1',
            'city' => 'Milano',
            'country' => 'Italia',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $second = BusinessLocation::create([
            'business_profile_id' => $profile->id,
            'name' => 'Sede Operativa',
            'type' => 'operational',
            'street_address' => 'Via Due 2',
            'city' => 'Monza',
            'country' => 'Italia',
            'is_primary' => false,
            'is_active' => true,
        ]);

        $this->actingAs($business)
            ->put(route('business.locations.update', $second), [
                'name' => $second->name,
                'type' => $second->type,
                'street_address' => $second->street_address,
                'city' => $second->city,
                'country' => $second->country,
                'is_primary' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect(route('business.locations.index', absolute: false));

        $this->assertFalse($first->refresh()->is_primary);
        $this->assertTrue($second->refresh()->is_primary);
    }

    public function test_professional_cannot_access_business_locations(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($professional)
            ->get(route('business.locations.index'))
            ->assertForbidden();
    }
}
