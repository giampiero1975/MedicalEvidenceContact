<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewSchedulingTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_can_schedule_interview_for_its_application(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create(['role' => 'professional']);

        $posting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'OSS RSA',
            'description' => 'Ricerca OSS.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $application = JobApplication::create([
            'job_posting_id' => $posting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_REVIEW,
        ]);

        $scheduledAt = now()->addDays(2)->setTime(10, 30)->format('Y-m-d H:i:s');

        $this->actingAs($business)
            ->post(route('business.applications.interviews.store', $application), [
                'scheduled_at' => $scheduledAt,
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
            'status' => 'scheduled',
        ]);

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => JobApplication::STATUS_INTERVIEW_SCHEDULED,
        ]);

        $this->assertDatabaseHas('job_application_events', [
            'job_application_id' => $application->id,
            'type' => 'interview_scheduled',
        ]);
    }

    public function test_other_business_cannot_schedule_interview(): void
    {
        $owner = User::factory()->create(['role' => 'business']);
        $otherBusiness = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create(['role' => 'professional']);

        $posting = JobPosting::create([
            'user_id' => $owner->id,
            'title' => 'Infermiere',
            'description' => 'Ricerca infermiere.',
            'positions' => 1,
            'workplace_address' => 'Roma',
            'contract_type' => 'Tempo indeterminato',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

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
}
