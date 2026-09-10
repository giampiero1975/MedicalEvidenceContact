<?php

namespace Tests\Feature;

use App\Mail\TransactionalActionMail;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class JobPostingRf027To034Test extends TestCase
{
    use RefreshDatabase;

    private function business(): User
    {
        $business = User::factory()->create([
            'role' => 'business',
            'email' => 'poc@example.test',
        ]);

        $business->businessProfile()->create([
            'user_id' => $business->id,
            'company_name' => 'Clinica RF027',
            'company_type' => 'Clinica privata',
            'location' => 'Milano',
            'employee_count' => 50,
        ]);

        return $business;
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'title' => 'Infermiere reparto medicina',
            'description' => '<p>Cerchiamo un <strong>infermiere</strong> per reparto medicina.</p>',
            'professional_category' => 'Infermiere',
            'positions' => 2,
            'workplace_address' => 'Via Roma 10',
            'workplace_city' => 'Milano',
            'workplace_province' => 'MI',
            'contract_type' => 'Tempo indeterminato',
            'salary_min' => '1.800,00',
            'salary_max' => '2.200,00',
            'expires_at' => today()->addDays(14)->toDateString(),
            'required_skills' => 'Iscrizione OPI, BLSD, esperienza reparto',
            'benefits' => 'Buoni pasto e welfare aziendale',
            'preferred_requirements' => 'Esperienza di almeno un anno',
            'work_schedule' => 'Full time 40h',
        ], $overrides);
    }

    public function test_business_can_publish_complete_rf027_job_posting_immediately_and_creator_receives_confirmation(): void
    {
        Mail::fake();
        $business = $this->business();

        $this->actingAs($business)
            ->post(route('job-postings.store'), $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('job-postings.index', absolute: false));

        $posting = JobPosting::query()->where('user_id', $business->id)->firstOrFail();

        $this->assertSame('active', $posting->status);
        $this->assertSame('Infermiere', $posting->professional_category);
        $this->assertSame('Milano', $posting->workplace_city);
        $this->assertSame('MI', $posting->workplace_province);
        $this->assertSame('Iscrizione OPI, BLSD, esperienza reparto', $posting->required_skills);
        $this->assertSame('Buoni pasto e welfare aziendale', $posting->benefits);
        $this->assertSame('Esperienza di almeno un anno', $posting->preferred_requirements);
        $this->assertSame('Full time 40h', $posting->work_schedule);
        $this->assertStringContainsString('<strong>infermiere</strong>', $posting->description);

        Mail::assertSent(TransactionalActionMail::class, fn (TransactionalActionMail $mail) =>
            $mail->hasTo('poc@example.test')
            && $mail->mailSubject === 'Annuncio pubblicato: Infermiere reparto medicina'
        );

        $professional = User::factory()->create(['role' => 'professional']);
        $this->actingAs($professional)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertSee('Infermiere reparto medicina');
    }

    public function test_rf027_rejects_invalid_lengths_category_contract_location_skills_and_expiry(): void
    {
        Mail::fake();
        $business = $this->business();

        $skills = implode(', ', array_map(fn ($i) => 'Skill '.$i, range(1, 11)));

        $response = $this->actingAs($business)->post(route('job-postings.store'), $this->payload([
            'title' => str_repeat('T', 101),
            'description' => '<p>'.str_repeat('D', 3001).'</p>',
            'professional_category' => 'Radiologo',
            'workplace_city' => null,
            'workplace_province' => null,
            'contract_type' => 'Collaborazione',
            'required_skills' => $skills,
            'expires_at' => today()->addDays(6)->toDateString(),
        ]));

        $response->assertSessionHasErrors([
            'title',
            'description',
            'professional_category',
            'workplace_city',
            'workplace_province',
            'contract_type',
            'required_skills',
            'expires_at',
        ]);
        $this->assertDatabaseCount('job_postings', 0);
    }

    public function test_business_can_modify_extend_and_close_posting_and_expired_posting_is_hidden_only_from_professionals(): void
    {
        Mail::fake();
        $business = $this->business();

        $this->actingAs($business)
            ->post(route('job-postings.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $posting = JobPosting::query()->where('user_id', $business->id)->firstOrFail();
        $newExpiry = today()->addDays(30)->toDateString();

        $this->actingAs($business)
            ->put(route('job-postings.update', $posting), $this->payload([
                'title' => 'Infermiere reparto medicina aggiornato',
                'expires_at' => $newExpiry,
                'status' => 'expired',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('job-postings.show', $posting, absolute: false));

        $posting->refresh();
        $this->assertSame('expired', $posting->status);
        $this->assertSame($newExpiry, $posting->expires_at->toDateString());

        $this->actingAs($business)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertSee('Infermiere reparto medicina aggiornato');

        $professional = User::factory()->create(['role' => 'professional']);
        $this->actingAs($professional)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertDontSee('Infermiere reparto medicina aggiornato');
    }

    public function test_business_cannot_delete_posting_with_active_application(): void
    {
        $business = $this->business();
        $professional = User::factory()->create(['role' => 'professional']);

        $posting = JobPosting::create([
            'user_id' => $business->id,
            'business_profile_id' => $business->businessProfile->id,
            'title' => 'Infermiere reparto medicina',
            'description' => 'Annuncio con candidatura attiva.',
            'professional_category' => 'Infermiere',
            'positions' => 2,
            'workplace_address' => 'Via Roma 10',
            'workplace_city' => 'Milano',
            'workplace_province' => 'MI',
            'contract_type' => 'Tempo indeterminato',
            'salary_min' => 1800,
            'salary_max' => 2200,
            'expires_at' => today()->addDays(14)->toDateString(),
            'status' => 'active',
        ]);

        JobApplication::create([
            'job_posting_id' => $posting->id,
            'user_id' => $professional->id,
            'status' => 'inviata',
        ]);

        $this->actingAs($business)
            ->delete(route('job-postings.destroy', $posting))
            ->assertRedirect(route('job-postings.show', $posting, absolute: false))
            ->assertSessionHas('warning');

        $this->assertDatabaseHas('job_postings', ['id' => $posting->id]);
    }
}
