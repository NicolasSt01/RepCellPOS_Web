<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Venta #{{ $sale->id }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: sans-serif; background-color: #f3f4f6; color: #111827; }
        .print-container { max-width: 210mm; margin: 20px auto; padding: 20mm; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        @media print {
            body { background-color: white; }
            .print-container { margin: 0; padding: 10mm; box-shadow: none; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="{{ ($preview ?? false) ? '' : 'window.print()' }}">

    <div class="print-container">
        <!-- Header -->
        <div class="flex justify-between items-start border-b pb-6 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $tenant->name }}</h1>
                <div class="mt-2 text-sm text-gray-600">
                    @if($tenant->address)<p>{{ $tenant->address }}</p>@endif
                    @if($tenant->phone)<p>Tel: {{ $tenant->phone }}</p>@endif
                    @if($tenant->email)<p>Email: {{ $tenant->email }}</p>@endif
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-semibold text-gray-800">COMPROBANTE DE VENTA</h2>
                <p class="text-gray-600 mt-1">Folio: <span class="font-medium text-gray-900">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</span></p>
                <p class="text-gray-600">Fecha: <span class="font-medium text-gray-900">{{ $sale->created_at->format('d/m/Y H:i') }}</span></p>
                <p class="text-gray-600">Atendido por: <span class="font-medium text-gray-900">{{ $sale->user->name }}</span></p>
@php
    $saleClient = $sale->workOrder?->client;
@endphp
@if($saleClient)
    <p class="text-sm text-gray-600"><span class="font-semibold">Cliente:</span> {{ $saleClient->name }}@if($saleClient->phone) — {{ $saleClient->phone }}@endif</p>
@endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-left border-collapse mb-8">
            <thead>
                <tr class="bg-gray-50 border-b border-t">
                    <th class="py-3 px-4 font-semibold text-sm text-gray-700 w-16 text-center">Cant.</th>
                    <th class="py-3 px-4 font-semibold text-sm text-gray-700">Descripción</th>
                    <th class="py-3 px-4 font-semibold text-sm text-gray-700 text-right w-32">Precio Unit.</th>
                    <th class="py-3 px-4 font-semibold text-sm text-gray-700 text-right w-32">Importe</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($sale->saleItems as $item)
                <tr>
                    <td class="py-3 px-4 text-sm text-gray-700 text-center">{{ $item->quantity }}</td>
                    <td class="py-3 px-4 text-sm text-gray-900">{{ $item->description }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700 text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="py-3 px-4 text-sm text-gray-900 font-medium text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end mb-8">
            <div class="w-72">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-sm text-gray-600">Subtotal:</span>
                    <span class="text-sm font-medium text-gray-900">${{ number_format($sale->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-sm text-gray-600">Impuestos:</span>
                    <span class="text-sm font-medium text-gray-900">${{ number_format($sale->tax_total, 2) }}</span>
                </div>
                <div class="flex justify-between py-3 border-b-2 border-gray-900">
                    <span class="text-base font-bold text-gray-900">Total a Pagar:</span>
                    <span class="text-base font-bold text-gray-900">${{ number_format($sale->total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8 border border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wider">Detalles del Pago</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                <div>
                    <p class="text-sm text-gray-600">Método de pago:</p>
                    <p class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' / ', $sale->payment_method)) }}</p>
                </div>
                
                @if($sale->payment_method === 'efectivo' || $sale->payment_method === 'mixto')
                <div>
                    <p class="text-sm text-gray-600">Efectivo recibido:</p>
                    <p class="font-medium text-gray-900">${{ number_format($sale->cash_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Cambio devuelto:</p>
                    <p class="font-medium text-gray-900">${{ number_format($sale->change_amount, 2) }}</p>
                </div>
                @endif
                
                @if($sale->payment_method === 'tarjeta_transferencia' || $sale->payment_method === 'mixto')
                <div>
                    <p class="text-sm text-gray-600">Monto en tarjeta/transferencia:</p>
                    <p class="font-medium text-gray-900">${{ number_format($sale->card_amount, 2) }}</p>
                </div>
                @if($sale->payment_reference)
                <div>
                    <p class="text-sm text-gray-600">Folio/Referencia:</p>
                    <p class="font-medium text-gray-900">{{ $sale->payment_reference }}</p>
                </div>
                @endif
                @endif
            </div>
        </div>

@if(($clauses ?? null) && count($clauses) > 0)
        <div class="mt-6 border-t border-gray-300 pt-4">
            @foreach($clauses as $clause)
                <p class="text-xs text-gray-500 mb-1">{{ $clause->content }}</p>
            @endforeach
        </div>
@endif

        <!-- Footer -->
        <div class="mt-12 text-center text-sm text-gray-500 border-t pt-8">
            <p>¡Gracias por su preferencia!</p>
            <p class="mt-1 text-xs">Este documento es un comprobante de venta interno y no tiene validez fiscal oficial.</p>
        </div>
    </div>

@if($preview ?? false)
    <div class="no-print" style="text-align:center;margin-top:20px;font-family:sans-serif;">
        <button onclick="window.print()" style="padding:10px 30px;font-size:16px;cursor:pointer;">🖨 Imprimir</button>
    </div>
@endif

</body>
</html>
