<?php

namespace Tests\Feature;

use App\Models\MoodleSite;
use App\Models\User;
use App\Models\UserCertificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalMoodleDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_dashboard_shows_latest_moodle_certificate_summary(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
            'nationality' => 'Italiana',
        ]);

        $site = MoodleSite::query()->create([
            'name' => 'Formazione OSS',
            'base_url' => 'https://moodle.example.test',
            'api_token_encrypted' => 'test-token',
            'certificate_sync_driver' => 'native_mod_customcert',
            'enabled' => true,
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
            'certificate_name' => 'Attestato di Partecipazione',
            'certificate_code' => 't5pdjkEqAX',
            'issued_at' => now(),
            'pdf_stored_path' => 'moodle-certificates/'.$professional->id.'/'.$site->id.'/4734.pdf',
        ]);

        $this->actingAs($professional)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Attestati Moodle')
            ->assertSee('Attestato di Partecipazione')
            ->assertSee('Sterilizzazione dello strumentario chirurgico')
            ->assertSee('PDF disponibile')
            ->assertSee('Vedi tutti gli attestati');
    }

    public function test_professional_dashboard_invites_user_to_connect_moodle_when_no_certificate_exists(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
            'nationality' => 'Italiana',
        ]);

        $this->actingAs($professional)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Attestati Moodle')
            ->assertSee('Collega il tuo account Moodle')
            ->assertSee('Collega Moodle');
    }
}
