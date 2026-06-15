<?php

namespace Tests\Feature;

use App\Models\Plan;
use Database\Seeders\PlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_creates_with_casts(): void
    {
        $plan = Plan::create([
            'name' => 'Test Plan',
            'slug' => 'test-plan',
            'description' => 'A test plan.',
            'price' => 149.99,
            'features' => [
                'work_orders' => true,
                'pos' => false,
            ],
            'limits' => [
                'max_users' => 5,
                'storage_mb' => 500,
            ],
            'sort_order' => 1,
            'is_active' => true,
            'is_highlight' => false,
        ]);

        $this->assertDatabaseHas('plans', [
            'name' => 'Test Plan',
            'slug' => 'test-plan',
            'price' => 149.99,
        ]);

        $this->assertIsArray($plan->features);
        $this->assertTrue($plan->features['work_orders']);
        $this->assertFalse($plan->features['pos']);

        $this->assertIsArray($plan->limits);
        $this->assertEquals(5, $plan->limits['max_users']);

        $this->assertIsBool($plan->is_active);
        $this->assertTrue($plan->is_active);

        $this->assertIsBool($plan->is_highlight);
        $this->assertFalse($plan->is_highlight);
    }

    public function test_plans_seeder_creates_three_plans(): void
    {
        $this->seed(PlansSeeder::class);

        $this->assertDatabaseCount('plans', 3);

        $basic = Plan::where('slug', 'basic')->first();
        $this->assertEquals('Básico', $basic->name);
        $this->assertEquals(99, $basic->price);
        $this->assertEquals(1, $basic->sort_order);
        $this->assertFalse($basic->is_highlight);
        $this->assertIsArray($basic->features);
        $this->assertIsArray($basic->limits);

        $pro = Plan::where('slug', 'pro')->first();
        $this->assertEquals('Pro', $pro->name);
        $this->assertEquals(199, $pro->price);
        $this->assertEquals(2, $pro->sort_order);
        $this->assertTrue($pro->is_highlight);

        $premium = Plan::where('slug', 'premium')->first();
        $this->assertEquals('Premium', $premium->name);
        $this->assertEquals(349, $premium->price);
        $this->assertEquals(3, $premium->sort_order);
        $this->assertFalse($premium->is_highlight);
    }
}
