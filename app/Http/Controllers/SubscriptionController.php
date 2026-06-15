<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\TenantSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\R2StorageService;

class SubscriptionController extends Controller
{
    public function upgrade()
    {
        $tenant = Auth::user()->tenant;
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $currentPlan = $tenant->plan;
        $pendingPayment = TenantSubscription::where('tenant_id', $tenant->id)
            ->where('status', 'pendiente')
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

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'plan_type' => $plan->slug,
            'amount' => $plan->price,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'pendiente',
        ]);

        return redirect()->route('subscription.upgrade')
            ->with('success', "Has seleccionado el plan {$plan->name}. Realiza el pago para activarlo.");
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
