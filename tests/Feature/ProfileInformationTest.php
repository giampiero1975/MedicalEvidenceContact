<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_profile_information_is_available(): void
    {
        $this->actingAs($user = User::factory()->create());

        $component = Livewire::test(UpdateProfileInformationForm::class);

        $this->assertEquals($user->name, $component->state['name']);
        $this->assertEquals($user->email, $component->state['email']);
        $this->assertEquals($user->nationality, $component->state['nationality']);
        $this->assertEquals($user->address_city, $component->state['address_city']);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', [
                'name' => 'Test Name',
                'email' => 'test@example.com',
                'nationality' => 'Italiana',
                'address_city' => 'Milano',
                'address_country' => 'Italia',
                'address_province' => 'MI',
                'postal_code' => '20100',
                'street_address' => 'Via Roma 20',
            ])
            ->call('updateProfileInformation');

        $this->assertEquals('Test Name', $user->fresh()->name);
        $this->assertEquals('test@example.com', $user->fresh()->email);
        $this->assertEquals('Milano', $user->fresh()->address_city);
        $this->assertEquals('MI', $user->fresh()->address_province);
        $this->assertEquals('20100', $user->fresh()->postal_code);
        $this->assertEquals('Via Roma 20', $user->fresh()->street_address);
    }

    public function test_professional_can_upload_required_profile_documents(): void
    {
        $disk = config('filesystems.professional_documents_disk', 'professional_documents');
        Storage::fake($disk);

        $this->actingAs($user = User::factory()->create([
            'nationality' => 'Argentina',
        ]));

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', [
                'name' => 'Test Name',
                'email' => 'test@example.com',
                'nationality' => 'Argentina',
                'address_city' => 'Torino',
                'address_country' => 'Italia',
                'address_province' => 'TO',
                'postal_code' => '10100',
                'street_address' => 'Via Po 5',
            ])
            ->set('state.residence_permit_document', UploadedFile::fake()->create('permesso.pdf', 120, 'application/pdf'))
            ->set('state.ata_certificate_document', UploadedFile::fake()->create('ata.pdf', 120, 'application/pdf'))
            ->call('updateProfileInformation');

        $user->refresh();

        $this->assertNotNull($user->residence_permit_path);
        $this->assertNotNull($user->ata_certificate_path);
        Storage::disk($disk)->assertExists($user->residence_permit_path);
        Storage::disk($disk)->assertExists($user->ata_certificate_path);
    }

    public function test_profile_information_rejects_manual_nationality_values(): void
    {
        $this->actingAs($user = User::factory()->create([
            'nationality' => 'Italiana',
        ]));

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', [
                'name' => 'Test Name',
                'email' => 'test@example.com',
                'nationality' => 'Valore scritto a mano',
                'address_city' => 'Milano',
                'address_country' => 'Italia',
                'address_province' => 'MI',
                'postal_code' => '20100',
                'street_address' => 'Via Roma 20',
            ])
            ->call('updateProfileInformation')
            ->assertHasErrors(['nationality']);

        $this->assertSame('Italiana', $user->fresh()->nationality);
    }
}
