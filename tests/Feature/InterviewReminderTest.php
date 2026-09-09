<?php

namespace Tests\Feature;

use App\Mail\TransactionalActionMail;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InterviewReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_accepted_interview_within_five_hours_sends_reminder_to_both_parties(): void
    {
        Carbon::setTestNow('2026-09-09 10:00:00');
        Mail::fake();

        [$business, $professional, $interview] = $this->scenario(
            status: Interview::STATUS_ACCEPTED,
            scheduledAt: now()->addHours(5)
        );

        $this->artisan('interviews:send-reminders')
            ->expectsOutput('Promemoria colloqui inviati: 1')
            ->assertExitCode(0);

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo($professional->email)
            && $mail->mailSubject === 'Promemoria colloquio tra 5 ore: OSS struttura sanitaria'
            && in_array('Email struttura: '.$business->email, $mail->details, true)
        );

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo($business->email)
            && in_array('Email professionista: '.$professional->email, $mail->details, true)
        );

        $this->assertNotNull($interview->fresh()->reminder_sent_at);
    }

    public function test_reminder_is_not_sent_more_than_five_hours_before_interview(): void
    {
        Carbon::setTestNow('2026-09-09 10:00:00');
        Mail::fake();

        [, , $interview] = $this->scenario(
            status: Interview::STATUS_ACCEPTED,
            scheduledAt: now()->addHours(5)->addMinute()
        );

        $this->artisan('interviews:send-reminders')
            ->expectsOutput('Promemoria colloqui inviati: 0')
            ->assertExitCode(0);

        Mail::assertNothingSent();
        $this->assertNull($interview->fresh()->reminder_sent_at);
    }

    public function test_reminder_is_only_sent_for_accepted_interviews(): void
    {
        Carbon::setTestNow('2026-09-09 10:00:00');
        Mail::fake();

        [, , $interview] = $this->scenario(
            status: Interview::STATUS_CANCELLED,
            scheduledAt: now()->addHours(4)
        );

        $this->artisan('interviews:send-reminders')
            ->expectsOutput('Promemoria colloqui inviati: 0')
            ->assertExitCode(0);

        Mail::assertNothingSent();
        $this->assertNull($interview->fresh()->reminder_sent_at);
    }

    public function test_reminder_is_not_sent_twice(): void
    {
        Carbon::setTestNow('2026-09-09 10:00:00');
        Mail::fake();

        [, , $interview] = $this->scenario(
            status: Interview::STATUS_ACCEPTED,
            scheduledAt: now()->addHours(4)
        );

        $this->artisan('interviews:send-reminders')->assertExitCode(0);
        $this->artisan('interviews:send-reminders')
            ->expectsOutput('Promemoria colloqui inviati: 0')
            ->assertExitCode(0);

        Mail::assertSent(TransactionalActionMail::class, 2);
        $this->assertNotNull($interview->fresh()->reminder_sent_at);
    }

    private function scenario(string $status, Carbon $scheduledAt): array
    {
        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'business.reminder@example.test',
            'phone' => '+390212345678',
        ]);
        $professional = User::factory()->create([
            'role' => 'professional',
            'email' => 'professional.reminder@example.test',
            'phone' => '+393331234567',
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
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => 30,
            'mode' => 'phone',
            'status' => $status,
            'contact_sharing_consent' => true,
        ]);

        return [$business, $professional, $interview];
    }
}
