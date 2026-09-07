<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminResponsiveNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_exposes_mobile_navigation_controls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Apri navigazione amministrativa')
            ->assertSee('Chiudi navigazione amministrativa')
            ->assertSee('Tipologie aziendali')
            ->assertSee('Area amministrazione');
    }

    public function test_admin_profile_uses_administration_area_label(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Area amministrazione')
            ->assertDontSee('Area business');
    }
}
