<?php

namespace Tests\Feature\Admin;

use App\Models\BusinessProfile;
use App\Models\BusinessType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBusinessTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_business_types_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.business-types.index'))
            ->assertOk()
            ->assertSee('Tipologie aziendali')
            ->assertSee('Clinica privata');
    }

    public function test_non_admin_cannot_manage_business_types(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($professional)
            ->get(route('admin.business-types.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_business_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->post(route('admin.business-types.store'), [
                'name' => '  Poliambulatorio  ',
                'sort_order' => 25,
                'is_active' => 1,
            ]);

        $businessType = BusinessType::where('name', 'Poliambulatorio')->firstOrFail();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.business-types.edit', $businessType));

        $this->assertDatabaseHas('business_types', [
            'name' => 'Poliambulatorio',
            'sort_order' => 25,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_business_type_and_existing_profiles_follow_rename(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $business = User::factory()->create(['role' => 'business']);
        $businessType = BusinessType::create([
            'name' => 'Centro medico',
            'sort_order' => 30,
            'is_active' => true,
        ]);
        $profile = BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'Centro Salute',
            'company_type' => 'Centro medico',
            'location' => 'Milano',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.business-types.update', $businessType), [
                'name' => 'Centro medico privato',
                'sort_order' => 35,
                'is_active' => 0,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.business-types.edit', $businessType));

        $this->assertDatabaseHas('business_types', [
            'id' => $businessType->id,
            'name' => 'Centro medico privato',
            'sort_order' => 35,
            'is_active' => false,
        ]);

        $this->assertSame('Centro medico privato', $profile->refresh()->company_type);
    }

    public function test_admin_can_toggle_business_type_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $businessType = BusinessType::where('name', 'RSA')->firstOrFail();

        $this->assertTrue($businessType->is_active);

        $this->actingAs($admin)
            ->patch(route('admin.business-types.toggle', $businessType))
            ->assertRedirect(route('admin.business-types.index'));

        $this->assertFalse($businessType->refresh()->is_active);
    }

    public function test_admin_can_delete_unused_business_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $businessType = BusinessType::create([
            'name' => 'Laboratorio privato',
            'sort_order' => 50,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.business-types.destroy', $businessType))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.business-types.index'));

        $this->assertDatabaseMissing('business_types', [
            'id' => $businessType->id,
        ]);
    }

    public function test_admin_cannot_delete_business_type_used_by_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $business = User::factory()->create(['role' => 'business']);
        $businessType = BusinessType::where('name', 'RSA')->firstOrFail();

        BusinessProfile::create([
            'user_id' => $business->id,
            'company_name' => 'RSA Aurora',
            'company_type' => $businessType->name,
            'location' => 'Milano',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.business-types.destroy', $businessType))
            ->assertSessionHasErrors('business_type')
            ->assertRedirect(route('admin.business-types.index'));

        $this->assertDatabaseHas('business_types', [
            'id' => $businessType->id,
            'name' => 'RSA',
        ]);
    }
}
