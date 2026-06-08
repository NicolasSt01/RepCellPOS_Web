<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket #{{ $sale->id }}</title>
    <style>
        @page { margin: 15mm; size: A4; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 10mm; border-bottom: 2px solid #333; padding-bottom: 5mm; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 2mm 0; font-size: 12px; color: #666; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 5mm; }
        .info-box { width: 48%; }
        .info-box h3 { margin: 0 0 2mm; font-size: 11px; color: #666; text-transform: uppercase; }
        .info-box p { margin: 1mm 0; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
        th { background: #f5f5f5; text-align: left; padding: 6px 8px; font-size: 11px; border-bottom: 2px solid #ddd; }
        td { padding: 6px 8px; font-size: 11px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { width: 300px; margin-left: auto; }
        .totals td { border: none; padding: 3px 8px; }
        .totals .grand-total { font-size: 16px; font-weight: bold; border-top: 2px solid #333; padding-top: 5px; }
        .payment-info { margin-top: 5mm; padding: 4mm; background: #f9f9f9; border-radius: 4px; }
        .payment-info h3 { margin: 0 0 2mm; font-size: 12px; }
        .payment-info p { margin: 1mm 0; font-size: 11px; }
        .footer { text-align: center; margin-top: 10mm; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 5mm; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $tenant->name }}</h1>
        <p>
            @if($tenant->address){{ $tenant->address }} — @endif
            @if($tenant->phone)Tel: {{ $tenant->phone }} @endif
            @if($tenant->email) | {{ $tenant->email }} @endif
        </p>
        <p><strong>Ticket #{{ $sale->id }}</strong> — {{ $sale->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-row">
        <div class="info-box">
            <h3>Cliente</h3>
            <p>{{ $sale->client->name ?? 'Público general' }}</p>
            @if($sale->client && $sale->client->phone)
                <p>Tel: {{ $sale->client->phone }}</p>
            @endif
        </div>
        <div class="info-box">
            <h3>Atendido por</h3>
            <p>{{ $sale->user->name }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Artículo</th>
                <th class="text-center">Cant</th>
                <th class="text-right">P/U</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-right">${{ number_format($sale->subtotal, 2) }}</td></tr>
        @if($sale->tax_total > 0)
        <tr><td>IVA</td><td class="text-right">${{ number_format($sale->tax_total, 2) }}</td></tr>
        @endif
        @if($sale->discount > 0)
        <tr><td>Descuento</td><td class="text-right">-${{ number_format($sale->discount, 2) }}</td></tr>
        @endif
        <tr class="grand-total"><td>TOTAL</td><td class="text-right">${{ number_format($sale->total, 2) }}</td></tr>
    </table>

    <div class="payment-info">
        <h3>Información de Pago</h3>
        <p><strong>Método:</strong>
            @switch($sale->payment_method)
                @case('efectivo') Efectivo @break
                @case('tarjeta_transferencia') Tarjeta / Transferencia @break
                @case('mixto') Mixto (Efectivo + Tarjeta) @break
            @endswitch
        </p>
        @if($sale->cash_amount > 0)
            <p><strong>Efectivo:</strong> ${{ number_format($sale->cash_amount, 2) }}</p>
        @endif
        @if($sale->card_amount > 0)
            <p><strong>Tarjeta:</strong> ${{ number_format($sale->card_amount, 2) }}</p>
        @endif
        @if($sale->payment_reference)
            <p><strong>Folio:</strong> {{ $sale->payment_reference }}</p>
        @endif
        @if($sale->change_amount > 0)
            <p><strong>Cambio:</strong> ${{ number_format($sale->change_amount, 2) }}</p>
        @endif
    </div>

    <div class="footer">
        <p>¡Gracias por su preferencia!</p>
    </div>

    @if(!($preview ?? false))<script>window.print();</script>@endif
</body>
</html>
