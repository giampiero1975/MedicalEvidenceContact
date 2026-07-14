<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\ProfessionalProfileItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalProfileItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_add_work_experience_and_education_from_dashboard(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
        ]);

        $this->actingAs($professional)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Esperienze e percorsi di studio')
            ->assertSee('name="type"', false)
            ->assertSee('name="title"', false)
            ->assertSee('name="duration"', false);

        $this->actingAs($professional)
            ->post(route('professional-profile-items.store'), [
                'type' => ProfessionalProfileItem::TYPE_WORK_EXPERIENCE,
                'title' => 'OSS reparto geriatria',
                'duration' => '2021 - 2024',
                'description' => 'Assistenza agli ospiti e supporto alle attivita quotidiane.',
            ])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->actingAs($professional)
            ->post(route('professional-profile-items.store'), [
                'type' => ProfessionalProfileItem::TYPE_EDUCATION,
                'title' => 'Qualifica OSS',
                'duration' => '2020 - 2021',
                'description' => 'Percorso regionale per operatore socio sanitario.',
            ])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('professional_profile_items', [
            'user_id' => $professional->id,
            'type' => ProfessionalProfileItem::TYPE_WORK_EXPERIENCE,
            'title' => 'OSS reparto geriatria',
            'duration' => '2021 - 2024',
        ]);

        $this->assertDatabaseHas('professional_profile_items', [
            'user_id' => $professional->id,
            'type' => ProfessionalProfileItem::TYPE_EDUCATION,
            'title' => 'Qualifica OSS',
            'duration' => '2020 - 2021',
        ]);
    }


    public function test_professional_can_update_profile_items(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
        ]);

        $item = $professional->professionalProfileItems()->create([
            'type' => ProfessionalProfileItem::TYPE_WORK_EXPERIENCE,
            'title' => 'Titolo iniziale',
            'duration' => '2020 - 2021',
            'description' => 'Descrizione iniziale.',
        ]);

        $this->actingAs($professional)
            ->put(route('professional-profile-items.update', $item), [
                'type' => ProfessionalProfileItem::TYPE_EDUCATION,
                'title' => 'Corso aggiornato',
                'duration' => '2022 - 2023',
                'description' => 'Descrizione aggiornata.',
            ])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('professional_profile_items', [
            'id' => $item->id,
            'user_id' => $professional->id,
            'type' => ProfessionalProfileItem::TYPE_EDUCATION,
            'title' => 'Corso aggiornato',
            'duration' => '2022 - 2023',
            'description' => 'Descrizione aggiornata.',
        ]);
    }

    public function test_professional_can_delete_profile_items(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
        ]);

        $item = $professional->professionalProfileItems()->create([
            'type' => ProfessionalProfileItem::TYPE_WORK_EXPERIENCE,
            'title' => 'Esperienza da eliminare',
            'duration' => '2024',
            'description' => 'Non deve restare.',
        ]);

        $this->actingAs($professional)
            ->delete(route('professional-profile-items.destroy', $item))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseMissing('professional_profile_items', [
            'id' => $item->id,
        ]);
    }

    public function test_professional_cannot_update_or_delete_another_professionals_profile_items(): void
    {
        $owner = User::factory()->create([
            'role' => 'professional',
        ]);

        $otherProfessional = User::factory()->create([
            'role' => 'professional',
        ]);

        $item = $owner->professionalProfileItems()->create([
            'type' => ProfessionalProfileItem::TYPE_WORK_EXPERIENCE,
            'title' => 'Elemento protetto',
            'duration' => '2020',
            'description' => 'Non modificabile da altri.',
        ]);

        $this->actingAs($otherProfessional)
            ->put(route('professional-profile-items.update', $item), [
                'type' => ProfessionalProfileItem::TYPE_EDUCATION,
                'title' => 'Tentativo modifica',
                'duration' => '2025',
                'description' => 'Non deve salvare.',
            ])
            ->assertForbidden();

        $this->actingAs($otherProfessional)
            ->delete(route('professional-profile-items.destroy', $item))
            ->assertForbidden();

        $this->assertDatabaseHas('professional_profile_items', [
            'id' => $item->id,
            'title' => 'Elemento protetto',
        ]);
    }

    public function test_business_cannot_add_professional_profile_items(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
        ]);

        $this->actingAs($business)
            ->post(route('professional-profile-items.store'), [
                'type' => ProfessionalProfileItem::TYPE_WORK_EXPERIENCE,
                'title' => 'Esperienza non ammessa',
                'duration' => '2024',
                'description' => 'Non deve essere salvata.',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('professional_profile_items', [
            'title' => 'Esperienza non ammessa',
        ]);
    }

    public function test_business_can_view_applicant_work_experience_and_education_without_contact_details(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
        ]);

        $professional = User::factory()->create([
            'role' => 'professional',
            'name' => 'Maria Aldomar',
            'first_name' => 'Maria',
            'last_name' => 'Aldomar',
            'email' => 'maria.aldomar.profile@example.test',
            'phone' => '3339998887',
            'residence' => 'Milano',
        ]);

        $professional->professionalProfileItems()->create([
            'type' => ProfessionalProfileItem::TYPE_WORK_EXPERIENCE,
            'title' => 'OSS RSA San Carlo',
            'duration' => '2022 - 2025',
            'description' => 'Assistenza in struttura residenziale.',
        ]);

        $professional->professionalProfileItems()->create([
            'type' => ProfessionalProfileItem::TYPE_EDUCATION,
            'title' => 'Corso OSS Regione Lombardia',
            'duration' => '2021',
            'description' => 'Percorso di qualifica professionale.',
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'OSS turno diurno',
            'description' => 'Posizione aperta in RSA.',
            'positions' => 1,
            'workplace_address' => 'Via Roma 10, Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ]);

        JobApplication::create([
            'job_posting_id' => $jobPosting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_RECEIVED,
        ]);

        $this->actingAs($business)
            ->get(route('job-postings.applications', $jobPosting))
            ->assertOk()
            ->assertSee('Maria Aldomar')
            ->assertSee('Esperienza:')
            ->assertSee('OSS RSA San Carlo')
            ->assertSee('Formazione:')
            ->assertSee('Corso OSS Regione Lombardia')
            ->assertDontSee('maria.aldomar.profile@example.test')
            ->assertDontSee('3339998887');
    }
}
