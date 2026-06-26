<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>OT #{{ $workOrder->work_order_number }}</title>
    <style>
        @page { margin: 5mm; size: 80mm auto; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            width: 70mm;
            margin: 0 auto;
            padding: 0;
        }
        .header { text-align: center; margin-bottom: 8px; }
        .header h1 { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .header p { font-size: 10px; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .info { width: 100%; }
        .info td { padding: 2px 0; font-size: 10px; vertical-align: top; }
        .info td:first-child { width: 30%; font-weight: bold; }
        .info td:last-child { width: 70%; }
        .section-title { font-weight: bold; font-size: 10px; margin-top: 6px; text-transform: uppercase; }
        .clauses { font-size: 8px; line-height: 1.3; margin-top: 4px; text-align: justify; }
        .footer { text-align: center; margin-top: 12px; font-size: 9px; }
        @media print { body { width: 70mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    @if(!isset($pdf))
    <div class="no-print" style="text-align:center;padding:10px;background:#eee;margin-bottom:10px;font-family:sans-serif;">
        <button onclick="window.print()" style="padding:8px 16px;font-size:14px;cursor:pointer;">🖨 Imprimir</button>
        <button onclick="window.location.href='{{ route('work_orders.print.pdf', $workOrder) }}'" style="padding:8px 16px;font-size:14px;cursor:pointer;">⬇ Descargar PDF</button>
        <button onclick="window.location.href='{{ route('work_orders.index') }}'" style="padding:8px 16px;font-size:14px;cursor:pointer;background:#4f46e5;color:white;border:none;border-radius:4px;">✕ Cerrar Vista Previa</button>
    </div>
    @endif

    <div class="header">
        <h1>{{ $tenant->name }}</h1>
        <p>{{ $tenant->address }}</p>
        @if($tenant->phone)<p>Tel: {{ $tenant->phone }}</p>@endif
    </div>

    <div class="divider"></div>

    <p style="text-align:center;font-weight:bold;font-size:13px;">COMPROBANTE DE RECEPCIÓN</p>
    <p style="text-align:center;font-size:11px;">OT #{{ $workOrder->work_order_number }}</p>

    <div class="divider"></div>

    <table class="info">
        <tr><td>Fecha:</td><td>{{ $workOrder->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Cliente:</td><td>{{ $workOrder->client->name }}</td></tr>
        <tr><td>Teléfono:</td><td>{{ $workOrder->client->phone }}</td></tr>
        <tr><td>Equipo:</td><td>{{ $workOrder->device_brand }} {{ $workOrder->device_model }}</td></tr>
        @if($workOrder->device_serial)<tr><td>Serie:</td><td>{{ $workOrder->device_serial }}</td></tr>@endif
        @if($workOrder->device_imei)<tr><td>IMEI:</td><td>{{ $workOrder->device_imei }}</td></tr>@endif
        @if($workOrder->unlock_pattern)<tr><td>Patrón:</td><td>{{ $workOrder->unlock_pattern }}</td></tr>@endif
        @if($workOrder->unlock_pin)<tr><td>PIN:</td><td>{{ $workOrder->unlock_pin }}</td></tr>@endif
    </table>

    <div class="section-title">Problema Reportado</div>
    <p style="font-size:10px;">{{ $workOrder->problem_description }}</p>

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

    <div style="margin-top:30px;">
        <p style="font-size:9px;">Firma del Cliente: _________________________________</p>
    </div>

    <div class="footer">
        <p>¡Gracias por su preferencia!</p>
        @if($workOrder->tracking_token)
            @php
                $qrSvg = \App\Helpers\QrCodeHelper::svg(route('tracking.show', $workOrder->tracking_token), 80);
            @endphp
            @if($qrSvg)
                <div style="text-align:center;margin-top:6px;">{!! $qrSvg !!}</div>
                <p style="font-size:8px;margin-top:2px;">Escanee para dar seguimiento</p>
            @endif
            <p style="font-size:7px;margin-top:4px;word-break:break-all;">{{ route('tracking.show', $workOrder->tracking_token) }}</p>
        @endif
    </div>

    @if(!isset($pdf))
    <script>
        window.onafterprint = function(){ window.location.href = '{{ route('work_orders.index') }}'; };
        window.onload = function(){ window.print(); };
    </script>
    @endif
</body>
</html>
