<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingDeletionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_cannot_delete_job_posting_with_active_applications(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create(['role' => 'professional']);
        $posting = $this->postingFor($business);

        JobApplication::create([
            'job_posting_id' => $posting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_REVIEW,
        ]);

        $this->actingAs($business)
            ->delete(route('job-postings.destroy', $posting))
            ->assertRedirect(route('job-postings.show', $posting, absolute: false))
            ->assertSessionHas('warning', 'Non puoi eliminare un annuncio con candidature attive. Puoi chiuderlo impostando lo stato su Scaduto.');

        $this->assertDatabaseHas('job_postings', ['id' => $posting->id]);
    }

    public function test_business_can_delete_job_posting_when_applications_are_terminal(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create(['role' => 'professional']);
        $posting = $this->postingFor($business);

        JobApplication::create([
            'job_posting_id' => $posting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_REJECTED,
        ]);

        $this->actingAs($business)
            ->delete(route('job-postings.destroy', $posting))
            ->assertRedirect(route('job-postings.index', absolute: false));

        $this->assertDatabaseMissing('job_postings', ['id' => $posting->id]);
    }

    public function test_other_business_cannot_delete_job_posting(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $otherBusiness = User::factory()->create(['role' => 'business']);
        $posting = $this->postingFor($business);

        $this->actingAs($otherBusiness)
            ->delete(route('job-postings.destroy', $posting))
            ->assertForbidden();

        $this->assertDatabaseHas('job_postings', ['id' => $posting->id]);
    }

    private function postingFor(User $business): JobPosting
    {
        return JobPosting::create([
            'user_id' => $business->id,
            'title' => 'OSS struttura sanitaria',
            'description' => 'Ricerca professionista.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek(),
            'status' => 'active',
        ]);
    }
}
