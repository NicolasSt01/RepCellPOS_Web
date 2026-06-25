<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\WorkOrder;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendPickupReminders extends Command
{
    protected $signature = 'pickup:remind
        {--days=3 : Días sin que el cliente recoja desde que se notificó listo}
        {--dry-run : Solo mostrar cuáles se notificarían sin enviar}';

    protected $description = 'Envía recordatorio a clientes con equipos listos para recoger';

    public function handle(NotificationService $notifier): int
    {
        $days = (int) $this->option('days');
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $remindedWorkOrderIds = Notification::where('event', 'pickup_reminder')
            ->pluck('work_order_id')
            ->toArray();

        $readyOrderIds = Notification::where('event', 'ready_for_pickup')
            ->where('status', 'sent')
            ->pluck('work_order_id')
->toArray();

        $readyOrders = WorkOrder::with('client')
            ->where('status', 'reparada')
            ->whereIn('id', $readyOrderIds)
            ->whereNotIn('id', $remindedWorkOrderIds)
            ->where('updated_at', '<=', $cutoff)
            ->get();

        if ($readyOrders->isEmpty()) {
            $this->info('No hay órdenes pendientes de recordatorio.');
            return 0;
        }

        $this->info("Órdenes por recordar: {$readyOrders->count()}");

        foreach ($readyOrders as $workOrder) {
            $clientName = $workOrder->client?->name ?? 'N/A';
            $this->line("  OT #{$workOrder->work_order_number} — {$clientName} ({$workOrder->device_brand} {$workOrder->device_model})");

            if ($dryRun) {
                continue;
            }

            try {
                $notifier->send($workOrder, 'pickup_reminder');
                $this->line("    → Recordatorio enviado.");
            } catch (\Exception $e) {
                $this->error("    → Error: {$e->getMessage()}");
            }
        }

        return 0;
    }
}
