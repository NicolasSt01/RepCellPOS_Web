<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\WorkOrder;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Métricas dinámicas, auto-scopadas por tenant
        $clientsCount = Client::count();
        $activeOrdersCount = WorkOrder::whereNotIn('status', ['terminada', 'cancelada'])->count();
        $salesToday = Sale::whereDate('created_at', today())->sum('total');
        $productsCount = Product::where('is_active', true)->count();

        // Actividad reciente: últimas 5 órdenes de trabajo con su cliente
        $recentOrders = WorkOrder::with('client')
            ->latest()
            ->take(5)
            ->get();

        // Datos para gráficas
        // 1. Distribución de órdenes por estado
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

        // 2. Ventas semanales (últimos 7 días)
        $weeklySalesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayName = $date->isoFormat('dddd'); // e.g. "lunes"
            $dayName = ucfirst(substr($dayName, 0, 3)); // e.g. "Lun"
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
            'recentOrders',
            'orderStatusChartData',
            'weeklySalesData'
        ));
    }
}

