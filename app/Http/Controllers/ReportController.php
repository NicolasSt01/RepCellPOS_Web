<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\WorkOrder;
use App\Models\Quote;
use App\Models\Product;
use App\Models\Category;
use App\Models\CashRegister;
use App\Models\CashRegisterMovement;
use App\Models\CashRegisterIncident;
use App\Models\Client;
use App\Models\KardexMovement;
use App\Models\SalesReturn;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Helpers\ReportHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $allReports = config('reports');

        if ($isSuperAdmin) {
            $reports = $allReports;
        } else {
            $tenant = $user->tenant;
            $reports = collect($allReports)->filter(function ($report) use ($tenant) {
                return $tenant->hasFeature($report['plan_feature']);
            })->all();
        }

        $grouped = collect($reports)->groupBy('area');

        return view('reportes.index', compact('grouped', 'isSuperAdmin'));
    }

    public function redirectToIndex()
    {
        return redirect()->route('reportes.index');
    }

    private function checkReportAccess(Request $request, string $feature): void
    {
        $user = $request->user();
        if ($user->isSuperAdmin()) return;
        $tenant = $user->tenant;
        if (!$tenant->hasFeature($feature)) {
            abort(403, 'Tu plan no incluye este reporte.');
        }
    }

    private function getDateRange(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        return [$dateFrom, $dateTo];
    }

    private function getPreviousPeriod(string $from, string $to): array
    {
        $days = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
        $prevTo = Carbon::parse($from)->subDay()->format('Y-m-d');
        $prevFrom = Carbon::parse($from)->subDays($days)->format('Y-m-d');
        return [$prevFrom, $prevTo];
    }

    // ========================
    // PHASE 1
    // ========================

    public function ventasPeriodo(Request $request)
    {
        $this->checkReportAccess($request, 'report.ventas-periodo');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $sales = Sale::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->get();

        $totalIngresos = $sales->sum('total');
        $totalTransacciones = $sales->count();
        $ticketPromedio = $totalTransacciones > 0 ? $totalIngresos / $totalTransacciones : 0;

        [$prevFrom, $prevTo] = $this->getPreviousPeriod($dateFrom, $dateTo);
        $prevSales = Sale::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$prevFrom . ' 00:00:00', $prevTo . ' 23:59:59'])
            ->sum('total');
        $prevPeriodDiff = ReportHelper::diffPercent($totalIngresos, $prevSales);

        $dailySales = Sale::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $salesByDayLabels = $dailySales->pluck('date')->toJson();
        $salesByDayData = $dailySales->pluck('total')->map(function ($v) {
            return (float) $v;
        })->toJson();

        return view('reportes.ventas-periodo', compact(
            'totalIngresos', 'ticketPromedio', 'totalTransacciones',
            'prevPeriodDiff', 'dailySales', 'salesByDayLabels', 'salesByDayData'
        ));
    }

    public function productividad(Request $request)
    {
        $this->checkReportAccess($request, 'report.taller-productividad');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $technicians = User::where('tenant_id', $tenantId)
            ->whereHas('workOrdersAssigned', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            })
            ->withCount(['workOrdersAssigned as total' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            }])
            ->get()
            ->map(function ($user) use ($dateFrom, $dateTo) {
                $completadas = WorkOrder::where('assigned_to', $user->id)
                    ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->whereIn('status', ['terminada', 'reparada'])
                    ->count();
                $enProceso = WorkOrder::where('assigned_to', $user->id)
                    ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->whereNotIn('status', ['terminada', 'reparada', 'cancelada'])
                    ->count();
                $activas = WorkOrder::where('assigned_to', $user->id)
                    ->whereNotIn('status', ['terminada', 'reparada', 'cancelada'])
                    ->count();
                $user->completadas = $completadas;
                $user->enProceso = $enProceso;
                $user->activas = $activas;
                return $user;
            });

        $totalTecnicos = $technicians->count();
        $totalCompletadas = $technicians->sum('completadas');
        $totalEnProceso = $technicians->sum('enProceso');

        return view('reportes.productividad', compact(
            'technicians', 'totalTecnicos', 'totalCompletadas', 'totalEnProceso'
        ));
    }

    public function cotizaciones(Request $request)
    {
        $this->checkReportAccess($request, 'report.taller-cotizaciones');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $quotes = Quote::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->with('workOrder.client')
            ->get();

        $totalCotizaciones = $quotes->count();
        $enviadas = $quotes->where('status', 'enviada')->count();
        $aprobadas = $quotes->whereIn('status', ['aprobada', 'cobrada'])->count();
        $cobradas = $quotes->where('status', 'cobrada')->count();
        $totalAprobado = $quotes->whereIn('status', ['aprobada', 'cobrada'])->sum('total');
        $totalCobrado = $quotes->where('status', 'cobrada')->sum('total');

        return view('reportes.cotizaciones', compact(
            'totalCotizaciones', 'enviadas', 'aprobadas', 'cobradas',
            'totalAprobado', 'totalCobrado', 'quotes'
        ));
    }

    public function valorizacionStock(Request $request)
    {
        $this->checkReportAccess($request, 'report.inventario-valorizacion');
        $tenantId = $request->user()->tenant_id;

        $products = Product::where('tenant_id', $tenantId)
            ->where('type', 'producto')
            ->with('category')
            ->get();

        $valorTotal = $products->sum(fn ($p) => $p->stock * $p->purchase_price);
        $totalProductos = $products->count();
        $productosCriticos = $products->filter(fn ($p) => $p->stock <= $p->min_stock);
        $bajoMinimo = $productosCriticos->count();
        $capitalBajoMinimo = $productosCriticos->sum(fn ($p) => $p->stock * $p->purchase_price);

        return view('reportes.valorizacion-stock', compact(
            'valorTotal', 'totalProductos', 'bajoMinimo', 'capitalBajoMinimo',
            'products', 'productosCriticos'
        ));
    }

    public function cuadreCaja(Request $request)
    {
        $this->checkReportAccess($request, 'report.caja-cuadre');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $registros = CashRegister::where('tenant_id', $tenantId)
            ->whereBetween('opened_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->with('user', 'sales', 'movements')
            ->get()
            ->map(function ($reg) {
                $totalVentas = $reg->sales->sum('total');
                $totalMovements = $reg->movements->sum('amount');
                $reg->expected_amount = $reg->opening_amount + $totalVentas - $totalMovements;
                $reg->difference = $reg->closing_amount ? $reg->closing_amount - $reg->expected_amount : 0;
                return $reg;
            });

        $totalTurnos = $registros->count();
        $totalEsperado = $registros->sum('expected_amount');
        $totalReal = $registros->sum('closing_amount');
        $totalDescuadre = $registros->sum('difference');

        return view('reportes.cuadre-caja', compact(
            'registros', 'totalTurnos', 'totalEsperado', 'totalReal', 'totalDescuadre'
        ));
    }

    public function aprobacionCotizaciones(Request $request)
    {
        $this->checkReportAccess($request, 'report.cotizaciones-aprobacion');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $quotesList = Quote::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->with('workOrder.client')
            ->get();

        $totalQuotes = $quotesList->count();
        $statusCounts = [
            'pendiente' => $quotesList->where('status', 'pendiente')->count(),
            'enviada' => $quotesList->where('status', 'enviada')->count(),
            'aprobada' => $quotesList->where('status', 'aprobada')->count(),
            'rechazada' => $quotesList->where('status', 'rechazada')->count(),
            'cobrada' => $quotesList->where('status', 'cobrada')->count(),
        ];
        $aprobadas = $statusCounts['aprobada'] + $statusCounts['cobrada'];
        $rechazadas = $statusCounts['rechazada'];
        $tasaAprobacion = $totalQuotes > 0 ? ($aprobadas / $totalQuotes) * 100 : 0;
        $tasaRechazo = $totalQuotes > 0 ? ($rechazadas / $totalQuotes) * 100 : 0;

        return view('reportes.aprobacion-cotizaciones', compact(
            'quotesList', 'totalQuotes', 'statusCounts', 'tasaAprobacion', 'tasaRechazo'
        ));
    }

    // ========================
    // PHASE 2
    // ========================

    public function ventasProductos(Request $request)
    {
        $this->checkReportAccess($request, 'report.ventas-productos');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $topProducts = SaleItem::where('sale_items.tenant_id', $tenantId)
            ->where('sale_items.type', 'producto')
            ->whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            })
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_id')
            ->with('product.category')
            ->get()
            ->map(function ($item) {
                $item->name = $item->product?->name ?? 'Producto eliminado';
                $item->category_name = $item->product?->category?->name ?? 'Sin categoría';
                return $item;
            })
            ->sortByDesc('total_revenue')
            ->take(20);

        $totalRevenue = $topProducts->sum('total_revenue');
        $topProducts->each(function ($item) use ($totalRevenue) {
            $item->percentage = $totalRevenue > 0 ? ($item->total_revenue / $totalRevenue) * 100 : 0;
        });

        $totalProductosVendidos = SaleItem::where('sale_items.tenant_id', $tenantId)
            ->where('sale_items.type', 'producto')
            ->whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            })
            ->sum('quantity');
        $productosUnicos = SaleItem::where('sale_items.tenant_id', $tenantId)
            ->where('sale_items.type', 'producto')
            ->whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            })
            ->distinct('product_id')->count('product_id');

        $categories = SaleItem::where('sale_items.tenant_id', $tenantId)
            ->where('sale_items.type', 'producto')
            ->whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            })
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(sale_items.subtotal) as total'))
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        $categoriasActivas = $categories->count();
        $productLabels = $topProducts->pluck('name')->toJson();
        $productData = $topProducts->pluck('total_revenue')->map(fn ($v) => (float) $v)->toJson();
        $categoryLabels = $categories->pluck('name')->toJson();
        $categoryData = $categories->pluck('total')->map(fn ($v) => (float) $v)->toJson();

        return view('reportes.ventas-productos', compact(
            'totalProductosVendidos', 'productosUnicos', 'categoriasActivas',
            'topProducts', 'productLabels', 'productData', 'categoryLabels', 'categoryData'
        ));
    }

    public function ventasPago(Request $request)
    {
        $this->checkReportAccess($request, 'report.ventas-pago');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $sales = Sale::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->get();

        $efectivo = $sales->where('payment_method', 'efectivo');
        $tarjeta = $sales->where('payment_method', 'tarjeta_transferencia');
        $mixto = $sales->filter(fn ($s) => $s->cash_amount && $s->card_amount);

        $totalEfectivo = $efectivo->sum('total');
        $totalTarjeta = $tarjeta->sum('total');
        $totalMixto = $mixto->sum('total');
        $totalGeneral = $sales->sum('total');
        $pctEfectivo = $totalGeneral > 0 ? ($totalEfectivo / $totalGeneral) * 100 : 0;

        $paymentMethods = collect([
            (object) ['method' => 'Efectivo', 'count' => $efectivo->count(), 'total' => $totalEfectivo, 'percentage' => $totalGeneral > 0 ? ($totalEfectivo / $totalGeneral) * 100 : 0],
            (object) ['method' => 'Tarjeta / Transferencia', 'count' => $tarjeta->count(), 'total' => $totalTarjeta, 'percentage' => $totalGeneral > 0 ? ($totalTarjeta / $totalGeneral) * 100 : 0],
            (object) ['method' => 'Mixto', 'count' => $mixto->count(), 'total' => $totalMixto, 'percentage' => $totalGeneral > 0 ? ($totalMixto / $totalGeneral) * 100 : 0],
        ]);

        $paymentLabels = json_encode(['Efectivo', 'Tarjeta/Transf.', 'Mixto']);
        $paymentData = json_encode([round($totalEfectivo, 2), round($totalTarjeta, 2), round($totalMixto, 2)]);

        return view('reportes.ventas-pago', compact(
            'totalEfectivo', 'totalTarjeta', 'totalMixto', 'pctEfectivo',
            'paymentMethods', 'paymentLabels', 'paymentData'
        ));
    }

    public function ventasTicket(Request $request)
    {
        $this->checkReportAccess($request, 'report.ventas-ticket');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $sales = Sale::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->get();

        $totalSales = $sales->sum('total');
        $totalCount = $sales->count();
        $ticketPromedio = $totalCount > 0 ? $totalSales / $totalCount : 0;

        $returns = SalesReturn::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->get();

        $totalDevuelto = $returns->sum('refund_total');
        $ticketPromNeto = $totalCount > 0 ? ($totalSales - $totalDevuelto) / $totalCount : 0;
        $pctDevuelto = $totalSales > 0 ? ($totalDevuelto / $totalSales) * 100 : 0;

        $monthlyData = Sale::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COUNT(*) as sales_count'), DB::raw('SUM(total) as total'))
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy('month')
            ->get()
            ->map(function ($item) use ($tenantId) {
                $returnTotal = SalesReturn::where('tenant_id', $tenantId)
                    ->whereBetween('created_at', [$item->month . '-01 00:00:00', $item->month . '-31 23:59:59'])
                    ->sum('refund_total');
                $item->return_total = $returnTotal;
                $item->returns_count = SalesReturn::where('tenant_id', $tenantId)
                    ->whereBetween('created_at', [$item->month . '-01 00:00:00', $item->month . '-31 23:59:59'])
                    ->count();
                $item->net = $item->total - $returnTotal;
                return $item;
            });

        return view('reportes.ventas-ticket', compact(
            'ticketPromedio', 'ticketPromNeto', 'totalDevuelto', 'pctDevuelto',
            'returns', 'monthlyData'
        ));
    }

    public function dispositivos(Request $request)
    {
        $this->checkReportAccess($request, 'report.taller-dispositivos');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $workOrders = WorkOrder::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->get();

        $totalOT = $workOrders->count();
        $marcasUnicas = $workOrders->pluck('device_brand')->unique()->filter()->count();
        $modelosUnicos = $workOrders->pluck('device_model')->unique()->filter()->count();
        $marcaTop = $workOrders->groupBy('device_brand')->sortByDesc->count()->keys()->first() ?? 'N/A';

        $devices = $workOrders->groupBy(fn ($o) => $o->device_brand . '||' . $o->device_model)
            ->map(function ($group, $key) {
                [$brand, $model] = explode('||', $key);
                return (object) [
                    'device_brand' => $brand ?: 'Sin marca',
                    'device_model' => $model ?: 'Sin modelo',
                    'count' => $group->count(),
                    'problem_summary' => $group->pluck('problem_description')->filter()->implode(', '),
                ];
            })
            ->sortByDesc('count')
            ->take(20)
            ->values();

        $brands = $workOrders->groupBy('device_brand')->map->count()->sortDesc()->take(10);
        $brandLabels = $brands->keys()->map(fn ($b) => $b ?: 'Sin marca')->toJson();
        $brandData = $brands->values()->toJson();

        return view('reportes.dispositivos', compact(
            'totalOT', 'marcasUnicas', 'modelosUnicos', 'marcaTop',
            'devices', 'brandLabels', 'brandData'
        ));
    }

    public function kardex(Request $request)
    {
        $this->checkReportAccess($request, 'report.inventario-kardex');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $movements = KardexMovement::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->with('product', 'user')
            ->orderByDesc('created_at')
            ->get();

        $totalEntradas = $movements->where('type', 'entrada')->sum('quantity');
        $totalSalidas = $movements->where('type', 'salida')->sum('quantity');
        $totalAjustes = $movements->where('type', 'ajuste')->count();
        $totalMovements = $movements->count();

        $typeLabels = json_encode(['Entradas', 'Salidas', 'Ajustes']);
        $typeData = json_encode([
            $movements->where('type', 'entrada')->count(),
            $movements->where('type', 'salida')->count(),
            $movements->where('type', 'ajuste')->count(),
        ]);

        return view('reportes.kardex', compact(
            'movements', 'totalEntradas', 'totalSalidas', 'totalAjustes',
            'totalMovements', 'typeLabels', 'typeData'
        ));
    }

    public function flujoEfectivo(Request $request)
    {
        $this->checkReportAccess($request, 'report.caja-flujo');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $dailyFlow = Sale::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as ingresos'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(function ($item) use ($tenantId) {
                $egresos = CashRegisterMovement::whereHas('cashRegister', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                })
                    ->whereBetween('created_at', [$item->date . ' 00:00:00', $item->date . ' 23:59:59'])
                    ->sum('amount');
                $item->egresos = $egresos;
                $item->neto = $item->ingresos - $egresos;
                return $item;
            });

        $totalIngresos = $dailyFlow->sum('ingresos');
        $totalEgresos = $dailyFlow->sum('egresos');
        $saldoNeto = $totalIngresos - $totalEgresos;

        $runningBalance = 0;
        $dailyFlow->each(function ($item) use (&$runningBalance) {
            $runningBalance += $item->neto;
            $item->saldo_acumulado = $runningBalance;
        });
        $saldoAcumulado = $runningBalance;

        $flowLabels = $dailyFlow->pluck('date')->toJson();
        $flowIngresos = $dailyFlow->pluck('ingresos')->map(fn ($v) => (float) $v)->toJson();
        $flowEgresos = $dailyFlow->pluck('egresos')->map(fn ($v) => (float) $v)->toJson();
        $flowNeto = $dailyFlow->pluck('neto')->map(fn ($v) => (float) $v)->toJson();

        return view('reportes.flujo-efectivo', compact(
            'dailyFlow', 'totalIngresos', 'totalEgresos', 'saldoNeto', 'saldoAcumulado',
            'flowLabels', 'flowIngresos', 'flowEgresos', 'flowNeto'
        ));
    }

    public function topClientes(Request $request)
    {
        $this->checkReportAccess($request, 'report.clientes-top');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $clients = Client::where('tenant_id', $tenantId)
            ->whereHas('sales', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            })
            ->withCount(['sales as total_purchases' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            }])
            ->get()
            ->map(function ($client) use ($dateFrom, $dateTo, $tenantId) {
                $sales = Sale::where('client_id', $client->id)
                    ->where('tenant_id', $tenantId)
                    ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                $client->total_spent = (float) $sales->sum('total');
                $client->total_work_orders = WorkOrder::where('client_id', $client->id)
                    ->where('tenant_id', $tenantId)
                    ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->count();
                $client->last_purchase_date = $sales->max('created_at');
                return $client;
            })
            ->sortByDesc('total_spent')
            ->take(30)
            ->values();

        $totalClientes = Client::where('tenant_id', $tenantId)
            ->whereHas('sales')
            ->count();
        $totalGastado = $clients->sum('total_spent');
        $clienteTop = $clients->first()?->name ?? 'N/A';
        $ticketPromedioCliente = $totalClientes > 0 ? $totalGastado / $totalClientes : 0;

        return view('reportes.top-clientes', compact(
            'clients', 'totalClientes', 'totalGastado', 'clienteTop', 'ticketPromedioCliente'
        ));
    }

    // ========================
    // PHASE 3
    // ========================

    public function sla(Request $request)
    {
        $this->checkReportAccess($request, 'report.taller-sla');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $activeStatuses = ['recibida', 'en_espera', 'en_revision', 'diagnosticada', 'cotizacion_enviada', 'cotizacion_aprobada', 'en_reparacion', 'reparada'];

        $activas = WorkOrder::where('tenant_id', $tenantId)
            ->whereIn('status', $activeStatuses)
            ->orderBy('created_at')
            ->get();

        $totalActivas = $activas->count();

        $vencidas = $activas->filter(fn ($o) => $o->promised_at && Carbon::parse($o->promised_at)->isPast())
            ->sortBy('promised_at');

        $totalVencidas = $vencidas->count();
        $totalSinPromesa = $activas->filter(fn ($o) => !$o->promised_at)->count();
        $cumplimiento = $totalActivas > 0 ? round((1 - ($totalVencidas / $totalActivas)) * 100, 1) : 100;

        $proximas = $activas->filter(fn ($o) => $o->promised_at && !Carbon::parse($o->promised_at)->isPast())
            ->sortBy('promised_at')
            ->take(10);

        return view('reportes.sla', compact(
            'totalActivas', 'totalVencidas', 'totalSinPromesa', 'cumplimiento',
            'vencidas', 'proximas'
        ));
    }

    public function cicloVida(Request $request)
    {
        $this->checkReportAccess($request, 'report.taller-ciclo-vida');
        $hasData = false;
        $avgTotal = 0;
        $avgReparacion = 0;
        $totalCompletadas = 0;
        $avgCotizacion = 0;
        $statusHistory = collect();

        try {
            $tableExists = DB::select("SELECT 1 FROM information_schema.tables WHERE table_name = 'work_order_status_history'");
            if ($tableExists) {
                $hasData = true;
            }
        } catch (\Exception $e) {
            $hasData = false;
        }

        return view('reportes.ciclo-vida', compact(
            'hasData', 'avgTotal', 'avgReparacion', 'totalCompletadas',
            'avgCotizacion', 'statusHistory'
        ));
    }

    public function rotacionInventario(Request $request)
    {
        $this->checkReportAccess($request, 'report.inventario-rotacion');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $products = Product::where('tenant_id', $tenantId)
            ->where('type', 'producto')
            ->get()
            ->map(function ($product) use ($dateFrom, $dateTo, $tenantId) {
                $salesQty = SaleItem::where('product_id', $product->id)
                    ->where('tenant_id', $tenantId)
                    ->whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                        $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                    })
                    ->sum('quantity');
                $product->sales_qty = (int) $salesQty;
                $avgStock = $product->stock; // simplified
                $product->rotation = $avgStock > 0 ? round($salesQty / $avgStock, 2) : 0;
                $product->last_sale_date = SaleItem::where('product_id', $product->id)
                    ->whereHas('sale', function ($q) use ($tenantId) {
                        $q->where('tenant_id', $tenantId);
                    })
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->max('sales.created_at');
                $product->capital = $product->stock * $product->purchase_price;
                return $product;
            });

        $rotacionGeneral = $products->avg('rotation');
        $diasInventario = $rotacionGeneral > 0 ? round(365 / $rotacionGeneral, 1) : 0;
        $sinMovimiento = $products->filter(fn ($p) => $p->sales_qty == 0);
        $productosSinMovimiento = $sinMovimiento->count();
        $capitalInmovilizado = $sinMovimiento->sum('capital');

        $products = $products->sortByDesc('rotation');

        return view('reportes.rotacion', compact(
            'rotacionGeneral', 'diasInventario', 'productosSinMovimiento',
            'capitalInmovilizado', 'products', 'sinMovimiento'
        ));
    }

    public function retencionClientes(Request $request)
    {
        $this->checkReportAccess($request, 'report.clientes-retencion');
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $tenantId = $request->user()->tenant_id;

        $allClientIds = Sale::where('tenant_id', $tenantId)
            ->whereNotNull('client_id')
            ->distinct('client_id')
            ->pluck('client_id');

        $newClientIds = Sale::where('tenant_id', $tenantId)
            ->whereNotNull('client_id')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select('client_id', DB::raw('MIN(created_at) as first_sale'))
            ->groupBy('client_id')
            ->having('first_sale', '>=', $dateFrom . ' 00:00:00')
            ->pluck('client_id');

        $recurringClientIds = Sale::where('tenant_id', $tenantId)
            ->whereNotNull('client_id')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select('client_id', DB::raw('MIN(created_at) as first_sale'))
            ->groupBy('client_id')
            ->having('first_sale', '<', $dateFrom . ' 00:00:00')
            ->pluck('client_id');

        $nuevos = $newClientIds->count();
        $recurrentes = $recurringClientIds->count();
        $totalActivos = $allClientIds->count();
        $tasaRetencion = $totalActivos > 0 ? round(($recurrentes / $totalActivos) * 100, 1) : 0;

        $nuevosClientes = Client::whereIn('id', $newClientIds)->get();
        $recurrentesClientes = Client::whereIn('id', $recurringClientIds)->get();

        $chartLabels = json_encode(['Nuevos', 'Recurrentes']);
        $chartData = json_encode([$nuevos, $recurrentes]);

        return view('reportes.retencion-clientes', compact(
            'nuevos', 'recurrentes', 'tasaRetencion', 'totalActivos',
            'nuevosClientes', 'recurrentesClientes', 'chartLabels', 'chartData'
        ));
    }

    // ========================
    // PHASE 4 — SuperAdmin
    // ========================

    public function saasMrr(Request $request)
    {
        $this->checkReportAccess($request, 'report.saas-mrr');
        $subscriptions = TenantSubscription::with('plan', 'tenant')
            ->where('status', 'activa')
            ->get();

        $mrr = $subscriptions->sum('amount');
        $arr = $mrr * 12;
        $suscripcionesActivas = $subscriptions->count();
        $arpu = $suscripcionesActivas > 0 ? $mrr / $suscripcionesActivas : 0;

        $planDistribution = $subscriptions->groupBy('plan_id')
            ->map(function ($group, $planId) {
                $plan = $group->first()->plan;
                $monthly = $group->sum('amount');
                return (object) [
                    'name' => $plan?->name ?? 'Sin plan',
                    'tenant_count' => $group->count(),
                    'monthly_revenue' => $monthly,
                    'annual_revenue' => $monthly * 12,
                ];
            })->values();

        $mrrHistory = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $mrrHistory->push([
                'label' => $month->format('M Y'),
                'value' => TenantSubscription::where('status', 'activa')
                    ->where('created_at', '<=', $month->endOfMonth())
                    ->sum('amount'),
            ]);
        }

        $mrrHistoryLabels = $mrrHistory->pluck('label')->toJson();
        $mrrHistoryData = $mrrHistory->pluck('value')->map(fn ($v) => (float) $v)->toJson();

        return view('reportes.saas-mrr', compact(
            'mrr', 'arr', 'suscripcionesActivas', 'arpu',
            'planDistribution', 'mrrHistoryLabels', 'mrrHistoryData'
        ));
    }

    public function saasCrecimiento(Request $request)
    {
        $this->checkReportAccess($request, 'report.saas-crecimiento');
        $activeTenants = Tenant::where('is_active', true)->count();
        $newTenants = Tenant::where('created_at', '>=', now()->startOfMonth())->count();
        $cancelledTenants = Tenant::where('is_active', false)->where('updated_at', '>=', now()->startOfMonth())->count();
        $totalTenants = Tenant::count();
        $churnRate = $totalTenants > 0 ? round(($cancelledTenants / $totalTenants) * 100, 1) : 0;

        $monthlyGrowth = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $new = Tenant::whereBetween('created_at', [$start, $end])->count();
            $cancelled = Tenant::where('is_active', false)
                ->whereBetween('updated_at', [$start, $end])
                ->count();
            $activeEnd = Tenant::where('created_at', '<=', $end)
                ->where(function ($q) use ($start) {
                    $q->where('is_active', true)
                        ->orWhere('updated_at', '>=', $start);
                })
                ->count();

            $monthlyGrowth->push((object) [
                'month' => $month->format('M Y'),
                'new' => $new,
                'cancelled' => $cancelled,
                'active_end' => $activeEnd,
                'growth' => $new - $cancelled,
            ]);
        }

        $growthLabels = $monthlyGrowth->pluck('month')->toJson();
        $growthActive = $monthlyGrowth->pluck('active_end')->toJson();
        $growthNew = $monthlyGrowth->pluck('new')->toJson();

        return view('reportes.saas-crecimiento', compact(
            'activeTenants', 'newTenants', 'cancelledTenants', 'churnRate',
            'monthlyGrowth', 'growthLabels', 'growthActive', 'growthNew'
        ));
    }

    public function saasUso(Request $request)
    {
        $this->checkReportAccess($request, 'report.saas-uso');
        $tenants = Tenant::withCount('users')
            ->with('plan')
            ->get()
            ->map(function ($tenant) {
                $tenant->work_orders_count = WorkOrder::where('tenant_id', $tenant->id)
                    ->where('created_at', '>=', now()->subMonth())
                    ->count();
                $tenant->sales_count = Sale::where('tenant_id', $tenant->id)
                    ->where('created_at', '>=', now()->subMonth())
                    ->count();
                $tenant->last_activity = WorkOrder::where('tenant_id', $tenant->id)
                    ->max('updated_at') ?? Sale::where('tenant_id', $tenant->id)
                    ->max('updated_at');
                $tenant->plan_name = $tenant->plan?->name ?? 'Sin plan';
                $tenant->subscription_status = $tenant->subscription_status;
                return $tenant;
            });

        $totalTenants = $tenants->count();
        $totalUsers = $tenants->sum('users_count');
        $totalOT = $tenants->sum('work_orders_count');
        $totalVentas = $tenants->sum('sales_count');

        return view('reportes.saas-uso', compact(
            'tenants', 'totalTenants', 'totalUsers', 'totalOT', 'totalVentas'
        ));
    }
}
