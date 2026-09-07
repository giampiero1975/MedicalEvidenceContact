<?php

namespace Tests\Feature;

use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewSchedulingTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_can_propose_interview_slot_for_its_application(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create(['role' => 'professional']);
        $posting = $this->postingFor($business);
        $application = JobApplication::create([
            'job_posting_id' => $posting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_REVIEW,
        ]);

        $this->actingAs($business)
            ->post(route('business.applications.interviews.store', $application), [
                'scheduled_at' => now()->addDays(2)->setTime(10, 30)->format('Y-m-d H:i:s'),
                'duration_minutes' => 45,
                'mode' => 'video',
                'location' => 'https://meet.example.test/colloquio',
                'notes' => 'Verificare disponibilità ai turni.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('interviews', [
            'job_application_id' => $application->id,
            'business_user_id' => $business->id,
            'duration_minutes' => 45,
            'mode' => 'video',
            'status' => Interview::STATUS_PROPOSED,
        ]);
        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => JobApplication::STATUS_INTERVIEW_SCHEDULED,
        ]);
        $this->assertDatabaseHas('job_application_events', [
            'job_application_id' => $application->id,
            'type' => 'interview_slot_proposed',
        ]);
    }

    public function test_business_can_propose_multiple_slots_until_professional_selects_one(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create(['role' => 'professional']);
        $posting = $this->postingFor($business);
        $application = JobApplication::create([
            'job_posting_id' => $posting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_REVIEW,
        ]);

        foreach ([2, 3] as $days) {
            $this->actingAs($business)
                ->post(route('business.applications.interviews.store', $application), [
                    'scheduled_at' => now()->addDays($days)->setTime(10, 0)->format('Y-m-d H:i:s'),
                    'duration_minutes' => 30,
                    'mode' => 'phone',
                ])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(2, $application->interviews()->where('status', Interview::STATUS_PROPOSED)->count());
    }

    public function test_business_cannot_add_slots_after_professional_has_requested_one(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create(['role' => 'professional']);
        $posting = $this->postingFor($business);
        $application = JobApplication::create([
            'job_posting_id' => $posting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_INTERVIEW_SCHEDULED,
        ]);

        Interview::create([
            'job_application_id' => $application->id,
            'business_user_id' => $business->id,
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 30,
            'mode' => 'phone',
            'status' => Interview::STATUS_REQUESTED,
            'contact_sharing_consent' => true,
        ]);

        $this->actingAs($business)
            ->from(route('business.applications.show', $application))
            ->post(route('business.applications.interviews.store', $application), [
                'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'duration_minutes' => 30,
                'mode' => 'phone',
            ])
            ->assertRedirect(route('business.applications.show', $application))
            ->assertSessionHasErrors('interview');

        $this->assertDatabaseCount('interviews', 1);
    }

    public function test_business_interview_page_only_lists_applications_without_active_interview_workflow(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $availableProfessional = User::factory()->create(['role' => 'professional', 'first_name' => 'Disponibile', 'last_name' => 'Test']);
        $scheduledProfessional = User::factory()->create(['role' => 'professional', 'first_name' => 'Gia Pianificato', 'last_name' => 'Test']);
        $posting = $this->postingFor($business);

        $availableApplication = JobApplication::create([
            'job_posting_id' => $posting->id,
            'user_id' => $availableProfessional->id,
            'status' => JobApplication::STATUS_REVIEW,
        ]);
        $scheduledApplication = JobApplication::create([
            'job_posting_id' => $posting->id,
            'user_id' => $scheduledProfessional->id,
            'status' => JobApplication::STATUS_INTERVIEW_SCHEDULED,
        ]);

        Interview::create([
            'job_application_id' => $scheduledApplication->id,
            'business_user_id' => $business->id,
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 30,
            'mode' => 'phone',
            'status' => Interview::STATUS_PROPOSED,
        ]);

        $response = $this->actingAs($business)
            ->get(route('interviews.index'))
            ->assertOk()
            ->assertSee('Disponibile Test');

        $response->assertViewHas('businessJobPostings', function ($postings) use ($availableApplication, $scheduledApplication): bool {
            $applicationIds = $postings->flatMap(fn ($jobPosting) => $jobPosting->applications)->pluck('id');

            return $applicationIds->contains($availableApplication->id)
                && ! $applicationIds->contains($scheduledApplication->id);
        });
    }

    public function test_other_business_cannot_propose_interview_slot(): void
    {
        $owner = User::factory()->create(['role' => 'business']);
        $otherBusiness = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create(['role' => 'professional']);
        $posting = $this->postingFor($owner);
        $application = JobApplication::create([
            'job_posting_id' => $posting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_RECEIVED,
        ]);

        $this->actingAs($otherBusiness)
            ->post(route('business.applications.interviews.store', $application), [
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'duration_minutes' => 30,
                'mode' => 'phone',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('interviews', 0);
    }

    private function postingFor(User $business): JobPosting
    {
        return JobPosting::create([
            'user_id' => $business->id,
            'title' => 'OSS RSA',
            'description' => 'Ricerca OSS.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);
    }
}
