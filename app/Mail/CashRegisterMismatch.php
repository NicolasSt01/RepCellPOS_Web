<?php

namespace App\Mail;

use App\Models\CashRegister;
use App\Models\CashRegisterIncident;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CashRegisterMismatch extends Mailable
{
    use Queueable, SerializesModels;

    public CashRegister $cashRegister;
    public CashRegisterIncident $incident;
    public Tenant $tenant;

    public function __construct(CashRegister $cashRegister, CashRegisterIncident $incident, Tenant $tenant)
    {
        $this->cashRegister = $cashRegister;
        $this->incident = $incident;
        $this->tenant = $tenant;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Incidente en cierre de caja - {$this->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cash_register.mismatch',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
