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

    public function test_business_cannot_see_contacts_before_final_confirmation(): void
    {
        [$business, $professional, $application] = $this->scenario();

        $this->actingAs($business)
            ->get(route('business.applications.show', $application))
            ->assertOk()
            ->assertDontSee($professional->email)
            ->assertDontSee($professional->phone)
            ->assertSee('Contatti protetti');
    }

    public function test_professional_slot_selection_with_consent_does_not_unlock_contacts_yet(): void
    {
        [$business, $professional, $application] = $this->scenario();
        $interview = $this->proposal($business, $application);

        $this->actingAs($professional)
            ->patch(route('professional.interviews.respond', $interview), [
                'contact_sharing_consent' => 1,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('interviews', [
            'id' => $interview->id,
            'status' => Interview::STATUS_REQUESTED,
            'contact_sharing_consent' => 1,
        ]);

        $this->actingAs($business)
            ->get(route('business.applications.show', $application))
            ->assertOk()
            ->assertDontSee($professional->email)
            ->assertDontSee($professional->phone);
    }

    public function test_business_final_confirmation_unlocks_contacts_after_professional_consent(): void
    {
        [$business, $professional, $application] = $this->scenario();
        $interview = $this->proposal($business, $application, Interview::STATUS_REQUESTED, true);

        $this->actingAs($business)
            ->patch(route('business.interviews.confirm', $interview), [
                'decision' => 'accepted',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(Interview::STATUS_ACCEPTED, $interview->refresh()->status);
        $this->assertTrue($interview->unlocksContacts());

        $this->actingAs($business)
            ->get(route('business.applications.show', $application))
            ->assertOk()
            ->assertSee($professional->email)
            ->assertSee($professional->phone);
    }

    public function test_professional_cannot_select_slot_without_contact_consent(): void
    {
        [$business, $professional, $application] = $this->scenario();
        $interview = $this->proposal($business, $application);

        $this->actingAs($professional)
            ->patch(route('professional.interviews.respond', $interview))
            ->assertSessionHasErrors('contact_sharing_consent');

        $this->assertSame(Interview::STATUS_PROPOSED, $interview->refresh()->status);
    }

    public function test_professional_cannot_select_second_slot_after_requesting_one(): void
    {
        [$business, $professional, $application] = $this->scenario();
        $first = $this->proposal($business, $application);
        $second = Interview::create([
            'job_application_id' => $application->id,
            'business_user_id' => $business->id,
            'scheduled_at' => now()->addDays(2),
            'duration_minutes' => 30,
            'mode' => 'phone',
            'status' => Interview::STATUS_PROPOSED,
        ]);

        $this->actingAs($professional)
            ->patch(route('professional.interviews.respond', $first), ['contact_sharing_consent' => 1])
            ->assertSessionHasNoErrors();

        $this->actingAs($professional)
            ->patch(route('professional.interviews.respond', $second), ['contact_sharing_consent' => 1])
            ->assertSessionHasErrors('response');

        $this->assertSame(Interview::STATUS_PROPOSED, $second->refresh()->status);
    }

    public function test_other_professional_cannot_select_interview_slot(): void
    {
        [$business, $professional, $application] = $this->scenario();
        $otherProfessional = User::factory()->create(['role' => 'professional']);
        $interview = $this->proposal($business, $application);

        $this->actingAs($otherProfessional)
            ->patch(route('professional.interviews.respond', $interview), ['contact_sharing_consent' => 1])
            ->assertForbidden();

        $this->assertSame(Interview::STATUS_PROPOSED, $interview->refresh()->status);
    }

    public function test_other_business_cannot_confirm_requested_slot(): void
    {
        [$business, $professional, $application] = $this->scenario();
        $otherBusiness = User::factory()->create(['role' => 'business']);
        $interview = $this->proposal($business, $application, Interview::STATUS_REQUESTED, true);

        $this->actingAs($otherBusiness)
            ->patch(route('business.interviews.confirm', $interview), ['decision' => 'accepted'])
            ->assertForbidden();

        $this->assertSame(Interview::STATUS_REQUESTED, $interview->refresh()->status);
    }

    private function proposal(User $business, JobApplication $application, string $status = Interview::STATUS_PROPOSED, bool $consent = false): Interview
    {
        return Interview::create([
            'job_application_id' => $application->id,
            'business_user_id' => $business->id,
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 30,
            'mode' => 'video',
            'location' => 'https://meet.example.test/interview',
            'status' => $status,
            'contact_sharing_consent' => $consent,
            'responded_at' => $status === Interview::STATUS_REQUESTED ? now() : null,
        ]);
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
