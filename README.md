# RepCellPOS

Sistema de gestión integral para talleres de reparación de celulares: órdenes de trabajo, inventario, punto de venta, control de caja, cotizaciones y seguimiento público para clientes.

## Tecnologías

- **Backend**: Laravel 13, PHP 8.4, MySQL
- **Frontend**: Blade, Alpine.js, Tailwind CSS
- **Testing**: Playwright (E2E), PHPUnit
- **Cola/Jobs**: MySQL (sync en desarrollo)

## Setup rápido

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Pruebas E2E con Playwright

### Infraestructura

- **Servidor**: `php artisan serve --env=e2e --port=8080`
- **Base de datos**: MySQL `repcellpos_e2e` (independiente de la BD principal)
- **Config**: `.env.e2e` (local, no se sube al repo)
- **Browser**: Chromium en modo headed

### Comandos

```bash
# Iniciar servidor E2E
php artisan serve --env=e2e --port=8080

# Limpiar datos entre ejecuciones
: > storage/logs/laravel.log
curl http://localhost:8080/__e2e/cleanup

# Ejecutar tests
npx playwright test e2e/ --headed
```

### Tests disponibles (12 pruebas)

| Archivo | Descripción |
|---------|-------------|
| `e2e/tenant-registration.spec.ts` | 10 tests: registro, validaciones, sidebar, errores |
| `e2e/pos-flow.spec.ts` | Creación de productos + venta + verificación de inventario |
| `e2e/work-order-flow.spec.ts` | OT completa + ticket impresión + email + cotización + tracking |

### Modos de correo

El test de work order detecta automáticamente el mailer:

- **`MAIL_MAILER=log`** — verifica contenido del email en `storage/logs/laravel.log`
- **`MAIL_MAILER=smtp`** — verifica que no haya errores SMTP en el log

### Rutas auxiliares E2E

| Ruta | Propósito |
|------|-----------|
| `GET /health` | Health check del servidor |
| `GET /__e2e/cleanup` | Trunca `work_orders`, `notifications` y resetea secuencias |
| `GET /__e2e/read-log` | Devuelve contenido de `storage/logs/laravel.log` |

## Funcionalidades implementadas

### Seguimiento público de órdenes (Tracking)

- Ruta pública `GET /seguimiento/{token}` para que clientes consulten estado de su orden
- Cotizaciones visibles desde el portal público
- El cliente puede **aprobar** o **rechazar** la cotización desde el tracking
- Rechazar cotización NO cancela la orden de trabajo (solo marca cotización como rechazada)
- Aprobar cotización reserva stock de productos y cambia estado a "Cotización Aprobada"

### Órdenes de Trabajo

- Formulario multi-paso con Alpine.js (Cliente → Equipo → Problema → Seguridad)
- Número de orden único global (`OT-00001`)
- Generación automática de `tracking_token` al crear la OT
- Ticket de impresión en formato comprobante de recepción
- Notificación por email al cliente con enlace de seguimiento

### Punto de Venta (POS)

- Registro de productos con stock y categorías
- Apertura/cierre de caja
- Ventas con descuento y cálculo de totales
- Kardex de inventario actualizado automáticamente
- Compatible con MySQL y SQLite (FIELD() condicional)

## Diagrama de flujo: Creación de OT → Email → Tracking → Cotización

```
Usuario crea OT → Se genera tracking_token → Email al cliente
                                                    ↓
                              Cliente abre link de seguimiento público
                                                    ↓
                              Admin crea cotización → Envia al cliente
                                                    ↓
                              Cliente ve cotización en tracking
                              ├── Acepta → stock reservado, OT actualizada
                              └── Rechaza → cotización marcada, OT intacta
```

## Cambios recientes

Ver `git log` para historial completo de commits.
