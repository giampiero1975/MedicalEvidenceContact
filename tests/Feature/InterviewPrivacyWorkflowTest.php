<?php

namespace Tests\Feature;

use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewPrivacyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_cannot_see_contacts_before_professional_accepts_with_consent(): void
    {
        [$business, $professional, $application] = $this->scenario();

        $this->actingAs($business)
            ->get(route('business.applications.show', $application))
            ->assertOk()
            ->assertDontSee($professional->email)
            ->assertDontSee($professional->phone)
            ->assertSee('Contatti protetti');
    }

    public function test_professional_acceptance_with_consent_unlocks_contacts_for_owner_business(): void
    {
        [$business, $professional, $application] = $this->scenario();

        $interview = Interview::create([
            'job_application_id' => $application->id,
            'business_user_id' => $business->id,
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 30,
            'mode' => 'video',
            'location' => 'https://meet.example.test/interview',
            'status' => 'scheduled',
        ]);

        $this->actingAs($professional)
            ->patch(route('professional.interviews.respond', $interview), [
                'response' => 'accepted',
                'contact_sharing_consent' => 1,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('interviews', [
            'id' => $interview->id,
            'status' => 'accepted',
            'contact_sharing_consent' => 1,
        ]);

        $this->actingAs($business)
            ->get(route('business.applications.show', $application))
            ->assertOk()
            ->assertSee($professional->email)
            ->assertSee($professional->phone);
    }

    public function test_professional_cannot_accept_without_contact_consent(): void
    {
        [$business, $professional, $application] = $this->scenario();

        $interview = Interview::create([
            'job_application_id' => $application->id,
            'business_user_id' => $business->id,
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 30,
            'mode' => 'phone',
            'status' => 'scheduled',
        ]);

        $this->actingAs($professional)
            ->patch(route('professional.interviews.respond', $interview), [
                'response' => 'accepted',
            ])
            ->assertSessionHasErrors('contact_sharing_consent');

        $this->assertSame('scheduled', $interview->refresh()->status);
    }

    private function scenario(): array
    {
        $business = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create([
            'role' => 'professional',
            'email' => 'privacy.professional@example.test',
            'phone' => '3331234567',
        ]);

        $jobPosting = JobPosting::create([
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
            'job_posting_id' => $jobPosting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_INTERVIEW_SCHEDULED,
        ]);

        return [$business, $professional, $application];
    }
}
