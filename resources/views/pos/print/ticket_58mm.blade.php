<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $sale->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 58mm;
            margin: 0 auto;
            padding: 5px;
            font-size: 10px;
            background-color: #fff;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mt-2 { margin-top: 5px; }
        .mb-2 { margin-bottom: 5px; }
        .border-t { border-top: 1px dashed #000; }
        .border-b { border-bottom: 1px dashed #000; }
        .py-1 { padding-top: 3px; padding-bottom: 3px; }
        .py-2 { padding-top: 5px; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px 0; text-align: left; }
        th { border-bottom: 1px dashed #000; }
        .item-row td { vertical-align: top; }
        
        @media print {
            @page { margin: 0; size: 58mm auto; }
            body { margin: 0; padding: 0; }
            #print-button { display: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="{{ ($preview ?? false) ? '' : 'window.print()' }}">

    <div class="text-center mb-2">
        <h2 class="font-bold" style="margin:0; font-size: 14px;">{{ $tenant->name }}</h2>
        @if($tenant->address)<p style="margin:2px 0;">{{ $tenant->address }}</p>@endif
        @if($tenant->phone)<p style="margin:2px 0;">Tel: {{ $tenant->phone }}</p>@endif
    </div>

    <div class="border-t py-2 border-b mb-2">
        <p style="margin:2px 0;">Tk: #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</p>
        <p style="margin:2px 0;">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
        <p style="margin:2px 0;">Caj: {{ $sale->user->name }}</p>
    </div>

@php
    $saleClient = $sale->workOrder?->client;
@endphp
@if($saleClient)
    <p style="font-size:9px;margin:4px 0 0 0;">Cliente: {{ $saleClient->name }}@if($saleClient->phone) — Tel: {{ $saleClient->phone }}@endif</p>
@endif

    <table class="mb-2">
        <thead>
            <tr>
                <th>C</th>
                <th>Desc</th>
                <th class="text-right">Imp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
            <tr class="item-row">
                <td>{{ $item->quantity }}</td>
                <td>{{ \Illuminate\Support\Str::limit($item->description, 15) }}<br><small>${{ number_format($item->unit_price, 2) }}</small></td>
                <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-t pt-2">
        <table style="width: 100%;">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">${{ number_format($sale->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>IVA:</td>
                <td class="text-right">${{ number_format($sale->tax_total, 2) }}</td>
            </tr>
            <tr>
                <td class="font-bold" style="font-size: 12px; padding-top: 5px;">Total:</td>
                <td class="text-right font-bold" style="font-size: 12px; padding-top: 5px;">${{ number_format($sale->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="border-t mt-2 pt-2">
        <p style="margin:2px 0;">Pago: {{ ucfirst($sale->payment_method) }}</p>
        @if($sale->payment_method === 'efectivo' || $sale->payment_method === 'mixto')
        <table style="width: 100%;">
            <tr>
                <td>Efectivo:</td>
                <td class="text-right">${{ number_format($sale->cash_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Cambio:</td>
                <td class="text-right">${{ number_format($sale->change_amount, 2) }}</td>
            </tr>
        </table>
        @endif
        @if($sale->payment_method === 'tarjeta_transferencia' || $sale->payment_method === 'mixto')
        <table style="width: 100%;">
            <tr>
                <td>Tarjeta:</td>
                <td class="text-right">${{ number_format($sale->card_amount, 2) }}</td>
            </tr>
            @if($sale->payment_reference)
            <tr>
                <td>Ref:</td>
                <td class="text-right">{{ $sale->payment_reference }}</td>
            </tr>
            @endif
        </table>
        @endif
    </div>

@if(($clauses ?? null) && count($clauses) > 0)
    <hr style="border-top:1px dashed #000;margin:8px 0;">
    @foreach($clauses as $clause)
        <p style="font-size:8px;margin:2px 0;text-align:center;">{{ $clause->content }}</p>
    @endforeach
@endif

    <div class="text-center mt-2 pt-2 border-t" style="font-size: 9px;">
        <p>¡Gracias por su compra!</p>
    </div>

@if($preview ?? false)
    <div class="no-print" style="text-align:center;margin-top:20px;font-family:sans-serif;">
        <button onclick="window.print()" style="padding:10px 30px;font-size:16px;cursor:pointer;">🖨 Imprimir</button>
    </div>
@endif

</body>
</html>
