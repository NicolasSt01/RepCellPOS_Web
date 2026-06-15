<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkOrderReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public WorkOrder $workOrder;
    public Tenant $tenant;

    public function __construct(WorkOrder $workOrder, Tenant $tenant)
    {
        $this->workOrder = $workOrder;
        $this->tenant = $tenant;
    }

    public function envelope(): Envelope
    {
        $number = $this->workOrder->work_order_number;
        return new Envelope(
            subject: "Orden de Trabajo #{$number} - {$this->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.work_orders.receipt',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
