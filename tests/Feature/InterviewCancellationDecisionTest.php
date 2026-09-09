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

class InterviewCancellationDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancellation_email_contains_reason_reschedule_link_and_definitive_confirmation_link(): void
    {
        Mail::fake();

        [$business, $professional, $interview] = $this->scenario();

        $this->actingAs($business)
            ->patch(route('interviews.cancel', $interview), [
                'cancellation_reason' => 'Cambio disponibilità della struttura.',
            ])
            ->assertSessionHasNoErrors();

        $rescheduleUrl = route('interviews.index').'#interview-'.$interview->id;
        $confirmationUrl = route('interviews.cancellation.confirmation', $interview);

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo($professional->email)
            && $mail->actionLabel === 'Riprogramma colloquio'
            && $mail->actionUrl === $rescheduleUrl
            && $mail->secondaryActionLabel === 'Conferma annullamento definitivo'
            && $mail->secondaryActionUrl === $confirmationUrl
            && in_array('Motivo: Cambio disponibilità della struttura.', $mail->details, true)
        );

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo($business->email)
            && $mail->secondaryActionUrl === $confirmationUrl
        );
    }

    public function test_participant_can_open_definitive_cancellation_confirmation_page(): void
    {
        [, $professional, $interview] = $this->scenario(Interview::STATUS_CANCELLED);

        $this->actingAs($professional)
            ->get(route('interviews.cancellation.confirmation', $interview))
            ->assertOk()
            ->assertSeeText("Confermare l'annullamento definitivo?", false)
            ->assertSeeText('Riprogramma')
            ->assertSeeText('Conferma annullamento definitivo');
    }

    public function test_participant_can_confirm_definitive_cancellation_once(): void
    {
        [$business, , $interview] = $this->scenario(Interview::STATUS_CANCELLED);

        $this->actingAs($business)
            ->patch(route('interviews.cancellation.confirm', $interview))
            ->assertRedirect(route('interviews.index'))
            ->assertSessionHas('status', 'Annullamento definitivo confermato.');

        $this->assertDatabaseHas('job_application_events', [
            'job_application_id' => $interview->job_application_id,
            'actor_user_id' => $business->id,
            'type' => 'interview_cancellation_confirmed',
        ]);

        $this->actingAs($business)
            ->patch(route('interviews.cancellation.confirm', $interview))
            ->assertRedirect(route('interviews.index'));

        $this->assertSame(
            1,
            $interview->jobApplication->events()
                ->where('actor_user_id', $business->id)
                ->where('type', 'interview_cancellation_confirmed')
                ->count()
        );
    }

    public function test_non_participant_cannot_open_or_confirm_cancellation(): void
    {
        [, , $interview] = $this->scenario(Interview::STATUS_CANCELLED);
        $otherProfessional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($otherProfessional)
            ->get(route('interviews.cancellation.confirmation', $interview))
            ->assertForbidden();

        $this->actingAs($otherProfessional)
            ->patch(route('interviews.cancellation.confirm', $interview))
            ->assertForbidden();
    }

    private function scenario(string $status = Interview::STATUS_ACCEPTED): array
    {
        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'business.rf057@example.test',
        ]);
        $professional = User::factory()->create([
            'role' => 'professional',
            'email' => 'professional.rf057@example.test',
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
