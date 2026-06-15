<?php

namespace Tests\Feature;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingDynamicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PlansSeeder::class);
    }

    public function test_landing_shows_plan_names_from_database(): void
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        $response = $this->get('/');

        $response->assertStatus(200);

        foreach ($plans as $plan) {
            $response->assertSeeText($plan->name);
        }
    }

    public function test_landing_shows_three_plan_cards(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $this->assertCount(3, $plans);

        foreach ($plans as $plan) {
            $response->assertSeeText($plan->name);
        }

        $response->assertSeeText('Comenzar prueba gratis');
        $response->assertSeeText('Todos los planes incluyen 7 días de prueba gratuita sin compromiso.');
    }
}
