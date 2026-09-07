<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_job_search_is_paginated_at_twenty_results_per_page(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);
        $business = User::factory()->create(['role' => 'business']);

        foreach (range(1, 21) as $index) {
            JobPosting::create([
                'user_id' => $business->id,
                'title' => 'Annuncio '.$index,
                'description' => 'Posizione disponibile.',
                'positions' => 1,
                'workplace_address' => 'Milano',
                'contract_type' => 'Tempo determinato',
                'expires_at' => now()->addWeek(),
                'status' => 'active',
            ]);
        }

        $response = $this->actingAs($professional)
            ->get(route('job-postings.index'))
            ->assertOk();

        $jobPostings = $response->viewData('jobPostings');

        $this->assertSame(20, $jobPostings->perPage());
        $this->assertCount(20, $jobPostings->items());
        $this->assertSame(21, $jobPostings->total());
        $this->assertSame(2, $jobPostings->lastPage());
    }
}
