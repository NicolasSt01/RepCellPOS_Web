<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'is_superadmin' => true,
            'tenant_id' => null,
        ]);
    }

    public function test_dashboard_shows_global_metrics(): void
    {
        $tenant1 = Tenant::factory()->create(['name' => 'Taller A', 'is_active' => true]);
        $tenant2 = Tenant::factory()->create(['name' => 'Taller B', 'is_active' => true]);
        $tenant3 = Tenant::factory()->create(['name' => 'Taller C', 'is_active' => false]);

        User::factory()->count(3)->create(['tenant_id' => $tenant1->id]);
        User::factory()->count(2)->create(['tenant_id' => $tenant2->id]);

        Client::factory()->count(5)->create(['tenant_id' => $tenant1->id]);
        Product::factory()->count(10)->create(['tenant_id' => $tenant1->id]);

        $user1 = User::where('tenant_id', $tenant1->id)->first();
        $client1 = Client::where('tenant_id', $tenant1->id)->first();
        WorkOrder::factory()->count(4)->create([
            'tenant_id' => $tenant1->id,
            'client_id' => $client1->id,
            'user_id' => $user1->id,
        ]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('3');
        $response->assertSee('2');
        $response->assertSee('Taller A');
        $response->assertSee('Taller B');
        $response->assertSee('Taller C');
    }

    public function test_dashboard_shows_recent_tenants(): void
    {
        Tenant::factory()->create(['name' => 'Taller Más Reciente', 'created_at' => now()]);
        Tenant::factory()->create(['name' => 'Taller Antiguo', 'created_at' => now()->subDays(10)]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Taller Más Reciente');
        $response->assertSee('Taller Antiguo');
    }

    public function test_dashboard_is_inaccessible_to_non_superadmin(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertForbidden();
    }
}
