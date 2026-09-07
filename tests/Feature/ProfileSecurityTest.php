<?php

namespace Tests\Feature;

use App\Actions\Jetstream\DeleteUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_security_page_is_available_to_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => 'professional']);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Profilo e sicurezza')
            ->assertSee($user->email);
    }

    public function test_admin_profile_does_not_expose_account_deletion_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertDontSee('Delete Account');
    }

    public function test_admin_cannot_be_deleted_through_jetstream_delete_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        try {
            app(DeleteUser::class)->delete($admin);
            $this->fail('Expected admin deletion to be forbidden.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
