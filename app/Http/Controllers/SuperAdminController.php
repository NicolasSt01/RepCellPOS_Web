<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->count();
        $totalUsers = User::where('is_superadmin', false)->count();
        $totalClients = Client::count();
        $totalProducts = Product::count();
        $totalWorkOrders = WorkOrder::count();
        $totalSales = Sale::count();

        $tenants = Tenant::withCount('users')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $recentTenants = Tenant::orderBy('created_at', 'desc')->take(5)->get();

        return view('superadmin.dashboard', compact(
            'totalTenants', 'activeTenants', 'totalUsers',
            'totalClients', 'totalProducts', 'totalWorkOrders',
            'totalSales', 'tenants', 'recentTenants'
        ));
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
}
