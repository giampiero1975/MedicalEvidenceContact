<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessDashboardApplicationSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_dashboard_shows_application_totals_for_each_owned_job_posting(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $otherBusiness = User::factory()->create(['role' => 'business']);
        $professionalOne = User::factory()->create(['role' => 'professional']);
        $professionalTwo = User::factory()->create(['role' => 'professional']);

        $firstPosting = $this->postingFor($business, 'OSS reparto residenziale');
        $secondPosting = $this->postingFor($business, 'Infermiere ambulatoriale');
        $otherPosting = $this->postingFor($otherBusiness, 'Annuncio altra struttura');

        JobApplication::create([
            'job_posting_id' => $firstPosting->id,
            'user_id' => $professionalOne->id,
            'status' => JobApplication::STATUS_RECEIVED,
        ]);
        JobApplication::create([
            'job_posting_id' => $firstPosting->id,
            'user_id' => $professionalTwo->id,
            'status' => JobApplication::STATUS_REVIEW,
        ]);
        JobApplication::create([
            'job_posting_id' => $otherPosting->id,
            'user_id' => $professionalOne->id,
            'status' => JobApplication::STATUS_RECEIVED,
        ]);

        $response = $this->actingAs($business)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Candidature per annuncio')
            ->assertSee('OSS reparto residenziale')
            ->assertSee('Infermiere ambulatoriale')
            ->assertDontSee('Annuncio altra struttura')
            ->assertSee('Ricevuta')
            ->assertSee('In valutazione');

        $response->assertViewHas('postingApplicationCounts', function ($postings) use ($firstPosting, $secondPosting): bool {
            $first = $postings->firstWhere('id', $firstPosting->id);
            $second = $postings->firstWhere('id', $secondPosting->id);

            return $postings->count() === 2
                && $first !== null
                && (int) $first->applications_count === 2
                && $second !== null
                && (int) $second->applications_count === 0;
        });
    }

    private function postingFor(User $business, string $title): JobPosting
    {
        return JobPosting::create([
            'user_id' => $business->id,
            'title' => $title,
            'description' => 'Ricerca professionista sanitario.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek(),
            'status' => 'active',
        ]);
    }
}
