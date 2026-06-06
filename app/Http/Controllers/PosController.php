<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $products = Product::where('is_active', true)->where('type', 'producto')->orderBy('name')->get();
        $cashRegister = CashRegister::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'abierta')
            ->first();

        return view('pos.index', compact('products', 'cashRegister'));
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.type' => 'required|in:producto,servicio',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_percentage' => 'numeric|min:0|max:100',
            'payment_method' => 'required|in:efectivo,tarjeta_transferencia',
            'payment_reference' => 'required_if:payment_method,tarjeta_transferencia|nullable|string',
            'amount_received' => 'required_if:payment_method,efectivo|numeric|min:0',
        ]);

        $cashRegister = CashRegister::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'abierta')
            ->first();

        if (!$cashRegister) {
            return redirect()->route('pos.index')->with('error', 'No hay caja abierta. Abre la caja primero.');
        }

        return DB::transaction(function () use ($validated, $cashRegister) {
            $subtotal = 0;
            $taxTotal = 0;

            foreach ($validated['items'] as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemSubtotal;
                $taxTotal += $itemSubtotal * (($item['tax_percentage'] ?? 0) / 100);
            }

            $total = $subtotal + $taxTotal;
            $changeAmount = 0;

            if ($validated['payment_method'] === 'efectivo') {
                $changeAmount = max(0, $validated['amount_received'] - $total);
            }

            $sale = Sale::create([
                'tenant_id' => Auth::user()->tenant_id,
                'user_id' => Auth::id(),
                'cash_register_id' => $cashRegister->id,
                'type' => 'venta_directa',
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'discount' => 0,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'change_amount' => $changeAmount,
            ]);

            foreach ($validated['items'] as $item) {
                SaleItem::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'] ?? null,
                    'type' => $item['type'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_percentage' => $item['tax_percentage'] ?? 0,
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);

                if ($item['type'] === 'producto' && isset($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->adjustStock($item['quantity'], 'salida', "Venta #{$sale->id}", $sale);
                    }
                }
            }

            return redirect()->route('pos.index')->with('success', "Venta #{$sale->id} registrada exitosamente. Total: $" . number_format($total, 2));
        });
    }
}
