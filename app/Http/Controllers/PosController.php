<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Sale;
use App\Models\TenantClause;
use App\Models\SaleItem;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->can('pos.access')) {
                abort(403, 'No tienes permiso para acceder a este módulo.');
            }
            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $tenant = Auth::user()->tenant;

        $topProductIds = SaleItem::select('product_id')
            ->selectRaw('COUNT(*) as total')
            ->whereHas('sale', fn($q) => $q->where('tenant_id', $tenant->id))
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->limit(15)
            ->pluck('product_id');

        if ($topProductIds->isNotEmpty()) {
            if (DB::connection()->getDriverName() === 'sqlite') {
                $topProducts = Product::whereIn('id', $topProductIds)
                    ->where('is_active', true)
                    ->get();
                $topProducts = $topProducts->sortBy(fn($p) => array_search($p->id, $topProductIds->toArray()))->values();
            } else {
                $idsOrder = implode(',', $topProductIds->toArray());
                $topProducts = Product::whereIn('id', $topProductIds)
                    ->where('is_active', true)
                    ->orderByRaw("FIELD(id, {$idsOrder})")
                    ->get();
            }
        } else {
            $topProducts = Product::where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->take(15)
                ->get();
        }

        $products = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        $cashRegister = CashRegister::where('tenant_id', $tenant->id)
            ->where('status', 'abierta')
            ->first();

        $previewSaleId = session()->pull('preview_sale_id');

        $workOrder = null;
        $quoteItems = [];

        if ($request->filled('work_order_id')) {
            $workOrder = WorkOrder::with('quote.quoteItems')->find($request->work_order_id);
            if ($workOrder && $workOrder->quote && $workOrder->quote->status === 'aprobada') {
                $quoteItems = $workOrder->quote->quoteItems;
            }
        }

        // Load active quotes for the POS quotes tab
        $activeQuotes = Quote::where('tenant_id', $tenant->id)
            ->whereIn('status', ['pendiente', 'enviada', 'aprobada'])
            ->with(['workOrder.client', 'workOrder.assignedTechnician', 'quoteItems.product'])
            ->orderByDesc('created_at')
            ->get();

        return view('pos.index', compact('topProducts', 'products', 'cashRegister', 'tenant', 'previewSaleId', 'workOrder', 'quoteItems', 'activeQuotes'));
    }

    public function checkout(Request $request): RedirectResponse
    {
        $tenant = Auth::user()->tenant;

        $rules = [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.type' => 'required|in:producto,servicio',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_percentage' => 'numeric|min:0|max:100',
            'payment_method' => 'required|in:efectivo,tarjeta_transferencia,mixto',
            'payment_reference' => 'required_if:payment_method,tarjeta_transferencia|nullable|string',
            'work_order_id' => 'nullable|exists:work_orders,id',
        ];

        if ($request->payment_method === 'efectivo') {
            $rules['amount_received'] = 'required|numeric|min:0';
        }

        if ($request->payment_method === 'mixto') {
            $rules['cash_amount'] = 'required|numeric|min:0';
            $rules['card_amount'] = 'required|numeric|min:0';
            $rules['payment_reference'] = 'required|string';
        }

        $validated = $request->validate($rules);

        $cashRegister = CashRegister::where('tenant_id', $tenant->id)
            ->where('status', 'abierta')
            ->first();

        if (!$cashRegister) {
            return redirect()->route('pos.index')->with('error', 'No hay caja abierta. Abre la caja primero.');
        }

        // Validate work order if cobro_orden
        $workOrder = null;
        if ($request->filled('work_order_id')) {
            $workOrder = WorkOrder::with('quote')->find($request->work_order_id);
            if (!$workOrder || !$workOrder->quote || $workOrder->quote->status !== 'aprobada') {
                return redirect()->route('pos.index')->with('error', 'La cotización no está aprobada o no existe.');
            }
        }

        $productIds = collect($validated['items'])->whereNotNull('product_id')->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($validated['items'] as $item) {
            if ($item['type'] === 'producto' && isset($item['product_id'])) {
                $product = $products->get($item['product_id']);
                if (!$product) {
                    return redirect()->route('pos.index')->with('error', "Producto no encontrado: {$item['description']}.");
                }
                $cartQuantity = collect($validated['items'])->where('product_id', $item['product_id'])->sum('quantity');
                $availableStock = $product->availableStock();
                if ($availableStock < $cartQuantity) {
                    $reservedInfo = $product->reserved_stock > 0 ? " ({$product->reserved_stock} reservados en cotizaciones)" : '';
                    return redirect()->route('pos.index')->with('error', "Stock insuficiente para \"{$product->name}\". Disponible: {$availableStock}{$reservedInfo}, solicitado: {$cartQuantity}.");
                }
            }
        }

        $sale = DB::transaction(function () use ($validated, $cashRegister, $tenant, $products, $workOrder) {
            $subtotal = 0;
            $taxTotal = 0;

            foreach ($validated['items'] as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemSubtotal;

                if ($tenant->tax_enabled) {
                    $taxTotal += $itemSubtotal * (($item['tax_percentage'] ?? 0) / 100);
                }
            }

            $total = $subtotal + $taxTotal;
            $changeAmount = 0;
            $cashAmount = null;
            $cardAmount = null;

            switch ($validated['payment_method']) {
                case 'efectivo':
                    $cashAmount = $validated['amount_received'];
                    $changeAmount = max(0, $cashAmount - $total);
                    break;

                case 'tarjeta_transferencia':
                    $cardAmount = $total;
                    break;

                case 'mixto':
                    $cashAmount = $validated['cash_amount'];
                    $cardAmount = $validated['card_amount'];
                    $remaining = $total - $cardAmount;
                    $changeAmount = max(0, $cashAmount - $remaining);
                    break;
            }

            $sale = Sale::create([
                'tenant_id' => $tenant->id,
                'user_id' => Auth::id(),
                'work_order_id' => $workOrder?->id,
                'cash_register_id' => $cashRegister->id,
                'type' => $workOrder ? 'cobro_orden' : 'venta_directa',
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'discount' => 0,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'change_amount' => $changeAmount,
                'cash_amount' => $cashAmount,
                'card_amount' => $cardAmount,
            ]);

            foreach ($validated['items'] as $item) {
                SaleItem::create([
                    'tenant_id' => $tenant->id,
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'] ?? null,
                    'type' => $item['type'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_percentage' => $tenant->tax_enabled ? ($item['tax_percentage'] ?? 0) : 0,
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);

                if ($item['type'] === 'producto' && isset($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $reference = $workOrder?->quote ?? $sale;
                        $notes = $workOrder ? "Cotización #{$workOrder->quote->id} — OT {$workOrder->work_order_number}" : "Venta #{$sale->id}";
                        $product->adjustStock($item['quantity'], 'salida', $notes, $reference);
                        if ($workOrder) {
                            $product->decrement('reserved_stock', $item['quantity']);
                        }
                    }
                }
            }

            // Mark the quote as charged (no longer cobrable)
            if ($workOrder && $workOrder->quote) {
                $workOrder->quote->markAsCharged();
            }

            // If this is a cobro_orden, update work order status
            if ($workOrder) {
                if ($workOrder->status === 'cotizacion_aprobada') {
                    $workOrder->update(['status' => 'en_reparacion']);
                    $workOrder->addTimelineEvent(
                        'en_reparacion',
                        Auth::user()->name,
                        "Cotización cobrada desde POS — Venta #{$sale->id}. Equipo listo para reparación."
                    );
                } elseif ($workOrder->status === 'reparada') {
                    $workOrder->update(['status' => 'terminada']);
                    $workOrder->addTimelineEvent(
                        'terminada',
                        Auth::user()->name,
                        "Orden cobrada y entregada al cliente desde POS — Venta #{$sale->id}"
                    );
                } else {
                    $workOrder->addTimelineEvent(
                        $workOrder->status,
                        Auth::user()->name,
                        "Orden cobrada desde POS — Venta #{$sale->id}"
                    );
                }
            }

            return $sale;
        });

        if ($request->boolean('preview')) {
            session()->flash('preview_sale_id', $sale->id);

            return redirect()->route('pos.index')->with('success', "Venta #{$sale->id} registrada exitosamente.");
        }

        $this->autoCancelAffectedQuotes($products);

        return redirect()->route('pos.index')->with('success', "Venta #{$sale->id} registrada exitosamente. Total: $" . number_format($sale->total, 2));
    }

    private function autoCancelAffectedQuotes(\Illuminate\Support\Collection $products): void
    {
        foreach ($products as $product) {
            $product->refresh();

            $affectedQuotes = Quote::whereIn('status', ['pendiente', 'enviada'])
                ->whereHas('quoteItems', fn($q) => $q->where('product_id', $product->id))
                ->orderBy('created_at')
                ->get();

            foreach ($affectedQuotes as $quote) {
                $neededQty = $quote->quoteItems()->where('product_id', $product->id)->sum('quantity');

                if ($product->stock < $neededQty) {
                    $quote->cancel(
                        "Cancelación automática — El producto \"{$product->name}\" ya no tiene suficiente stock para completar esta cotización (stock: {$product->stock}, necesario: {$neededQty})."
                    );
                    $quote->workOrder->addTimelineEvent(
                        'cancelada',
                        'Sistema',
                        "Cotización cancelada automáticamente por falta de stock de \"{$product->name}\" tras una venta directa."
                    );
                }
            }
        }
    }

    private function authorizeTenant(Sale $sale): void
    {
        abort_if($sale->tenant_id !== Auth::user()->tenant_id, 403);
    }

    public function print(Sale $sale): View
    {
        $this->authorizeTenant($sale);

        $sale->load(['saleItems', 'user', 'workOrder.client']);
        $tenant = $sale->tenant;
        $format = $tenant->print_format ?? 'ticket_80mm';
        $clauses = TenantClause::where('tenant_id', $tenant->id)
            ->where('print_on_receipt', true)
            ->where('is_active', true)
            ->get();

        return view("pos.print.{$format}", compact('sale', 'tenant', 'clauses'));
    }

    public function printPreview(Sale $sale): View
    {
        $this->authorizeTenant($sale);

        $sale->load(['saleItems', 'user', 'workOrder.client']);
        $tenant = $sale->tenant;
        $format = $tenant->print_format ?? 'ticket_80mm';
        $clauses = TenantClause::where('tenant_id', $tenant->id)
            ->where('print_on_receipt', true)
            ->where('is_active', true)
            ->get();

        return view("pos.print.{$format}", compact('sale', 'tenant', 'clauses'))->with('preview', true);
    }
}
