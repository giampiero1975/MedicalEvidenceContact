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

        $professional = User::factory()->create(['role' => 'professional', 'email' => 'professional@example.test']);
        $business = User::factory()->create(['role' => 'business', 'email' => 'business@example.test']);
        $jobPosting = $this->postingFor($business, 'OSS reparto assistenziale');

        $this->actingAs($professional)
            ->post(route('job-applications.store', $jobPosting), ['application_confirmation' => '1'])
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

    public function test_interview_slot_selection_and_final_confirmation_generate_transactional_notifications(): void
    {
        Mail::fake();

        $professional = User::factory()->create(['role' => 'professional', 'email' => 'professional@example.test']);
        $business = User::factory()->create(['role' => 'business', 'email' => 'business@example.test']);
        $jobPosting = $this->postingFor($business, 'Infermiere ambulatoriale');
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
            && str_starts_with($mail->mailSubject, 'Nuovo slot colloquio:')
        );

        $interview = Interview::query()->firstOrFail();

        $this->actingAs($professional)
            ->patch(route('professional.interviews.respond', $interview), [
                'contact_sharing_consent' => true,
            ])
            ->assertSessionHasNoErrors();

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo('business@example.test')
            && str_starts_with($mail->mailSubject, 'Richiesta colloquio:')
        );

        $this->actingAs($business)
            ->patch(route('business.interviews.confirm', $interview->fresh()), [
                'decision' => 'accepted',
            ])
            ->assertSessionHasNoErrors();

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo('professional@example.test')
            && str_starts_with($mail->mailSubject, 'Colloquio confermato:')
        );
        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo('business@example.test')
            && str_starts_with($mail->mailSubject, 'Colloquio confermato:')
        );
    }

    private function postingFor(User $business, string $title): JobPosting
    {
        return JobPosting::create([
            'user_id' => $business->id,
            'title' => $title,
            'description' => 'Ricerca professionista.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek(),
            'status' => 'active',
        ]);
    }
}
