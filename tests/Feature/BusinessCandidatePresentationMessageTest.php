<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessCandidatePresentationMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_business_sees_presentation_message_without_unlocking_contacts(): void
    {
        [$business, $professional, $application] = $this->scenario(
            'Ho maturato esperienza in strutture residenziali e sono disponibile a breve.'
        );

        $this->actingAs($business)
            ->get(route('business.applications.show', $application))
            ->assertOk()
            ->assertSeeText('Messaggio di presentazione')
            ->assertSeeText('Ho maturato esperienza in strutture residenziali e sono disponibile a breve.')
            ->assertSeeText('Contatti protetti')
            ->assertDontSee($professional->email)
            ->assertDontSee($professional->phone);
    }

    public function test_workspace_shows_empty_state_when_presentation_message_was_not_provided(): void
    {
        [$business, , $application] = $this->scenario(null);

        $this->actingAs($business)
            ->get(route('business.applications.show', $application))
            ->assertOk()
            ->assertSeeText('Messaggio di presentazione')
            ->assertSeeText('Nessun messaggio di presentazione inserito.');
    }

    public function test_other_business_cannot_read_candidate_presentation_message(): void
    {
        [, , $application] = $this->scenario('Messaggio riservato alla struttura proprietaria.');
        $otherBusiness = User::factory()->create(['role' => 'business']);

        $this->actingAs($otherBusiness)
            ->get(route('business.applications.show', $application))
            ->assertForbidden();
    }

    private function scenario(?string $presentationMessage): array
    {
        $business = User::factory()->create(['role' => 'business']);
        $professional = User::factory()->create([
            'role' => 'professional',
            'first_name' => 'Giulia',
            'last_name' => 'Rossi',
            'email' => 'giulia.presentation@example.test',
            'phone' => '3331234567',
            'residence' => 'Milano',
        ]);

        $jobPosting = JobPosting::create([
            'user_id' => $business->id,
            'title' => 'OSS struttura sanitaria',
            'description' => 'Ricerca professionista.',
            'positions' => 1,
            'workplace_address' => 'Milano',
            'contract_type' => 'Tempo determinato',
            'expires_at' => now()->addWeek(),
            'status' => 'active',
        ]);

        $application = JobApplication::create([
            'job_posting_id' => $jobPosting->id,
            'user_id' => $professional->id,
            'status' => JobApplication::STATUS_RECEIVED,
            'presentation_message' => $presentationMessage,
        ]);

        return [$business, $professional, $application];
    }
}
