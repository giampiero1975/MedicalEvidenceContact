<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingPublicationPeriodFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_filter_job_postings_from_last_week(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);
        $business = User::factory()->create(['role' => 'business']);

        $recent = $this->postingFor($business, 'Annuncio recente', now()->subDays(3));
        $old = $this->postingFor($business, 'Annuncio otto giorni fa', now()->subDays(8));

        $this->actingAs($professional)
            ->get(route('job-postings.index', ['publication_period' => 'week']))
            ->assertOk()
            ->assertSee($recent->title)
            ->assertDontSee($old->title)
            ->assertSee('Ultima settimana');
    }

    public function test_professional_can_filter_job_postings_from_last_month(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);
        $business = User::factory()->create(['role' => 'business']);

        $withinMonth = $this->postingFor($business, 'Annuncio venti giorni fa', now()->subDays(20));
        $olderThanMonth = $this->postingFor($business, 'Annuncio quaranta giorni fa', now()->subDays(40));

        $this->actingAs($professional)
            ->get(route('job-postings.index', ['publication_period' => 'month']))
            ->assertOk()
            ->assertSee($withinMonth->title)
            ->assertDontSee($olderThanMonth->title)
            ->assertSee('Ultimo mese');
    }

    public function test_professional_search_form_exposes_pdr_publication_date_presets(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($professional)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertSee('Data pubblicazione')
            ->assertSee('Più recenti')
            ->assertSee('Ultima settimana')
            ->assertSee('Ultimo mese');
    }

    private function postingFor(User $business, string $title, $createdAt): JobPosting
    {
        $posting = JobPosting::create([
            'user_id' => $business->id,
            'title' => $title,
            'description' => 'Descrizione annuncio per filtro data pubblicazione.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addMonth(),
            'status' => 'active',
        ]);

        $posting->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->saveQuietly();

        return $posting;
    }
}
