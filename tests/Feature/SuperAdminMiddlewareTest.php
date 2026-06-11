<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'is_superadmin' => true,
            'tenant_id' => null,
        ]);

        $tenant = Tenant::factory()->create();
        $this->regularUser = User::factory()->create([
            'is_superadmin' => false,
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_superadmin_can_access_admin_routes(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_regular_user_cannot_access_admin_routes(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }
}
