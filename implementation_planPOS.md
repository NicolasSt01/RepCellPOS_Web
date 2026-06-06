# Plan de Implementación — Módulo Punto de Venta (POS)

Corrección del cálculo de impuestos en el carrito del POS, rediseño del panel lateral del carrito de compras, adición de cobros mixtos (efectivo y tarjeta) y previsualización de impresión de tickets.

## User Review Required

> [!IMPORTANT]
> **Cambios en la Base de Datos:**
> Para soportar pagos mixtos, modificaremos la estructura de la tabla `sales`:
> 1. Añadir columna `cash_amount` (decimal 10,2) para registrar la cantidad pagada en efectivo.
> 2. Añadir columna `card_amount` (decimal 10,2) para registrar la cantidad pagada con tarjeta o transferencia.
> 3. Modificar la columna `payment_method` de tipo `enum` a `string` para poder soportar el nuevo método de pago `'mixto'`.
>
> **Ajustes en Control de Caja:**
> Las funciones `getTotalCashSales()` y `getTotalCardSales()` del modelo `CashRegister` se actualizarán para sumar `cash_amount` y `card_amount` de forma respectiva, lo que calculará con total exactitud las ventas incluso para transacciones mixtas.

## Open Questions

> [!NOTE]
> No hay dudas críticas identificadas. La prioridad del pago con tarjeta y el cálculo del cambio se implementarán exactamente de la siguiente manera:
> - El campo de tarjeta estará limitado al total de la venta (para evitar montos de tarjeta excesivos).
> - El cambio se calculará como: `monto_efectivo - (total - monto_tarjeta)`.
> - Solo se entregará cambio si la porción en efectivo ingresada supera la diferencia restante.

## Proposed Changes

---

### Módulo Punto de Venta (POS)

#### [MODIFY] [sales migration](file:///Users/nicolas/Documents/RepCellPOS_Web/database/migrations/2026_06_06_050000_create_sales_and_cash_registers_tables.php) (o crear una nueva migración para no alterar el historial)
*Crearemos una nueva migración `database/migrations/2026_06_06_070000_modify_sales_table_for_mixed_payments.php` para añadir `cash_amount`, `card_amount` y cambiar `payment_method` a string de manera limpia y profesional.*

#### [MODIFY] [Sale.php](file:///Users/nicolas/Documents/RepCellPOS_Web/app/Models/Sale.php)
*Agregar `cash_amount` y `card_amount` a `$fillable` y `$casts`.*

#### [MODIFY] [CashRegister.php](file:///Users/nicolas/Documents/RepCellPOS_Web/app/Models/CashRegister.php)
*Modificar las funciones de reporte de caja para calcular en base a las nuevas columnas de montos específicos.*

#### [MODIFY] [PosController.php](file:///Users/nicolas/Documents/RepCellPOS_Web/app/Http/Controllers/PosController.php)
*Implementar validación y cálculo de impuestos según `tax_enabled` del tenant. Procesar montos mixtos en `checkout` y agregar método `print`.*

#### [MODIFY] [pos/index.blade.php](file:///Users/nicolas/Documents/RepCellPOS_Web/resources/views/pos/index.blade.php)
*Rediseñar el panel de compras (carrito), crear el modal de checkout interactivo con Alpine.js para pagos mixtos y el modal de previsualización de ticket.*

#### [NEW] [ticket_58mm.blade.php](file:///Users/nicolas/Documents/RepCellPOS_Web/resources/views/pos/print/ticket_58mm.blade.php)
#### [NEW] [ticket_80mm.blade.php](file:///Users/nicolas/Documents/RepCellPOS_Web/resources/views/pos/print/ticket_80mm.blade.php)
#### [NEW] [a4.blade.php](file:///Users/nicolas/Documents/RepCellPOS_Web/resources/views/pos/print/a4.blade.php)
*Nuevas plantillas de impresión para cada formato soportado.*

---

## Verification Plan

### Automated Tests
- Ejecutar `php artisan test`
- Crear una prueba unitaria/de integración en `tests/Feature/PosMixedPaymentTest.php` para validar la lógica del controlador con pagos mixtos, prioridad de tarjeta y cálculo de cambio.

### Manual Verification
1. Configuración de impuestos deshabilitada: comprobar que el POS no cargue IVA.
2. Configuración de impuestos habilitada (modo por producto o sobre el total): verificar el correcto cálculo.
3. Pago mixto en el modal:
   - Registrar venta de $150 con $100 tarjeta y $50 efectivo. Verificar cambio = $0.00.
   - Registrar venta de $150 con $100 tarjeta y $70 efectivo. Verificar cambio = $20.00.
4. Botón "Cobrar": verificar que guarde y envíe a imprimir de forma inmediata.
5. Botón "Cobrar con vista previa": verificar que muestre el ticket formateado en el modal de vista previa antes de limpiar el carrito.
