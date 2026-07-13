<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUiPlaygroundTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_ui_playground(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.ui.index'))
            ->assertOk()
            ->assertSee('UI Playground')
            ->assertSee('Colori fondamentali');
    }

    public function test_professional_cannot_view_ui_playground(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($professional)
            ->get(route('admin.ui.index'))
            ->assertForbidden();
    }
}
