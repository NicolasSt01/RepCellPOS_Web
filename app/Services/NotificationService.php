<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Notification;
use App\Models\WorkOrder;

class NotificationService
{
    public function send(WorkOrder $workOrder, string $event, ?string $customMessage = null): ?Notification
    {
        $client = $workOrder->client;

        if (!$client) {
            return null;
        }

        $message = $customMessage ?? $this->getDefaultMessage($event, $workOrder);
        $trackingToken = $workOrder->tracking_token ?? Notification::generateTrackingToken();

        if (!$workOrder->tracking_token) {
            $workOrder->update(['tracking_token' => $trackingToken]);
        }

        $notification = Notification::create([
            'tenant_id' => $workOrder->tenant_id,
            'work_order_id' => $workOrder->id,
            'client_id' => $client->id,
            'channel' => $client->notification_preference,
            'event' => $event,
            'status' => 'pending',
            'message' => $message,
            'tracking_token' => $trackingToken,
        ]);

        $this->dispatch($notification, $client, $workOrder);

        return $notification;
    }

    protected function dispatch(Notification $notification, Client $client, WorkOrder $workOrder): void
    {
        match ($client->notification_preference) {
            'email' => $this->sendEmail($notification, $client, $workOrder),
            'whatsapp' => $this->sendWhatsapp($notification, $client, $workOrder),
            'call' => $notification->markAsLogged(),
            default => $notification->markAsFailed('Canal no soportado'),
        };
    }

    protected function sendEmail(Notification $notification, Client $client, WorkOrder $workOrder): void
    {
        if (!$client->email) {
            $notification->markAsFailed('Cliente sin email');
            return;
        }

        try {
            // TODO: Implementar envío real via Mail
            $notification->markAsSent('Email queued');
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
        }
    }

    protected function sendWhatsapp(Notification $notification, Client $client, WorkOrder $workOrder): void
    {
        try {
            // TODO: Implementar integración con WhatsApp Business API / n8n webhook
            $notification->markAsSent('WhatsApp queued');
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
        }
    }

    protected function getDefaultMessage(string $event, WorkOrder $workOrder): string
    {
        $trackingUrl = url("/seguimiento/{$workOrder->tracking_token}");

        return match ($event) {
            'order_created' => "Tu equipo ha sido recibido. Orden: {$workOrder->work_order_number}. Sigue el estado: {$trackingUrl}",
            'diagnosis_completed' => "El diagnóstico de tu equipo ha sido completado. Orden: {$workOrder->work_order_number}. {$trackingUrl}",
            'quote_sent' => "Te enviamos la cotización de tu reparación. Orden: {$workOrder->work_order_number}. {$trackingUrl}",
            'quote_approved' => "Tu cotización fue aprobada. Tu equipo está en reparación. Orden: {$workOrder->work_order_number}. {$trackingUrl}",
            'quote_rejected' => "La cotización fue rechazada. Orden: {$workOrder->work_order_number}. {$trackingUrl}",
            'repair_completed' => "La reparación de tu equipo fue completada. Orden: {$workOrder->work_order_number}. {$trackingUrl}",
            'ready_for_pickup' => "Tu equipo está listo para recoger. Orden: {$workOrder->work_order_number}. {$trackingUrl}",
            default => "Actualización en tu orden {$workOrder->work_order_number}. {$trackingUrl}",
        };
    }
}
