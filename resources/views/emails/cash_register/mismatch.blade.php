<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        h1 { color: #dc2626; font-size: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th { background: #f3f4f6; text-align: left; padding: 8px; font-size: 13px; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        .highlight { font-weight: bold; color: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Incidente en cierre de caja</h1>
        <p>Se ha detectado una discrepancia en el cierre de caja de <strong>{{ $tenant->name }}</strong>.</p>

        <table>
            <tr><th colspan="2">Detalles del incidente</th></tr>
            <tr><td>Cajero</td><td>{{ $cashRegister->user->name }}</td></tr>
            <tr><td>Apertura</td><td>${{ number_format($cashRegister->opening_amount, 2) }}</td></tr>
            <tr><td>Efectivo esperado</td><td>${{ number_format($incident->expected_amount, 2) }}</td></tr>
            <tr><td>Efectivo contado</td><td>${{ number_format($incident->actual_amount, 2) }}</td></tr>
            <tr><td>Diferencia</td><td class="highlight">${{ number_format($incident->difference, 2) }}</td></tr>
            <tr><td>Fecha de cierre</td><td>{{ $cashRegister->closed_at->format('d/m/Y H:i') }}</td></tr>
            @if($incident->notes)
            <tr><td>Notas</td><td>{{ $incident->notes }}</td></tr>
            @endif
        </table>

        <p>Revisa los movimientos y toma las medidas necesarias.</p>
    </div>
</body>
</html>
