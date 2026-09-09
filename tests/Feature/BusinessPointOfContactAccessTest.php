<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BusinessPointOfContactAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_additional_poc_can_view_all_job_postings_for_the_same_company_only(): void
    {
        $owner = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $owner->id,
            'company_name' => 'Clinica Condivisa',
            'company_type' => 'Clinica privata',
            'location' => 'Milano',
            'employee_count' => 50,
        ]);

        $pocUser = User::factory()->create(['role' => 'business']);
        $profile->addPointOfContact([
            'user_id' => $pocUser->id,
            'first_name' => $pocUser->first_name ?: 'Laura',
            'last_name' => $pocUser->last_name ?: 'Neri',
            'email' => $pocUser->email,
            'phone' => $pocUser->phone,
            'role' => 'Responsabile HR',
        ]);

        $companyPosting = JobPosting::create([
            'user_id' => $owner->id,
            'business_profile_id' => $profile->id,
            'title' => 'Annuncio aziendale condiviso',
            'description' => 'Annuncio visibile a tutti i POC della stessa azienda.',
            'positions' => 1,
            'workplace_address' => 'Via Roma 10, Milano',
            'contract_type' => 'Tempo indeterminato',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $otherOwner = User::factory()->create(['role' => 'business']);
        $otherProfile = BusinessProfile::create([
            'user_id' => $otherOwner->id,
            'company_name' => 'Altra Azienda',
            'company_type' => 'RSA',
            'location' => 'Torino',
            'employee_count' => 50,
        ]);
        $otherPosting = JobPosting::create([
            'user_id' => $otherOwner->id,
            'business_profile_id' => $otherProfile->id,
            'title' => 'Annuncio altra azienda',
            'description' => 'Non deve essere visibile al POC.',
            'positions' => 1,
            'workplace_address' => 'Via Torino 2, Torino',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($pocUser)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertSee('Annuncio aziendale condiviso')
            ->assertDontSee('Annuncio altra azienda');

        $this->actingAs($pocUser)
            ->get(route('job-postings.show', $companyPosting))
            ->assertOk();

        $this->actingAs($pocUser)
            ->get(route('job-postings.show', $otherPosting))
            ->assertForbidden();
    }

    public function test_additional_poc_publishes_job_posting_under_shared_company_profile(): void
    {
        Mail::fake();

        $owner = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $owner->id,
            'company_name' => 'RSA Condivisa',
            'company_type' => 'RSA',
            'location' => 'Roma',
            'employee_count' => 50,
        ]);

        $pocUser = User::factory()->create(['role' => 'business']);
        $profile->addPointOfContact([
            'user_id' => $pocUser->id,
            'first_name' => $pocUser->first_name ?: 'Paolo',
            'last_name' => $pocUser->last_name ?: 'Verdi',
            'email' => $pocUser->email,
            'phone' => $pocUser->phone,
            'role' => 'Recruiter',
        ]);

        $this->actingAs($pocUser)
            ->post(route('job-postings.store'), [
                'title' => 'OSS per RSA condivisa',
                'description' => 'Ricerca OSS per struttura residenziale.',
                'positions' => 2,
                'workplace_address' => 'Via Appia 20, Roma',
                'contract_type' => 'Tempo determinato',
                'expires_at' => now()->addMonth()->toDateString(),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('job-postings.index', absolute: false));

        $this->assertDatabaseHas('job_postings', [
            'user_id' => $pocUser->id,
            'business_profile_id' => $profile->id,
            'title' => 'OSS per RSA condivisa',
            'status' => 'active',
        ]);
    }

    public function test_additional_poc_dashboard_lists_shared_company_job_postings(): void
    {
        $owner = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $owner->id,
            'company_name' => 'Farmacia Condivisa',
            'company_type' => 'Farmacia',
            'location' => 'Bologna',
            'employee_count' => 10,
        ]);

        $pocUser = User::factory()->create(['role' => 'business']);
        $profile->addPointOfContact([
            'user_id' => $pocUser->id,
            'first_name' => $pocUser->first_name ?: 'Anna',
            'last_name' => $pocUser->last_name ?: 'Blu',
            'email' => $pocUser->email,
            'phone' => $pocUser->phone,
            'role' => 'HR',
        ]);

        JobPosting::create([
            'user_id' => $owner->id,
            'business_profile_id' => $profile->id,
            'title' => 'Farmacista sede Bologna',
            'description' => 'Posizione condivisa nel dashboard aziendale.',
            'positions' => 1,
            'workplace_address' => 'Via Indipendenza 1, Bologna',
            'contract_type' => 'Tempo indeterminato',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($pocUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Farmacista sede Bologna');
    }
}
