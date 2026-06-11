<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SuperAdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_permissions_are_created(): void
    {
        $this->seed(\Database\Seeders\SuperAdminPermissionSeeder::class);

        $this->assertDatabaseHas('permissions', ['name' => 'superadmin.view_tenants']);
        $this->assertDatabaseHas('permissions', ['name' => 'superadmin.manage_tenants']);
        $this->assertDatabaseHas('permissions', ['name' => 'superadmin.view_analytics']);
        $this->assertDatabaseHas('permissions', ['name' => 'superadmin.manage_subscriptions']);
        $this->assertDatabaseHas('permissions', ['name' => 'superadmin.view_logs']);
    }
}
