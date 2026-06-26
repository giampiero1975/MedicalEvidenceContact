<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_user_can_publish_a_job_posting(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
        ]);

        $businessProfile = $business->businessProfile()->create([
            'user_id' => $business->id,
            'company_name' => 'Clinica Test',
            'company_type' => 'Clinica privata',
            'location' => 'Milano',
            'employee_count' => 80,
        ]);

        $response = $this->actingAs($business)->post('/annunci', [
            'title' => 'Infermiere reparto degenza',
            'description' => 'Cerchiamo un infermiere per reparto degenza.',
            'positions' => 2,
            'workplace_address' => 'Via Roma 10, Milano',
            'required_skills' => 'Iscrizione OPI, disponibilita turni',
            'contract_type' => 'Tempo indeterminato',
            'salary_min' => 28000,
            'salary_max' => 34000,
            'expires_at' => now()->addMonth()->toDateString(),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('job-postings.index', absolute: false));
        $this->assertDatabaseHas('job_postings', [
            'user_id' => $business->id,
            'business_profile_id' => $businessProfile->id,
            'title' => 'Infermiere reparto degenza',
            'positions' => 2,
            'contract_type' => 'Tempo indeterminato',
            'status' => 'active',
        ]);
    }

    public function test_professional_user_can_view_active_job_postings_on_main_page(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
        ]);

        $business = User::factory()->create([
            'role' => 'business',
        ]);

        JobPosting::create([
            'user_id' => $business->id,
            'title' => 'OSS struttura residenziale',
            'description' => 'Annuncio visibile ai professionisti.',
            'positions' => 3,
            'workplace_address' => 'Via Milano 3, Roma',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        JobPosting::create([
            'user_id' => $business->id,
            'title' => 'Annuncio scaduto',
            'description' => 'Non deve comparire.',
            'positions' => 1,
            'workplace_address' => 'Via Torino 1, Roma',
            'contract_type' => 'Part-time',
            'expires_at' => now()->subDay()->toDateString(),
            'status' => 'expired',
        ]);

        $response = $this->actingAs($professional)->get('/annunci');

        $response->assertStatus(200);
        $response->assertSee('OSS struttura residenziale');
        $response->assertDontSee('Annuncio scaduto');
    }

    public function test_professional_user_can_filter_active_job_postings(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
        ]);

        $matchingBusiness = User::factory()->create([
            'role' => 'business',
        ]);

        $matchingProfile = $matchingBusiness->businessProfile()->create([
            'user_id' => $matchingBusiness->id,
            'company_name' => 'RSA Milano Nord',
            'company_type' => 'RSA',
            'location' => 'Milano',
            'employee_count' => 60,
        ]);

        JobPosting::create([
            'user_id' => $matchingBusiness->id,
            'business_profile_id' => $matchingProfile->id,
            'title' => 'OSS RSA Milano',
            'description' => 'Posizione per reparto assistenziale.',
            'positions' => 2,
            'workplace_address' => 'Via Padova 10, Milano',
            'required_skills' => 'OSS, turni diurni',
            'contract_type' => 'Tempo indeterminato',
            'salary_min' => 28000,
            'salary_max' => 32000,
            'expires_at' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        $otherBusiness = User::factory()->create([
            'role' => 'business',
        ]);

        $otherProfile = $otherBusiness->businessProfile()->create([
            'user_id' => $otherBusiness->id,
            'company_name' => 'Clinica Bologna',
            'company_type' => 'Clinica privata',
            'location' => 'Bologna',
            'employee_count' => 120,
        ]);

        JobPosting::create([
            'user_id' => $otherBusiness->id,
            'business_profile_id' => $otherProfile->id,
            'title' => 'Infermiere sala operatoria',
            'description' => 'Posizione non coerente con i filtri.',
            'positions' => 1,
            'workplace_address' => 'Via Indipendenza 2, Bologna',
            'required_skills' => 'OPI, sala operatoria',
            'contract_type' => 'Part-time',
            'salary_min' => 42000,
            'salary_max' => 46000,
            'expires_at' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($professional)
            ->get(route('job-postings.index', [
                'keyword' => 'assistenziale',
                'location' => 'Milano',
                'contract_type' => 'Tempo indeterminato',
                'company_category' => 'RSA',
                'professional_category' => 'OSS',
                'salary_min' => 25000,
                'salary_max' => 35000,
                'published_from' => now()->subDay()->toDateString(),
                'published_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('OSS RSA Milano')
            ->assertSee('1 risultati')
            ->assertDontSee('Infermiere sala operatoria');
    }


    public function test_professional_announcements_page_does_not_show_dashboard_sections(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
        ]);

        $this->actingAs($professional)
            ->get(route('job-postings.index'))
            ->assertOk()
            ->assertSee('Annunci disponibili')
            ->assertDontSee('Documenti professionali')
            ->assertDontSee('Esperienze e percorsi di studio')
            ->assertDontSee('Le tue candidature');
    }

    public function test_professional_user_cannot_create_job_postings(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
        ]);

        $this->actingAs($professional)
            ->get('/annunci/crea')
            ->assertForbidden();
    }

    public function test_professional_user_can_apply_to_a_job_posting_and_see_it_in_dashboard_list(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
        ]);

        $business = User::factory()->create([
            'role' => 'business',
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'Fisioterapista ambulatoriale',
            'description' => 'Opportunita per fisioterapista in ambulatorio.',
            'positions' => 1,
            'workplace_address' => 'Via Napoli 8, Torino',
            'contract_type' => 'Collaborazione',
            'expires_at' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($professional)
            ->post(route('job-applications.store', $jobPosting));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('job_applications', [
            'job_posting_id' => $jobPosting->id,
            'user_id' => $professional->id,
            'status' => 'inviata',
        ]);

        $this->actingAs($professional)
            ->get('/dashboard')
            ->assertSee('Le tue candidature')
            ->assertSee('Colloqui')
            ->assertSee('Invito da confermare')
            ->assertSee('Conferma colloquio')
            ->assertSee('Fisioterapista ambulatoriale')
            ->assertSee('Candidatura inviata');
    }

    public function test_business_user_cannot_apply_to_a_job_posting(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'Annuncio business',
            'description' => 'Un business non puo candidarsi.',
            'positions' => 1,
            'workplace_address' => 'Via Firenze 1, Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($business)
            ->post(route('job-applications.store', $jobPosting))
            ->assertForbidden();
    }

    public function test_business_user_can_view_profiles_that_applied_without_contact_details(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
        ]);

        $professional = User::factory()->create([
            'role' => 'professional',
            'name' => 'Giulia Rossi',
            'first_name' => 'Giulia',
            'last_name' => 'Rossi',
            'email' => 'giulia.rossi@example.test',
            'phone' => '3331234567',
            'residence' => 'Bologna',
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'Tecnico radiologo',
            'description' => 'Posizione aperta in diagnostica.',
            'positions' => 1,
            'workplace_address' => 'Via San Luca 12, Bologna',
            'contract_type' => 'Tempo indeterminato',
            'expires_at' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        JobApplication::create([
            'job_posting_id' => $jobPosting->id,
            'user_id' => $professional->id,
            'status' => 'inviata',
        ]);

        $this->actingAs($business)
            ->get(route('job-postings.applications', $jobPosting))
            ->assertOk()
            ->assertSee('Candidature ricevute')
            ->assertSee('Giulia Rossi')
            ->assertSee('Bologna')
            ->assertSee('inviata')
            ->assertSee('Fissa colloquio')
            ->assertSee('Proponi slot')
            ->assertSee('Invito a colloquio inviato')
            ->assertDontSee('giulia.rossi@example.test')
            ->assertDontSee('3331234567');
    }

    public function test_business_owner_can_update_a_job_posting(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'Titolo iniziale',
            'description' => 'Descrizione iniziale.',
            'positions' => 1,
            'workplace_address' => 'Via Roma 1, Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($business)
            ->put(route('job-postings.update', $jobPosting), [
                'title' => 'Titolo aggiornato',
                'description' => 'Descrizione aggiornata.',
                'positions' => 3,
                'workplace_address' => 'Via Milano 20, Milano',
                'required_skills' => 'Esperienza reparto',
                'contract_type' => 'Tempo indeterminato',
                'salary_min' => 30000,
                'salary_max' => 36000,
                'expires_at' => now()->addMonth()->toDateString(),
                'status' => 'active',
            ]);

        $response->assertRedirect(route('job-postings.show', $jobPosting, absolute: false));
        $this->assertDatabaseHas('job_postings', [
            'id' => $jobPosting->id,
            'title' => 'Titolo aggiornato',
            'positions' => 3,
            'contract_type' => 'Tempo indeterminato',
        ]);
    }

    public function test_business_owner_can_delete_a_job_posting(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'Annuncio da eliminare',
            'description' => 'Questo annuncio verra eliminato.',
            'positions' => 1,
            'workplace_address' => 'Via Venezia 2, Padova',
            'contract_type' => 'Collaborazione',
            'expires_at' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($business)
            ->delete(route('job-postings.destroy', $jobPosting))
            ->assertRedirect(route('job-postings.index', absolute: false));

        $this->assertDatabaseMissing('job_postings', [
            'id' => $jobPosting->id,
        ]);
    }

    public function test_business_user_cannot_update_or_delete_another_business_job_posting(): void
    {
        $owner = User::factory()->create([
            'role' => 'business',
        ]);

        $otherBusiness = User::factory()->create([
            'role' => 'business',
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $owner->id,
            'title' => 'Annuncio protetto',
            'description' => 'Solo il proprietario puo gestirlo.',
            'positions' => 1,
            'workplace_address' => 'Via Como 5, Monza',
            'contract_type' => 'Part-time',
            'expires_at' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($otherBusiness)
            ->get(route('job-postings.edit', $jobPosting))
            ->assertForbidden();

        $this->actingAs($otherBusiness)
            ->put(route('job-postings.update', $jobPosting), [
                'title' => 'Tentativo modifica',
                'description' => 'Non autorizzato.',
                'positions' => 2,
                'workplace_address' => 'Via Test 1',
                'contract_type' => 'Tempo determinato',
                'expires_at' => now()->addWeek()->toDateString(),
            ])
            ->assertForbidden();

        $this->actingAs($otherBusiness)
            ->delete(route('job-postings.destroy', $jobPosting))
            ->assertForbidden();
    }

    public function test_business_user_cannot_view_applications_for_another_business_job_posting(): void
    {
        $owner = User::factory()->create([
            'role' => 'business',
        ]);

        $otherBusiness = User::factory()->create([
            'role' => 'business',
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $owner->id,
            'title' => 'Infermiera sala operatoria',
            'description' => 'Posizione riservata al business proprietario.',
            'positions' => 1,
            'workplace_address' => 'Via Verdi 4, Firenze',
            'contract_type' => 'Turni',
            'expires_at' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($otherBusiness)
            ->get(route('job-postings.applications', $jobPosting))
            ->assertForbidden();
    }
}
