<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminTenant = Role::firstOrCreate(['name' => 'admin_tenant', 'guard_name' => 'web']);
        $adminTenant->syncPermissions(Permission::all());

        $secretario = Role::firstOrCreate(['name' => 'secretario', 'guard_name' => 'web']);
        $secretario->syncPermissions([
            'clients.view',
            'clients.create',
            'clients.edit',
            'work_orders.view',
            'work_orders.create',
            'work_orders.edit',
            'work_orders.change_status',
            'work_orders.add_notes',
            'quotes.view',
            'quotes.create',
            'quotes.approve',
            'products.view',
            'pos.access',
            'pos.sell',
            'pos.charge_orders',
            'pos.apply_discounts',
            'cash_register.open',
            'cash_register.close',
            'cash_register.view_history',
            'reports.sales',
        ]);

        $tecnico = Role::firstOrCreate(['name' => 'tecnico', 'guard_name' => 'web']);
        $tecnico->syncPermissions([
            'work_orders.view',
            'work_orders.edit',
            'work_orders.change_status',
            'work_orders.set_priority',
            'work_orders.add_notes',
            'quotes.view',
            'quotes.create',
            'products.view',
            'kardex.view',
        ]);
    }
}
