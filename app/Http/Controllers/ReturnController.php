<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;

use App\Models\CashRegisterMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\WasteRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnController extends Controller
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

    public function index()
    {
        $returns = SalesReturn::with('sale', 'user', 'returnItems')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('returns.index', compact('returns'));
    }

    public function create()
    {
        return view('returns.create');
    }

    public function searchSale(Request $request)
    {
        $request->validate(['folio' => 'required']);

        $sale = Sale::where('tenant_id', auth()->user()->tenant_id)
            ->where(function ($q) use ($request) {
                $q->where('id', $request->folio)
                  ->orWhere('payment_reference', $request->folio);
            })
            ->with(['saleItems' => function ($q) {
                $q->where('type', 'producto');
            }])
            ->first();

        if (!$sale) {
            return response()->json(['found' => false, 'message' => 'Venta no encontrada']);
        }

        $hasReturn = SalesReturn::where('sale_id', $sale->id)->exists();

        $items = $sale->saleItems->map(function ($item) {
            $returnedQty = SalesReturnItem::where('sale_item_id', $item->id)->sum('quantity');
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'returned_qty' => $returnedQty,
                'available_qty' => $item->quantity - $returnedQty,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
            ];
        });

        return response()->json([
            'found' => true,
            'sale' => [
                'id' => $sale->id,
                'created_at' => $sale->created_at->format('d/m/Y H:i'),
                'payment_method' => $sale->payment_method,
                'total' => $sale->total,
                'client_name' => $sale->client?->name ?? 'Mostrador',
            ],
            'has_return' => $hasReturn,
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'reason' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.refund_amount' => 'required|numeric|min:0',
            'items.*.restock' => 'required|boolean',
            'items.*.waste_reason' => 'exclude_if:items.*.restock,true|required|in:mal_aspecto,mal_funcionamiento,defecto_fabrica,otro',
        ]);

        $sale = Sale::with('saleItems')->findOrFail($validated['sale_id']);

        if ($sale->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $hasReturn = SalesReturn::where('sale_id', $sale->id)->exists();

        $openRegister = CashRegister::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'abierta')
            ->first();

        if (!$openRegister) {
            return redirect()->route('returns.index')
                ->with('error', 'No hay una caja abierta. Abra la caja antes de procesar una devolución.');
        }

        DB::transaction(function () use ($validated, $sale, $openRegister) {
            $returnTotal = 0;

            $salesReturn = SalesReturn::create([
                'tenant_id' => $sale->tenant_id,
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
                'refund_total' => 0,
                'reason' => $validated['reason'] ?? null,
                'status' => 'completada',
            ]);

            foreach ($validated['items'] as $itemData) {
                $saleItem = $sale->saleItems->firstWhere('id', $itemData['sale_item_id']);
                if (!$saleItem) continue;

                $alreadyReturned = SalesReturnItem::where('sale_item_id', $saleItem->id)->sum('quantity');
                $availableQty = $saleItem->quantity - $alreadyReturned;

                if ($itemData['quantity'] > $availableQty) {
                    throw ValidationException::withMessages([
                        'items' => 'Cantidad excede lo disponible para devolución',
                    ]);
                }

                $returnItem = SalesReturnItem::create([
                    'tenant_id' => $sale->tenant_id,
                    'sales_return_id' => $salesReturn->id,
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'description' => $saleItem->description,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $saleItem->unit_price,
                    'refund_subtotal' => $itemData['refund_amount'],
                    'restock' => $itemData['restock'],
                ]);

                $returnTotal += $itemData['refund_amount'];

                if ($itemData['restock'] && $saleItem->product_id) {
                    $product = Product::find($saleItem->product_id);
                    if ($product) {
                        $product->adjustStock(
                            $itemData['quantity'],
                            'entrada',
                            "Devolución venta #{$sale->id}",
                            $salesReturn
                        );
                    }
                } elseif (!$itemData['restock'] && $saleItem->product_id) {
                    WasteRecord::create([
                        'tenant_id' => $sale->tenant_id,
                        'sales_return_item_id' => $returnItem->id,
                        'product_id' => $saleItem->product_id,
                        'quantity' => $itemData['quantity'],
                        'reason' => $itemData['waste_reason'],
                    ]);
                }
            }

            $salesReturn->update(['refund_total' => $returnTotal]);

            if ($returnTotal > 0) {
                $reasonText = $validated['reason'] ?? 'Sin motivo';
                CashRegisterMovement::create([
                    'cash_register_id' => $openRegister->id,
                    'type' => 'devolucion',
                    'amount' => $returnTotal,
                    'reason' => "Devolución venta #{$sale->id} — {$reasonText}",
                ]);

                $sale->refunded_total += $returnTotal;
                $saleTotal = (float) $sale->total;

                if ((float) $sale->refunded_total >= $saleTotal) {
                    $sale->return_status = 'total';
                } else {
                    $sale->return_status = 'parcial';
                }

                $sale->save();
            }
        });

        return redirect()->route('returns.index')
            ->with('success', 'Devolución registrada correctamente.');
    }
}
