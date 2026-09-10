<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRf026ExternalCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_for_an_unregistered_company(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.job-postings.store'), [
            'company_source' => 'external',
            'external_company_name' => 'Fondazione Sanitaria Esterna',
            'title' => 'Infermiere per struttura esterna',
            'description' => 'Annuncio pubblicato dall admin per una azienda non registrata.',
            'positions' => 2,
            'workplace_address' => 'Via Roma 10, Milano MI',
            'contract_type' => 'Tempo determinato',
            'salary_min' => 22000,
            'salary_max' => 28000,
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $response->assertSessionHasNoErrors();

        $jobPosting = JobPosting::where('title', 'Infermiere per struttura esterna')->firstOrFail();

        $this->assertSame($admin->id, $jobPosting->user_id);
        $this->assertNull($jobPosting->business_profile_id);
        $this->assertSame('Fondazione Sanitaria Esterna', $jobPosting->external_company_name);
        $this->assertSame('Fondazione Sanitaria Esterna', $jobPosting->companyName());
    }

    public function test_external_company_name_is_required_when_admin_selects_unregistered_company(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.job-postings.store'), [
            'company_source' => 'external',
            'title' => 'Annuncio senza azienda',
            'description' => 'Descrizione test.',
            'positions' => 1,
            'workplace_address' => 'Milano MI',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ])->assertSessionHasErrors('external_company_name');
    }

    public function test_registered_company_flow_remains_available(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $business = User::factory()->create(['role' => 'business']);
        $profile = $business->businessProfile()->create([
            'company_name' => 'Clinica Registrata',
            'company_type' => 'RSA',
            'location' => 'Roma',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.job-postings.store'), [
            'company_source' => 'registered',
            'user_id' => $business->id,
            'title' => 'Annuncio azienda registrata',
            'description' => 'Descrizione test.',
            'positions' => 1,
            'workplace_address' => 'Roma RM',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $response->assertSessionHasNoErrors();

        $jobPosting = JobPosting::where('title', 'Annuncio azienda registrata')->firstOrFail();
        $this->assertSame($business->id, $jobPosting->user_id);
        $this->assertSame($profile->id, $jobPosting->business_profile_id);
        $this->assertNull($jobPosting->external_company_name);
    }
}
