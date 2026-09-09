<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingExpiryValidationTest extends TestCase
{
    use RefreshDatabase;

    private function business(): User
    {
        $business = User::factory()->create(['role' => 'business']);

        $business->businessProfile()->create([
            'user_id' => $business->id,
            'company_name' => 'RSA Test',
            'company_type' => 'RSA',
            'location' => 'Milano',
            'employee_count' => 50,
        ]);

        return $business;
    }

    private function payload(string $expiresAt): array
    {
        return [
            'title' => 'OSS reparto assistenziale',
            'description' => 'Ricerca professionista per attività assistenziali.',
            'positions' => 1,
            'workplace_address' => 'Via Roma 10, Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => $expiresAt,
        ];
    }

    public function test_business_cannot_publish_job_posting_expiring_before_seven_days(): void
    {
        $business = $this->business();

        $response = $this->actingAs($business)->post(
            route('job-postings.store'),
            $this->payload(today()->addDays(6)->toDateString())
        );

        $response->assertSessionHasErrors('expires_at');
        $this->assertDatabaseCount('job_postings', 0);
    }

    public function test_business_can_publish_job_posting_expiring_exactly_in_seven_days(): void
    {
        $business = $this->business();

        $response = $this->actingAs($business)->post(
            route('job-postings.store'),
            $this->payload(today()->addDays(7)->toDateString())
        );

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('job_postings', [
            'user_id' => $business->id,
            'expires_at' => today()->addDays(7)->toDateString(),
            'status' => 'active',
        ]);
    }

    public function test_job_posting_form_exposes_seven_day_minimum_expiry_date(): void
    {
        $business = $this->business();

        $this->actingAs($business)
            ->get(route('job-postings.create'))
            ->assertOk()
            ->assertSee('min="'.today()->addDays(7)->toDateString().'"', false)
            ->assertSee('La scadenza deve essere almeno 7 giorni da oggi.');
    }
}
