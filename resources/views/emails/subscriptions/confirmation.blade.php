<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Bienvenido a {{ $planName }}!</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding:0 0 24px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size:0;">
                                        <span style="font-size:22px;font-weight:700;color:#1e3a5f;letter-spacing:-0.5px;">RepCellPOS</span>
                                        <span style="font-size:14px;color:#94a3b8;font-weight:400;margin-left:8px;">by Nexacore</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Card -->
                    <tr>
                        <td style="background-color:#ffffff;border-radius:12px;border:1px solid #e2e8f0;padding:0;">
                            <!-- Accent bar -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td height="4" style="background:linear-gradient(90deg,#059669,#34d399);border-radius:12px 12px 0 0;font-size:0;line-height:0;">&nbsp;</td>
                                </tr>
                            </table>
                            <!-- Content -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 32px 24px 32px;">
                                <tr>
                                    <td>
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="44" height="44" style="background-color:#ecfdf5;border-radius:50%;text-align:center;vertical-align:middle;font-size:0;">
                                                    <span style="font-size:22px;line-height:44px;">&#10003;</span>
                                                </td>
                                                <td style="padding-left:14px;">
                                                    <h1 style="margin:0;font-size:20px;font-weight:700;color:#1e293b;letter-spacing:-0.3px;">Suscripción confirmada</h1>
                                                    <p style="margin:2px 0 0 0;font-size:14px;color:#64748b;">Gracias por confiar en RepCellPOS</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:0 32px;"><hr style="border:none;border-top:1px solid #e2e8f0;margin:0;"></td>
                                </tr>
                            </table>
                            <!-- Plan details -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 32px;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 16px 0;font-size:14px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;font-weight:500;">Plan contratado</p>
                                        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;">
                                            <tr>
                                                <td style="background-color:#f8fafc;border-radius:8px;padding:16px;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;">
                                                        <tr>
                                                            <td>
                                                                <p style="margin:0;font-size:18px;font-weight:700;color:#1e293b;">{{ $planName }}</p>
                                                                <p style="margin:4px 0 0 0;font-size:14px;color:#64748b;">{{ $planDescription }}</p>
                                                            </td>
                                                            <td width="120" align="right" style="vertical-align:middle;">
                                                                <p style="margin:0;font-size:22px;font-weight:700;color:#059669;">${{ number_format($amount, 2) }}</p>
                                                                <p style="margin:0;font-size:12px;color:#94a3b8;">MXN / mes</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <!-- Next payment -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:0 32px 24px 32px;">
                                <tr>
                                    <td>
                                        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;">
                                            <tr>
                                                <td style="padding:12px 0;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:24px;">
                                                                <p style="margin:0;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Inicio</p>
                                                                <p style="margin:4px 0 0 0;font-size:15px;font-weight:600;color:#1e293b;">{{ $startDate->format('d/m/Y') }}</p>
                                                            </td>
                                                            <td style="padding-right:24px;">
                                                                <p style="margin:0;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Próximo pago</p>
                                                                <p style="margin:4px 0 0 0;font-size:15px;font-weight:600;color:#1e293b;">{{ $nextPaymentDate->format('d/m/Y') }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:0 32px;"><hr style="border:none;border-top:1px solid #e2e8f0;margin:0;"></td>
                                </tr>
                            </table>
                            <!-- Feature highlights -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 32px;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 12px 0;font-size:13px;color:#64748b;font-weight:500;">Tu plan incluye:</p>
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            @foreach($features as $feature => $enabled)
                                                @if($enabled)
                                                    <tr>
                                                        <td style="padding:4px 0;">
                                                            <table role="presentation" cellpadding="0" cellspacing="0">
                                                                <tr>
                                                                    <td width="16" style="font-size:0;color:#059669;">&#10003;</td>
                                                                    <td style="padding-left:8px;font-size:14px;color:#475569;">
                                                                        @switch($feature)
                                                                            @case('work_orders') Órdenes de Trabajo @break
                                                                            @case('quotes') Cotizaciones @break
                                                                            @case('pos') Punto de Venta (POS) @break
                                                                            @case('notifications_email') Notificaciones Email @break
                                                                            @case('notifications_whatsapp') Notificaciones WhatsApp @break
                                                                            @case('notifications_low_stock') Alertas de Stock Bajo @break
                                                                            @case('reports_advanced') Reportes Avanzados @break
                                                                            @default {{ $feature }}
                                                                        @endswitch
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Spacer -->
                    <tr>
                        <td height="20" style="font-size:0;line-height:0;">&nbsp;</td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding:0 16px;">
                            <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
                                <strong style="color:#64748b;">RepCellPOS</strong> es una marca de<br>
                                <strong style="color:#64748b;">Nexacore Desarrollo e Integración de Sistemas S.A. de C.V.</strong><br>
                                <a href="https://nexacore.com.mx" style="color:#6366f1;text-decoration:none;">nexacore.com.mx</a>
                            </p>
                            <p style="margin:12px 0 0 0;font-size:11px;color:#cbd5e1;line-height:1.5;">
                                Este correo fue enviado automáticamente. Si tienes dudas, responde a este mensaje o contacta a tu administrador.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
