<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo tenant registrado</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;">
        <tr>
            <td align="center" style="padding:20px 10px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1);">

                    <tr>
                        <td style="background-color:#059669;padding:24px 32px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">RepCellPOS</h1>
                            <p style="margin:4px 0 0;color:#a7f3d0;font-size:14px;">Nuevo registro de empresa</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px;">
                            <h2 style="margin:0 0 16px;font-size:18px;color:#111827;">Nuevo tenant registrado</h2>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom:8px;font-size:14px;color:#6b7280;">Empresa</td>
                                    <td style="padding-bottom:8px;font-size:14px;font-weight:600;color:#111827;text-align:right;">{{ $tenant->name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom:8px;font-size:14px;color:#6b7280;">Teléfono</td>
                                    <td style="padding-bottom:8px;font-size:14px;font-weight:600;color:#111827;text-align:right;">{{ $tenant->phone }}</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom:8px;font-size:14px;color:#6b7280;">Admin</td>
                                    <td style="padding-bottom:8px;font-size:14px;font-weight:600;color:#111827;text-align:right;">{{ $admin->name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom:8px;font-size:14px;color:#6b7280;">Email</td>
                                    <td style="padding-bottom:8px;font-size:14px;font-weight:600;color:#111827;text-align:right;">{{ $admin->email }}</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom:8px;font-size:14px;color:#6b7280;">Plan</td>
                                    <td style="padding-bottom:8px;font-size:14px;font-weight:600;color:#111827;text-align:right;">{{ $tenant->plan->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom:8px;font-size:14px;color:#6b7280;">Registrado</td>
                                    <td style="padding-bottom:8px;font-size:14px;font-weight:600;color:#111827;text-align:right;">{{ $tenant->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>

                            <div style="text-align:center;margin-top:24px;">
                                <a href="{{ url('/admin') }}"
                                   style="display:inline-block;padding:12px 32px;background-color:#059669;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:600;">
                                    Ir al panel de administración
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#f9fafb;padding:16px 32px;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                RepCellPOS &bull; Panel de administración
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
