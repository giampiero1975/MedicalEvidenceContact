<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRf025SuspensionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_suspend_and_reactivate_professional_and_business_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $professional = User::factory()->create(['role' => 'professional']);
        $business = User::factory()->create(['role' => 'business']);

        $this->actingAs($admin)
            ->patch(route('admin.users.suspension', $professional))
            ->assertRedirect(route('admin.users.index', absolute: false));

        $this->actingAs($admin)
            ->patch(route('admin.users.suspension', $business))
            ->assertRedirect(route('admin.users.index', absolute: false));

        $this->assertNotNull($professional->fresh()->suspended_at);
        $this->assertNotNull($business->fresh()->suspended_at);

        $this->actingAs($professional->fresh())
            ->get(route('dashboard'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('admin.users.suspension', $professional))
            ->assertRedirect(route('admin.users.index', absolute: false));

        $this->assertNull($professional->fresh()->suspended_at);
    }

    public function test_admin_accounts_cannot_be_suspended_with_rf025_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.users.suspension', $otherAdmin))
            ->assertStatus(422);

        $this->assertNull($otherAdmin->fresh()->suspended_at);
    }

    public function test_admin_can_suspend_and_reactivate_job_posting_publication(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $business = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create(['role' => 'professional']);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'Annuncio sospendibile',
            'description' => 'Annuncio usato per verificare RF-025.',
            'positions' => 1,
            'workplace_address' => 'Via Roma 1, Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addDays(10)->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.job-postings.suspension', $jobPosting))
            ->assertRedirect(route('admin.job-postings.index', absolute: false));

        $this->assertNotNull($jobPosting->fresh()->suspended_at);

        $this->actingAs($professional)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertDontSee('Annuncio sospendibile');

        $this->actingAs($professional)
            ->get(route('job-postings.show', $jobPosting))
            ->assertForbidden();

        $this->actingAs($business)
            ->get(route('job-postings.show', $jobPosting))
            ->assertOk();

        $this->actingAs($admin)
            ->patch(route('admin.job-postings.suspension', $jobPosting))
            ->assertRedirect(route('admin.job-postings.index', absolute: false));

        $this->assertNull($jobPosting->fresh()->suspended_at);

        $this->actingAs($professional)
            ->get(route('job-postings.show', $jobPosting))
            ->assertOk();
    }
}
