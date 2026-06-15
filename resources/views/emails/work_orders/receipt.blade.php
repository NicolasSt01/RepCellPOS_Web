<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Trabajo</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;">
        <tr>
            <td align="center" style="padding:20px 10px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1);">

                    <tr>
                        <td style="background-color:#2563eb;padding:24px 32px;text-align:center;">
                            @if($tenant->logo)
                                <img src="{{ asset('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}" style="max-height:50px;margin-bottom:12px;">
                            @endif
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">{{ $tenant->name }}</h1>
                            <p style="margin:4px 0 0;color:#bfdbfe;font-size:14px;">Orden de Trabajo #{{ $workOrder->work_order_number }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 32px 0;text-align:center;">
                            <span style="display:inline-block;padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;text-transform:uppercase;background-color:#dbeafe;color:#1d4ed8;">
                                {{ ucfirst($workOrder->status) }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom:12px;font-size:14px;color:#6b7280;">Fecha de recepción</td>
                                    <td style="padding-bottom:12px;font-size:14px;font-weight:600;color:#111827;text-align:right;">
                                        {{ $workOrder->created_at->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom:12px;font-size:14px;color:#6b7280;">Cliente</td>
                                    <td style="padding-bottom:12px;font-size:14px;font-weight:600;color:#111827;text-align:right;">
                                        {{ $workOrder->client->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom:12px;font-size:14px;color:#6b7280;">Teléfono</td>
                                    <td style="padding-bottom:12px;font-size:14px;font-weight:600;color:#111827;text-align:right;">
                                        {{ $workOrder->client->phone }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom:12px;font-size:14px;color:#6b7280;">Equipo</td>
                                    <td style="padding-bottom:12px;font-size:14px;font-weight:600;color:#111827;text-align:right;">
                                        {{ $workOrder->device_brand }} {{ $workOrder->device_model }}
                                    </td>
                                </tr>
                                @if($workOrder->device_serial)
                                <tr>
                                    <td style="padding-bottom:12px;font-size:14px;color:#6b7280;">Serie</td>
                                    <td style="padding-bottom:12px;font-size:14px;font-weight:600;color:#111827;text-align:right;">
                                        {{ $workOrder->device_serial }}
                                    </td>
                                </tr>
                                @endif
                                @if($workOrder->device_imei)
                                <tr>
                                    <td style="padding-bottom:12px;font-size:14px;color:#6b7280;">IMEI</td>
                                    <td style="padding-bottom:12px;font-size:14px;font-weight:600;color:#111827;text-align:right;">
                                        {{ $workOrder->device_imei }}
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px;">
                            <hr style="border:none;border-top:1px solid #e5e7eb;margin:0;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 32px;">
                            <h2 style="margin:0 0 8px;font-size:14px;font-weight:600;color:#111827;">Problema Reportado</h2>
                            <p style="margin:0;font-size:14px;color:#374151;line-height:1.5;">
                                {{ $workOrder->problem_description }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 20px;text-align:center;">
                            <a href="{{ route('tracking.show', $workOrder->tracking_token) }}"
                               style="display:inline-block;padding:12px 24px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:600;">
                                Dar seguimiento a mi orden
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#f9fafb;padding:16px 32px;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                {{ $tenant->name }} &bull; {{ $tenant->address }}
                                @if($tenant->phone)<br>{{ $tenant->phone }}@endif
                            </p>
                        </td>
                    </tr>
                </table>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="padding:12px 10px;">
                            <p style="margin:0;font-size:11px;color:#9ca3af;">
                                Este correo fue enviado automáticamente por {{ $tenant->name }}.
                                No responder a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
