<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\ProfessionalProfileItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessCandidateApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_can_view_candidate_detail_for_its_application(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'RSA Aurora',
            'company_type' => 'RSA',
            'location' => 'Milano',
        ]);

        $professional = User::factory()->create([
            'role' => 'professional',
            'first_name' => 'Maria',
            'last_name' => 'Rossi',
        ]);

        ProfessionalProfileItem::create([
            'user_id' => $professional->id,
            'type' => ProfessionalProfileItem::TYPE_WORK_EXPERIENCE,
            'title' => 'OSS presso RSA Aurora',
            'duration' => '2023 - oggi',
        ]);

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
            ->get(route('business.applications.show', $application))
            ->assertOk()
            ->assertSee('Scheda candidatura')
            ->assertSee('Maria Rossi')
            ->assertSee('OSS presso RSA Aurora')
            ->assertSee('Workflow HR');
    }

    public function test_other_business_cannot_view_candidate_detail(): void
    {
        $owner = User::factory()->create(['role' => 'business']);
        $ownerProfile = BusinessProfile::create([
            'user_id' => $owner->id,
            'company_name' => 'RSA Aurora',
            'company_type' => 'RSA',
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
            ->get(route('business.applications.show', $application))
            ->assertForbidden();
    }
}
