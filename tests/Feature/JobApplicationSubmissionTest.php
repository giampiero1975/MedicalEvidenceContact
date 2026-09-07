<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class JobApplicationSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_must_explicitly_confirm_application_submission(): void
    {
        Mail::fake();
        [$professional, $jobPosting] = $this->scenario();

        $this->actingAs($professional)
            ->post(route('job-applications.store', $jobPosting), [
                'presentation_message' => 'Sono interessato alla posizione.',
            ])
            ->assertSessionHasErrors('application_confirmation');

        $this->assertDatabaseCount('job_applications', 0);
    }

    public function test_professional_can_submit_optional_presentation_message_up_to_500_characters(): void
    {
        Mail::fake();
        [$professional, $jobPosting] = $this->scenario();

        $this->actingAs($professional)
            ->post(route('job-applications.store', $jobPosting), [
                'presentation_message' => 'Ho esperienza nel settore e sono disponibile per un colloquio.',
                'application_confirmation' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('job_applications', [
            'job_posting_id' => $jobPosting->id,
            'user_id' => $professional->id,
            'presentation_message' => 'Ho esperienza nel settore e sono disponibile per un colloquio.',
            'status' => JobApplication::STATUS_RECEIVED,
        ]);
    }

    public function test_presentation_message_cannot_exceed_500_characters(): void
    {
        Mail::fake();
        [$professional, $jobPosting] = $this->scenario();

        $this->actingAs($professional)
            ->post(route('job-applications.store', $jobPosting), [
                'presentation_message' => str_repeat('a', 501),
                'application_confirmation' => '1',
            ])
            ->assertSessionHasErrors('presentation_message');

        $this->assertDatabaseCount('job_applications', 0);
    }

    public function test_job_detail_exposes_documented_application_confirmation_ui(): void
    {
        [$professional, $jobPosting] = $this->scenario();

        $this->actingAs($professional)
            ->get(route('job-postings.show', $jobPosting))
            ->assertOk()
            ->assertSee('Conferma candidatura')
            ->assertSee('Messaggio di presentazione')
            ->assertSee("Confermo l'invio della candidatura")
            ->assertSee('Invia candidatura');
    }

    private function scenario(): array
    {
        $professional = User::factory()->create([
            'role' => 'professional',
            'first_name' => 'Giulia',
            'last_name' => 'Rossi',
            'residence' => 'Milano',
        ]);
        $business = User::factory()->create(['role' => 'business']);
        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'OSS struttura sanitaria',
            'description' => 'Ricerca professionista.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek(),
            'status' => 'active',
        ]);

        return [$professional, $jobPosting];
    }
}
