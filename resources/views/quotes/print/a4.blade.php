<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización #{{ $quote->workOrder->work_order_number }}</title>
    <style>
        @page { margin: 15mm; size: A4; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 0;
        }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2563eb; padding-bottom: 15px; }
        .header h1 { font-size: 20px; color: #1e40af; }
        .header p { font-size: 12px; color: #666; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin: 15px 0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .info-table td { padding: 6px 8px; border: 1px solid #e5e7eb; }
        .info-table td:first-child { width: 25%; font-weight: bold; background: #f9fafb; }
        .section-title { font-size: 13px; font-weight: bold; margin: 15px 0 5px; color: #1e40af; }
        .items-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .items-table th { padding: 8px; border: 1px solid #d1d5db; background: #2563eb; color: #fff; font-size: 11px; text-align: center; }
        .items-table td { padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 11px; }
        .items-table td.right { text-align: right; }
        .total-table { width: 300px; border-collapse: collapse; margin-left: auto; margin-top: 10px; }
        .total-table td { padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 12px; }
        .total-table td:first-child { font-weight: bold; background: #f9fafb; }
        .total-table td.right { text-align: right; }
        .grand-total { font-size: 14px; font-weight: bold; background: #eff6ff !important; }
        .terms { margin: 15px 0; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb; }
        .terms p { font-size: 11px; color: #555; }
        .footer { margin-top: 40px; text-align: center; font-size: 11px; color: #888; border-top: 1px solid #e5e7eb; padding-top: 15px; }
        .badge { display: inline-block; padding: 1px 8px; font-size: 10px; border: 1px solid #ccc; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="header">
        @if($tenant->logo)
            <img src="{{ asset('storage/' . $tenant->logo) }}" alt="Logo" style="max-height:60px;margin-bottom:10px;">
        @endif
        <h1>{{ $tenant->name }}</h1>
        <p>{{ $tenant->address }}</p>
        @if($tenant->phone)<p>Tel: {{ $tenant->phone }}</p>@endif
        @if($tenant->email)<p>Email: {{ $tenant->email }}</p>@endif
    </div>

    <div class="title">COTIZACIÓN</div>
    <p style="text-align:center;font-size:14px;font-weight:bold;">#{{ $quote->workOrder->work_order_number }}</p>

    <table class="info-table">
        <tr><td>Fecha</td><td>{{ $quote->created_at->format('d/m/Y') }}</td></tr>
        <tr><td>Estado</td><td>{{ ucfirst($quote->status) }}</td></tr>
        <tr><td>Cliente</td><td>{{ $quote->workOrder->client->name }}</td></tr>
        <tr><td>Teléfono</td><td>{{ $quote->workOrder->client->phone }}</td></tr>
        @if($quote->workOrder->client->email)<tr><td>Email</td><td>{{ $quote->workOrder->client->email }}</td></tr>@endif
        <tr><td>Equipo</td><td>{{ $quote->workOrder->device_brand }} {{ $quote->workOrder->device_model }}</td></tr>
        <tr><td>Orden de Trabajo</td><td>#{{ $quote->workOrder->work_order_number }}</td></tr>
    </table>

    <div class="section-title">Conceptos</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th style="text-align:left;">Descripción</th>
                <th style="width:80px;">Tipo</th>
                <th style="width:50px;">Cant.</th>
                <th style="width:90px;">P. Unit.</th>
                <th style="width:90px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quote->quoteItems as $item)
            <tr>
                <td style="text-align:center;">{{ $loop->iteration }}</td>
                <td>{{ $item->description }}</td>
                <td style="text-align:center;"><span class="badge">{{ $item->type === 'producto' ? 'Producto' : 'Servicio' }}</span></td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                <td class="right">${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:20px;color:#999;">
                    No hay conceptos registrados en esta cotización.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="total-table">
        <tr>
            <td>Subtotal</td>
            <td class="right">${{ number_format($quote->subtotal, 2) }}</td>
        </tr>
        @if($quote->tax_total > 0)
        <tr>
            <td>Impuestos</td>
            <td class="right">${{ number_format($quote->tax_total, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td>Total</td>
            <td class="right">${{ number_format($quote->total, 2) }}</td>
        </tr>
    </table>

    @if($quote->notes)
    <div class="section-title">Notas / Términos</div>
    <div class="terms">
        <p>{{ nl2br(e($quote->notes)) }}</p>
    </div>
    @endif

    <div class="footer">
        <p>{{ $tenant->name }} &bull; {{ $tenant->address }} @if($tenant->phone)&bull; {{ $tenant->phone }}@endif</p>
        <p>¡Gracias por su preferencia!</p>
    </div>
</body>
</html>
