<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminRestrictionTest extends TestCase
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

    public function test_superadmin_cannot_access_pos(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('pos.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_dashboard(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('dashboard'));
        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_settings(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('settings.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_products(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('products.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_work_orders(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('work_orders.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_clients(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('clients.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_sales(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('sales.index'));
        $response->assertForbidden();
    }

    public function test_regular_user_can_access_tenant_routes(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'is_superadmin' => false,
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
    }
}
