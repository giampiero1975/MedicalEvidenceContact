<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalApplicationsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_view_only_own_applications(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'RSA Aurora',
            'company_type' => 'RSA',
            'location' => 'Milano',
        ]);

        $professional = User::factory()->create(['role' => 'professional']);
        $otherProfessional = User::factory()->create(['role' => 'professional']);

        $ownPosting = $this->createPosting($business, $profile, 'OSS turno diurno');
        $otherPosting = $this->createPosting($business, $profile, 'Infermiere notturno');

        JobApplication::create([
            'job_posting_id' => $ownPosting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_REVIEW,
        ]);

        JobApplication::create([
            'job_posting_id' => $otherPosting->id,
            'user_id' => $otherProfessional->id,
            'status' => JobApplication::STATUS_RECEIVED,
        ]);

        $this->actingAs($professional)
            ->get(route('professional.applications.index'))
            ->assertOk()
            ->assertSee('Le mie candidature')
            ->assertSee('OSS turno diurno')
            ->assertSee('In valutazione')
            ->assertDontSee('Infermiere notturno');
    }

    public function test_professional_can_filter_applications_by_status(): void
    {
        $business = User::factory()->create(['role' => 'business']);
        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'Clinica Salute',
            'company_type' => 'Clinica privata',
            'location' => 'Torino',
        ]);
        $professional = User::factory()->create(['role' => 'professional']);

        $reviewPosting = $this->createPosting($business, $profile, 'OSS reparto medicina');
        $hiredPosting = $this->createPosting($business, $profile, 'OSS lungodegenza');

        JobApplication::create([
            'job_posting_id' => $reviewPosting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_REVIEW,
        ]);
        JobApplication::create([
            'job_posting_id' => $hiredPosting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_HIRED,
        ]);

        $this->actingAs($professional)
            ->get(route('professional.applications.index', ['status' => JobApplication::STATUS_HIRED]))
            ->assertOk()
            ->assertSee('OSS lungodegenza')
            ->assertSee('Assunto')
            ->assertDontSee('OSS reparto medicina');
    }

    public function test_business_cannot_open_professional_applications_page(): void
    {
        $business = User::factory()->create(['role' => 'business']);

        $this->actingAs($business)
            ->get(route('professional.applications.index'))
            ->assertForbidden();
    }

    public function test_invalid_status_filter_is_rejected(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($professional)
            ->get(route('professional.applications.index', ['status' => 'invalid_status']))
            ->assertSessionHasErrors('status');
    }

    private function createPosting(User $business, BusinessProfile $profile, string $title): JobPosting
    {
        return JobPosting::create([
            'user_id' => $business->id,
            'business_profile_id' => $profile->id,
            'title' => $title,
            'description' => 'Posizione aperta per professionista sanitario.',
            'positions' => 1,
            'workplace_address' => $profile->location,
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);
    }
}
