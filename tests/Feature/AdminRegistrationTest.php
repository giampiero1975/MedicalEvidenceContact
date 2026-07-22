<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_registration_screen_is_not_publicly_available(): void
    {
        $this->get('/admin/register')->assertNotFound();
    }

    public function test_admin_users_cannot_register_from_public_admin_endpoint(): void
    {
        $this->post('/admin/register', [
            'first_name' => 'Ada',
            'last_name' => 'Admin',
            'email' => 'ada.admin@example.com',
            'phone' => '333111222',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'ada.admin@example.com',
        ]);
    }
}
