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

class SubscriptionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public TenantSubscription $subscription;
    public Plan $plan;
    public string $planName;
    public string $planDescription;
    public float $amount;
    public \DateTimeInterface $startDate;
    public \DateTimeInterface $nextPaymentDate;
    public array $features;

    public function __construct(Tenant $tenant, TenantSubscription $subscription, Plan $plan)
    {
        $this->tenant = $tenant;
        $this->subscription = $subscription;
        $this->plan = $plan;
        $this->planName = $plan->name;
        $this->planDescription = $plan->description;
        $this->amount = (float) $plan->price;
        $this->startDate = $subscription->start_date ?? now();
        $this->nextPaymentDate = $subscription->next_payment_date ?? now()->addMonth();
        $this->features = $plan->features ?? [];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "¡Bienvenido a {$this->planName}! Tu suscripción en RepCellPOS está activa",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscriptions.confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
