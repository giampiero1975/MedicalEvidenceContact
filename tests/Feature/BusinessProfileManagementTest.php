<?php

namespace Tests\Feature;

use App\Models\BusinessPointOfContact;
use App\Models\BusinessProfile;
use App\Models\BusinessType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_can_update_company_information_address_and_description(): void
    {
        BusinessType::query()->updateOrCreate(
            ['name' => 'RSA'],
            ['is_active' => true, 'sort_order' => 10]
        );

        $business = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'RSA Prima',
            'company_type' => 'RSA',
            'vat_number' => '12345678901',
            'location' => 'Milano',
            'employee_count' => 50,
        ]);

        $response = $this->actingAs($business)->put(route('business.profile.update'), [
            'company_name' => 'RSA Nuova',
            'company_type' => 'RSA',
            'vat_number' => '12345678901',
            'employee_count' => 50,
            'address_street' => 'Via Roma 25',
            'address_city' => 'Milano',
            'address_province' => 'MI',
            'postal_code' => '20100',
            'address_country' => 'Italia',
            'location' => 'Milano',
            'description' => str_repeat('A', 1000),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('business.profile.edit', absolute: false));

        $profile->refresh();
        $this->assertSame('RSA Nuova', $profile->company_name);
        $this->assertSame('Via Roma 25', $profile->address_street);
        $this->assertSame('Milano', $profile->address_city);
        $this->assertSame('MI', $profile->address_province);
        $this->assertSame('20100', $profile->postal_code);
        $this->assertSame('Italia', $profile->address_country);
        $this->assertSame(1000, strlen($profile->description));
    }

    public function test_business_profile_rejects_description_over_one_thousand_characters(): void
    {
        BusinessType::query()->updateOrCreate(
            ['name' => 'RSA'],
            ['is_active' => true, 'sort_order' => 10]
        );

        $business = User::factory()->create(['role' => 'business']);
        BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'RSA Prima',
            'company_type' => 'RSA',
            'vat_number' => '12345678901',
        ]);

        $this->actingAs($business)
            ->from(route('business.profile.edit'))
            ->put(route('business.profile.update'), [
                'company_name' => 'RSA Prima',
                'company_type' => 'RSA',
                'vat_number' => '12345678901',
                'description' => str_repeat('A', 1001),
            ])
            ->assertSessionHasErrors('description');
    }

    public function test_business_can_modify_and_designate_primary_poc(): void
    {
        [$business, $profile, $primary] = $this->businessWithPrimaryPoc();
        $secondUser = User::factory()->create(['role' => 'business', 'email' => 'second@example.test']);
        $second = $profile->addPointOfContact([
            'user_id' => $secondUser->id,
            'first_name' => 'Laura',
            'last_name' => 'Neri',
            'email' => 'second@example.test',
            'phone' => '333222111',
            'role' => 'HR',
        ]);

        $this->actingAs($business)
            ->put(route('business-points-of-contact.update', $second), [
                'first_name' => 'Laura',
                'last_name' => 'Verdi',
                'email' => 'second@example.test',
                'phone' => '333999888',
                'role' => 'Responsabile HR',
            ])
            ->assertRedirect(route('business-points-of-contact.index', absolute: false));

        $this->assertDatabaseHas('business_points_of_contact', [
            'id' => $second->id,
            'last_name' => 'Verdi',
            'phone' => '333999888',
            'role' => 'Responsabile HR',
        ]);

        $this->actingAs($business)
            ->patch(route('business-points-of-contact.primary', $second))
            ->assertRedirect(route('business-points-of-contact.index', absolute: false));

        $this->assertFalse($primary->fresh()->is_primary);
        $this->assertTrue($second->fresh()->is_primary);
        $this->assertTrue($profile->fresh()->primaryPointOfContact->is($second));
    }

    public function test_only_poc_cannot_be_deleted(): void
    {
        [$business, $profile, $primary] = $this->businessWithPrimaryPoc();

        $this->actingAs($business)
            ->delete(route('business-points-of-contact.destroy', $primary))
            ->assertRedirect(route('business-points-of-contact.index', absolute: false));

        $this->assertDatabaseHas('business_points_of_contact', [
            'id' => $primary->id,
            'business_profile_id' => $profile->id,
        ]);
    }

    public function test_business_can_delete_a_poc_when_another_remains(): void
    {
        [$business, $profile, $primary] = $this->businessWithPrimaryPoc();
        $secondUser = User::factory()->create(['role' => 'business', 'email' => 'delete-me@example.test']);
        $second = $profile->addPointOfContact([
            'user_id' => $secondUser->id,
            'first_name' => 'Paolo',
            'last_name' => 'Blu',
            'email' => 'delete-me@example.test',
            'phone' => '333000111',
            'role' => 'Recruiter',
        ]);

        $this->actingAs($business)
            ->delete(route('business-points-of-contact.destroy', $second))
            ->assertRedirect(route('business-points-of-contact.index', absolute: false));

        $this->assertDatabaseMissing('business_points_of_contact', ['id' => $second->id]);
        $this->assertDatabaseMissing('users', ['id' => $secondUser->id]);
        $this->assertTrue($primary->fresh()->is_primary);
    }

    public function test_poc_cannot_manage_contacts_of_another_company(): void
    {
        [$business] = $this->businessWithPrimaryPoc();

        $otherOwner = User::factory()->create(['role' => 'business']);
        $otherProfile = BusinessProfile::create([
            'user_id' => $otherOwner->id,
            'company_name' => 'Altra RSA',
            'company_type' => 'RSA',
        ]);
        $otherPoc = BusinessPointOfContact::create([
            'business_profile_id' => $otherProfile->id,
            'user_id' => $otherOwner->id,
            'first_name' => 'Altro',
            'last_name' => 'Contatto',
            'email' => $otherOwner->email,
            'role' => 'Titolare',
            'is_primary' => true,
        ]);

        $this->actingAs($business)
            ->patch(route('business-points-of-contact.primary', $otherPoc))
            ->assertForbidden();
    }

    private function businessWithPrimaryPoc(): array
    {
        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'owner-'.uniqid().'@example.test',
        ]);

        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'Clinica Test',
            'company_type' => 'RSA',
        ]);

        $primary = $profile->addPointOfContact([
            'user_id' => $business->id,
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'email' => $business->email,
            'phone' => '3331234567',
            'role' => 'Titolare',
        ]);

        return [$business, $profile, $primary];
    }
}
