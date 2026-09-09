<?php

namespace Tests\Feature;

use App\Models\BusinessType;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingCompanyCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_filter_multiple_company_categories(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        foreach (['RSA', 'Farmacia', 'Clinica privata'] as $index => $name) {
            BusinessType::create([
                'name' => $name,
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }

        $rsa = $this->businessWithPosting('RSA', 'OSS RSA Milano');
        $farmacia = $this->businessWithPosting('Farmacia', 'Farmacista territoriale');
        $clinica = $this->businessWithPosting('Clinica privata', 'Infermiere clinica privata');

        $response = $this->actingAs($professional)->get(route('job-postings.index', [
            'company_categories' => ['RSA', 'Farmacia'],
        ]));

        $response->assertOk()
            ->assertSee($rsa->title)
            ->assertSee($farmacia->title)
            ->assertDontSee($clinica->title);
    }

    public function test_professional_search_form_exposes_active_company_categories_as_checkboxes(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        BusinessType::create([
            'name' => 'RSA',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        BusinessType::create([
            'name' => 'Farmacia',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        BusinessType::create([
            'name' => 'Tipo disattivato',
            'is_active' => false,
            'sort_order' => 3,
        ]);

        $this->actingAs($professional)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertSee('name="company_categories[]"', false)
            ->assertSee('value="RSA"', false)
            ->assertSee('value="Farmacia"', false)
            ->assertDontSee('value="Tipo disattivato"', false);
    }

    public function test_professional_company_category_filter_rejects_unknown_values(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        BusinessType::create([
            'name' => 'RSA',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($professional)
            ->get(route('job-postings.index', [
                'company_categories' => ['Categoria inesistente'],
            ]))
            ->assertSessionHasErrors('company_categories.0');
    }

    private function businessWithPosting(string $companyType, string $title): JobPosting
    {
        $business = User::factory()->create(['role' => 'business']);

        $profile = $business->businessProfile()->create([
            'user_id' => $business->id,
            'company_name' => 'Azienda '.$companyType,
            'company_type' => $companyType,
            'location' => 'Milano',
            'employee_count' => 50,
        ]);

        return JobPosting::create([
            'user_id' => $business->id,
            'business_profile_id' => $profile->id,
            'title' => $title,
            'description' => 'Annuncio di test per il filtro categoria azienda.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addMonth(),
            'status' => 'active',
        ]);
    }
}
