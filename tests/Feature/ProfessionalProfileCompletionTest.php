<?php

namespace Tests\Feature;

use App\Models\ProfessionalProfileItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalProfileCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_personal_data_alone_does_not_mark_profile_as_complete(): void
    {
        $professional = $this->completeProfessional([
            'nationality' => 'Italiana',
            'ata_certificate_path' => null,
        ]);

        $this->actingAs($professional)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('profileCompletion', fn (int $completion) => $completion < 100);
    }

    public function test_italian_professional_reaches_full_completion_with_curriculum_and_required_document(): void
    {
        $professional = $this->completeProfessional([
            'nationality' => 'Italiana',
            'ata_certificate_path' => 'professional-documents/test/ata.pdf',
        ]);

        ProfessionalProfileItem::create([
            'user_id' => $professional->id,
            'type' => ProfessionalProfileItem::TYPE_WORK_EXPERIENCE,
            'title' => 'Operatore socio sanitario',
            'duration' => '2022 - 2026',
            'description' => 'Esperienza in struttura residenziale.',
        ]);

        $this->actingAs($professional)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('profileCompletion', 100)
            ->assertSee('Dati, curriculum e documenti richiesti');
    }

    public function test_non_italian_professional_requires_residence_permit_for_full_completion(): void
    {
        $professional = $this->completeProfessional([
            'nationality' => 'Argentina',
            'ata_certificate_path' => 'professional-documents/test/ata.pdf',
            'residence_permit_path' => null,
        ]);

        ProfessionalProfileItem::create([
            'user_id' => $professional->id,
            'type' => ProfessionalProfileItem::TYPE_EDUCATION,
            'title' => 'Qualifica OSS',
            'duration' => '2021',
        ]);

        $this->actingAs($professional)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('profileCompletion', fn (int $completion) => $completion < 100);

        $professional->update([
            'residence_permit_path' => 'professional-documents/test/permesso.pdf',
        ]);

        $this->actingAs($professional->fresh())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('profileCompletion', 100);
    }

    private function completeProfessional(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'professional',
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'phone' => '3331234567',
            'nationality' => 'Italiana',
            'address_city' => 'Milano',
            'address_country' => 'Italia',
            'address_province' => 'MI',
            'postal_code' => '20100',
            'street_address' => 'Via Roma 10',
        ], $overrides));
    }
}
