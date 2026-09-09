<?php

namespace Tests\Feature;

use App\Mail\TransactionalActionMail;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InterviewRescheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_can_start_reschedule_from_accepted_interview(): void
    {
        Mail::fake();

        [$business, $professional, $interview] = $this->scenario();

        $this->actingAs($business)
            ->patch(route('interviews.reschedule', $interview))
            ->assertSessionHasNoErrors();

        $this->assertSame(Interview::STATUS_CANCELLED, $interview->fresh()->status);

        $this->assertDatabaseHas('job_application_events', [
            'job_application_id' => $interview->job_application_id,
            'type' => 'interview_reschedule_requested',
        ]);

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->mailSubject === 'Riprogrammazione colloquio: OSS struttura sanitaria'
            && $mail->hasTo($professional->email)
        );

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->mailSubject === 'Riprogrammazione colloquio: OSS struttura sanitaria'
            && $mail->hasTo($business->email)
        );
    }

    public function test_professional_can_request_reschedule_from_accepted_interview(): void
    {
        Mail::fake();

        [$business, $professional, $interview] = $this->scenario();

        $this->actingAs($professional)
            ->patch(route('interviews.reschedule', $interview))
            ->assertSessionHasNoErrors();

        $this->assertSame(Interview::STATUS_CANCELLED, $interview->fresh()->status);

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) => $mail->hasTo($business->email));
        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) => $mail->hasTo($professional->email));
    }

    public function test_cancelled_interview_can_be_reopened_for_rescheduling(): void
    {
        Mail::fake();

        [$business, , $interview] = $this->scenario(Interview::STATUS_CANCELLED);

        $this->actingAs($business)
            ->patch(route('interviews.reschedule', $interview))
            ->assertSessionHasNoErrors();

        $this->assertSame(Interview::STATUS_CANCELLED, $interview->fresh()->status);

        $this->assertDatabaseHas('job_application_events', [
            'job_application_id' => $interview->job_application_id,
            'type' => 'interview_reschedule_requested',
        ]);
    }

    public function test_non_participant_cannot_request_reschedule(): void
    {
        Mail::fake();

        [, , $interview] = $this->scenario();
        $otherBusiness = User::factory()->create(['role' => 'business']);

        $this->actingAs($otherBusiness)
            ->patch(route('interviews.reschedule', $interview))
            ->assertForbidden();

        $this->assertSame(Interview::STATUS_ACCEPTED, $interview->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_requested_interview_cannot_be_rescheduled(): void
    {
        Mail::fake();

        [$business, , $interview] = $this->scenario(Interview::STATUS_REQUESTED);

        $this->actingAs($business)
            ->patch(route('interviews.reschedule', $interview))
            ->assertSessionHasErrors('interview');

        $this->assertSame(Interview::STATUS_REQUESTED, $interview->fresh()->status);
        Mail::assertNothingSent();
    }

    private function scenario(string $status = Interview::STATUS_ACCEPTED): array
    {
        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'business.reschedule@example.test',
        ]);
        $professional = User::factory()->create([
            'role' => 'professional',
            'email' => 'professional.reschedule@example.test',
        ]);

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
