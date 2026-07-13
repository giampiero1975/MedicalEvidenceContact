<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalExperiencePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_open_experience_page(): void
    {
        $professional = User::factory()->create([
            'role' => 'professional',
        ]);

        $this->actingAs($professional)
            ->get(route('professional.experiences.index'))
            ->assertOk()
            ->assertSee('Esperienze e percorsi di studio')
            ->assertSee('name="type"', false)
            ->assertSee('name="title"', false)
            ->assertSee('name="duration"', false);
    }

    public function test_business_cannot_open_professional_experience_page(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
        ]);

        $this->actingAs($business)
            ->get(route('professional.experiences.index'))
            ->assertForbidden();
    }
}
