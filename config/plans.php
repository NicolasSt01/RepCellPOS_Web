<?php

return [
    'features' => [
        'work_orders' => 'Ordenes de Trabajo',
        'quotes' => 'Cotizaciones',
        'pos' => 'Punto de Venta (POS)',
        'notifications_email' => 'Notificaciones Email',
        'notifications_whatsapp' => 'Notificaciones WhatsApp',
        'notifications_low_stock' => 'Alertas de Stock Bajo',
        'reports_advanced' => 'Reportes Avanzados',

        // Reportes individuales (identificadores para bloqueo por plan)
        'report.ventas-periodo' => 'Reporte: Ventas por Período',
        'report.ventas-productos' => 'Reporte: Top Productos',
        'report.ventas-pago' => 'Reporte: Ventas por Método de Pago',
        'report.ventas-ticket' => 'Reporte: Ticket Promedio',
        'report.taller-productividad' => 'Reporte: Productividad por Técnico',
        'report.taller-ciclo-vida' => 'Reporte: Ciclo de Vida',
        'report.taller-sla' => 'Reporte: SLA',
        'report.taller-cotizaciones' => 'Reporte: Conversión Cotizaciones',
        'report.taller-dispositivos' => 'Reporte: Dispositivos más Reparados',
        'report.inventario-valorizacion' => 'Reporte: Valorización de Stock',
        'report.inventario-rotacion' => 'Reporte: Rotación de Inventario',
        'report.inventario-kardex' => 'Reporte: Kardex',
        'report.caja-cuadre' => 'Reporte: Cuadre de Caja',
        'report.caja-flujo' => 'Reporte: Flujo de Efectivo',
        'report.clientes-retencion' => 'Reporte: Retención de Clientes',
        'report.clientes-top' => 'Reporte: Top Clientes',
        'report.cotizaciones-aprobacion' => 'Reporte: Aprobación Cotizaciones',
        'report.saas-mrr' => 'Reporte: MRR/ARR',
        'report.saas-crecimiento' => 'Reporte: Crecimiento y Churn',
        'report.saas-uso' => 'Reporte: Uso por Tenant',
    ],
    'limits' => [
        'max_users' => ['label' => 'Usuarios', 'suffix' => '', 'unlimited' => true],
        'max_clients' => ['label' => 'Clientes', 'suffix' => '', 'unlimited' => true],
        'max_monthly_work_orders' => ['label' => 'OT por mes', 'suffix' => '', 'unlimited' => true],
        'storage_mb' => ['label' => 'Almacenamiento', 'suffix' => 'MB', 'unlimited' => false],
    ],
];
