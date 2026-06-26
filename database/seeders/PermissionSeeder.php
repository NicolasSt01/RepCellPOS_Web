<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',

            'work_orders.view',
            'work_orders.create',
            'work_orders.edit',
            'work_orders.change_status',
            'work_orders.set_priority',
            'work_orders.add_notes',

            'quotes.view',
            'quotes.create',
            'quotes.approve',

            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            'kardex.view',
            'kardex.adjust',

            'pos.access',
            'pos.sell',
            'pos.charge_orders',
            'pos.apply_discounts',

            'cash_register.open',
            'cash_register.close',
            'cash_register.withdraw',
            'cash_register.view_history',

            'reports.sales',
            'reports.work_orders',
            'reports.analytics',

            // Reportes individuales (Fase 0+)
            'reports.ventas-periodo',
            'reports.ventas-productos',
            'reports.ventas-pago',
            'reports.ventas-ticket',
            'reports.taller-productividad',
            'reports.taller-ciclo-vida',
            'reports.taller-sla',
            'reports.taller-cotizaciones',
            'reports.taller-dispositivos',
            'reports.inventario-valorizacion',
            'reports.inventario-rotacion',
            'reports.inventario-kardex',
            'reports.caja-cuadre',
            'reports.caja-flujo',
            'reports.clientes-retencion',
            'reports.clientes-top',
            'reports.cotizaciones-aprobacion',
            'reports.saas-mrr',
            'reports.saas-crecimiento',
            'reports.saas-uso',

            'settings.company',
            'settings.clauses',
            'settings.taxes',
            'settings.users',
            'settings.roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
