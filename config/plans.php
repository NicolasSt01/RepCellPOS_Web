<?php

return [
    'features' => [
        'work_orders' => 'Ordenes de Trabajo',
        'quotes' => 'Cotizaciones',
        'pos' => 'Punto de Venta (POS)',
        'notifications_email' => 'Notificaciones Email',
        'notifications_whatsapp' => 'Notificaciones WhatsApp',
        'reports_advanced' => 'Reportes Avanzados',
    ],
    'limits' => [
        'max_users' => ['label' => 'Usuarios', 'suffix' => '', 'unlimited' => true],
        'max_clients' => ['label' => 'Clientes', 'suffix' => '', 'unlimited' => true],
        'max_monthly_work_orders' => ['label' => 'OT por mes', 'suffix' => '', 'unlimited' => true],
        'storage_mb' => ['label' => 'Almacenamiento', 'suffix' => 'MB', 'unlimited' => false],
    ],
];
