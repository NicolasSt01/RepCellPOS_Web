<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function show(WorkOrder $workOrder): View
    {
        $quote = $workOrder->quote ?? $workOrder->quote()->create([
            'tenant_id' => $workOrder->tenant_id,
            'status' => 'pendiente',
        ]);

        $quote->load('quoteItems.product');
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('quotes.show', compact('workOrder', 'quote', 'products'));
    }

    public function addItem(Request $request, Quote $quote): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'type' => 'required|in:producto,servicio',
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'tax_percentage' => 'numeric|min:0|max:100',
        ]);

        if (!empty($validated['product_id'])) {
            $product = Product::findOrFail($validated['product_id']);

            $availableStock = $product->availableStock();

            if ($validated['quantity'] > $availableStock) {
                return redirect()->route('quotes.show', $quote->workOrder)
                    ->with('error', "Stock insuficiente para \"{$product->name}\". Disponible: {$availableStock}, solicitado: {$validated['quantity']}. El stock se reserva hasta que el cliente apruebe la cotización.");
            }
        }

        QuoteItem::create(array_merge($validated, [
            'tenant_id' => $quote->tenant_id,
            'quote_id' => $quote->id,
        ]));

        return redirect()->route('quotes.show', $quote->workOrder)
            ->with('success', 'Item agregado a la cotización.');
    }

    public function removeItem(QuoteItem $quoteItem): RedirectResponse
    {
        $workOrder = $quoteItem->quote->workOrder;
        $quoteItem->delete();

        return redirect()->route('quotes.show', $workOrder)
            ->with('success', 'Item eliminado de la cotización.');
    }

    public function send(Quote $quote): RedirectResponse
    {
        $quote->update(['status' => 'enviada']);
        $quote->workOrder->update(['status' => 'cotizacion_enviada']);
        $quote->workOrder->addTimelineEvent('cotizacion_enviada', auth()->user()->name, 'Cotización enviada al cliente');

        return redirect()->route('work_orders.show', $quote->workOrder)
            ->with('success', 'Cotización enviada al cliente.');
    }

    public function approve(Quote $quote): RedirectResponse
    {
        try {
            $quote->approve();
        } catch (\RuntimeException $e) {
            return redirect()->route('quotes.show', $quote->workOrder)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('work_orders.show', $quote->workOrder)
            ->with('success', 'Cotización aprobada. Orden en reparación.');
    }

    public function reject(Request $request, Quote $quote): RedirectResponse
    {
        $quote->reject($request->input('reason'));

        return redirect()->route('work_orders.show', $quote->workOrder)
            ->with('success', 'Cotización rechazada. Orden cancelada.');
    }
}
