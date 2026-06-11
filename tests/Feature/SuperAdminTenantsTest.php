<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminTenantsTest extends TestCase
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

    public function test_tenant_index_shows_all_tenants(): void
    {
        Tenant::factory()->create(['name' => 'Taller A', 'is_active' => true]);
        Tenant::factory()->create(['name' => 'Taller B', 'is_active' => false]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.tenants.index'));

        $response->assertOk();
        $response->assertSee('Taller A');
        $response->assertSee('Taller B');
    }

    public function test_tenant_show_displays_details(): void
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Mi Taller',
            'email' => 'taller@example.com',
            'phone' => '555-1234',
            'is_active' => true,
        ]);

        $client = \App\Models\Client::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        User::factory()->count(2)->create(['tenant_id' => $tenant->id]);

        WorkOrder::factory()->count(5)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.tenants.show', $tenant));

        $response->assertOk();
        $response->assertSee('Mi Taller');
        $response->assertSee('taller@example.com');
        $response->assertSee('555-1234');
    }

    public function test_can_activate_tenant(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->superadmin)->post(route('admin.tenants.toggle-status', $tenant));

        $response->assertRedirect();
        $this->assertTrue($tenant->fresh()->is_active);
    }

    public function test_can_deactivate_tenant(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->superadmin)->post(route('admin.tenants.toggle-status', $tenant));

        $response->assertRedirect();
        $this->assertFalse($tenant->fresh()->is_active);
    }

    public function test_non_superadmin_cannot_manage_tenants(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('admin.tenants.index'));
        $response->assertForbidden();
    }
}
