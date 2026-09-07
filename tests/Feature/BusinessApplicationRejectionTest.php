<?php

namespace Tests\Feature;

use App\Mail\TransactionalActionMail;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BusinessApplicationRejectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_business_can_reject_application_and_professional_is_notified(): void
    {
        Mail::fake();

        [$business, $professional, $application] = $this->scenario();

        $this->actingAs($business)
            ->patch(route('job-applications.status.update', $application), [
                'status' => JobApplication::STATUS_REJECTED,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => JobApplication::STATUS_REJECTED,
        ]);

        $this->assertDatabaseHas('job_application_events', [
            'job_application_id' => $application->id,
            'type' => 'status_changed',
            'from_status' => JobApplication::STATUS_RECEIVED,
            'to_status' => JobApplication::STATUS_REJECTED,
        ]);

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo($professional->email)
            && $mail->mailSubject === 'Aggiornamento candidatura: OSS struttura sanitaria'
        );
    }

    public function test_repeated_rejected_status_does_not_send_duplicate_rejection_email(): void
    {
        Mail::fake();

        [$business, $professional, $application] = $this->scenario(JobApplication::STATUS_REJECTED);

        $this->actingAs($business)
            ->patch(route('job-applications.status.update', $application), [
                'status' => JobApplication::STATUS_REJECTED,
            ])
            ->assertSessionHasNoErrors();

        Mail::assertNothingSent();
    }

    public function test_other_business_cannot_reject_application(): void
    {
        Mail::fake();

        [, , $application] = $this->scenario();
        $otherBusiness = User::factory()->create(['role' => 'business']);

        $this->actingAs($otherBusiness)
            ->patch(route('job-applications.status.update', $application), [
                'status' => JobApplication::STATUS_REJECTED,
            ])
            ->assertForbidden();

        $this->assertSame(JobApplication::STATUS_RECEIVED, $application->fresh()->status);
        Mail::assertNothingSent();
    }

    private function scenario(string $status = JobApplication::STATUS_RECEIVED): array
    {
        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'business@example.test',
        ]);
        $professional = User::factory()->create([
            'role' => 'professional',
            'email' => 'professional@example.test',
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'OSS struttura sanitaria',
            'description' => 'Ricerca OSS.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek(),
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
