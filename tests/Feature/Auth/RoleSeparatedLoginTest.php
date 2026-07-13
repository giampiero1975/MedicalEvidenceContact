<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSeparatedLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_only_from_admin_login_flow(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.test',
            'password' => 'Password123!',
        ]);

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'Password123!',
        ])->assertSessionHasErrors('email');

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'Password123!',
            'staff_login' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_professional_is_rejected_by_admin_login_flow(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
            'email' => 'professional@example.test',
            'password' => 'Password123!',
        ]);

        $this->post(route('login'), [
            'email' => $professional->email,
            'password' => 'Password123!',
            'staff_login' => '1',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_professional_can_login_from_standard_login_flow(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
            'email' => 'professional@example.test',
            'password' => 'Password123!',
        ]);

        $this->post(route('login'), [
            'email' => $professional->email,
            'password' => 'Password123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($professional);
    }
}
