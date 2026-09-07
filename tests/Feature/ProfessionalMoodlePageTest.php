<?php

namespace Tests\Feature;

use App\Models\MoodleSite;
use App\Models\MoodleUserLink;
use App\Models\User;
use App\Models\UserCertificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalMoodlePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_open_moodle_page(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);

        $this->actingAs($professional)
            ->get(route('professional.moodle.index'))
            ->assertOk()
            ->assertSee('Moodle e attestati')
            ->assertSee('Collega un account Moodle');
    }

    public function test_business_cannot_open_professional_moodle_page(): void
    {
        $business = User::factory()->create(['role' => 'business']);

        $this->actingAs($business)
            ->get(route('professional.moodle.index'))
            ->assertForbidden();
    }

    public function test_disconnect_revokes_link_and_keeps_synced_certificates_visible(): void
    {
        $professional = User::factory()->create(['role' => 'professional']);
        $site = $this->createMoodleSite();

        $link = MoodleUserLink::query()->create([
            'laravel_user_id' => $professional->id,
            'moodle_site_id' => $site->id,
            'moodle_user_id' => 2701,
            'moodle_username' => 'mario.rossi',
            'moodle_email' => 'mario.rossi@example.test',
            'linked_via' => 'email_code',
            'linked_at' => now(),
            'last_verified_at' => now(),
            'status' => 'active',
        ]);

        UserCertificate::query()->create([
            'laravel_user_id' => $professional->id,
            'moodle_site_id' => $site->id,
            'moodle_user_id' => 2701,
            'moodle_customcert_id' => 48,
            'moodle_customcert_issue_id' => 4734,
            'moodle_course_module_id' => 872,
            'course_id' => 40,
            'course_fullname' => 'Sterilizzazione dello strumentario chirurgico',
            'course_shortname' => 'Sterilizzazione OSS',
            'certificate_name' => 'Attestato storico Moodle',
            'certificate_code' => 'CERT-4734',
            'issued_at' => now(),
        ]);

        $this->actingAs($professional)
            ->delete(route('professional.moodle.disconnect', $link))
            ->assertRedirect(route('professional.moodle.index'));

        $this->assertDatabaseHas('moodle_user_links', [
            'id' => $link->id,
            'status' => 'revoked',
        ]);

        $this->assertDatabaseHas('user_certificates', [
            'laravel_user_id' => $professional->id,
            'certificate_code' => 'CERT-4734',
        ]);

        $this->actingAs($professional)
            ->get(route('professional.moodle.index'))
            ->assertOk()
            ->assertSee('Scollegato')
            ->assertSee('Attestato storico Moodle')
            ->assertSee('Sterilizzazione dello strumentario chirurgico');
    }

    public function test_professional_cannot_disconnect_another_users_moodle_link(): void
    {
        $owner = User::factory()->create(['role' => 'professional']);
        $otherProfessional = User::factory()->create(['role' => 'professional']);
        $site = $this->createMoodleSite();

        $link = MoodleUserLink::query()->create([
            'laravel_user_id' => $owner->id,
            'moodle_site_id' => $site->id,
            'moodle_user_id' => 2702,
            'moodle_username' => 'owner.user',
            'linked_via' => 'email_code',
            'linked_at' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($otherProfessional)
            ->delete(route('professional.moodle.disconnect', $link))
            ->assertForbidden();

        $this->assertSame('active', $link->refresh()->status);
    }

    private function createMoodleSite(): MoodleSite
    {
        return MoodleSite::query()->create([
            'name' => 'Formazione OSS',
            'base_url' => 'https://moodle.example.test',
            'api_token_encrypted' => 'test-token',
            'certificate_sync_driver' => 'native_mod_customcert',
            'enabled' => true,
        ]);
    }
}
