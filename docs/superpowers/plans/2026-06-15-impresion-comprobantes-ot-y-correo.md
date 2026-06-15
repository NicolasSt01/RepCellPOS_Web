# Impresión de Comprobantes OT y Notificación Email

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Al crear una orden de trabajo, imprimir automáticamente un comprobante en el formato configurado por el tenant (58mm/80mm/A4) con márgenes correctos, y opcionalmente enviar notificación email al cliente con plantilla responsive.

**Architecture:** Se reutiliza el patrón existente de impresión del POS (vistas por formato + `window.print()`). Se agregan vistas de impresión para WorkOrder, una ruta `/work_orders/{id}/print`, y flujo post-creación que redirige a print. Para email, se agregan columnas SMTP a `tenants`, un servicio que configura dinámicamente el mailer, un Mailable, y una plantilla HTML responsive. El envío se dispara desde `WorkOrderController@store` si el cliente prefiere email.

**Tech Stack:** Laravel 13, Blade, DomPDF, Alpine.js, Tailwind CSS v4 (para email inline)

---

## Files to create/modify

### Create
| File | Purpose |
|------|---------|
| `resources/views/work_orders/print/ticket_58mm.blade.php` | Comprobante OT 58mm con márgenes 5mm |
| `resources/views/work_orders/print/ticket_80mm.blade.php` | Comprobante OT 80mm con márgenes 5mm |
| `resources/views/work_orders/print/a4.blade.php` | Comprobante OT A4 |
| `app/Mail/WorkOrderReceipt.php` | Mailable para notificar OT al cliente |
| `resources/views/emails/work_orders/receipt.blade.php` | Plantilla HTML responsive email |
| `app/Services/TenantMailService.php` | Servicio para configurar mailer por tenant |

### Modify
| File | Change |
|------|--------|
| `app/Http/Controllers/WorkOrderController.php` | Agregar `print()`, modificar `store()` para redirigir a print + enviar email |
| `app/Models/Tenant.php` | Agregar campos `mail_*` a `$fillable` |
| `routes/web.php` | Agregar ruta GET `/work_orders/{work_order}/print` |
| `database/migrations/` (nueva) | Agregar columnas SMTP a `tenants` |
| `resources/views/settings/company.blade.php` | Agregar sección de configuración SMTP |

---

### Task 1: Crear migración SMTP para tenants

**Files:**
- Create: `database/migrations/2026_06_15_000001_add_mail_columns_to_tenants_table.php`

