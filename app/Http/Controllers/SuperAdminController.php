<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->count();
        $trialTenants = Tenant::where('subscription_status', 'trial')->count();
        $expiredTrials = Tenant::where(function ($q) {
            $q->where('subscription_status', 'expired')
              ->orWhere(function ($q2) {
                  $q2->whereNotNull('trial_ends_at')
                      ->where('trial_ends_at', '<', now());
              });
        })->count();
        $activeSubscriptions = Tenant::where('subscription_status', 'active')->count();
        $totalUsers = User::where('is_superadmin', false)->count();

        $mrr = TenantSubscription::where('status', 'activa')->sum('amount');

        $previousMonthMrr = TenantSubscription::where('status', 'activa')
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('amount');

        $mrrChange = $previousMonthMrr > 0
            ? round((($mrr - $previousMonthMrr) / $previousMonthMrr) * 100, 1)
            : 0;

        $planDistribution = Plan::withCount(['tenants' => function ($q) {
            $q->where('is_active', true);
        }])->where('is_active', true)->orderBy('sort_order')->get();

        $expiringSoon = TenantSubscription::where('status', 'activa')
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->addDays(7))
            ->with('tenant.plan')
            ->take(10)
            ->get();

        $monthlyRevenue = TenantSubscription::where('status', 'activa')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $dateFormat = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $revenueHistory = TenantSubscription::selectRaw("{$dateFormat} as month, SUM(amount) as total")
            ->where('status', 'activa')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $recentTenants = Tenant::orderBy('created_at', 'desc')->take(5)->get();
        $pendingPayments = TenantSubscription::where('status', 'pendiente')
            ->whereNotNull('payment_proof')
            ->with('tenant')
            ->latest()
            ->take(10)
            ->get();

        return view('superadmin.dashboard', compact(
            'totalTenants', 'activeTenants', 'trialTenants', 'expiredTrials',
            'activeSubscriptions', 'mrr', 'mrrChange', 'monthlyRevenue',
            'totalUsers', 'planDistribution', 'expiringSoon',
            'revenueHistory', 'recentTenants', 'pendingPayments'
        ));
    }

    public function finances()
    {
        $totalRevenue = TenantSubscription::whereIn('status', ['activa', 'pendiente'])->sum('amount');
        $confirmedRevenue = TenantSubscription::where('status', 'activa')->sum('amount');
        $pendingRevenue = TenantSubscription::where('status', 'pendiente')->sum('amount');
        $mrr = TenantSubscription::where('status', 'activa')->sum('amount');

        $dateFormat = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthlyCollection = TenantSubscription::selectRaw("{$dateFormat} as month, SUM(amount) as total")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $recentPayments = TenantSubscription::whereNotNull('paid_via')
            ->with('tenant', 'plan')
            ->latest()
            ->take(20)
            ->get();

        $pendingPayments = TenantSubscription::where('status', 'pendiente')
            ->whereNotNull('payment_proof')
            ->with('tenant', 'plan')
            ->latest()
            ->take(20)
            ->get();

        $expiredTenants = Tenant::where('subscription_status', 'expired')
            ->latest()
            ->take(20)
            ->get();

        return view('superadmin.finances', compact(
            'totalRevenue', 'confirmedRevenue', 'pendingRevenue', 'mrr',
            'monthlyCollection', 'recentPayments', 'pendingPayments', 'expiredTenants'
        ));
    }

    public function confirmPayment($id)
    {
        $subscription = TenantSubscription::with('tenant')->findOrFail($id);
        $subscription->update(['status' => 'activa']);

        $tenant = $subscription->tenant;
        if ($tenant) {
            $tenant->update([
                'plan_id' => $subscription->plan_id,
                'subscription_status' => 'active',
                'trial_ends_at' => null,
            ]);
        }

        return redirect()->back()->with('success', 'Pago confirmado exitosamente.');
    }

    public function rejectPayment($id)
    {
        $subscription = TenantSubscription::findOrFail($id);
        $subscription->update(['status' => 'rechazado']);

        return redirect()->back()->with('success', 'Pago rechazado.');
    }

    public function exportFinances()
    {
        $subscriptions = TenantSubscription::with('tenant', 'plan')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="finanzas.csv"',
        ];

        $callback = function () use ($subscriptions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Tenant', 'Plan', 'Monto', 'Estado', 'Método de Pago', 'Fecha']);

            foreach ($subscriptions as $sub) {
                fputcsv($file, [
                    $sub->id,
                    $sub->tenant?->name ?? 'N/A',
                    $sub->plan?->name ?? 'N/A',
                    number_format((float) $sub->amount, 2, '.', ''),
                    $sub->status,
                    $sub->paid_via ?? 'N/A',
                    $sub->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function tenants(Request $request)
    {
        $query = Tenant::withCount('users');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $tenants = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('superadmin.tenants.index', compact('tenants'));
    }

    public function tenantDetail(Tenant $tenant)
    {
        $tenant->loadCount(['users', 'clients', 'workOrders', 'sales']);
        $tenant->load('subscription');

        $recentWorkOrders = $tenant->workOrders()->latest()->take(5)->get();
        $recentSales = $tenant->sales()->latest()->take(5)->get();

        return view('superadmin.tenants.show', compact('tenant', 'recentWorkOrders', 'recentSales'));
    }

    public function toggleTenantStatus(Tenant $tenant)
    {
        $tenant->update(['is_active' => !$tenant->is_active]);

        $status = $tenant->fresh()->is_active ? 'activado' : 'desactivado';

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', "Tenant {$tenant->name} {$status} exitosamente.");
    }

    public function subscriptionCreate(Tenant $tenant)
    {
        return view('superadmin.subscriptions.create', compact('tenant'));
    }

    public function subscriptionStore(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan_type' => 'required|string|in:mensual,anual,prueba,personalizado',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string|in:activa,pendiente,expirada,cancelada',
            'notes' => 'nullable|string',
        ]);

        $tenant->subscription()->create($validated);

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', 'Suscripción creada exitosamente.');
    }

    public function subscriptionEdit(Tenant $tenant, TenantSubscription $subscription)
    {
        return view('superadmin.subscriptions.edit', compact('tenant', 'subscription'));
    }

    public function subscriptionUpdate(Request $request, Tenant $tenant, TenantSubscription $subscription)
    {
        $validated = $request->validate([
            'plan_type' => 'required|string|in:mensual,anual,prueba,personalizado',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string|in:activa,pendiente,expirada,cancelada',
            'notes' => 'nullable|string',
        ]);

        $subscription->update($validated);

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', 'Suscripción actualizada exitosamente.');
    }

    public function subscriptionPay(Request $request, Tenant $tenant, TenantSubscription $subscription)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
        ]);

        $subscription->markAsPaid($validated['amount'], $validated['reference']);

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', 'Pago registrado exitosamente.');
    }

    public function plans()
    {
        $plans = Plan::orderBy('sort_order')->get();
        return view('superadmin.plans.index', compact('plans'));
    }

    public function planCreate()
    {
        return view('superadmin.plans.create');
    }

    public function planStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
            'limits' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_highlight' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['features'] = $validated['features'] ? array_map('trim', explode("\n", $validated['features'])) : [];
        $validated['limits'] = $validated['limits'] ? json_decode($validated['limits'], true) : [];
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_highlight'] = $request->boolean('is_highlight');

        Plan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan creado exitosamente.');
    }

    public function planEdit(Plan $plan)
    {
        return view('superadmin.plans.edit', compact('plan'));
    }

    public function planUpdate(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
            'limits' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_highlight' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['features'] = $validated['features'] ? array_map('trim', explode("\n", $validated['features'])) : [];
        $validated['limits'] = $validated['limits'] ? json_decode($validated['limits'], true) : [];
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_highlight'] = $request->boolean('is_highlight');

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan actualizado exitosamente.');
    }

    public function planDestroy(Plan $plan)
    {
        $tenantCount = Tenant::where('plan_id', $plan->id)->count();
        if ($tenantCount > 0) {
            return redirect()->route('admin.plans.index')
                ->with('error', "No se puede eliminar el plan \"{$plan->name}\" porque {$tenantCount} tenant(s) lo tienen asignado.");
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan eliminado exitosamente.');
    }
}
