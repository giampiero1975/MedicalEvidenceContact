<?php

namespace Tests\Feature;

use App\Mail\TransactionalActionMail;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProfessionalProfileViewedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_is_notified_when_business_first_views_profile(): void
    {
        Mail::fake();

        [$business, $professional, $application] = $this->scenario();

        $this->actingAs($business)
            ->get(route('business.applications.show', $application))
            ->assertOk();

        $this->assertDatabaseHas('job_application_events', [
            'job_application_id' => $application->id,
            'actor_user_id' => $business->id,
            'type' => 'professional_profile_viewed',
        ]);

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo($professional->email)
            && $mail->mailSubject === 'La struttura ha visualizzato il tuo profilo'
            && $mail->actionLabel === 'Visualizza candidature'
            && $mail->actionUrl === route('professional.applications.index')
        );
    }

    public function test_reopening_same_application_does_not_send_duplicate_notification(): void
    {
        Mail::fake();

        [$business, $professional, $application] = $this->scenario();

        $this->actingAs($business)->get(route('business.applications.show', $application))->assertOk();
        $this->actingAs($business)->get(route('business.applications.show', $application))->assertOk();

        $this->assertSame(
            1,
            $application->events()->where('type', 'professional_profile_viewed')->count()
        );

        Mail::assertSent(TransactionalActionMail::class, 1);
        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo($professional->email)
        );
    }

    public function test_non_owner_business_cannot_trigger_profile_view_notification(): void
    {
        Mail::fake();

        [, , $application] = $this->scenario();
        $otherBusiness = User::factory()->create(['role' => 'business']);

        $this->actingAs($otherBusiness)
            ->get(route('business.applications.show', $application))
            ->assertForbidden();

        $this->assertDatabaseMissing('job_application_events', [
            'job_application_id' => $application->id,
            'type' => 'professional_profile_viewed',
        ]);

        Mail::assertNothingSent();
    }

    private function scenario(): array
    {
        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'business.view@example.test',
        ]);
        $professional = User::factory()->create([
            'role' => 'professional',
            'email' => 'professional.view@example.test',
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
            'status' => JobApplication::STATUS_RECEIVED,
        ]);

        return [$business, $professional, $application];
    }
}
