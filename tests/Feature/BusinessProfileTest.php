<?php

namespace Tests\Feature;

use App\Actions\Fortify\CreateNewUser;
use App\Models\BusinessProfile;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_profile_belongs_to_a_business_user(): void
    {
        $user = User::factory()->create([
            'role' => 'business',
        ]);

        $profile = BusinessProfile::create([
            'user_id' => $user->id,
            'company_name' => 'RSA Aurora',
            'company_type' => 'RSA',
            'location' => 'Torino',
            'employee_count' => 45,
        ]);

        $this->assertTrue($profile->user->is($user));
        $this->assertTrue($user->businessProfile->is($profile));
    }

    public function test_business_profile_can_add_points_of_contact(): void
    {
        $profile = BusinessProfile::create([
            'user_id' => User::factory()->create(['role' => 'business'])->id,
            'company_name' => 'Clinica Delta',
            'company_type' => 'Clinica privata',
            'location' => 'Milano',
            'employee_count' => 120,
        ]);

        $pointOfContact = $profile->addPointOfContact([
            'first_name' => 'Paola',
            'last_name' => 'Verdi',
            'email' => 'paola.verdi@example.com',
            'phone' => '02999888',
        ]);

        $this->assertSame('Paola Verdi', $pointOfContact->fullName());
        $this->assertTrue($pointOfContact->businessProfile->is($profile));
        $this->assertTrue($profile->primaryPointOfContact->is($pointOfContact));
        $this->assertDatabaseHas('business_points_of_contact', [
            'business_profile_id' => $profile->id,
            'email' => 'paola.verdi@example.com',
        ]);
    }

    public function test_business_profile_has_job_postings(): void
    {
        $user = User::factory()->create([
            'role' => 'business',
        ]);

        $profile = BusinessProfile::create([
            'user_id' => $user->id,
            'company_name' => 'Cooperativa Salute',
            'company_type' => 'Cooperativa',
            'location' => 'Bologna',
            'employee_count' => 70,
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $user->id,
            'business_profile_id' => $profile->id,
            'title' => 'OSS turno mattina',
            'description' => 'Ricerca OSS per struttura residenziale.',
            'positions' => 2,
            'workplace_address' => 'Via Emilia 20, Bologna',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $this->assertTrue($profile->jobPostings->first()->is($jobPosting));
        $this->assertTrue($jobPosting->businessProfile->is($profile));
    }

    public function test_business_registration_creates_business_profile_and_primary_poc(): void
    {
        $user = app(CreateNewUser::class)->create([
            'account_type' => 'business',
            'first_name' => 'Mario',
            'last_name' => 'Bianchi',
            'email' => 'mario.bianchi@example.com',
            'phone' => '021234567',
            'company_name' => 'Farmacia Centrale',
            'company_type' => 'Farmacia',
            'vat_number' => '12345678901',
            'business_address_street' => 'Via Nazionale 10',
            'business_address_city' => 'Roma',
            'business_address_province' => 'RM',
            'business_postal_code' => '00100',
            'business_address_country' => 'Italia',
            'employee_count' => '11-50',
            'poc_first_name' => 'Mario',
            'poc_last_name' => 'Bianchi',
            'poc_email' => 'mario.bianchi@example.com',
            'poc_phone' => '021234567',
            'poc_role' => 'Titolare',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertSame('business', $user->role);
        $this->assertSame('Farmacia Centrale', $user->businessProfile->company_name);
        $this->assertSame('12345678901', $user->businessProfile->vat_number);
        $this->assertNotNull($user->businessProfile->primaryPointOfContact);
        $this->assertSame('Mario Bianchi', $user->businessProfile->primaryPointOfContact->fullName());
        $this->assertSame('Titolare', $user->businessProfile->primaryPointOfContact->role);
    }

    public function test_business_user_can_add_point_of_contact_from_internal_area(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
        ]);

        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'Farmacia Centrale',
            'company_type' => 'Farmacia',
            'location' => 'Roma',
            'employee_count' => 12,
        ]);

        $response = $this->actingAs($business)
            ->post(route('business-points-of-contact.store'), [
                'first_name' => 'Laura',
                'last_name' => 'Neri',
                'email' => 'laura.neri@example.com',
                'phone' => '06999888',
            ]);

        $response->assertRedirect(route('business-points-of-contact.index', absolute: false));
        $this->assertDatabaseHas('business_points_of_contact', [
            'business_profile_id' => $profile->id,
            'first_name' => 'Laura',
            'last_name' => 'Neri',
            'email' => 'laura.neri@example.com',
        ]);
    }

    public function test_professional_user_cannot_access_business_points_of_contact(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
        ]);

        $this->actingAs($professional)
            ->get(route('business-points-of-contact.index'))
            ->assertForbidden();

        $this->actingAs($professional)
            ->post(route('business-points-of-contact.store'), [
                'first_name' => 'Laura',
                'last_name' => 'Neri',
                'email' => 'laura.neri@example.com',
            ])
            ->assertForbidden();
    }
}
