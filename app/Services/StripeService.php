<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Price as StripePrice;
use Stripe\Product as StripeProduct;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\Webhook;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\Plan;
use App\Mail\SubscriptionConfirmation;
use App\Mail\InvoiceReceipt;
use App\Services\TenantMailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCustomer(Tenant $tenant): string
    {
        if ($tenant->stripe_customer_id) {
            return $tenant->stripe_customer_id;
        }

        $customer = Customer::create([
            'name' => $tenant->name,
            'email' => $tenant->email,
            'metadata' => [
                'tenant_id' => (string) $tenant->id,
                'tenant_slug' => $tenant->slug,
            ],
        ]);

        $tenant->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    public function getOrCreateStripePrice(Plan $plan): string
    {
        if ($plan->stripe_price_id) {
            return $plan->stripe_price_id;
        }

        $product = StripeProduct::create([
            'name' => $plan->name,
            'description' => $plan->description,
            'active' => true,
            'metadata' => [
                'plan_id' => (string) $plan->id,
                'plan_slug' => $plan->slug,
            ],
        ]);

        $price = StripePrice::create([
            'product' => $product->id,
            'currency' => 'mxn',
            'unit_amount' => (int) ($plan->price * 100),
            'recurring' => [
                'interval' => 'month',
            ],
            'metadata' => [
                'plan_id' => (string) $plan->id,
                'plan_slug' => $plan->slug,
            ],
        ]);

        $plan->update([
            'stripe_product_id' => $product->id,
            'stripe_price_id' => $price->id,
        ]);

        return $price->id;
    }

    public function createCheckoutSession(Tenant $tenant, Plan $plan, TenantSubscription $subscription): Session
    {
        $customerId = $this->createCustomer($tenant);
        $priceId = $this->getOrCreateStripePrice($plan);

        return Session::create([
            'customer' => $customerId,
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'metadata' => [
                'subscription_id' => (string) $subscription->id,
                'tenant_id' => (string) $tenant->id,
                'plan_slug' => $plan->slug,
            ],
            'success_url' => route('subscription.upgrade') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('subscription.upgrade'),
            'locale' => 'es',
        ]);
    }

    public function handleWebhook(string $payload, string $sigHeader): void
    {
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook: payload inválido', ['error' => $e->getMessage()]);
            throw $e;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe webhook: firma inválida', ['error' => $e->getMessage()]);
            throw $e;
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'invoice.payment_succeeded' => $this->handleInvoicePaid($event->data->object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
            default => Log::info('Stripe webhook: evento no manejado', ['type' => $event->type]),
        };
    }

    protected function handleCheckoutCompleted(mixed $session): void
    {
        $subscriptionId = $session->metadata->subscription_id ?? null;
        if (!$subscriptionId) return;

        $subscription = TenantSubscription::find($subscriptionId);
        if (!$subscription) return;

        $stripeSubscription = Subscription::retrieve($session->subscription);

        $subscription->update([
            'stripe_subscription_id' => $session->subscription,
            'stripe_price_id' => $session->mode === 'subscription'
                ? ($stripeSubscription->items->data[0]->price->id ?? null)
                : null,
            'status' => 'activa',
            'paid_via' => 'stripe',
            'payment_proof' => null,
            'last_payment_date' => now()->toDateString(),
            'next_payment_date' => $stripeSubscription->current_period_end
                ? now()->createFromTimestamp($stripeSubscription->current_period_end)->toDateString()
                : null,
        ]);

        $tenant = $subscription->tenant;
        if ($tenant) {
            $tenant->update([
                'plan_id' => $subscription->plan_id,
                'subscription_status' => 'active',
                'trial_ends_at' => null,
            ]);
        }

        $this->sendSubscriptionConfirmation($tenant, $subscription);
    }

    protected function handleInvoicePaid(mixed $invoice): void
    {
        $subscriptionId = $invoice->subscription;
        if (!$subscriptionId) return;

        $subscription = TenantSubscription::where('stripe_subscription_id', $subscriptionId)->first();
        if (!$subscription) return;

        $history = $subscription->payment_history ?? [];
        $history[] = [
            'date' => now()->toDateString(),
            'amount' => $invoice->amount_paid / 100,
            'reference' => $invoice->id,
            'stripe_invoice' => $invoice->id,
        ];

        $subscription->update([
            'last_payment_date' => now()->toDateString(),
            'next_payment_date' => isset($invoice->lines->data[0]->period->end)
                ? now()->createFromTimestamp($invoice->lines->data[0]->period->end)->toDateString()
                : null,
            'status' => 'activa',
            'payment_history' => $history,
        ]);

        $tenant = $subscription->tenant;
        if ($tenant) {
            $tenant->update(['subscription_status' => 'active']);
        }

        $this->sendInvoiceReceipt($tenant, $subscription, $invoice);
    }

    protected function handleSubscriptionUpdated(mixed $stripeSubscription): void
    {
        $subscription = TenantSubscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        if (!$subscription) return;

        $updateData = [];

        if (in_array($stripeSubscription->status, ['past_due', 'unpaid'])) {
            $updateData['status'] = 'pendiente';
        } elseif ($stripeSubscription->status === 'active') {
            $updateData['status'] = 'activa';

            if ($stripeSubscription->cancel_at_period_end) {
                $updateData['cancel_at_period_end'] = true;
                // Also update next_payment_date to current_period_end (last paid day)
                if ($stripeSubscription->current_period_end) {
                    $updateData['next_payment_date'] = now()->createFromTimestamp($stripeSubscription->current_period_end)->toDateString();
                }
            } else {
                $updateData['cancel_at_period_end'] = false;
            }
        } elseif (in_array($stripeSubscription->status, ['canceled', 'incomplete_expired'])) {
            $updateData['status'] = 'cancelada';
            $updateData['cancel_at_period_end'] = false;
            $tenant = $subscription->tenant;
            if ($tenant) {
                $tenant->update(['subscription_status' => 'expired']);
            }
        }

        if (!empty($updateData)) {
            $subscription->update($updateData);
        }
    }

    protected function handleSubscriptionDeleted(mixed $stripeSubscription): void
    {
        $subscription = TenantSubscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        if (!$subscription) return;

        $subscription->update(['status' => 'cancelada']);

        $tenant = $subscription->tenant;
        if ($tenant) {
            $tenant->update(['subscription_status' => 'expired']);
        }
    }

    private function sendTenantMail(Tenant $tenant, \Illuminate\Mail\Mailable $mailable): void
    {
        try {
            $hasSmtp = $tenant->mail_host && $tenant->mail_username && $tenant->mail_password;

            if ($hasSmtp) {
                app(TenantMailService::class)->configureForTenant($tenant);
                Config::set('mail.default', 'smtp');
            }

            Mail::to($tenant->email)->send($mailable);
        } catch (\Exception $e) {
            Log::warning('Stripe: no se pudo enviar correo', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendSubscriptionConfirmation(?Tenant $tenant, TenantSubscription $subscription): void
    {
        if (!$tenant || !$tenant->email) return;

        $plan = $subscription->plan;
        if (!$plan) return;

        $this->sendTenantMail(
            $tenant,
            new SubscriptionConfirmation($tenant, $subscription, $plan)
        );
    }

    private function sendInvoiceReceipt(?Tenant $tenant, TenantSubscription $subscription, mixed $invoice): void
    {
        if (!$tenant || !$tenant->email) return;

        $plan = $subscription->plan;
        if (!$plan) return;

        $period = $invoice->lines->data[0]->period ?? null;

        $this->sendTenantMail(
            $tenant,
            new InvoiceReceipt(
                tenant: $tenant,
                subscription: $subscription,
                plan: $plan,
                invoiceReference: $invoice->id ?? 'N/A',
                paidDate: now(),
                periodStart: $period ? now()->createFromTimestamp($period->start) : now(),
                periodEnd: $period ? now()->createFromTimestamp($period->end) : now()->addMonth(),
                nextPaymentDate: $subscription->next_payment_date ?? now()->addMonth(),
            )
        );
    }

    public function cancelSubscription(string $stripeSubscriptionId, TenantSubscription $localSubscription): void
    {
        Subscription::update($stripeSubscriptionId, [
            'cancel_at_period_end' => true,
        ]);

        $localSubscription->update(['cancel_at_period_end' => true]);
    }

    public function resumeSubscription(string $stripeSubscriptionId, TenantSubscription $localSubscription): void
    {
        Subscription::update($stripeSubscriptionId, [
            'cancel_at_period_end' => false,
        ]);

        $localSubscription->update(['cancel_at_period_end' => false]);
    }
}
