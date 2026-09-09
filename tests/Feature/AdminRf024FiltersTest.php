<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\BusinessType;
use App\Models\JobPosting;
use App\Models\ProfessionalProfession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRf024FiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_professional_profiles_by_category_location_and_registration_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $matching = User::factory()->create([
            'role' => 'professional',
            'name' => 'Mario Matching',
            'address_city' => 'Milano',
            'residence' => 'Milano',
            'created_at' => now()->subDays(3),
        ]);
        ProfessionalProfession::create([
            'user_id' => $matching->id,
            'profession' => 'oss',
        ]);

        $other = User::factory()->create([
            'role' => 'professional',
            'name' => 'Laura Other',
            'address_city' => 'Roma',
            'residence' => 'Roma',
            'created_at' => now()->subDays(20),
        ]);
        ProfessionalProfession::create([
            'user_id' => $other->id,
            'profession' => 'infermiere',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', [
                'role' => 'professional',
                'professional_category' => 'oss',
                'location' => 'Milano',
                'registered_from' => now()->subWeek()->toDateString(),
                'registered_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Mario Matching')
            ->assertDontSee('Laura Other');
    }

    public function test_admin_can_filter_business_profiles_by_type_and_location(): void
    {
        BusinessType::query()->updateOrCreate(
            ['name' => 'RSA'],
            ['is_active' => true, 'sort_order' => 10]
        );
        BusinessType::query()->updateOrCreate(
            ['name' => 'Farmacia'],
            ['is_active' => true, 'sort_order' => 20]
        );

        $admin = User::factory()->create(['role' => 'admin']);
        $matching = User::factory()->create(['role' => 'business', 'name' => 'Business Milano']);
        BusinessProfile::create([
            'user_id' => $matching->id,
            'company_name' => 'RSA Milano',
            'company_type' => 'RSA',
            'location' => 'Milano',
        ]);

        $other = User::factory()->create(['role' => 'business', 'name' => 'Business Roma']);
        BusinessProfile::create([
            'user_id' => $other->id,
            'company_name' => 'Farmacia Roma',
            'company_type' => 'Farmacia',
            'location' => 'Roma',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', [
                'role' => 'business',
                'business_type' => 'RSA',
                'location' => 'Milano',
            ]))
            ->assertOk()
            ->assertSee('Business Milano')
            ->assertDontSee('Business Roma');
    }

    public function test_admin_can_filter_job_postings_by_status_publication_and_expiry_dates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $business = User::factory()->create(['role' => 'business']);

        $matching = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'Annuncio Matching',
            'description' => 'Annuncio da mostrare.',
            'positions' => 1,
            'workplace_address' => 'Via Roma 1',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addDays(10)->toDateString(),
            'status' => 'active',
        ]);
        $matching->forceFill(['created_at' => now()->subDays(2)])->save();

        $other = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'Annuncio Scaduto',
            'description' => 'Annuncio da escludere.',
            'positions' => 1,
            'workplace_address' => 'Via Milano 2',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->subDay()->toDateString(),
            'status' => 'expired',
        ]);
        $other->forceFill(['created_at' => now()->subMonth()])->save();

        $this->actingAs($admin)
            ->get(route('admin.job-postings.index', [
                'status' => 'active',
                'published_from' => now()->subWeek()->toDateString(),
                'published_to' => now()->toDateString(),
                'expires_from' => now()->addWeek()->toDateString(),
                'expires_to' => now()->addWeeks(2)->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Annuncio Matching')
            ->assertDontSee('Annuncio Scaduto');
    }
}
