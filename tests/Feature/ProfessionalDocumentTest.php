<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfessionalDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_dashboard_shows_ata_document_upload(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
            'nationality' => 'Italiana',
        ]);

        $this->actingAs($professional)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Documenti professionali')
            ->assertSee('Attestato ATA')
            ->assertDontSee('Permesso di soggiorno');
    }

    public function test_professional_dashboard_shows_residence_permit_for_non_italian_nationality(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
            'nationality' => 'Argentina',
        ]);

        $this->actingAs($professional)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Attestato ATA')
            ->assertSee('Permesso di soggiorno');
    }

    public function test_professional_can_upload_dashboard_documents(): void
    {
        Storage::fake('public');

        $professional = User::factory()->create([
            'role' => 'professional',
            'nationality' => 'Argentina',
        ]);

        $this->actingAs($professional)
            ->post(route('professional-documents.store'), [
                'ata_certificate_document' => UploadedFile::fake()->create('ata.pdf', 120, 'application/pdf'),
                'residence_permit_document' => UploadedFile::fake()->create('permesso.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect(route('dashboard', absolute: false));

        $professional->refresh();

        $this->assertNotNull($professional->ata_certificate_path);
        $this->assertNotNull($professional->residence_permit_path);
        Storage::disk('public')->assertExists($professional->ata_certificate_path);
        Storage::disk('public')->assertExists($professional->residence_permit_path);
    }

    public function test_business_cannot_upload_professional_documents(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
        ]);

        $this->actingAs($business)
            ->post(route('professional-documents.store'), [
                'ata_certificate_document' => UploadedFile::fake()->create('ata.pdf', 120, 'application/pdf'),
            ])
            ->assertForbidden();
    }
}
