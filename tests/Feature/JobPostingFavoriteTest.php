<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingFavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_save_and_remove_active_job_posting_from_favorites(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);
        $business = User::factory()->create(['role' => 'business']);
        $jobPosting = $this->postingFor($business);

        $this->actingAs($professional)
            ->post(route('job-postings.favorites.store', $jobPosting))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('job_posting_favorites', [
            'user_id' => $professional->id,
            'job_posting_id' => $jobPosting->id,
        ]);

        $this->actingAs($professional)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertSee('Rimuovi preferito');

        $this->actingAs($professional)
            ->delete(route('job-postings.favorites.destroy', $jobPosting))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('job_posting_favorites', [
            'user_id' => $professional->id,
            'job_posting_id' => $jobPosting->id,
        ]);
    }

    public function test_saving_same_job_posting_twice_does_not_duplicate_favorite(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);
        $business = User::factory()->create(['role' => 'business']);
        $jobPosting = $this->postingFor($business);

        $this->actingAs($professional)->post(route('job-postings.favorites.store', $jobPosting));
        $this->actingAs($professional)->post(route('job-postings.favorites.store', $jobPosting));

        $this->assertDatabaseCount('job_posting_favorites', 1);
    }

    public function test_business_cannot_save_job_posting_as_favorite(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $jobPosting = $this->postingFor($business);

        $this->actingAs($business)
            ->post(route('job-postings.favorites.store', $jobPosting))
            ->assertForbidden();

        $this->assertDatabaseCount('job_posting_favorites', 0);
    }

    public function test_professional_cannot_save_expired_job_posting_as_favorite(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);
        $business = User::factory()->create(['role' => 'business']);
        $jobPosting = $this->postingFor($business);
        $jobPosting->update([
            'status' => 'expired',
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($professional)
            ->post(route('job-postings.favorites.store', $jobPosting))
            ->assertForbidden();

        $this->assertDatabaseCount('job_posting_favorites', 0);
    }

    private function postingFor(User $business): JobPosting
    {
        return JobPosting::create([
            'user_id' => $business->id,
            'title' => 'OSS struttura sanitaria',
            'description' => 'Ricerca professionista sanitario.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek(),
            'status' => 'active',
        ]);
    }
}
