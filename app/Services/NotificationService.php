<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Config;

class NotificationService
{
    public function send(WorkOrder $workOrder, string $event, ?string $customMessage = null, array $metadata = []): ?Notification
    {
        $client = $workOrder->client;

        if (!$client) {
            return null;
        }

        $channel = $client->notification_preference;
        $message = $customMessage ?? $this->getMessage($event, $workOrder, $channel);
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

        $this->dispatch($notification, $client, $workOrder, $metadata);

        return $notification;
    }

    protected function dispatch(Notification $notification, Client $client, WorkOrder $workOrder, array $metadata = []): void
    {
        match ($client->notification_preference) {
            'email' => $this->sendEmail($notification, $client, $workOrder, $metadata),
            'whatsapp' => $this->sendWhatsapp($notification, $client, $workOrder),
            'call' => $notification->markAsLogged(),
            default => $notification->markAsFailed('Canal no soportado'),
        };
    }

    protected function sendEmail(Notification $notification, Client $client, WorkOrder $workOrder, array $metadata = []): void
    {
        if (!$client->email) {
            $notification->markAsFailed('Cliente sin email');
            return;
        }

        try {
            $tenant = $workOrder->tenant;
            $isLogMailer = Config::get('mail.default') === 'log';

            if (!$isLogMailer && (!$tenant->mail_host || !$tenant->mail_username || !$tenant->mail_password)) {
                $notification->markAsLogged('SMTP no configurado');
                return;
            }

            if (!$isLogMailer) {
                app(TenantMailService::class)->configureForTenant($tenant);
            }

            $mailable = match ($notification->event) {
                'order_created' => new \App\Mail\WorkOrderReceipt($workOrder, $tenant),
                'status_changed' => new \App\Mail\WorkOrderStatusChanged($workOrder, $tenant, $metadata['to_status'] ?? $workOrder->status, $metadata['comment'] ?? null),
                'quote_sent' => new \App\Mail\WorkOrderStatusChanged($workOrder, $tenant, 'cotizacion_enviada'),
                'quote_approved' => new \App\Mail\WorkOrderStatusChanged($workOrder, $tenant, 'cotizacion_aprobada'),
                'quote_rejected' => new \App\Mail\WorkOrderStatusChanged($workOrder, $tenant, 'cancelada'),
                'repair_completed' => new \App\Mail\WorkOrderStatusChanged($workOrder, $tenant, 'reparada'),
                'ready_for_pickup' => new \App\Mail\WorkOrderStatusChanged($workOrder, $tenant, 'reparada'),
                'pickup_reminder' => new \App\Mail\WorkOrderStatusChanged($workOrder, $tenant, 'reparada', 'Recordatorio: su equipo sigue listo para recoger'),
                default => new \App\Mail\WorkOrderStatusChanged($workOrder, $tenant, $notification->event),
            };

            \Illuminate\Support\Facades\Mail::to($client->email)->send($mailable);
            $notification->markAsSent('Email sent successfully');
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            \Illuminate\Support\Facades\Log::error('Error sending notification email: ' . $e->getMessage());
        }
    }

    protected function sendWhatsapp(Notification $notification, Client $client, WorkOrder $workOrder): void
    {
        try {
            $tenant = $workOrder->tenant;
            $webhookUrl = $tenant->whatsapp_webhook_url;

            if (!$webhookUrl) {
                $notification->markAsLogged('WhatsApp webhook no configurado');
                return;
            }

            $phone = preg_replace('/[^0-9]/', '', $client->phone);
            if (!$phone) {
                $notification->markAsFailed('Cliente sin teléfono válido');
                return;
            }

            $message = $notification->message;

            \Illuminate\Support\Facades\Http::post($webhookUrl, [
                'phone' => $phone,
                'message' => $message,
                'work_order_id' => $workOrder->id,
                'work_order_number' => $workOrder->work_order_number,
                'event' => $notification->event,
                'tenant_id' => $tenant->id,
            ]);

            $notification->markAsSent('WhatsApp message queued to n8n');
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            \Illuminate\Support\Facades\Log::error('Error sending WhatsApp notification: ' . $e->getMessage());
        }
    }

    protected function getMessage(string $event, WorkOrder $workOrder, string $channel): string
    {
        $template = NotificationTemplate::getTemplate($workOrder->tenant_id, $event, $channel);

        if ($template) {
            return $template->replacePlaceholders($workOrder);
        }

        return $this->getDefaultMessage($event, $workOrder);
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
                'pickup_reminder' => "Recordatorio: tu equipo sigue listo para recoger. Orden: {$workOrder->work_order_number}. {$trackingUrl}",
                default => "Actualización en tu orden {$workOrder->work_order_number}. {$trackingUrl}",
        };
    }
}
