<?php

namespace Tests\Feature;

use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewCancellationUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_sees_cancel_action_for_accepted_interview(): void
    {
        [$business, , $interview] = $this->scenario();

        $this->actingAs($business)
            ->get(route('interviews.index'))
            ->assertOk()
            ->assertSee(route('interviews.cancel', $interview), false)
            ->assertSee('placeholder="Motivo annullamento (facoltativo)"', false)
            ->assertSeeText('Annulla colloquio');
    }

    public function test_professional_sees_cancel_action_for_accepted_interview(): void
    {
        [, $professional, $interview] = $this->scenario();

        $this->actingAs($professional)
            ->get(route('interviews.index'))
            ->assertOk()
            ->assertSee(route('interviews.cancel', $interview), false)
            ->assertSee('placeholder="Motivo annullamento (facoltativo)"', false)
            ->assertSeeText('Annulla colloquio');
    }

    public function test_cancel_action_is_not_exposed_for_requested_interview(): void
    {
        [$business, , $interview] = $this->scenario(Interview::STATUS_REQUESTED);

        $this->actingAs($business)
            ->get(route('interviews.index'))
            ->assertOk()
            ->assertDontSee(route('interviews.cancel', $interview), false);
    }

    private function scenario(string $status = Interview::STATUS_ACCEPTED): array
    {
        $business = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create(['role' => 'professional']);

        $posting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'OSS struttura sanitaria',
            'description' => 'Ricerca OSS.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addMonth(),
            'status' => 'active',
        ]);

        $application = JobApplication::create([
            'job_posting_id' => $posting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_INTERVIEW_SCHEDULED,
        ]);

        $interview = Interview::create([
            'job_application_id' => $application->id,
            'business_user_id' => $business->id,
            'scheduled_at' => now()->addDays(3),
            'duration_minutes' => 30,
            'mode' => 'phone',
            'status' => $status,
            'contact_sharing_consent' => true,
        ]);

        return [$business, $professional, $interview];
    }
}
