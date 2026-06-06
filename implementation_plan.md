# Plan de Implementación — RepCellPOS

> **Stack:** Laravel 13 · PHP 8.x · MySQL · Blade · Tailwind CSS v4  
> **Documento base:** `Documentacion_Software.md`

---

## Fase 1 — Fundación: Auth, Multitenancy, Roles y Clientes

Construir la base arquitectónica del sistema: multitenancy por columna `tenant_id`, autenticación, roles con permisos granulares (Spatie), gestión de usuarios por tenant, cláusulas legales y CRUD de clientes.

### MOD-01: Configuración Base (MySQL + .env)

- [ ] Modificar `.env`: cambiar DB a MySQL (`RepCellPOS`), `APP_NAME=RepCellPOS`, `APP_LOCALE=es`
- [ ] Crear base de datos MySQL `RepCellPOS`

### MOD-01: Multitenancy (Single-Database con `tenant_id`)

- [ ] Crear migración `create_tenants_table` — `id`, `name`, `slug`, `logo`, `address`, `phone`, `email`, `social_media` (json), `tax_enabled`, `tax_percentage`, `tax_mode` (enum), `print_format` (enum), `work_order_prefix`, `work_order_sequence`, `is_active`, `timestamps`
- [ ] Crear modelo `Tenant`
- [ ] Crear `TenantScope` — global scope que filtra por `tenant_id`
- [ ] Crear trait `BelongsToTenant` — aplica scope, auto-asigna `tenant_id`, define relación
- [ ] Crear middleware `SetTenantMiddleware` — resuelve tenant del usuario autenticado
- [ ] Registrar middleware en `bootstrap/app.php`

### MOD-01: Autenticación y Usuarios

- [ ] Modificar migración `users`: agregar `tenant_id` (nullable), `phone`, `is_superadmin`, `is_active`
- [ ] Modificar modelo `User`: agregar `BelongsToTenant` (condicional), `HasRoles`, relaciones
- [ ] Crear `AuthController` — login/logout con verificación de tenant activo
- [ ] Crear vista `auth/login.blade.php` — diseño premium con dark mode
- [ ] Actualizar `routes/web.php` — rutas de auth y dashboard

### MOD-01/MOD-10: Roles y Permisos Granulares

- [ ] Ejecutar `composer require spatie/laravel-permission`
- [ ] Publicar migración de Spatie y migrar
- [ ] Crear `PermissionSeeder` — todos los permisos granulares (38 permisos)
- [ ] Crear `RoleSeeder` — roles base: `admin_tenant`, `secretario`, `tecnico`
- [ ] Crear `SuperAdminSeeder` — usuario superadmin inicial
- [ ] Modificar `DatabaseSeeder` para ejecutar seeders en orden

### MOD-10: Gestión de Cláusulas/Políticas

- [ ] Crear migración `tenant_clauses` — `title`, `content`, `type` (enum), `is_active`, `print_on_receipt`, `sort_order`
- [ ] Crear modelo `TenantClause` con `BelongsToTenant`

### MOD-02: Gestión de Clientes

- [ ] Crear migración `clients` — `name`, `phone`, `email`, `notification_preference` (enum), `notes`, `softDeletes`
- [ ] Crear modelo `Client` con `BelongsToTenant`, relación `workOrders()`
- [ ] Crear `ClientController` — CRUD completo con búsqueda por nombre/teléfono
- [ ] Crear vistas: `index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php`
- [ ] Actualizar `routes/web.php` con resource controller

### Layout y Dashboard Base

- [ ] Crear layout `layouts/app.blade.php` — sidebar dinámico por permisos, header, dark mode
- [ ] Crear vista `dashboard.blade.php` — cards resumen con métricas placeholder

---

## Fase 2 — Operación Core: OT, Inventario y Cotizaciones

### MOD-03: Órdenes de Trabajo

- [ ] Crear migración `work_orders`:
  - `id`, `tenant_id`, `client_id`, `user_id` (secretario que recibe)
  - `device_brand`, `device_model`, `device_serial`, `device_imei`
  - `unlock_pattern`, `unlock_pin` (nullable, permitir "N/A")
  - `problem_description` (text)
  - `status` (enum: `recibida`, `en_espera`, `en_revision`, `diagnosticada`, `cotizacion_enviada`, `cotizacion_aprobada`, `en_reparacion`, `reparada`, `terminada`, `cancelada`)
  - `priority` (enum: `baja`, `media`, `alta`)
  - `timeline` (json)
  - `work_order_number` (string, unique por tenant: prefijo+secuencial)
  - `timestamps`, `softDeletes`
