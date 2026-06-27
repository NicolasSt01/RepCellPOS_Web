<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica tu correo</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;">
        <tr>
            <td align="center" style="padding:20px 10px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1);">

                    <tr>
                        <td style="background-color:#2563eb;padding:24px 32px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">{{ $tenant->name }}</h1>
                            <p style="margin:4px 0 0;color:#bfdbfe;font-size:14px;">Verificación de correo electrónico</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px;">
                            <h2 style="margin:0 0 16px;font-size:18px;color:#111827;">¡Bienvenido a RepCellPOS!</h2>
                            <p style="margin:0 0 12px;font-size:14px;color:#374151;line-height:1.6;">
                                Gracias por registrarte, <strong>{{ $user->name }}</strong>. Por favor verifica tu dirección de correo electrónico para comenzar a usar la plataforma.
                            </p>
                            <p style="margin:0 0 24px;font-size:14px;color:#374151;line-height:1.6;">
                                Haz clic en el botón de abajo para confirmar tu correo:
                            </p>
                            <div style="text-align:center;">
                                <a href="{{ route('verification.verify', $user->email_verification_token) }}"
                                   style="display:inline-block;padding:12px 32px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:600;">
                                    Verificar mi correo
                                </a>
                            </div>
                            <p style="margin:24px 0 0;font-size:12px;color:#6b7280;line-height:1.5;">
                                Si no creaste esta cuenta, puedes ignorar este mensaje.
                            </p>
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
