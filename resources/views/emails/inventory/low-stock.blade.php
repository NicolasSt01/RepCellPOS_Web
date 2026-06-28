<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Stock Bajo</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;">
        <tr>
            <td align="center" style="padding:20px 10px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1);">

                    <tr>
                        <td style="background-color:#dc2626;padding:24px 32px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">⚠️ Stock Bajo</h1>
                            <p style="margin:4px 0 0;color:#fca5a5;font-size:14px;">{{ $tenant->name }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;font-size:14px;color:#374151;line-height:1.6;">
                                Hola <strong>{{ $admin->name }}</strong>, los siguientes productos han alcanzado su stock mínimo después de una venta:
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="padding:0;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr style="background-color:#f9fafb;">
                                                <th style="padding:10px 12px;font-size:12px;color:#6b7280;text-align:left;border-bottom:1px solid #e5e7eb;">Producto</th>
                                                <th style="padding:10px 12px;font-size:12px;color:#6b7280;text-align:center;border-bottom:1px solid #e5e7eb;">Stock Actual</th>
                                                <th style="padding:10px 12px;font-size:12px;color:#6b7280;text-align:center;border-bottom:1px solid #e5e7eb;">Stock Mínimo</th>
                                            </tr>
                                            @foreach ($products as $product)
                                            <tr>
                                                <td style="padding:10px 12px;font-size:14px;color:#111827;border-bottom:1px solid #f3f4f6;">{{ $product->name }}</td>
                                                <td style="padding:10px 12px;font-size:14px;color:#dc2626;font-weight:600;text-align:center;border-bottom:1px solid #f3f4f6;">{{ $product->stock }}</td>
                                                <td style="padding:10px 12px;font-size:14px;color:#374151;text-align:center;border-bottom:1px solid #f3f4f6;">{{ $product->min_stock }}</td>
                                            </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <div style="text-align:center;margin-top:24px;">
                                <a href="{{ route('products.index') }}?low_stock=1"
                                   style="display:inline-block;padding:12px 32px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:600;">
                                    Ver inventario
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#f9fafb;padding:16px 32px;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                {{ $tenant->name }} &bull; RepCellPOS
                            </p>
                        </td>
                    </tr>
                </table>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="padding:12px 10px;">
                            <p style="margin:0;font-size:11px;color:#9ca3af;">
                                Este correo fue enviado automáticamente por RepCellPOS.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
