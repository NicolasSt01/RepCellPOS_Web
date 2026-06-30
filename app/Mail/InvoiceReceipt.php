<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\Plan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public TenantSubscription $subscription;
    public Plan $plan;
    public string $planName;
    public float $amount;
    public string $invoiceReference;
    public \DateTimeInterface $paidDate;
    public \DateTimeInterface $periodStart;
    public \DateTimeInterface $periodEnd;
    public \DateTimeInterface $nextPaymentDate;

    public function __construct(
        Tenant $tenant,
        TenantSubscription $subscription,
        Plan $plan,
        string $invoiceReference,
        \DateTimeInterface $paidDate,
        \DateTimeInterface $periodStart,
        \DateTimeInterface $periodEnd,
        \DateTimeInterface $nextPaymentDate,
    ) {
        $this->tenant = $tenant;
        $this->subscription = $subscription;
        $this->plan = $plan;
        $this->planName = $plan->name;
        $this->amount = (float) $plan->price;
        $this->invoiceReference = $invoiceReference;
        $this->paidDate = $paidDate;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
        $this->nextPaymentDate = $nextPaymentDate;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Recibo de pago - {$this->planName} - RepCellPOS",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscriptions.invoice-receipt',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
