<?php

namespace Tests\Feature;

use Database\Seeders\RecoveredLegacyDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecoveredLegacyDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_recovers_legacy_users_job_posting_and_application(): void
    {
        $this->seed(RecoveredLegacyDataSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'giampiero.digregorio@metmi.it',
            'role' => 'professional',
            'nationality' => 'Italiana',
            'address_city' => 'Garbagnate Milanese',
            'address_country' => 'Italia',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'simone.manca@metmi.it',
            'role' => 'business',
        ]);

        $this->assertDatabaseHas('business_profiles', [
            'company_name' => 'metmi',
            'company_type' => 'RSA',
            'location' => 'Milano',
            'employee_count' => 50,
        ]);

        $this->assertDatabaseHas('business_points_of_contact', [
            'email' => 'simone.manca@metmi.it',
            'first_name' => 'Simone',
            'last_name' => 'Manca',
        ]);

        $this->assertDatabaseHas('job_postings', [
            'title' => 'Infermiere struttura sanitaria _Campi Bisenzio | Campi Bisenzio (Firenze)',
            'positions' => 8,
            'contract_type' => 'Part-time',
            'salary_min' => 2000,
            'salary_max' => 2500,
            'expires_at' => '2026-05-27 00:00:00',
        ]);

        $this->assertDatabaseHas('job_applications', [
            'status' => 'inviata',
        ]);
    }

    public function test_it_is_idempotent(): void
    {
        $this->seed(RecoveredLegacyDataSeeder::class);
        $this->seed(RecoveredLegacyDataSeeder::class);

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('business_profiles', 1);
        $this->assertDatabaseCount('professional_profiles', 1);
        $this->assertDatabaseCount('business_points_of_contact', 1);
        $this->assertDatabaseCount('job_postings', 1);
        $this->assertDatabaseCount('job_applications', 1);
    }
}