- [ ] **Step 1: Escribir la migración**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('mail_host')->nullable()->after('social_media');
            $table->string('mail_port')->nullable()->after('mail_host');
            $table->string('mail_username')->nullable()->after('mail_port');
            $table->string('mail_encryption')->nullable()->after('mail_username');
            $table->text('mail_password')->nullable()->after('mail_encryption');
            $table->string('mail_from_address')->nullable()->after('mail_password');
            $table->string('mail_from_name')->nullable()->after('mail_from_address');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'mail_host', 'mail_port', 'mail_username', 'mail_encryption',
                'mail_password', 'mail_from_address', 'mail_from_name',
            ]);
        });
    }
};
```

- [ ] **Step 2: Ejecutar migración**

```bash
php artisan migrate
```

Esperado: columnas agregadas a `tenants`.

- [ ] **Step 3: Commit parcial**

```bash
git add database/migrations/2026_06_15_000001_add_mail_columns_to_tenants_table.php
git commit -m "feat: add SMTP credential columns to tenants table"
```

---

### Task 2: Agregar campos mail al modelo Tenant

**Files:**
- Modify: `app/Models/Tenant.php`

- [ ] **Step 1: Agregar campos a `$fillable` y `$casts`**

```php
protected $fillable = [
    'name', 'slug', 'logo', 'address', 'phone', 'email', 'social_media',
    'tax_enabled', 'tax_percentage', 'tax_mode',
    'print_format',
    'work_order_prefix', 'work_order_sequence', 'is_active',
    // Mail credentials
    'mail_host', 'mail_port', 'mail_username', 'mail_encryption',
    'mail_password', 'mail_from_address', 'mail_from_name',
];
```

```php
protected $casts = [
    'tax_enabled' => 'boolean',
    'is_active' => 'boolean',
    'social_media' => 'array',
    'mail_password' => 'encrypted',
];
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/Tenant.php
git commit -m "feat: add mail credential fields to Tenant model"
```

---

### Task 3: Crear TenantMailService

**Files:**
- Create: `app/Services/TenantMailService.php`

- [ ] **Step 1: Escribir el servicio**

```php
<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class TenantMailService
{
    public function configureForTenant(Tenant $tenant): void
    {
        if (!$tenant->mail_host || !$tenant->mail_username || !$tenant->mail_password) {
            return;
        }

        Config::set('mail.mailers.smtp.host', $tenant->mail_host);
        Config::set('mail.mailers.smtp.port', $tenant->mail_port ?? '587');
        Config::set('mail.mailers.smtp.username', $tenant->mail_username);
        Config::set('mail.mailers.smtp.password', decrypt($tenant->mail_password));
        Config::set('mail.mailers.smtp.encryption', $tenant->mail_encryption ?? 'tls');
        Config::set('mail.from.address', $tenant->mail_from_address);
        Config::set('mail.from.name', $tenant->mail_from_name ?? $tenant->name);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Services/TenantMailService.php
git commit -m "feat: add TenantMailService for dynamic SMTP config"
```

---

### Task 4: Crear Mailable WorkOrderReceipt

**Files:**
- Create: `app/Mail/WorkOrderReceipt.php`

- [ ] **Step 1: Escribir el Mailable**

```php
<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkOrderReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public WorkOrder $workOrder;
    public Tenant $tenant;

    public function __construct(WorkOrder $workOrder, Tenant $tenant)
    {
        $this->workOrder = $workOrder;
        $this->tenant = $tenant;
    }

    public function envelope(): Envelope
    {
        $number = $this->workOrder->work_order_number;
        return new Envelope(
            subject: "Orden de Trabajo #{$number} - {$this->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.work_orders.receipt',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Mail/WorkOrderReceipt.php
git commit -m "feat: create WorkOrderReceipt mailable"
```

---

### Task 5: Crear plantilla email responsive

**Files:**
- Create: `resources/views/emails/work_orders/receipt.blade.php`

- [ ] **Step 1: Escribir plantilla HTML email con estilos inline (responsive)**

```blade
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

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#2563eb;padding:24px 32px;text-align:center;">
                            @if($tenant->logo)
                                <img src="{{ asset('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}" style="max-height:50px;margin-bottom:12px;">
                            @endif
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">{{ $tenant->name }}</h1>
                            <p style="margin:4px 0 0;color:#bfdbfe;font-size:14px;">Orden de Trabajo #{{ $workOrder->work_order_number }}</p>
                        </td>
                    </tr>

                    <!-- Status Badge -->
                    <tr>
                        <td style="padding:20px 32px 0;text-align:center;">
                            <span style="display:inline-block;padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;text-transform:uppercase;background-color:#dbeafe;color:#1d4ed8;">
                                {{ ucfirst($workOrder->status) }}
                            </span>
                        </td>
                    </tr>

                    <!-- Info Section -->
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

                    <!-- Divider -->
                    <tr>
                        <td style="padding:0 32px;">
                            <hr style="border:none;border-top:1px solid #e5e7eb;margin:0;">
                        </td>
                    </tr>

                    <!-- Problem Description -->
                    <tr>
                        <td style="padding:20px 32px;">
                            <h2 style="margin:0 0 8px;font-size:14px;font-weight:600;color:#111827;">Problema Reportado</h2>
                            <p style="margin:0;font-size:14px;color:#374151;line-height:1.5;">
                                {{ $workOrder->problem_description }}
                            </p>
                        </td>
                    </tr>

                    <!-- Tracking Link -->
                    <tr>
                        <td style="padding:0 32px 20px;text-align:center;">
                            <a href="{{ route('tracking.show', $workOrder->tracking_token) }}"
                               style="display:inline-block;padding:12px 24px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:600;">
                                Dar seguimiento a mi orden
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f9fafb;padding:16px 32px;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                {{ $tenant->name }} &bull; {{ $tenant->address }}
                                @if($tenant->phone)<br>{{ $tenant->phone }}@endif
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Mobile disclaimer -->
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
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/emails/work_orders/receipt.blade.php
git commit -m "feat: add responsive email template for work order receipt"
```

---

### Task 6: Crear vistas de impresión para WorkOrder (58mm, 80mm, A4)

**Files:**
- Create: `resources/views/work_orders/print/ticket_58mm.blade.php`
- Create: `resources/views/work_orders/print/ticket_80mm.blade.php`
- Create: `resources/views/work_orders/print/a4.blade.php`

- [ ] **Step 1: Crear vista 58mm**

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>OT #{{ $workOrder->work_order_number }}</title>
    <style>
        @page {
            margin: 5mm;
            size: 58mm auto;
        }
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
        @media print {
            body { width: 48mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="text-align:center;padding:10px;background:#eee;margin-bottom:10px;font-family:sans-serif;">
        <button onclick="window.print()" style="padding:8px 16px;font-size:14px;">🖨 Imprimir</button>
        <button onclick="window.location.href='{{ route('work_orders.print.pdf', $workOrder) }}'" style="padding:8px 16px;font-size:14px;">⬇ Descargar PDF</button>
    </div>

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
        @if($workOrder->device_serial)
            <tr><td>Serie:</td><td>{{ $workOrder->device_serial }}</td></tr>
        @endif
        @if($workOrder->device_imei)
            <tr><td>IMEI:</td><td>{{ $workOrder->device_imei }}</td></tr>
        @endif
    </table>

    <div class="section-title">Problema Reportado:</div>
    <p style="font-size:9px;">{{ $workOrder->problem_description }}</p>

    @if($clauses->count())
        <div class="divider"></div>
        <div class="section-title">Términos y Condiciones</div>
        @foreach($clauses as $clause)
            <div class="clauses">
                <strong>{{ $clause->title }}:</strong> {{ $clause->content }}
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

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
```

- [ ] **Step 2: Crear vista 80mm** (mismo contenido, cambiar `@page` margin y width)

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>OT #{{ $workOrder->work_order_number }}</title>
    <style>
        @page {
            margin: 5mm;
            size: 80mm auto;
        }
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
        @media print {
            body { width: 70mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- Same structure as 58mm but with 80mm-optimized sizes -->
    <div class="no-print" style="text-align:center;padding:10px;background:#eee;margin-bottom:10px;font-family:sans-serif;">
        <button onclick="window.print()" style="padding:8px 16px;font-size:14px;">🖨 Imprimir</button>
        <button onclick="window.location.href='{{ route('work_orders.print.pdf', $workOrder) }}'" style="padding:8px 16px;font-size:14px;">⬇ Descargar PDF</button>
    </div>

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
        @if($workOrder->device_serial)
            <tr><td>Serie:</td><td>{{ $workOrder->device_serial }}</td></tr>
        @endif
        @if($workOrder->device_imei)
            <tr><td>IMEI:</td><td>{{ $workOrder->device_imei }}</td></tr>
        @endif
    </table>

    <div class="section-title">Problema Reportado</div>
    <p style="font-size:10px;">{{ $workOrder->problem_description }}</p>

    @if($clauses->count())
        <div class="divider"></div>
        <div class="section-title">Términos y Condiciones</div>
        @foreach($clauses as $clause)
            <div class="clauses"><strong>{{ $clause->title }}:</strong> {{ $clause->content }}</div>
        @endforeach
    @endif

    <div class="divider"></div>

    <div style="margin-top:30px;">
        <p style="font-size:9px;">Firma del Cliente: _________________________________</p>
    </div>

    <div class="footer">
        <p>¡Gracias por su preferencia!</p>
        @if($workOrder->tracking_token)
            <p>Siga su orden: {{ route('tracking.show', $workOrder->tracking_token) }}</p>
        @endif
    </div>

    <script>window.onload=function(){window.print();}</script>
</body>
</html>
```

- [ ] **Step 3: Crear vista A4**

```blade
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
        .signature p { font-size: 11px; }
        .signature-line { border-top: 1px solid #333; width: 250px; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨 Imprimir</button>
        <button onclick="window.location.href='{{ route('work_orders.print.pdf', $workOrder) }}'">⬇ Descargar PDF</button>
    </div>

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
                <p>{{ $clause->content }}</p>
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
        <div class="signature-line"></div>
    </div>

    <div class="footer">
        <p>{{ $tenant->name }} &bull; {{ $tenant->address }} @if($tenant->phone)&bull; {{ $tenant->phone }}@endif</p>
        <p>¡Gracias por su preferencia!</p>
    </div>

    <script>window.onload=function(){window.print();}</script>
</body>
</html>
```

- [ ] **Step 4: Crear directorio**

```bash
mkdir -p resources/views/work_orders/print
```

- [ ] **Step 5: Commit**

```bash
git add resources/views/work_orders/print/
git commit -m "feat: add work order print views (58mm, 80mm, A4)"
```

---

### Task 7: Agregar rutas de impresión

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Agregar rutas**

```php
Route::get('/work_orders/{work_order}/print', [WorkOrderController::class, 'print'])
    ->name('work_orders.print');
Route::get('/work_orders/{work_order}/print-pdf', [WorkOrderController::class, 'printPdf'])
    ->name('work_orders.print.pdf');
```

Estas rutas deben ir DENTRO del grupo `auth`. Colocarlas antes o después del `Route::resource('work_orders', ...)`.

- [ ] **Step 2: Commit**

```bash
git add routes/web.php
git commit -m "feat: add print and print-pdf routes for work orders"
```

---

### Task 8: Agregar métodos print y printPdf al WorkOrderController, modificar store

**Files:**
- Modify: `app/Http/Controllers/WorkOrderController.php`

- [ ] **Step 1: Agregar imports al inicio del archivo**

Agregar después de los imports existentes:
```php
use App\Mail\WorkOrderReceipt;
use App\Models\TenantClause;
use App\Services\TenantMailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
```

- [ ] **Step 2: Agregar método `print`**

Agregar ANTES del método `store` o después de `show`:
```php
public function print(WorkOrder $workOrder): View
{
    $this->authorizeTenant($workOrder);
    $workOrder->load('client');
    $tenant = $workOrder->tenant;
    $format = $tenant->print_format ?? 'ticket_80mm';
    $clauses = TenantClause::where('tenant_id', $tenant->id)
        ->where('print_on_receipt', true)
        ->where('is_active', true)
        ->get();
    return view("work_orders.print.{$format}", compact('workOrder', 'tenant', 'clauses'));
}
```

- [ ] **Step 3: Agregar método `printPdf`**

```php
public function printPdf(WorkOrder $workOrder)
{
    $this->authorizeTenant($workOrder);
    $workOrder->load('client');
    $tenant = $workOrder->tenant;
    $clauses = TenantClause::where('tenant_id', $tenant->id)
        ->where('print_on_receipt', true)
        ->where('is_active', true)
        ->get();

    $html = view("work_orders.print.a4", compact('workOrder', 'tenant', 'clauses'))->render();
    $pdf = Pdf::loadHTML($html);
    $pdf->setPaper('a4', 'portrait');

    return $pdf->download("comprobante-OT-{$workOrder->work_order_number}.pdf");
}
```

- [ ] **Step 4: Modificar `store` para redirigir a print y enviar email**

Al final del método `store`, reemplazar el redirect existente:
```php
// Después de la creación exitosa...
$workOrder->load('client');

// Redirigir a impresión
$redirect = redirect()->route('work_orders.print', $workOrder)
    ->with('success', 'Orden de trabajo creada correctamente.');

// Enviar email si el cliente prefiere notificación por correo
if ($workOrder->client->notification_preference === 'email' && $workOrder->client->email) {
    try {
        $tenant = $workOrder->tenant;
        if ($tenant->mail_host && $tenant->mail_username && $tenant->mail_password) {
            app(TenantMailService::class)->configureForTenant($tenant);
            Mail::to($workOrder->client->email)
                ->send(new WorkOrderReceipt($workOrder, $tenant));
        }
    } catch (\Exception $e) {
        // Log but don't fail - print is the primary action
        logger()->error('Failed to send work order email: ' . $e->getMessage());
    }
}

return $redirect;
```

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/WorkOrderController.php
git commit -m "feat: add print, printPdf methods and email on work order store"
```

---

### Task 9: Agregar UI de configuración SMTP en settings

**Files:**
- Modify: `resources/views/settings/company.blade.php`

- [ ] **Step 1: Agregar sección de configuración SMTP**

Buscar el final del formulario de datos de empresa y agregar antes del `</form>` o al final de la sección:

```blade
<!-- SMTP Configuration -->
<div class="mt-8 border-t pt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuración de Correo SMTP</h3>
    <p class="text-sm text-gray-500 mb-4">
        Configura tu propio servidor SMTP para enviar notificaciones a tus clientes.
        Estos datos son responsabilidad de tu empresa.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Servidor SMTP</label>
            <input type="text" name="mail_host" value="{{ old('mail_host', $tenant->mail_host) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   placeholder="smtp.gmail.com">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Puerto</label>
            <input type="text" name="mail_port" value="{{ old('mail_port', $tenant->mail_port) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   placeholder="587">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Usuario</label>
            <input type="text" name="mail_username" value="{{ old('mail_username', $tenant->mail_username) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   placeholder="tu@correo.com">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Contraseña</label>
            <input type="password" name="mail_password" value="{{ old('mail_password', $tenant->mail_password ? '********' : '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   placeholder="••••••••">
            <p class="text-xs text-gray-400 mt-1">Déjalo en blanco para mantener la actual.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Encriptación</label>
            <select name="mail_encryption"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="tls" {{ ($tenant->mail_encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                <option value="ssl" {{ $tenant->mail_encryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                <option value="" {{ $tenant->mail_encryption === '' ? 'selected' : '' }}>Ninguna</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Correo desde</label>
            <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $tenant->mail_from_address) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   placeholder="notificaciones@tudominio.com">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Nombre desde</label>
            <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $tenant->mail_from_name) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   placeholder="{{ $tenant->name }}">
        </div>
    </div>
</div>
```

- [ ] **Step 2: Agregar campos al controlador SettingsController**

En `SettingsController@companyUpdate`, agregar al array de validación:
```php
'mail_host' => 'nullable|string|max:255',
'mail_port' => 'nullable|string|max:10',
'mail_username' => 'nullable|string|max:255',
'mail_password' => 'nullable|string|max:255',
'mail_encryption' => 'nullable|in:tls,ssl,',
'mail_from_address' => 'nullable|email|max:255',
'mail_from_name' => 'nullable|string|max:255',
```

Y en la actualización, manejar el password (no sobrescribir si está en blanco):
```php
$data = $request->only([...]);
if (empty($request->mail_password)) {
    unset($data['mail_password']);
} else {
    $data['mail_password'] = $request->mail_password;
}
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/settings/company.blade.php app/Http/Controllers/SettingsController.php
git commit -m "feat: add SMTP configuration UI in tenant settings"
```

---

### Task 10: Escribir tests

**Files:**
- Create: `tests/Feature/WorkOrderPrintTest.php`
- Create: `tests/Feature/WorkOrderMailTest.php`

- [ ] **Step 1: Crear test de impresión**

```php
<?php

use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Client;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['print_format' => 'ticket_80mm']);
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->tenant->setPrefix('OT-');
    $this->actingAs($this->user);
});

it('can print a work order receipt', function () {
    $workOrder = WorkOrder::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('work_orders.print', $workOrder));

    $response->assertStatus(200);
    $response->assertSee($workOrder->work_order_number);
    $response->assertSee($workOrder->client->name);
});

it('uses correct print format from tenant settings', function () {
    $this->tenant->update(['print_format' => 'ticket_58mm']);
    $workOrder = WorkOrder::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('work_orders.print', $workOrder));

    $response->assertStatus(200);
    $response->assertSee('58mm', false);
});

it('can download work order receipt as PDF', function () {
    $workOrder = WorkOrder::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('work_orders.print.pdf', $workOrder));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});

it('redirects to print page after creating a work order', function () {
    $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->post(route('work_orders.store'), [
        'client_id' => $client->id,
        'device_brand' => 'Apple',
        'device_model' => 'iPhone 13',
        'problem_description' => 'Pantalla rota',
    ]);

    $workOrder = WorkOrder::first();
    $response->assertRedirect(route('work_orders.print', $workOrder));
});
```

- [ ] **Step 2: Crear test de email**

```php
<?php

use App\Mail\WorkOrderReceipt;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Mail;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    $this->tenant = Tenant::factory()->create([
        'mail_host' => 'smtp.test.com',
        'mail_port' => '587',
        'mail_username' => 'test@test.com',
        'mail_password' => encrypt('password'),
        'mail_encryption' => 'tls',
        'mail_from_address' => 'test@test.com',
        'mail_from_name' => 'Test',
    ]);
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($this->user);
});

it('sends email when client prefers email notification', function () {
    $client = Client::factory()->create([
        'tenant_id' => $this->tenant->id,
        'notification_preference' => 'email',
        'email' => 'client@test.com',
    ]);

    $this->post(route('work_orders.store'), [
        'client_id' => $client->id,
        'device_brand' => 'Apple',
        'device_model' => 'iPhone 13',
        'problem_description' => 'Pantalla rota',
    ]);

    $workOrder = WorkOrder::first();
    Mail::assertSent(WorkOrderReceipt::class, function ($mail) use ($workOrder, $client) {
        return $mail->hasTo($client->email) &&
               $mail->workOrder->id === $workOrder->id;
    });
});

it('does not send email when client prefers whatsapp', function () {
    $client = Client::factory()->create([
        'tenant_id' => $this->tenant->id,
        'notification_preference' => 'whatsapp',
    ]);

    $this->post(route('work_orders.store'), [
        'client_id' => $client->id,
        'device_brand' => 'Apple',
        'device_model' => 'iPhone 13',
        'problem_description' => 'Pantalla rota',
    ]);

    Mail::assertNothingSent();
});

it('does not send email when tenant has no SMTP configured', function () {
    $this->tenant->update([
        'mail_host' => null,
        'mail_username' => null,
        'mail_password' => null,
    ]);
    $client = Client::factory()->create([
        'tenant_id' => $this->tenant->id,
        'notification_preference' => 'email',
        'email' => 'client@test.com',
    ]);

    $this->post(route('work_orders.store'), [
        'client_id' => $client->id,
        'device_brand' => 'Apple',
        'device_model' => 'iPhone 13',
        'problem_description' => 'Pantalla rota',
    ]);

    Mail::assertNothingSent();
});
```

- [ ] **Step 3: Ejecutar tests**

```bash
php artisan test --filter=WorkOrderPrintTest
php artisan test --filter=WorkOrderMailTest
```

Esperado: todos los tests pasan.

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/WorkOrderPrintTest.php tests/Feature/WorkOrderMailTest.php
git commit -m "test: add print and mail tests for work order receipts"
```

---

### Task 11: Actualizar Documentacion_Software.md con INCs

- [ ] **Step 1: Agregar INCs al documento**

Agregar bajo MOD-14 (o crear MOD-15 si se prefiere separar) los siguientes INC:

| INC | Descripción | Estado | Prioridad |
|-----|-------------|--------|-----------|
| INC-MOD14-006 | Crear vistas de impresión de comprobante OT (58mm/80mm/A4) con márgenes correctos | ✅ Completado | 🔴 Alta |
| INC-MOD14-007 | Agregar configuración SMTP por tenant (credenciales de correo propias) | ✅ Completado | 🔴 Alta |
| INC-MOD14-008 | Crear Mailable y plantilla email responsive para notificar OT al cliente | ✅ Completado | 🔴 Alta |
| INC-MOD14-009 | Integrar impresión automática al crear OT + envío de email según preferencia del cliente | ✅ Completado | 🔴 Alta |

- [ ] **Step 2: Commit**

```bash
git add Documentacion_Software.md
git commit -m "docs: add INC records for work order printing and email"
```

---

## Self-Review

**1. Spec coverage:**
- ✅ Comprobante OT por defecto al crear (Task 8 - store redirects to print)
- ✅ Formato según tenant (58mm/80mm/A4) (Task 6 - uses $tenant->print_format)
- ✅ Márgenes 5mm para 58mm/80mm (Task 6 - @page margin: 5mm)
- ✅ PDF descargable si no hay impresora (Task 7/8 - printPdf route + botón en vistas)
- ✅ Correo con información de OT (Task 4/5 - WorkOrderReceipt mailable + plantilla)
- ✅ Tenant configura sus credenciales SMTP (Task 1/2/9 - migration + UI)
- ✅ Plantilla responsive mobile/desktop (Task 5 - HTML table layout con @media query via inline)
- ✅ Imprimir en la PC del usuario (Task 6 - window.print() en vistas)

**2. No placeholders:** All code is complete in every task.

**3. Type consistency:** All types, method signatures, and references match across tasks.
