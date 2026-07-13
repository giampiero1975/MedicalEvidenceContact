<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_admin_dashboard_and_users_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();
    }

    public function test_non_admin_cannot_access_admin_area(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($professional)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($professional)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_professional_with_uuid_and_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'role' => 'professional',
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'email' => 'mario.rossi@example.test',
            'phone' => '3331234567',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'nationality' => 'Italiana',
            'address_city' => 'Milano',
            'address_country' => 'Italia',
            'address_province' => 'MI',
            'postal_code' => '20100',
            'street_address' => 'Via Roma 1',
        ]);

        $user = User::where('email', 'mario.rossi@example.test')->firstOrFail();

        $response->assertRedirect(route('admin.users.edit', $user));
        $this->assertNotEmpty($user->uuid);
        $this->assertSame('professional', $user->role);
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertDatabaseHas('professional_profiles', ['user_id' => $user->id]);
    }

    public function test_admin_can_create_business_with_business_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'role' => 'business',
            'first_name' => 'Anna',
            'last_name' => 'Bianchi',
            'email' => 'anna.bianchi@example.test',
            'phone' => '3337654321',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'company_name' => 'Clinica Test SRL',
            'company_type' => 'Clinica privata',
            'location' => 'Monza',
            'employee_count' => 25,
        ]);

        $user = User::where('email', 'anna.bianchi@example.test')->firstOrFail();

        $response->assertRedirect(route('admin.users.edit', $user));
        $this->assertDatabaseHas('business_profiles', [
            'user_id' => $user->id,
            'company_name' => 'Clinica Test SRL',
            'location' => 'Monza',
        ]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
