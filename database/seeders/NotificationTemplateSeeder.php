<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    protected array $defaults = [
        'order_created' => [
            'email' => [
                'subject' => 'Orden {work_order_number} recibida — {client_name}',
                'body' => "Estimado {client_name},\n\nTu equipo ha sido recibido en nuestro taller. El número de orden es {work_order_number}.\n\nPuedes dar seguimiento aquí: {tracking_url}\n\nGracias por tu preferencia.",
            ],
            'whatsapp' => [
                'body' => "Estimado {client_name}, tu equipo ya está en nuestro taller. Orden: {work_order_number}. Da seguimiento aquí: {tracking_url}",
            ],
        ],
        'diagnosis_completed' => [
            'email' => [
                'subject' => 'Diagnóstico completado — Orden {work_order_number}',
                'body' => "Estimado {client_name},\n\nHemos terminado el diagnóstico de tu equipo (Orden {work_order_number}).\n\nRevisa los detalles aquí: {tracking_url}\n\nQuedamos atentos a tu autorización.",
            ],
            'whatsapp' => [
                'body' => "Estimado {client_name}, el diagnóstico de tu equipo está listo. Orden: {work_order_number}. Detalles: {tracking_url}",
            ],
        ],
        'quote_sent' => [
            'email' => [
                'subject' => 'Cotización disponible — Orden {work_order_number}',
                'body' => "Estimado {client_name},\n\nHemos preparado la cotización para tu equipo (Orden {work_order_number}).\n\nPuedes revisarla y aprobarla aquí: {tracking_url}\n\nQuedamos a tus órdenes.",
            ],
            'whatsapp' => [
                'body' => "Estimado {client_name}, tu cotización está lista. Orden: {work_order_number}. Revisa y aprueba aquí: {tracking_url}",
            ],
        ],
        'quote_approved' => [
            'email' => [
                'subject' => 'Cotización aprobada — Orden {work_order_number}',
                'body' => "Estimado {client_name},\n\nGracias por aprobar la cotización. Ya estamos trabajando en tu equipo (Orden {work_order_number}).\n\nSigue el avance aquí: {tracking_url}",
            ],
            'whatsapp' => [
                'body' => "Estimado {client_name}, cotización aprobada. Ya trabajamos en tu equipo. Orden: {work_order_number}. Avance: {tracking_url}",
            ],
        ],
        'quote_rejected' => [
            'email' => [
                'subject' => 'Cotización rechazada — Orden {work_order_number}',
                'body' => "Estimado {client_name},\n\nHemos recibido tu decisión sobre la cotización de la Orden {work_order_number}.\n\nSi cambias de opinión, puedes contactarnos. Tu equipo está resguardado en nuestro taller.\n\nGracias.",
            ],
            'whatsapp' => [
                'body' => "Estimado {client_name}, hemos recibido tu decisión sobre la cotización. Orden: {work_order_number}. Quedamos a tus órdenes.",
            ],
        ],
        'repair_completed' => [
            'email' => [
                'subject' => '¡Reparación completada! — Orden {work_order_number}',
                'body' => "Estimado {client_name},\n\nLa reparación de tu equipo ha sido completada (Orden {work_order_number}).\n\nYa puedes pasar a recogerlo a nuestro taller.\n\n¡Gracias por confiar en nosotros!",
            ],
            'whatsapp' => [
                'body' => "Estimado {client_name}, tu equipo está reparado. Orden: {work_order_number}. Puedes pasar a recogerlo. ¡Gracias!",
            ],
        ],
        'ready_for_pickup' => [
            'email' => [
                'subject' => 'Listo para recoger — Orden {work_order_number}',
                'body' => "Estimado {client_name},\n\nTu equipo está listo para recoger (Orden {work_order_number}).\n\nTe esperamos en nuestro taller en horario laboral.\n\n¡Gracias por tu preferencia!",
            ],
            'whatsapp' => [
                'body' => "Estimado {client_name}, tu equipo está listo para recoger. Orden: {work_order_number}. ¡Te esperamos!",
            ],
        ],
    ];

    public function run(?int $tenantId = null): void
    {
        if ($tenantId) {
            $this->seedForTenant($tenantId);
        } else {
            $tenants = \App\Models\Tenant::all();
            foreach ($tenants as $tenant) {
                $this->seedForTenant($tenant->id);
            }
        }
    }

    public function seedForTenant(int $tenantId): void
    {
        foreach ($this->defaults as $event => $channels) {
            foreach ($channels as $channel => $data) {
                $existing = NotificationTemplate::where('tenant_id', $tenantId)
                    ->where('event', $event)
                    ->where('channel', $channel)
                    ->exists();

                if ($existing) {
                    continue;
                }

                NotificationTemplate::create([
                    'tenant_id' => $tenantId,
                    'event' => $event,
                    'channel' => $channel,
                    'subject' => $data['subject'] ?? null,
                    'body' => $data['body'],
                    'is_active' => true,
                ]);
            }
        }
    }

    public static function getDefaults(): array
    {
        return (new self)->defaults;
    }
}
