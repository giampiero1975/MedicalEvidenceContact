<?php

namespace Tests\Feature;

use App\Actions\Fortify\CreateNewUser;
use App\Mail\TransactionalActionMail;
use App\Models\BusinessProfile;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BusinessProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_profile_belongs_to_a_business_user(): void
    {
        $user = User::factory()->create(['role' => 'business']);
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
        $user = User::factory()->create(['role' => 'business']);
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
            'company_street_address' => 'Via Nazionale 10',
            'company_city' => 'Roma',
            'company_province' => 'RM',
            'company_postal_code' => '00100',
            'company_country' => 'Italia',
            'employee_count' => 50,
            'poc_role' => 'Titolare',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertSame('business', $user->role);
        $this->assertSame('Farmacia Centrale', $user->businessProfile->company_name);
        $this->assertSame('12345678901', $user->businessProfile->vat_number);
        $this->assertSame('Via Nazionale 10', $user->businessProfile->address_street);
        $this->assertSame('Roma', $user->businessProfile->address_city);
        $this->assertNotNull($user->businessProfile->primaryPointOfContact);
        $this->assertSame('Mario Bianchi', $user->businessProfile->primaryPointOfContact->fullName());
        $this->assertSame('Titolare', $user->businessProfile->primaryPointOfContact->role);
    }

    public function test_business_user_can_add_point_of_contact_with_login_credentials(): void
    {
        Mail::fake();
        Notification::fake();

        $business = User::factory()->create(['role' => 'business']);
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
                'role' => 'Responsabile HR',
            ]);

        $response->assertRedirect(route('business-points-of-contact.index', absolute: false));

        $pocUser = User::where('email', 'laura.neri@example.com')->firstOrFail();
        $this->assertSame('business', $pocUser->role);

        $this->assertDatabaseHas('business_points_of_contact', [
            'business_profile_id' => $profile->id,
            'user_id' => $pocUser->id,
            'first_name' => 'Laura',
            'last_name' => 'Neri',
            'email' => 'laura.neri@example.com',
            'role' => 'Responsabile HR',
        ]);

        Mail::assertSent(TransactionalActionMail::class, function (TransactionalActionMail $mail) use ($pocUser): bool {
            return $mail->hasTo($pocUser->email)
                && str_contains($mail->mailSubject, 'Accesso Medical Evidence Contact');
        });

        Notification::assertSentTo($pocUser, VerifyEmail::class);
    }

    public function test_professional_user_cannot_access_business_points_of_contact(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

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
