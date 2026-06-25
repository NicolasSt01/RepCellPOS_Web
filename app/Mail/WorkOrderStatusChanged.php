<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkOrderStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public WorkOrder $workOrder;
    public Tenant $tenant;
    public string $newStatus;
    public ?string $comment;

    public function __construct(WorkOrder $workOrder, Tenant $tenant, string $newStatus, ?string $comment = null)
    {
        $this->workOrder = $workOrder;
        $this->tenant = $tenant;
        $this->newStatus = $newStatus;
        $this->comment = $comment;
    }

    public function envelope(): Envelope
    {
        $statusName = match ($this->newStatus) {
            'recibida' => 'Recibida',
            'en_espera' => 'En espera',
            'en_revision' => 'En revisión',
            'diagnosticada' => 'Diagnosticada',
            'cotizacion_enviada' => 'Cotización enviada',
            'cotizacion_aprobada' => 'Cotización aprobada',
            'en_reparacion' => 'En reparación',
            'reparada' => 'Reparada',
            'terminada' => 'Terminada',
            'cancelada' => 'Cancelada',
            default => ucfirst($this->newStatus),
        };
        $number = $this->workOrder->work_order_number;
        return new Envelope(
            subject: "OT #{$number} - {$statusName} - {$this->tenant->name}",
        );
    }

    public function content(): Content
    {
        $statusName = match ($this->newStatus) {
            'recibida' => 'Recibida',
            'en_espera' => 'En espera',
            'en_revision' => 'En revisión',
            'diagnosticada' => 'Diagnosticada',
            'cotizacion_enviada' => 'Cotización enviada',
            'cotizacion_aprobada' => 'Cotización aprobada',
            'en_reparacion' => 'En reparación',
            'reparada' => 'Reparada',
            'terminada' => 'Terminada',
            'cancelada' => 'Cancelada',
            default => ucfirst($this->newStatus),
        };

        return new Content(
            view: 'emails.work_orders.status_changed',
            with: [
                'newStatus' => $statusName,
                'comment' => $this->comment,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
