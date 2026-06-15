<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>OT #{{ $workOrder->work_order_number }}</title>
    <style>
        @page { margin: 5mm; size: 58mm auto; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            width: 48mm;
            margin: 0 auto;
            padding: 0;
        }
        .header { text-align: center; margin-bottom: 6px; }
        .header h1 { font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .header p { font-size: 9px; }
        .divider { border-top: 1px dashed #000; margin: 4px 0; }
        .info { width: 100%; }
        .info td { padding: 1px 0; font-size: 9px; vertical-align: top; }
        .info td:first-child { width: 35%; font-weight: bold; }
        .info td:last-child { width: 65%; }
        .section-title { font-weight: bold; font-size: 9px; margin-top: 4px; text-transform: uppercase; }
        .clauses { font-size: 7px; line-height: 1.2; margin-top: 4px; text-align: justify; }
        .footer { text-align: center; margin-top: 8px; font-size: 8px; }
        @media print { body { width: 48mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    @if(!isset($pdf))
    <div class="no-print" style="text-align:center;padding:10px;background:#eee;margin-bottom:10px;font-family:sans-serif;">
        <button onclick="window.print()" style="padding:8px 16px;font-size:14px;cursor:pointer;">🖨 Imprimir</button>
        <button onclick="window.location.href='{{ route('work_orders.print.pdf', $workOrder) }}'" style="padding:8px 16px;font-size:14px;cursor:pointer;">⬇ Descargar PDF</button>
    </div>
    @endif

    <div class="header">
        <h1>{{ $tenant->name }}</h1>
        <p>{{ $tenant->address }}</p>
        @if($tenant->phone)<p>Tel: {{ $tenant->phone }}</p>@endif
    </div>

    <div class="divider"></div>

    <p style="text-align:center;font-weight:bold;font-size:11px;">COMPROBANTE DE RECEPCIÓN</p>
    <p style="text-align:center;">OT #{{ $workOrder->work_order_number }}</p>

    <div class="divider"></div>

    <table class="info">
        <tr><td>Fecha:</td><td>{{ $workOrder->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Cliente:</td><td>{{ $workOrder->client->name }}</td></tr>
        <tr><td>Teléfono:</td><td>{{ $workOrder->client->phone }}</td></tr>
        <tr><td>Equipo:</td><td>{{ $workOrder->device_brand }} {{ $workOrder->device_model }}</td></tr>
        @if($workOrder->device_serial)<tr><td>Serie:</td><td>{{ $workOrder->device_serial }}</td></tr>@endif
        @if($workOrder->device_imei)<tr><td>IMEI:</td><td>{{ $workOrder->device_imei }}</td></tr>@endif
    </table>

    <div class="section-title">Problema Reportado:</div>
    <p style="font-size:9px;">{{ $workOrder->problem_description }}</p>

    @if($clauses->count())
        <div class="divider"></div>
        <div class="section-title">Términos y Condiciones</div>
        @foreach($clauses as $clause)
            <div class="clauses">
                <strong>{{ $clause->title }}:</strong>
                @if($clause->has_file)
                    <a href="{{ $clause->file_url }}" target="_blank" style="color:#2563eb;text-decoration:underline;">Ver política</a>
                @else
                    {{ $clause->content }}
                @endif
            </div>
        @endforeach
    @endif

    <div class="divider"></div>

    <div style="margin-top:20px;">
        <p style="font-size:8px;">Firma del Cliente: _________________________________</p>
    </div>

    <div class="footer">
        <p>¡Gracias por su preferencia!</p>
        @if($workOrder->tracking_token)
            <p>Siga su orden: {{ route('tracking.show', $workOrder->tracking_token) }}</p>
        @endif
    </div>

    @if(!isset($pdf))
    <script>window.onload=function(){window.print();}</script>
    @endif
</body>
</html>
