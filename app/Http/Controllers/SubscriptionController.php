<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\R2StorageService;
use App\Services\StripeService;
use Stripe\BillingPortal\Session as PortalSession;

class SubscriptionController extends Controller
{
    public function __construct(
        private StripeService $stripeService
    ) {}

    public function suspended()
    {
        $tenant = Auth::user()->tenant;
        $admin = User::where('tenant_id', $tenant->id)
            ->role('admin_tenant')
            ->first();

        return view('subscription.suspended', compact('tenant', 'admin'));
    }

    public function upgrade()
    {
        $tenant = Auth::user()->tenant;
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $currentPlan = $tenant->plan;
        $pendingPayment = TenantSubscription::where('tenant_id', $tenant->id)
            ->whereIn('status', ['pendiente', 'activa'])
            ->latest()
            ->first();

        return view('subscription.upgrade', compact('plans', 'tenant', 'currentPlan', 'pendingPayment'));
    }

    public function selectPlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $tenant = Auth::user()->tenant;
        $plan = Plan::findOrFail($validated['plan_id']);

        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'plan_type' => $plan->slug,
            'amount' => $plan->price,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'pendiente',
        ]);

        try {
            $session = $this->stripeService->createCheckoutSession($tenant, $plan, $subscription);
            return redirect($session->url);
        } catch (\Exception $e) {
            $subscription->delete();
            return redirect()->route('subscription.upgrade')
                ->with('error', 'Error al procesar el pago. Intenta de nuevo.');
        }
    }

    public function portal()
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant->stripe_customer_id) {
            return redirect()->route('subscription.upgrade')
                ->with('error', 'No tienes una suscripción activa.');
        }

        $session = PortalSession::create([
            'customer' => $tenant->stripe_customer_id,
            'return_url' => route('subscription.upgrade'),
        ]);

        return redirect($session->url);
    }

    public function cancel()
    {
        $tenant = Auth::user()->tenant;

        $subscription = TenantSubscription::where('tenant_id', $tenant->id)
            ->where('paid_via', 'stripe')
            ->where('status', 'activa')
            ->latest()
            ->first();

        if (!$subscription || !$subscription->stripe_subscription_id) {
            return redirect()->route('subscription.upgrade')
                ->with('error', 'No tienes una suscripción activa con Stripe.');
        }

        $this->stripeService->cancelSubscription(
            $subscription->stripe_subscription_id,
            $subscription
        );

        return redirect()->route('subscription.upgrade')
            ->with('success', 'Tu suscripción se cancelará al final del período actual (' .
                $subscription->next_payment_date->format('d/m/Y') . '). Seguirás teniendo acceso hasta esa fecha.');
    }

    public function resume()
    {
        $tenant = Auth::user()->tenant;

        $subscription = TenantSubscription::where('tenant_id', $tenant->id)
            ->where('paid_via', 'stripe')
            ->where('status', 'activa')
            ->latest()
            ->first();

        if (!$subscription || !$subscription->stripe_subscription_id) {
            return redirect()->route('subscription.upgrade')
                ->with('error', 'No tienes una suscripción activa con Stripe.');
        }

        $this->stripeService->resumeSubscription(
            $subscription->stripe_subscription_id,
            $subscription
        );

        return redirect()->route('subscription.upgrade')
            ->with('success', 'Tu suscripción se ha reactivado. Se renovará automáticamente.');
    }

    public function uploadProof(Request $request, R2StorageService $r2)
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:tenant_subscriptions,id',
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $subscription = TenantSubscription::findOrFail($validated['subscription_id']);

        $path = $r2->upload($request->file('payment_proof'), 'payment-proofs');
        $subscription->update([
            'payment_proof' => $path,
            'paid_via' => 'transfer',
        ]);

        return redirect()->route('subscription.upgrade')
            ->with('success', 'Comprobante subido. El administrador lo revisará para activar tu plan.');
    }
}
