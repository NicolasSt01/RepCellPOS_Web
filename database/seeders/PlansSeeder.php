<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Básico',
            'slug' => 'basic',
            'description' => 'Perfecto para talleres pequeños que inician.',
            'price' => 99,
            'features' => [
                'work_orders' => true,
                'quotes' => true,
                'pos' => false,
                'notifications_email' => false,
                'notifications_whatsapp' => false,
                'reports_advanced' => true,
                'report.ventas-periodo' => true,
                'report.ventas-productos' => true,
                'report.ventas-pago' => true,
                'report.ventas-ticket' => true,
                'report.taller-productividad' => true,
                'report.taller-ciclo-vida' => true,
                'report.taller-sla' => true,
                'report.taller-cotizaciones' => true,
                'report.taller-dispositivos' => true,
                'report.inventario-valorizacion' => true,
                'report.inventario-rotacion' => true,
                'report.inventario-kardex' => true,
                'report.caja-cuadre' => true,
                'report.caja-flujo' => true,
                'report.clientes-retencion' => true,
                'report.clientes-top' => true,
                'report.cotizaciones-aprobacion' => true,
            ],
            'limits' => [
                'max_users' => 1,
                'max_clients' => 50,
                'max_monthly_work_orders' => 30,
                'storage_mb' => 100,
            ],
            'sort_order' => 1,
            'is_active' => true,
            'is_highlight' => false,
        ]);

        Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'Para talleres en crecimiento con más demanda.',
            'price' => 199,
            'features' => [
                'work_orders' => true,
                'quotes' => true,
                'pos' => true,
                'notifications_email' => true,
                'notifications_whatsapp' => false,
                'reports_advanced' => true,
                'report.ventas-periodo' => true,
                'report.ventas-productos' => true,
                'report.ventas-pago' => true,
                'report.ventas-ticket' => true,
                'report.taller-productividad' => true,
                'report.taller-ciclo-vida' => true,
                'report.taller-sla' => true,
                'report.taller-cotizaciones' => true,
                'report.taller-dispositivos' => true,
                'report.inventario-valorizacion' => true,
                'report.inventario-rotacion' => true,
                'report.inventario-kardex' => true,
                'report.caja-cuadre' => true,
                'report.caja-flujo' => true,
                'report.clientes-retencion' => true,
                'report.clientes-top' => true,
                'report.cotizaciones-aprobacion' => true,
            ],
            'limits' => [
                'max_users' => 3,
                'max_clients' => -1,
                'max_monthly_work_orders' => -1,
                'storage_mb' => 500,
            ],
            'sort_order' => 2,
            'is_active' => true,
            'is_highlight' => true,
        ]);

        Plan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'description' => 'Máximo poder para talleres establecidos.',
            'price' => 349,
            'features' => [
                'work_orders' => true,
                'quotes' => true,
                'pos' => true,
                'notifications_email' => true,
                'notifications_whatsapp' => true,
                'reports_advanced' => true,
                'report.ventas-periodo' => true,
                'report.ventas-productos' => true,
                'report.ventas-pago' => true,
                'report.ventas-ticket' => true,
                'report.taller-productividad' => true,
                'report.taller-ciclo-vida' => true,
                'report.taller-sla' => true,
                'report.taller-cotizaciones' => true,
                'report.taller-dispositivos' => true,
                'report.inventario-valorizacion' => true,
                'report.inventario-rotacion' => true,
                'report.inventario-kardex' => true,
                'report.caja-cuadre' => true,
                'report.caja-flujo' => true,
                'report.clientes-retencion' => true,
                'report.clientes-top' => true,
                'report.cotizaciones-aprobacion' => true,
            ],
            'limits' => [
                'max_users' => 10,
                'max_clients' => -1,
                'max_monthly_work_orders' => -1,
                'storage_mb' => 2000,
            ],
            'sort_order' => 3,
            'is_active' => true,
            'is_highlight' => false,
        ]);
    }
}
