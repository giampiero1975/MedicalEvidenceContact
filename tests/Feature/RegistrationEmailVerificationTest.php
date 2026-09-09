<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class RegistrationEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_registration_sends_verification_email(): void
    {
        Notification::fake();

        $this->post('/register', [
            'account_type' => 'professional',
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'email' => 'mario.rf006@example.test',
            'phone' => '3331234567',
            'nationality' => 'Italiana',
            'profession' => 'infermiere',
            'address_city' => 'Roma',
            'address_country' => 'Italia',
            'address_province' => 'RM',
            'postal_code' => '00100',
            'street_address' => 'Via Roma 10',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'mario.rf006@example.test')->firstOrFail();

        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_user_is_sent_to_verification_notice_before_dashboard(): void
    {
        $user = User::factory()->unverified()->create([
            'role' => 'professional',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
    }
}
