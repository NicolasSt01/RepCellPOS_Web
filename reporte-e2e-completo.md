# Reporte E2E Completo — Producción

**Fecha:** 2026-06-28  
**URL:** `https://repcellpos.nexacore.com.mx`  
**Tests:** 5 Playwright tests, 2 workers, `--headed`, timeout 300s

---

## Resultados: ✅ 5/5 pruebas pasaron (31.1s)

| # | Suite | Prueba | Estado | Tiempo |
|---|---|---|---|---|
| 1 | Registro | Registro completo: landing → formulario → dashboard | ✅ | 11.6s |
| 2 | Registro | Error con email duplicado | ✅ | 2.8s |
| 3 | Registro | Error si contraseñas no coinciden | ✅ | 2.6s |
| 4 | Registro | Verificación de email y envíos de correo sin errores | ✅ | 6.2s |
| 5 | POS + OT + Tracking | Alta de productos, venta POS, orden de trabajo y tracking | ✅ | 25.0s |

---

## 1. Registro de tenant (4 pruebas)

### 1.1 Registro completo
- Landing page carga con título correcto
- Click en "Comenzar gratis" → `/register`
- Formulario se llena y envía
- Redirección a `/dashboard` con nombre del admin y empresa visibles
- Sidebar muestra: Operaciones, Ventas, Inventario, Configuración
- Sidebar NO muestra: Superadmin, Plataforma

### 1.2 Error con email duplicado
- Intento de registro con mismo email → permanece en `/register`
- Mensaje de error visible

### 1.3 Error si contraseñas no coinciden
- `password_confirmation` diferente → permanece en `/register`
- Mensaje de error visible

### 1.4 Verificación de email y envíos de correo
- **Correo de verificación** enviado a `salassys25@gmail.com` (admin del tenant)
- **Notificación a superadmin** enviada a `yojaja100@gmail.com`
- Link de verificación visitado → **vista dedicada** con checkmark verde y botón "Ir al panel de control"
- Cuenta regresiva de 5s con redirección automática
- Log de Laravel sin errores de envío

---

## 2. Flujo POS + OT + Tracking (1 prueba integrada)

### 2.1 Productos e inventario
- Categoría "Repuestos" creada
- Producto "Pantalla iPhone 11" creado (stock: 10, precio: $200)
- Producto "Batería iPhone 11" creado (stock: 15, precio: $50)

### 2.2 Caja
- Caja abierta con fondo inicial de $500

### 2.3 Venta POS
- 2 pantallas + 1 batería agregadas al carrito
- Total: $450
- Pago confirmado exitosamente
- **Stock verificado:** 8 pantallas (10-2), 14 baterías (15-1)
- Venta visible en historial `/sales`

### 2.4 Orden de trabajo
- Cliente "Juan Pérez" creado y seleccionado
- Equipo: Apple iPhone 15 Pro Max (serial, IMEI)
- Problema: "Pantalla estrellada, no funciona el touch"
- OT creada → redirección a página de impresión
- **Ticket de impresión** generado correctamente

### 2.5 Cotización y tracking público
- Cotización creada: servicio "Mano de obra por cambio de pantalla" — $350
- Cotización enviada al cliente
- **URL de tracking** extraída del ticket de impresión
- Página de tracking pública carga correctamente:
  - Equipo visible: Apple iPhone 15 Pro Max
  - Cotización visible con items y totales
  - Botón "Aprobar Cotización" funciona
  - Cotización aprobada exitosamente
  - Timeline de progreso visible

---

## Correos enviados durante la prueba

| Tipo | Destinatario real |
|---|---|
| `VerifyEmail` — Verificación de cuenta | `salassys25@gmail.com` |
| `NewTenantNotification` — Nuevo registro | `yojaja100@gmail.com` |
| `WorkOrderReceipt` — Recibo de OT | `salassys25@gmail.com` (cliente) |

---

## 3. Nuevas funcionalidades implementadas

