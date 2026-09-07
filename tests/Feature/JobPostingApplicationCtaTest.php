<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingApplicationCtaTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_job_list_application_cta_opens_detail_instead_of_submitting_directly(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);
        $business = User::factory()->create(['role' => 'business']);
        $jobPosting = $this->postingFor($business);

        $response = $this->actingAs($professional)->get(route('job-postings.index'));

        $response
            ->assertOk()
            ->assertSee('Candidati')
            ->assertSee(route('job-postings.show', $jobPosting), false)
            ->assertDontSee('action="'.route('job-applications.store', $jobPosting).'"', false);

        $this->assertDatabaseCount('job_applications', 0);
    }

    public function test_job_list_does_not_offer_application_cta_after_professional_has_applied(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);
        $business = User::factory()->create(['role' => 'business']);
        $jobPosting = $this->postingFor($business);

        JobApplication::create([
            'job_posting_id' => $jobPosting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_RECEIVED,
        ]);

        $this->actingAs($professional)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertSee('Candidatura ricevuta')
            ->assertDontSee('>Candidati<', false);
    }

    private function postingFor(User $business): JobPosting
    {
        return JobPosting::create([
            'user_id' => $business->id,
            'title' => 'Infermiere area clinica',
            'description' => 'Ricerca professionista sanitario.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek(),
            'status' => 'active',
        ]);
    }
}
