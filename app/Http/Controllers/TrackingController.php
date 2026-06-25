<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TrackingController extends Controller
{
    public function show(string $token): View
    {
        $workOrder = WorkOrder::where('tracking_token', $token)
            ->with(['client', 'tenant', 'quote.quoteItems.product'])
            ->firstOrFail();

        return view('tracking.show', compact('workOrder'));
    }

    public function approveQuote(string $token): RedirectResponse
    {
        $workOrder = WorkOrder::where('tracking_token', $token)
            ->with(['quote.quoteItems.product'])
            ->firstOrFail();

        $quote = $workOrder->quote;

        if (!$quote || $quote->status !== 'enviada') {
            return redirect()->route('tracking.show', $token)
                ->with('error', 'La cotización no está disponible para aprobación.');
        }

        try {
            DB::transaction(function () use ($quote, $workOrder) {
                $quote->refresh();

                foreach ($quote->quoteItems as $item) {
                    if ($item->product_id && $item->product) {
                        if ($item->product->availableStock() < $item->quantity) {
                            throw new \RuntimeException(
                                "Stock insuficiente para {$item->product->name}. Disponible: {$item->product->availableStock()}, requerido: {$item->quantity}."
                            );
                        }
                    }
                }

                $quote->reserveStock();
                $quote->update(['status' => 'aprobada']);
                $workOrder->update(['status' => 'cotizacion_aprobada']);
                $workOrder->addTimelineEvent('cotizacion_aprobada', 'Cliente', 'Cotización aprobada por el cliente — stock reservado');
            });

            try {
                app(NotificationService::class)->send($workOrder, 'quote_approved');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error notificando aprobación desde tracking: ' . $e->getMessage());
            }

            return redirect()->route('tracking.show', $token)
                ->with('success', 'Cotización aprobada correctamente.');
        } catch (\RuntimeException $e) {
            return redirect()->route('tracking.show', $token)
                ->with('error', $e->getMessage());
        }
    }

    public function rejectQuote(Request $request, string $token): RedirectResponse
    {
        $workOrder = WorkOrder::where('tracking_token', $token)
            ->with(['quote'])
            ->firstOrFail();

        $quote = $workOrder->quote;

        if (!$quote || $quote->status !== 'enviada') {
            return redirect()->route('tracking.show', $token)
                ->with('error', 'La cotización no está disponible para rechazo.');
        }

        $reason = $request->input('reason', '');

        DB::transaction(function () use ($quote, $workOrder, $reason) {
            $quote->update(['status' => 'rechazada', 'cancellation_reason' => $reason]);
            $workOrder->update(['status' => 'cancelada']);
            $workOrder->addTimelineEvent('cancelada', 'Cliente', 'Cotización rechazada por el cliente.' . ($reason ? " Motivo: {$reason}" : ''));
        });

        try {
            app(NotificationService::class)->send($workOrder, 'quote_rejected');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error notificando rechazo desde tracking: ' . $e->getMessage());
        }

        return redirect()->route('tracking.show', $token)
            ->with('info', 'Cotización rechazada.');
    }
}
