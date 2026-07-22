<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_admin_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_admin_login_screen_does_not_expose_admin_registration(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertDontSee('/admin/register', false);
        $response->assertDontSee('Registra admin');
    }

    public function test_staff_login_alias_redirects_to_admin_login(): void
    {
        $response = $this->get('/staff/login');

        $response->assertRedirect('/admin/login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_admin_users_must_use_admin_login(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertGuest();

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
            'staff_login' => '1',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_non_admin_users_cannot_use_admin_login(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
        ]);

        $this->post('/login', [
            'email' => $professional->email,
            'password' => 'password',
            'staff_login' => '1',
        ]);

        $this->assertGuest();
    }
}
