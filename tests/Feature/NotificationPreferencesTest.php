<?php

namespace Tests\Feature;

use App\Mail\TransactionalActionMail;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_open_notification_preferences_page(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($professional)
            ->get(route('notification-preferences.edit'))
            ->assertOk()
            ->assertSeeText('Preferenze notifiche')
            ->assertSeeText('Visualizzazioni profilo')
            ->assertSeeText('Candidature')
            ->assertSeeText('Colloqui')
            ->assertSeeText('Digest giornaliero')
            ->assertSeeText('Digest settimanale');
    }

    public function test_business_can_store_enabled_state_and_frequency_per_category(): void
    {
        $business = User::factory()->create(['role' => 'business']);

        $this->actingAs($business)
            ->put(route('notification-preferences.update'), [
                'preferences' => [
                    'account' => ['enabled' => '1', 'frequency' => 'immediate'],
                    'interviews' => ['frequency' => 'daily'],
                    'applications' => ['enabled' => '1', 'frequency' => 'weekly'],
                    'job_postings' => ['enabled' => '1', 'frequency' => 'daily'],
                ],
            ])
            ->assertRedirect(route('notification-preferences.edit'))
            ->assertSessionHas('status', 'Preferenze notifiche aggiornate.');

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $business->id,
            'category' => 'interviews',
            'enabled' => false,
            'frequency' => NotificationPreference::FREQUENCY_DAILY,
        ]);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $business->id,
            'category' => 'applications',
            'enabled' => true,
            'frequency' => NotificationPreference::FREQUENCY_WEEKLY,
        ]);
    }

    public function test_invalid_notification_frequency_is_rejected(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($professional)
            ->from(route('notification-preferences.edit'))
            ->put(route('notification-preferences.update'), [
                'preferences' => [
                    'account' => ['enabled' => '1', 'frequency' => 'hourly'],
                ],
            ])
            ->assertRedirect(route('notification-preferences.edit'))
            ->assertSessionHasErrors('preferences.account.frequency');
    }

    public function test_transactional_email_contains_platform_brand_cta_and_notification_preferences_link(): void
    {
        $mail = new TransactionalActionMail(
            mailSubject: 'Oggetto chiaro',
            heading: 'Titolo email',
            intro: 'Corpo della comunicazione.',
            actionLabel: 'Apri sezione',
            actionUrl: route('dashboard'),
        );

        $html = $mail->render();

        $this->assertStringContainsString('Medical Evidence Contact', $html);
        $this->assertStringContainsString('MEC', $html);
        $this->assertStringContainsString('Apri sezione', $html);
        $this->assertStringContainsString(route('notification-preferences.edit'), $html);
        $this->assertStringContainsString('modificare o disattivare le notifiche', $html);
    }
}
