<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_can_update_status_of_application_for_its_job_posting(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'RSA Aurora',
            'company_type' => 'RSA',
            'location' => 'Milano',
        ]);

        $professional = User::factory()->create(['role' => 'professional']);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'business_profile_id' => $profile->id,
            'title' => 'OSS per RSA',
            'description' => 'Ricerca operatore socio sanitario.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $application = JobApplication::create([
            'job_posting_id' => $jobPosting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_RECEIVED,
        ]);

        $this->actingAs($business)
            ->patch(route('job-applications.status.update', $application), [
                'status' => JobApplication::STATUS_REVIEW,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => JobApplication::STATUS_REVIEW,
        ]);
    }

    public function test_business_cannot_update_application_owned_by_another_business(): void
    {
        $owner = User::factory()->create(['role' => 'business']);
        $ownerProfile = BusinessProfile::create([
            'user_id' => $owner->id,
            'company_name' => 'Clinica Uno',
            'company_type' => 'Clinica',
            'location' => 'Milano',
        ]);

        $otherBusiness = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create(['role' => 'professional']);

        $jobPosting = JobPosting::create([
            'user_id' => $owner->id,
            'business_profile_id' => $ownerProfile->id,
            'title' => 'Infermiere',
            'description' => 'Ricerca infermiere.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo indeterminato',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $application = JobApplication::create([
            'job_posting_id' => $jobPosting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_RECEIVED,
        ]);

        $this->actingAs($otherBusiness)
            ->patch(route('job-applications.status.update', $application), [
                'status' => JobApplication::STATUS_HIRED,
            ])
            ->assertForbidden();

        $this->assertSame(JobApplication::STATUS_RECEIVED, $application->refresh()->status);
    }

    public function test_invalid_pipeline_status_is_rejected(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'RSA Aurora',
            'company_type' => 'RSA',
            'location' => 'Milano',
        ]);
        $professional = User::factory()->create(['role' => 'professional']);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'business_profile_id' => $profile->id,
            'title' => 'OSS',
            'description' => 'Ricerca OSS.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Part-time',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $application = JobApplication::create([
            'job_posting_id' => $jobPosting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_RECEIVED,
        ]);

        $this->actingAs($business)
            ->patch(route('job-applications.status.update', $application), [
                'status' => 'stato_non_valido',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame(JobApplication::STATUS_RECEIVED, $application->refresh()->status);
    }
}