### 3.1 WhatsApp deshabilitado según plan
- En creación/edición de clientes: opción WhatsApp solo aparece si el plan tiene `notifications_whatsapp`
- En creación de OT: misma lógica en el formulario de nuevo cliente
- `NotificationService.dispatch()` detecta si el cliente tiene WhatsApp pero el plan no lo permite, y hace fallback a email o llamada
- Planes: Básico (❌), Pro (❌), Premium (✅)

### 3.2 Alertas de stock bajo en dashboard
- Nuevo feature `notifications_low_stock` en todos los planes (true)
- Dashboard muestra tabla con productos donde `stock <= min_stock` y `min_stock > 0`
- Incluye enlace directo a editar producto para reabastecer
- Feature configurable por plan en superadmin

### 3.3 Preferencia de notificación del cliente visible en OT
- Al seleccionar cliente existente en creación de OT → se muestra 📧/💬/📞 con email
- En detalle de OT (`work_orders/show`) → badge visual con icono del canal
- Backend devuelve `notification_preference` en búsqueda de clientes

---

## Archivos creados/modificados (nueva ronda)

| Archivo | Cambio |
|---|---|
| `database/seeders/PlansSeeder.php` | **Modificado** — Nuevo feature `notifications_low_stock` en todos los planes |
| `config/plans.php` | **Modificado** — Nueva feature `notifications_low_stock` en definiciones |
| `app/Services/NotificationService.php` | **Modificado** — Fallback automático si WhatsApp no está habilitado |
| `app/Http/Controllers/DashboardController.php` | **Modificado** — Query de productos con stock bajo |
| `app/Http/Controllers/WorkOrderController.php` | **Modificado** — Incluye `notification_preference` en búsqueda de clientes |
| `resources/views/dashboard.blade.php` | **Modificado** — Nueva sección "Productos con Stock Bajo" |
| `resources/views/clients/create.blade.php` | **Modificado** — WhatsApp condicional según plan |
| `resources/views/clients/edit.blade.php` | **Modificado** — WhatsApp condicional según plan |
| `resources/views/work_orders/create.blade.php` | **Modificado** — WhatsApp condicional + preferencia cliente visible |
| `resources/views/work_orders/show.blade.php` | **Modificado** — Badge visual del canal de notificación |
| `resources/views/landing.blade.php` | **Modificado** — Nuevo feature listado en planes |
| `resources/views/subscription/upgrade.blade.php` | **Modificado** — Nuevo feature listado en upgrade |
| `routes/health.php` | **Modificado** — Nueva ruta `__e2e/seed-plans` para actualizar planes existentes |
| `app/Mail/VerifyEmail.php` | **Nuevo** — Mailable para verificación de correo |
| `app/Mail/NewTenantNotification.php` | **Nuevo** — Mailable para notificar a superadmin |
| `resources/views/emails/auth/verify-email.blade.php` | **Nuevo** — Template HTML del correo de verificación |
| `resources/views/emails/auth/new-tenant.blade.php` | **Nuevo** — Template HTML de notificación a superadmin |
| `resources/views/auth/verified.blade.php` | **Nuevo** — Vista post-verificación con checkmark, botón y countdown |
| `app/Http/Controllers/Auth/AuthController.php` | **Modificado** — `verifyEmail()` ahora renderiza vista en vez de redirect |
| `app/Http/Controllers/TenantController.php` | **Modificado** — Envío de correos (verificación + notificación) en `register()` |
| `routes/health.php` | **Modificado** — Nueva ruta `GET /__e2e/get-verification-token` |
| `.env` | **Modificado** — `MAIL_MAILER=log` → `smtp` |
| `e2e/tenant-registration-prod.spec.ts` | **Nuevo** — 4 tests de registro + verificación |
| `e2e/pos-and-wo-prod.spec.ts` | **Nuevo** — Test integrado POS + OT + tracking |

---

## Para verificar visualmente

1. Revisar bandeja de `salassys25@gmail.com` → correo "Verifica tu correo electrónico"
2. Revisar bandeja de `yojaja100@gmail.com` → correo "Nuevo registro: Taller ..."
3. En Dokploy: confirmar `MAIL_MAILER=smtp` en variables de entorno
4. La URL de tracking de la última ejecución está en la salida del test (buscar `/seguimiento/...`)
