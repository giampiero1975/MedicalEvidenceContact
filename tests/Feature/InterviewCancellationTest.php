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

class InterviewCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_can_cancel_accepted_interview_and_both_parties_are_notified(): void
    {
        Mail::fake();

        [$business, $professional, $interview] = $this->scenario();

        $this->actingAs($business)
            ->patch(route('interviews.cancel', $interview), [
                'cancellation_reason' => 'Imprevisto organizzativo.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(Interview::STATUS_CANCELLED, $interview->fresh()->status);

        $this->assertDatabaseHas('job_application_events', [
            'job_application_id' => $interview->job_application_id,
            'type' => 'interview_cancelled',
        ]);

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->mailSubject === 'Colloquio annullato: OSS struttura sanitaria'
            && $mail->hasTo($professional->email)
        );

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->mailSubject === 'Colloquio annullato: OSS struttura sanitaria'
            && $mail->hasTo($business->email)
        );
    }

    public function test_professional_can_cancel_accepted_interview(): void
    {
        Mail::fake();

        [$business, $professional, $interview] = $this->scenario();

        $this->actingAs($professional)
            ->patch(route('interviews.cancel', $interview))
            ->assertSessionHasNoErrors();

        $this->assertSame(Interview::STATUS_CANCELLED, $interview->fresh()->status);

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) => $mail->hasTo($business->email));
        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) => $mail->hasTo($professional->email));
    }

    public function test_non_participant_cannot_cancel_interview(): void
    {
        Mail::fake();

        [, , $interview] = $this->scenario();
        $otherBusiness = User::factory()->create(['role' => 'business']);

        $this->actingAs($otherBusiness)
            ->patch(route('interviews.cancel', $interview))
            ->assertForbidden();

        $this->assertSame(Interview::STATUS_ACCEPTED, $interview->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_only_accepted_interview_can_be_cancelled(): void
    {
        Mail::fake();

        [$business, , $interview] = $this->scenario(Interview::STATUS_REQUESTED);

        $this->actingAs($business)
            ->patch(route('interviews.cancel', $interview))
            ->assertSessionHasErrors('interview');

        $this->assertSame(Interview::STATUS_REQUESTED, $interview->fresh()->status);
        Mail::assertNothingSent();
    }

    private function scenario(string $status = Interview::STATUS_ACCEPTED): array
    {
        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'business.cancel@example.test',
        ]);
        $professional = User::factory()->create([
            'role' => 'professional',
            'email' => 'professional.cancel@example.test',
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
