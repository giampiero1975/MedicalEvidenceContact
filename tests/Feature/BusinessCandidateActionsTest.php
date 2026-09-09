<?php

namespace Tests\Feature;

use App\Mail\TransactionalActionMail;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BusinessCandidateActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_business_sees_explicit_interview_and_rejection_actions(): void
    {
        [$business, , $application] = $this->scenario();

        $this->actingAs($business)
            ->get(route('business.applications.show', $application))
            ->assertOk()
            ->assertSeeText('Azioni candidatura')
            ->assertSeeText('Fissa colloquio')
            ->assertSee('href="#fissa-colloquio"', false)
            ->assertSeeText('Rifiuta candidatura')
            ->assertSee('name="status"', false)
            ->assertSee('value="'.JobApplication::STATUS_REJECTED.'"', false);
    }

    public function test_explicit_rejection_action_uses_existing_rejection_workflow(): void
    {
        Mail::fake();

        [$business, $professional, $application] = $this->scenario();

        $this->actingAs($business)
            ->patch(route('job-applications.status.update', $application), [
                'status' => JobApplication::STATUS_REJECTED,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(JobApplication::STATUS_REJECTED, $application->fresh()->status);

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo($professional->email)
        );
    }

    public function test_rejection_action_is_hidden_after_application_is_already_rejected(): void
    {
        [$business, , $application] = $this->scenario(JobApplication::STATUS_REJECTED);

        $this->actingAs($business)
            ->get(route('business.applications.show', $application))
            ->assertOk()
            ->assertSeeText('Fissa colloquio')
            ->assertDontSeeText('Rifiuta candidatura');
    }

    private function scenario(string $status = JobApplication::STATUS_RECEIVED): array
    {
        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'business.actions@example.test',
        ]);
        $professional = User::factory()->create([
            'role' => 'professional',
            'email' => 'professional.actions@example.test',
        ]);

        $jobPosting = JobPosting::create([
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
            'job_posting_id' => $jobPosting->id,
            'user_id' => $professional->id,
            'status' => $status,
        ]);

        return [$business, $professional, $application];
    }
}
