<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfessionalDocumentsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_open_documents_page(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
            'nationality' => 'Italiana',
        ]);

        $this->actingAs($professional)
            ->get(route('professional.documents.index'))
            ->assertOk()
            ->assertSee('Documenti professionali')
            ->assertSee('Attestato ATA')
            ->assertDontSee('Permesso di soggiorno');
    }

    public function test_non_italian_professional_sees_residence_permit(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
            'nationality' => 'Argentina',
        ]);

        $this->actingAs($professional)
            ->get(route('professional.documents.index'))
            ->assertOk()
            ->assertSee('Attestato ATA')
            ->assertSee('Permesso di soggiorno');
    }

    public function test_upload_from_documents_page_returns_to_documents_page(): void
    {
        Storage::fake('professional_documents');

        $professional = User::factory()->create([
            'role' => 'professional',
            'nationality' => 'Italiana',
        ]);

        $this->actingAs($professional)
            ->post(route('professional-documents.store'), [
                'ata_certificate_document' => UploadedFile::fake()->create('ata.pdf', 120, 'application/pdf'),
                'redirect_to' => 'documents',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('professional.documents.index', absolute: false));

        $professional->refresh();

        $this->assertNotNull($professional->ata_certificate_path);
        Storage::disk('professional_documents')->assertExists($professional->ata_certificate_path);
    }

    public function test_business_cannot_open_documents_page(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
        ]);

        $this->actingAs($business)
            ->get(route('professional.documents.index'))
            ->assertForbidden();
    }
}