- [ ] Crear modelo `WorkOrder` con `BelongsToTenant`, casts para `timeline`, `status`, `priority`
- [ ] Implementar máquina de estados con validaciones de transiciones válidas
- [ ] Crear `WorkOrderController`:
  - `index()` — listado con filtros por estado, prioridad, búsqueda
  - `create()` / `store()` — formulario de recepción de equipo
  - `show()` — detalle completo con timeline
  - `edit()` / `update()` — edición de datos (solo ciertos estados)
  - `changeStatus()` — cambiar estado con comentario opcional
  - `setPriority()` — asignar prioridad rápida
  - `addNote()` — agregar anotación al timeline
- [ ] Implementar lógica de timeline: cada cambio de estado registra evento en JSON
- [ ] Sistema de imágenes: carga drag & drop del equipo (usar Spatie Media Library o similar)
- [ ] Vistas: `index.blade.php`, `create.blade.php`, `show.blade.php`, `edit.blade.php`
- [ ] Generación de comprobante: ticket térmico (58/80mm) y A4
- [ ] Actualizar `routes/web.php`

### MOD-05: Inventario y Kardex

- [ ] Crear migración `categories` — `id`, `tenant_id`, `name`, `slug`, `description`, `is_active`, `timestamps`
- [ ] Crear modelo `Category` con `BelongsToTenant`
- [ ] Crear migración `products`:
  - `id`, `tenant_id`, `category_id`, `code` (SKU), `part_number`, `name`, `description`
  - `type` (enum: `producto`, `servicio`)
  - `stock` (integer, default 0), `min_stock` (integer, default 0)
  - `purchase_price`, `sale_price` (decimal)
  - `has_tax` (boolean), `tax_percentage` (decimal)
  - `barcode`, `compatible_brand`, `compatible_model`
  - `is_active` (boolean)
  - `timestamps`, `softDeletes`
- [ ] Crear modelo `Product` con `BelongsToTenant`
- [ ] Crear migración `kardex_movements`:
  - `id`, `tenant_id`, `product_id`, `type` (enum: `entrada`, `salida`, `ajuste`)
  - `quantity`, `previous_stock`, `resulting_stock`
  - `reference_type`, `reference_id` (polimórfica: venta, OT, ajuste, compra)
  - `user_id`, `notes`, `timestamps`
- [ ] Crear modelo `KardexMovement` con `BelongsToTenant`
- [ ] Implementar lógica de trazabilidad: todo movimiento de inventario registra kardex
- [ ] Implementar descuento automático de stock al vender/usar en OT
- [ ] Implementar alertas de stock mínimo
- [ ] Crear `ProductController` — CRUD completo con categorías
- [ ] Crear `KardexController` — reporte por producto con filtros de fecha
- [ ] Vistas: CRUD de productos, reporte de kardex

### MOD-04: Cotizaciones

- [ ] Crear migración `quotes`:
  - `id`, `tenant_id`, `work_order_id`, `status` (enum: `pendiente`, `enviada`, `aprobada`, `rechazada`)
  - `subtotal`, `tax_total`, `total` (decimal)
  - `notes`, `timestamps`
- [ ] Crear modelo `Quote` con `BelongsToTenant`
- [ ] Crear migración `quote_items`:
  - `id`, `tenant_id`, `quote_id`, `product_id` (nullable), `type` (enum: `producto`, `servicio`)
  - `description`, `quantity`, `unit_price`, `tax_percentage`, `subtotal`
  - `timestamps`
- [ ] Crear modelo `QuoteItem` con `BelongsToTenant`
- [ ] Implementar interfaz tipo carrito dentro de la orden de trabajo
- [ ] Cálculo automático de subtotal, impuestos, total
- [ ] Flujo de aprobación/rechazo por parte del cliente
- [ ] Al aprobar: avanzar OT a `cotizacion_aprobada` → `en_reparacion`
- [ ] Al rechazar: avanzar OT a `cancelada`
- [ ] Generación de PDF de cotización
- [ ] Vistas: carrito de cotización, detalle, PDF

---

## Fase 3 — Ventas y Cobros: POS y Caja

### MOD-06: Punto de Venta (POS)

- [ ] Crear migración `sales`:
  - `id`, `tenant_id`, `user_id`, `client_id` (nullable, para OT)
  - `work_order_id` (nullable, para cobro de OT)
  - `type` (enum: `venta_directa`, `cobro_orden`)
  - `subtotal`, `tax_total`, `total`, `discount` (decimal, default 0)
  - `payment_method` (enum: `efectivo`, `tarjeta_transferencia`)
  - `payment_reference` (nullable, folio de tarjeta/transferencia)
  - `change_amount` (decimal, default 0, para efectivo)
  - `cash_register_id`
  - `timestamps`
