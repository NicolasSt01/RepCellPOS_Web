<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>OT #{{ $workOrder->work_order_number }}</title>
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
        .clauses { margin: 10px 0; padding: 10px; background: #f9fafb; border-radius: 6px; }
        .clauses h3 { font-size: 11px; margin-bottom: 3px; }
        .clauses p { font-size: 10px; color: #555; }
        .footer { margin-top: 40px; text-align: center; font-size: 11px; color: #888; border-top: 1px solid #e5e7eb; padding-top: 15px; }
        .no-print { text-align: center; padding: 10px; background: #eee; margin-bottom: 15px; }
        .no-print button { padding: 8px 16px; font-size: 14px; margin: 0 5px; cursor: pointer; }
        @media print { .no-print { display: none; } }
        .signature { margin-top: 50px; }
        .signature-line { border-top: 1px solid #333; width: 250px; margin-top: 5px; }
    </style>
</head>
<body>
    @if(!isset($pdf))
    <div class="no-print">
        <button onclick="window.print()">🖨 Imprimir</button>
        <button onclick="window.location.href='{{ route('work_orders.print.pdf', $workOrder) }}'">⬇ Descargar PDF</button>
    </div>
    @endif

    <div class="header">
        @if($tenant->logo)
            <img src="{{ asset('storage/' . $tenant->logo) }}" alt="Logo" style="max-height:60px;margin-bottom:10px;">
        @endif
        <h1>{{ $tenant->name }}</h1>
        <p>{{ $tenant->address }}</p>
        @if($tenant->phone)<p>Tel: {{ $tenant->phone }}</p>@endif
        @if($tenant->email)<p>Email: {{ $tenant->email }}</p>@endif
    </div>

    <div class="title">COMPROBANTE DE RECEPCIÓN</div>
    <p style="text-align:center;font-size:14px;font-weight:bold;">Orden de Trabajo #{{ $workOrder->work_order_number }}</p>

    <table class="info-table">
        <tr><td>Fecha de Recepción</td><td>{{ $workOrder->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Cliente</td><td>{{ $workOrder->client->name }}</td></tr>
        <tr><td>Teléfono</td><td>{{ $workOrder->client->phone }}</td></tr>
        <tr><td>Equipo</td><td>{{ $workOrder->device_brand }} {{ $workOrder->device_model }}</td></tr>
        @if($workOrder->device_serial)<tr><td>Número de Serie</td><td>{{ $workOrder->device_serial }}</td></tr>@endif
        @if($workOrder->device_imei)<tr><td>IMEI</td><td>{{ $workOrder->device_imei }}</td></tr>@endif
    </table>

    <div class="section-title">Problema Reportado</div>
    <p style="font-size:12px;margin:5px 0;">{{ $workOrder->problem_description }}</p>

    @if($clauses->count())
        <div class="section-title">Términos y Condiciones</div>
        <div class="clauses">
            @foreach($clauses as $clause)
                <h3>{{ $clause->title }}</h3>
                @if($clause->has_file)
                    <p><a href="{{ $clause->file_url }}" target="_blank" style="color:#2563eb;text-decoration:underline;">Ver política: {{ $clause->file_name }}</a></p>
                @else
                    <p>{{ $clause->content }}</p>
                @endif
            @endforeach
        </div>
    @endif

    @if($workOrder->tracking_token)
        <p style="font-size:12px;margin-top:15px;">
            Siga el estado de su orden: {{ route('tracking.show', $workOrder->tracking_token) }}
        </p>
    @endif

    <div class="signature">
        <p>Firma del Cliente:</p>
        <div class="signature-line" style="border-top:1px solid #333;width:250px;margin-top:5px;"></div>
    </div>

    <div class="footer">
        <p>{{ $tenant->name }} &bull; {{ $tenant->address }} @if($tenant->phone)&bull; {{ $tenant->phone }}@endif</p>
        <p>¡Gracias por su preferencia!</p>
    </div>

    @if(!isset($pdf))
    <script>window.onload=function(){window.print();}</script>
    @endif
</body>
</html>
