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
        $tenant = Auth::user()->tenant;
        $products = Product::where('is_active', true)->where('type', 'producto')->orderBy('name')->get();
        $cashRegister = CashRegister::where('tenant_id', $tenant->id)
            ->where('status', 'abierta')
            ->first();

        $previewSaleId = session()->pull('preview_sale_id');

        return view('pos.index', compact('products', 'cashRegister', 'tenant', 'previewSaleId'));
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

        $sale = DB::transaction(function () use ($validated, $cashRegister, $tenant) {
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
                'cash_register_id' => $cashRegister->id,
                'type' => 'venta_directa',
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
                        $product->adjustStock($item['quantity'], 'salida', "Venta #{$sale->id}", $sale);
                    }
                }
            }

            return $sale;
        });

        if ($request->boolean('preview')) {
            session()->flash('preview_sale_id', $sale->id);

            return redirect()->route('pos.index')->with('success', "Venta #{$sale->id} registrada exitosamente.");
        }

        return redirect()->route('pos.index')->with('success', "Venta #{$sale->id} registrada exitosamente. Total: $" . number_format($sale->total, 2));
    }

    public function print(Sale $sale): View
    {
        $sale->load(['saleItems', 'user', 'client']);
        $tenant = $sale->tenant;

        $format = $tenant->print_format ?? 'ticket_58mm';

        return view("pos.print.{$format}", compact('sale', 'tenant'));
    }

    public function printPreview(Sale $sale): View
    {
        $sale->load(['saleItems', 'user', 'client']);
        $tenant = $sale->tenant;

        $format = $tenant->print_format ?? 'ticket_58mm';

        return view("pos.print.{$format}", compact('sale', 'tenant'))->with('preview', true);
    }
}
