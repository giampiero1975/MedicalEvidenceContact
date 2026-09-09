<?php

namespace Tests\Feature;

use App\Mail\TransactionalActionMail;
use App\Models\NotificationEvent;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\TransactionalNotificationDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TransactionalNotificationDispatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_immediate_notification_is_sent_when_enabled(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        app(TransactionalNotificationDispatcher::class)->dispatch(
            $user,
            'applications',
            $this->mail()
        );

        Mail::assertSent(TransactionalActionMail::class, fn ($mail) => $mail->mailSubject === 'Test notifica');
        $this->assertDatabaseCount('notification_events', 0);
    }

    public function test_disabled_notification_is_not_sent_or_queued(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        NotificationPreference::create([
            'user_id' => $user->id,
            'category' => 'applications',
            'enabled' => false,
            'frequency' => NotificationPreference::FREQUENCY_IMMEDIATE,
        ]);

        app(TransactionalNotificationDispatcher::class)->dispatch(
            $user,
            'applications',
            $this->mail()
        );

        Mail::assertNothingSent();
        $this->assertDatabaseCount('notification_events', 0);
    }

    public function test_daily_notification_is_queued_instead_of_sent_immediately(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        NotificationPreference::create([
            'user_id' => $user->id,
            'category' => 'applications',
            'enabled' => true,
            'frequency' => NotificationPreference::FREQUENCY_DAILY,
        ]);

        app(TransactionalNotificationDispatcher::class)->dispatch(
            $user,
            'applications',
            $this->mail()
        );

        Mail::assertNothingSent();
        $this->assertDatabaseHas('notification_events', [
            'user_id' => $user->id,
            'category' => 'applications',
            'frequency' => NotificationPreference::FREQUENCY_DAILY,
            'subject' => 'Test notifica',
        ]);
    }

    public function test_weekly_notification_is_queued_instead_of_sent_immediately(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        NotificationPreference::create([
            'user_id' => $user->id,
            'category' => 'applications',
            'enabled' => true,
            'frequency' => NotificationPreference::FREQUENCY_WEEKLY,
        ]);

        app(TransactionalNotificationDispatcher::class)->dispatch(
            $user,
            'applications',
            $this->mail()
        );

        Mail::assertNothingSent();
        $this->assertDatabaseHas('notification_events', [
            'user_id' => $user->id,
            'category' => 'applications',
            'frequency' => NotificationPreference::FREQUENCY_WEEKLY,
            'subject' => 'Test notifica',
        ]);
    }

    private function mail(): TransactionalActionMail
    {
        return new TransactionalActionMail(
            mailSubject: 'Test notifica',
            heading: 'Titolo test',
            intro: 'Corpo test',
            actionLabel: 'Apri',
            actionUrl: 'https://example.test/sezione',
            details: ['Dettaglio test'],
        );
    }
}
