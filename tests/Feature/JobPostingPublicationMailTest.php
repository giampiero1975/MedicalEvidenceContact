<?php

namespace Tests\Feature;

use App\Mail\TransactionalActionMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class JobPostingPublicationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_receives_publication_confirmation_for_new_job_posting(): void
    {
        Mail::fake();

        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'business@example.test',
        ]);

        $this->actingAs($business)
            ->post(route('job-postings.store'), [
                'title' => 'Infermiere reparto degenza',
                'description' => '<p>Cerchiamo un <strong>infermiere</strong> per reparto degenza.</p>',
                'professional_category' => 'Infermiere',
                'positions' => 2,
                'workplace_address' => 'Via Roma 10',
                'workplace_city' => 'Milano',
                'workplace_province' => 'MI',
                'contract_type' => 'Tempo indeterminato',
                'salary_min' => 28000,
                'salary_max' => 34000,
                'expires_at' => now()->addMonth()->toDateString(),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('job-postings.index', absolute: false));

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo('business@example.test')
            && $mail->mailSubject === 'Annuncio pubblicato: Infermiere reparto degenza'
        );
    }
}
