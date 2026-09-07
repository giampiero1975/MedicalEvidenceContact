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

class TransactionalMailWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_testing_environment_uses_non_delivery_array_mailer(): void
    {
        $this->assertSame('testing', app()->environment());
        $this->assertSame('array', config('mail.default'));
    }

    public function test_new_application_generates_confirmation_and_business_notification_without_real_delivery(): void
    {
        Mail::fake();

        $professional = User::factory()->create([
            'role' => 'professional',
            'email' => 'professional@example.test',
        ]);
        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'business@example.test',
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'OSS reparto assistenziale',
            'description' => 'Ricerca OSS.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek(),
            'status' => 'active',
        ]);

        $this->actingAs($professional)
            ->post(route('job-applications.store', $jobPosting))
            ->assertRedirect(route('dashboard', absolute: false));

        Mail::assertSent(TransactionalActionMail::class, 2);
        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo('professional@example.test')
            && $mail->mailSubject === 'Candidatura inviata: OSS reparto assistenziale'
        );
        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo('business@example.test')
            && $mail->mailSubject === 'Nuova candidatura: OSS reparto assistenziale'
        );
    }

    public function test_interview_invitation_and_response_generate_transactional_notifications(): void
    {
        Mail::fake();

        $professional = User::factory()->create([
            'role' => 'professional',
            'email' => 'professional@example.test',
        ]);
        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'business@example.test',
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'Infermiere ambulatoriale',
            'description' => 'Ricerca infermiere.',
            'positions' => 1,
            'workplace_address' => 'Torino',
            'contract_type' => 'Tempo indeterminato',
            'expires_at' => now()->addWeek(),
            'status' => 'active',
        ]);

        $application = JobApplication::create([
            'job_posting_id' => $jobPosting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_RECEIVED,
        ]);

        $this->actingAs($business)
            ->post(route('business.applications.interviews.store', $application), [
                'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'duration_minutes' => 30,
                'mode' => 'phone',
            ])
            ->assertSessionHasNoErrors();

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo('professional@example.test')
            && str_starts_with($mail->mailSubject, 'Invito a colloquio:')
        );

        $interview = Interview::query()->firstOrFail();

        $this->actingAs($professional)
            ->patch(route('professional.interviews.respond', $interview), [
                'response' => 'accepted',
                'contact_sharing_consent' => true,
            ])
            ->assertSessionHasNoErrors();

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo('business@example.test')
            && str_starts_with($mail->mailSubject, 'Colloquio confermato:')
        );
    }
}
