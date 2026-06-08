<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket #{{ $sale->id }}</title>
    <style>
        @page { margin: 0; size: 80mm auto; }
        body { font-family: 'Courier New', monospace; font-size: 11px; width: 72mm; margin: 0 auto; padding: 3mm; }
        .header { text-align: center; margin-bottom: 4mm; }
        .header h2 { margin: 0; font-size: 14px; }
        .header p { margin: 1mm 0; font-size: 10px; }
        hr { border: none; border-top: 1px dashed #000; margin: 2mm 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; font-size: 10px; padding: 1px 2px; }
        th { border-bottom: 1px solid #000; }
        .item-desc { width: 50%; }
        .item-qty, .item-price, .item-total { text-align: right; width: 16%; }
        .totals td { font-weight: bold; padding: 2px; }
        .totals td:last-child { text-align: right; }
        .grand-total { font-size: 14px; font-weight: bold; }
        .payment-info { margin-top: 3mm; font-size: 10px; }
        .footer { text-align: center; margin-top: 4mm; font-size: 9px; }
        @media print { html, body { width: 80mm; } }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $tenant->name }}</h2>
        @if($tenant->address)<p>{{ $tenant->address }}</p>@endif
        @if($tenant->phone)<p>Tel: {{ $tenant->phone }}</p>@endif
        <p>Ticket #{{ $sale->id }}</p>
        <p>{{ $sale->created_at->format('d/m/Y H:i') }}</p>
        <p>Atendió: {{ $sale->user->name }}</p>
    </div>
    <hr>
    <table>
        <thead>
            <tr>
                <th class="item-desc">Artículo</th>
                <th class="item-qty">Cant</th>
                <th class="item-price">P/U</th>
                <th class="item-total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
            <tr>
                <td class="item-desc">{{ $item->description }}</td>
                <td class="item-qty">{{ $item->quantity }}</td>
                <td class="item-price">${{ number_format($item->unit_price, 2) }}</td>
                <td class="item-total">${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <table class="totals">
        <tr><td>Subtotal</td><td>${{ number_format($sale->subtotal, 2) }}</td></tr>
        @if($sale->tax_total > 0)
        <tr><td>IVA</td><td>${{ number_format($sale->tax_total, 2) }}</td></tr>
        @endif
        @if($sale->discount > 0)
        <tr><td>Descuento</td><td>-${{ number_format($sale->discount, 2) }}</td></tr>
        @endif
        <tr class="grand-total"><td>TOTAL</td><td>${{ number_format($sale->total, 2) }}</td></tr>
    </table>
    <hr>
    <div class="payment-info">
        <strong>Método de pago:</strong>
        @switch($sale->payment_method)
            @case('efectivo')
                Efectivo — Recibido: ${{ number_format($sale->cash_amount, 2) }}
                @if($sale->change_amount > 0)
                    <br>Cambio: ${{ number_format($sale->change_amount, 2) }}
                @endif
                @break
            @case('tarjeta_transferencia')
                Tarjeta / Transferencia — Folio: {{ $sale->payment_reference }}
                @break
            @case('mixto')
                Efectivo: ${{ number_format($sale->cash_amount, 2) }}<br>
                Tarjeta: ${{ number_format($sale->card_amount, 2) }} — Folio: {{ $sale->payment_reference }}
                @if($sale->change_amount > 0)
                    <br>Cambio: ${{ number_format($sale->change_amount, 2) }}
                @endif
                @break
        @endswitch
    </div>
    <div class="footer">
        <p>¡Gracias por su preferencia!</p>
    </div>
    @if(!($preview ?? false))<script>window.print();</script>@endif
</body>
</html>
