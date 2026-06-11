<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SuperAdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'superadmin.view_tenants',
            'superadmin.manage_tenants',
            'superadmin.view_analytics',
            'superadmin.manage_subscriptions',
            'superadmin.view_logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['guard_name' => 'web', 'name' => $permission]);
        }

        $this->command->info('Superadmin permissions seeded successfully.');
    }
}
