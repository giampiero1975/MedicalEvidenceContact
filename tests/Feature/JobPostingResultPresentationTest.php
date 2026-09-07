<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingResultPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_job_results_show_required_metadata_and_new_badge(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);
        $business = User::factory()->create(['role' => 'business']);
        $businessProfile = $business->businessProfile()->create([
            'user_id' => $business->id,
            'company_name' => 'RSA Aurora',
            'company_type' => 'RSA',
            'location' => 'Milano',
            'employee_count' => 80,
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'business_profile_id' => $businessProfile->id,
            'title' => 'Infermiere reparto riabilitazione',
            'description' => 'Posizione per reparto riabilitativo.',
            'positions' => 2,
            'workplace_address' => 'Milano, MI',
            'contract_type' => 'Tempo indeterminato',
            'salary_min' => 30000,
            'salary_max' => 36000,
            'expires_at' => now()->addDays(10),
            'status' => 'active',
        ]);

        $jobPosting->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->saveQuietly();

        $this->actingAs($professional)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertSee('Infermiere reparto riabilitazione')
            ->assertSee('RSA Aurora')
            ->assertSee('Milano, MI')
            ->assertSee('Tempo indeterminato')
            ->assertSee('€ 30.000')
            ->assertSee('€ 36.000')
            ->assertSee('Pubblicato')
            ->assertSee($jobPosting->created_at->format('d/m/Y'))
            ->assertSee('Scadenza')
            ->assertSee($jobPosting->expires_at->format('d/m/Y'))
            ->assertSee('Nuova');
    }

    public function test_job_older_than_three_days_does_not_show_new_badge(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);
        $business = User::factory()->create(['role' => 'business']);
        $businessProfile = $business->businessProfile()->create([
            'user_id' => $business->id,
            'company_name' => 'Clinica Storica',
            'company_type' => 'Clinica privata',
            'location' => 'Torino',
            'employee_count' => 120,
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'business_profile_id' => $businessProfile->id,
            'title' => 'Fisioterapista ambulatoriale senior',
            'description' => 'Posizione pubblicata da più di tre giorni.',
            'positions' => 1,
            'workplace_address' => 'Torino, TO',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addDays(10),
            'status' => 'active',
        ]);

        $jobPosting->forceFill(['created_at' => now()->subDays(4), 'updated_at' => now()->subDays(4)])->saveQuietly();

        $this->actingAs($professional)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertSee('Fisioterapista ambulatoriale senior')
            ->assertDontSee('Nuova');
    }
}
