<?php

namespace Tests\Feature;

use App\Mail\TransactionalActionMail;
use App\Models\JobPosting;
use App\Models\NotificationEvent;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class JobPostingExpiryReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_receives_reminder_seven_days_before_expiry(): void
    {
        Mail::fake();

        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'business.expiry@example.test',
        ]);

        $posting = $this->posting($business, now()->addDays(7));

        Artisan::call('job-postings:send-expiry-reminders');

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo($business->email)
            && $mail->mailSubject === 'Annuncio in scadenza tra 7 giorni: '.$posting->title
            && $mail->actionLabel === 'Gestisci annuncio'
            && $mail->actionUrl === route('job-postings.edit', $posting)
        );

        $this->assertNotNull($posting->fresh()->expiry_reminder_sent_at);
    }

    public function test_reminder_is_not_sent_twice(): void
    {
        Mail::fake();

        $business = User::factory()->create(['role' => 'business']);
        $this->posting($business, now()->addDays(7));

        Artisan::call('job-postings:send-expiry-reminders');
        Artisan::call('job-postings:send-expiry-reminders');

        Mail::assertSent(TransactionalActionMail::class, 1);
    }

    public function test_reminder_is_not_sent_before_the_seven_day_window(): void
    {
        Mail::fake();

        $business = User::factory()->create(['role' => 'business']);
        $this->posting($business, now()->addDays(8));

        Artisan::call('job-postings:send-expiry-reminders');

        Mail::assertNothingSent();
    }

    public function test_reminder_is_not_sent_for_non_active_posting(): void
    {
        Mail::fake();

        $business = User::factory()->create(['role' => 'business']);
        $this->posting($business, now()->addDays(7), 'expired');

        Artisan::call('job-postings:send-expiry-reminders');

        Mail::assertNothingSent();
    }

    public function test_disabled_job_posting_notifications_suppress_expiry_reminder(): void
    {
        Mail::fake();

        $business = User::factory()->create(['role' => 'business']);
        $posting = $this->posting($business, now()->addDays(7));

        NotificationPreference::create([
            'user_id' => $business->id,
            'category' => 'job_postings',
            'enabled' => false,
            'frequency' => NotificationPreference::FREQUENCY_IMMEDIATE,
        ]);

        Artisan::call('job-postings:send-expiry-reminders');

        Mail::assertNothingSent();
        $this->assertNull($posting->fresh()->expiry_reminder_sent_at);
        $this->assertDatabaseCount('notification_events', 0);
    }

    public function test_daily_job_posting_notifications_queue_expiry_reminder_for_digest(): void
    {
        Mail::fake();

        $business = User::factory()->create(['role' => 'business']);
        $posting = $this->posting($business, now()->addDays(7));

        NotificationPreference::create([
            'user_id' => $business->id,
            'category' => 'job_postings',
            'enabled' => true,
            'frequency' => NotificationPreference::FREQUENCY_DAILY,
        ]);

        Artisan::call('job-postings:send-expiry-reminders');

        Mail::assertNothingSent();
        $this->assertNotNull($posting->fresh()->expiry_reminder_sent_at);
        $this->assertDatabaseHas('notification_events', [
            'user_id' => $business->id,
            'category' => 'job_postings',
            'frequency' => NotificationPreference::FREQUENCY_DAILY,
            'subject' => 'Annuncio in scadenza tra 7 giorni: '.$posting->title,
        ]);
        $this->assertSame(1, NotificationEvent::query()->count());
    }

    private function posting(User $business, $expiresAt, string $status = 'active'): JobPosting
    {
        return JobPosting::create([
            'user_id' => $business->id,
            'title' => 'OSS struttura sanitaria',
            'description' => 'Ricerca OSS.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => $expiresAt,
            'status' => $status,
        ]);
    }
}
