<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\WorkOrder;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $clientsCount = Client::count();
        $activeOrdersCount = WorkOrder::whereNotIn('status', ['terminada', 'cancelada'])->count();
        $salesToday = Sale::whereDate('created_at', today())->sum('total');
        $productsCount = Product::where('is_active', true)->count();

        // Work order KPIs
        $pendingOrders = WorkOrder::whereIn('status', ['en_espera', 'recibida'])->count();
        $inRepairOrders = WorkOrder::where('status', 'en_reparacion')->count();
        $completedThisMonth = WorkOrder::where('status', 'terminada')
            ->whereMonth('updated_at', now()->month)
            ->count();

        $unassignedOrders = WorkOrder::whereNull('assigned_to')
            ->whereNotIn('status', ['terminada', 'cancelada'])
            ->count();

        // Technician workload (top 5)
        $technicianWorkload = WorkOrder::selectRaw('assigned_to, count(*) as total')
            ->whereNotNull('assigned_to')
            ->whereNotIn('status', ['terminada', 'cancelada'])
            ->groupBy('assigned_to')
            ->with('assignedTechnician')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $recentOrders = WorkOrder::with(['client', 'assignedTechnician'])
            ->latest()
            ->take(5)
            ->get();

        // Productos con stock bajo mínimo
        $tenant = Auth::user()->tenant;
        $lowStockProducts = collect();
        if ($tenant && $tenant->hasFeature('notifications_low_stock')) {
            $lowStockProducts = Product::where('type', 'producto')
                ->where('is_active', true)
                ->whereColumn('stock', '<=', 'min_stock')
                ->where('min_stock', '>', 0)
                ->orderBy('stock')
                ->take(10)
                ->get();
        }

        // Distribución de órdenes por estado
        $ordersByStatus = WorkOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $statuses = [
            'recibida' => 'Recibida',
            'en_espera' => 'En Espera',
            'en_revision' => 'En Revisión',
            'diagnosticada' => 'Diagnosticada',
            'cotizacion_enviada' => 'Cotización Enviada',
            'cotizacion_aprobada' => 'Cotización Aprobada',
            'en_reparacion' => 'En Reparación',
            'reparada' => 'Reparada',
            'terminada' => 'Terminada/Entregada',
            'cancelada' => 'Cancelada'
        ];

        $orderStatusChartData = [];
        foreach ($statuses as $key => $label) {
            $orderStatusChartData[] = [
                'status' => $key,
                'label' => $label,
                'count' => $ordersByStatus[$key] ?? 0
            ];
        }

        // Ventas semanales (últimos 7 días)
        $weeklySalesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayName = ucfirst(substr($date->isoFormat('dddd'), 0, 3));
            $amount = Sale::whereDate('created_at', $date->toDateString())->sum('total');
            $weeklySalesData[] = [
                'day' => $dayName,
                'amount' => floatval($amount),
                'date' => $date->format('d/m')
            ];
        }

        return view('dashboard', compact(
            'clientsCount',
            'activeOrdersCount',
            'salesToday',
            'productsCount',
            'pendingOrders',
            'inRepairOrders',
            'completedThisMonth',
            'unassignedOrders',
            'technicianWorkload',
            'recentOrders',
            'orderStatusChartData',
            'weeklySalesData',
            'lowStockProducts'
        ));
    }
}