- [ ] Crear modelo `Sale` con `BelongsToTenant`
- [ ] Crear migración `sale_items` — similar a quote_items
- [ ] Crear modelo `SaleItem`
- [ ] Interfaz POS con:
  - Búsqueda de productos por nombre/código de barras
  - Carrito con cálculo automático
  - Selección de método de pago
  - Cálculo de cambio (efectivo)
  - Folio para tarjeta/transferencia
- [ ] Cobro de órdenes de trabajo / cotizaciones aprobadas desde POS
- [ ] Descuento automático de inventario al realizar venta
- [ ] Generación de ticket de venta (impresión)
- [ ] Vista: interfaz POS full-page

### MOD-07: Control de Caja

- [ ] Crear migración `cash_registers`:
  - `id`, `tenant_id`, `user_id` (quien abre)
  - `opening_amount` (decimal), `closing_amount` (decimal, nullable)
  - `opened_at`, `closed_at` (datetime, nullable)
  - `status` (enum: `abierta`, `cerrada`)
  - `notes`, `timestamps`
- [ ] Crear modelo `CashRegister` con `BelongsToTenant`
- [ ] Crear migración `cash_register_movements` (retiros):
  - `id`, `cash_register_id`, `type` (enum: `retiro`), `amount`, `reason`, `authorized_by`, `timestamps`
- [ ] Implementar apertura de caja (monto inicial)
- [ ] Implementar cierre de caja (conteo real, diferencia)
- [ ] Registro automático de cada transacción en la caja activa
- [ ] Reporte de corte de caja desglosado por método de pago
- [ ] Retiros parciales con motivo y autorización
- [ ] Historial de aperturas/cierres por usuario y fecha
- [ ] Vistas: apertura/cierre, corte, historial

---

## Fase 4 — Comunicación y Portal

### MOD-08: Notificaciones al Cliente

- [ ] Crear sistema base de notificaciones (tabla `notifications`)
- [ ] Servicio de notificaciones con canales:
  - **Correo electrónico:** SMTP configurable por tenant, plantillas Blade
  - **WhatsApp:** Integración con n8n vía webhook (o API de WhatsApp Business)
  - **Llamada telefónica:** registro manual con log
- [ ] Eventos que disparan notificaciones:
  - OT creada
  - Diagnóstico completado
  - Cotización generada/enviada
  - Cotización aprobada/rechazada
  - Reparación completada
  - Equipo listo para recoger
- [ ] Plantillas de mensajes configurables por tenant
- [ ] Preferencia de notificación por cliente (whatsapp/email/call)

### MOD-09: Portal Público de Seguimiento

- [ ] Ruta pública con token único por OT (`/seguimiento/{token}`)
- [ ] Vista pública (sin autenticación) con:
  - Timeline visual tipo progress bar animado
  - Datos básicos de la orden (sin datos sensibles)
  - Diseño responsive mobile-first
- [ ] Enviar link en cada notificación al cliente

---

## Dependencias Técnicas

```bash
# Fase 1
composer require spatie/laravel-permission

# Fase 2 (opcionales)
composer require spatie/laravel-medialibrary    # Para imágenes de equipos
composer require barryvdh/laravel-dompdf        # Para PDF de cotizaciones
composer require mike42/escpos-php              # Para impresión térmica
```

---

## Convenciones

- **Modelos:** `App\Models\*` con `BelongsToTenant` trait
- **Controladores:** `App\Http\Controllers\*Controller` (resourceful)
- **Vistas:** `resources/views/{module}/*.blade.php` con layout `layouts.app`
- **Rutas:** web.php con middleware `auth` + `set.tenant`
- **Migraciones:** snakes_case table names, plural
- **Permisos:** `{recurso}.{accion}` — ej: `clients.view`, `work_orders.create`

## Orden de Implementación por Fase

```
Fase 1 — Fundación
  ├── .env + MySQL
  ├── spatie/laravel-permission
  ├── Multitenancy (Tenant, Scope, Trait, Middleware)
  ├── Auth (User modificado, AuthController, login view)
  ├── Roles y Permisos (Seeders)
  ├── Cláusulas (TenantClause)
  ├── Clientes (CRUD completo)
  └── Layout + Dashboard

Fase 2 — Operación Core
  ├── Órdenes de Trabajo (migración + modelo + controlador + vistas)
  ├── Inventario (Productos, Categorías, Kardex)
  └── Cotizaciones (Quote, QuoteItem, carrito, PDF)

Fase 3 — Ventas y Cobros
  ├── Punto de Venta (POS interface, Sales, SaleItems)
  └── Control de Caja (CashRegister, cortes, retiros)

Fase 4 — Comunicación y Portal
  ├── Notificaciones (Email, WhatsApp, Log llamadas)
  └── Portal Público de Seguimiento
```

## Verification Plan

```bash
php artisan migrate:fresh --seed
php artisan test
php artisan route:list
```
