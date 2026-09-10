<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingContractTypeFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_filter_by_multiple_contract_types(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);
        $business = User::factory()->create(['role' => 'business']);

        $this->posting($business, 'Annuncio indeterminato', 'Tempo indeterminato');
        $this->posting($business, 'Annuncio determinato', 'Tempo determinato');
        $this->posting($business, 'Annuncio stage', 'Stage');

        $this->actingAs($professional)
            ->get(route('job-postings.index', [
                'contract_types' => ['Tempo indeterminato', 'Tempo determinato'],
            ]))
            ->assertOk()
            ->assertSee('Annuncio indeterminato')
            ->assertSee('Annuncio determinato')
            ->assertDontSee('Annuncio stage')
            ->assertSee('2 risultati');
    }

    public function test_professional_search_form_exposes_contract_type_checkboxes(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($professional)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertSee('name="contract_types[]"', false)
            ->assertSee('Tempo indeterminato')
            ->assertSee('Tempo determinato')
            ->assertSee('A chiamata')
            ->assertSee('Stage')
            ->assertSee('Altro');
    }

    public function test_invalid_contract_type_filter_is_rejected(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($professional)
            ->get(route('job-postings.index', [
                'contract_types' => ['Contratto inesistente'],
            ]))
            ->assertSessionHasErrors('contract_types.0');
    }

    private function posting(User $business, string $title, string $contractType): JobPosting
    {
        return JobPosting::create([
            'user_id' => $business->id,
            'title' => $title,
            'description' => 'Annuncio di test per filtro multiplo.',
            'professional_category' => 'OSS',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'workplace_city' => 'Milano',
            'workplace_province' => 'MI',
            'contract_type' => $contractType,
            'expires_at' => now()->addMonth(),
            'status' => 'active',
        ]);
    }
}
