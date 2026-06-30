<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de pago - {{ $planName }}</title>
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
                                    <td height="4" style="background:linear-gradient(90deg,#6366f1,#a5b4fc);border-radius:12px 12px 0 0;font-size:0;line-height:0;">&nbsp;</td>
                                </tr>
                            </table>
                            <!-- Content -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 32px 24px 32px;">
                                <tr>
                                    <td>
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="44" height="44" style="background-color:#eef2ff;border-radius:50%;text-align:center;vertical-align:middle;font-size:0;">
                                                    <span style="font-size:20px;line-height:44px;">&#9741;</span>
                                                </td>
                                                <td style="padding-left:14px;">
                                                    <h1 style="margin:0;font-size:20px;font-weight:700;color:#1e293b;letter-spacing:-0.3px;">Recibo de pago</h1>
                                                    <p style="margin:2px 0 0 0;font-size:14px;color:#64748b;">Pago recibido correctamente</p>
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
                            <!-- Payment info -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 32px;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 16px 0;font-size:14px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;font-weight:500;">Detalle del pago</p>
                                        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;">
                                            <tr>
                                                <td style="background-color:#f8fafc;border-radius:8px;padding:16px;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;">
                                                        <tr>
                                                            <td>
                                                                <p style="margin:0;font-size:15px;font-weight:600;color:#1e293b;">{{ $planName }}</p>
                                                                <p style="margin:2px 0 0 0;font-size:13px;color:#64748b;">Período: {{ $periodStart->format('d/m/Y') }} - {{ $periodEnd->format('d/m/Y') }}</p>
                                                            </td>
                                                            <td width="140" align="right" style="vertical-align:middle;">
                                                                <p style="margin:0;font-size:22px;font-weight:700;color:#1e293b;">-${{ number_format($amount, 2) }}</p>
                                                                <p style="margin:0;font-size:12px;color:#94a3b8;">MXN</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <!-- Reference and dates -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:0 32px 24px 32px;">
                                <tr>
                                    <td>
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:6px 40px 6px 0;">
                                                    <p style="margin:0;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Referencia</p>
                                                    <p style="margin:4px 0 0 0;font-size:14px;font-weight:500;color:#475569;font-family:Menlo,monospace;">#{{ $invoiceReference }}</p>
                                                </td>
                                                <td style="padding:6px 0;">
                                                    <p style="margin:0;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Pagado el</p>
                                                    <p style="margin:4px 0 0 0;font-size:14px;font-weight:500;color:#475569;">{{ $paidDate->format('d/m/Y') }}</p>
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
                            <!-- Next billing -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:20px 32px 28px 32px;">
                                <tr>
                                    <td>
                                        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;">
                                            <tr>
                                                <td align="center" style="background-color:#f8fafc;border-radius:8px;padding:16px;">
                                                    <p style="margin:0;font-size:13px;color:#64748b;">Próximo corte</p>
                                                    <p style="margin:2px 0 0 0;font-size:18px;font-weight:700;color:#1e293b;">{{ $nextPaymentDate->format('d/m/Y') }}</p>
                                                    <p style="margin:2px 0 0 0;font-size:13px;color:#94a3b8;">El cobro se realizará automáticamente</p>
                                                </td>
                                            </tr>
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
                                Este correo fue enviado automáticamente por tu sistema de suscripciones. Si tienes dudas, contacta a tu administrador.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
