# Plan de Reportes — RepCellPOS

> Documento de planificación de reportes para el sistema RepCellPOS (POS + taller de reparación de celulares, multi-tenant).
> Stack: Laravel 13 · PHP 8.3 · Blade + Alpine.js + Tailwind · MySQL · `barryvdh/laravel-dompdf` (PDF) · `spatie/laravel-permission`.

---

## 0. Convenciones y arquitectura común

Todos los reportes seguirán el mismo patrón ya establecido en `work_orders/reports.blade.php` para mantener consistencia:

### Estructura técnica estándar
- **Ruta**: `routes/web.php` → `Route::get('/reportes/{area}', [ReportController::class, '{area}'])->name('reportes.{area}')->middleware(['auth', 'permission:reportes.ver']);`
- **Controlador**: `app/Http/Controllers/ReportController.php` (nuevo, centralizado) o un controlador por área (`Reportes\VentasController`, etc.).
- **Vista**: `resources/views/reportes/{area}.blade.php` extendiendo `layouts.app`.
- **Consultas**: Usar Eloquent + `selectRaw` para agregaciones; respetar scope multi-tenant si aplica.
- **Filtros comunes**: `date_from`, `date_to`, botones de rango rápido (Hoy / Semana / Mes / Trimestre / Año), botón "Limpiar".
- **Exportación**: Botón "Exportar PDF" (DomPDF) y "Exportar CSV" en cada reporte.
- **Permisos**: Crear rol/permiso `reportes.ver` y derivados por área (`reportes.ventas`, `reportes.taller`, etc.) mediante `spatie/laravel-permission`.
- **KPIs**: Cards superiores (como en `reports.blade.php`) + tabla detallada + (opcional) gráfico con Chart.js o ApexCharts.
- **Paginación**: `->paginate(25)` con `{{ $data->links() }}`.

### Phase 0 (transversal, hacer una sola vez)
1. Crear migración/seed para permisos de reportes.
2. Crear `ReportController` base con traits de filtros de fecha.
3. Crear layout de reportes (`layouts.reporte`) con header de filtros reutilizable como componente Blade `<x-reportes.filtros />`.
4. Instalar (si se quieren gráficos) `apexcharts` vía npm o usar CDN.
5. Helpers: `formatMoney()`, `formatDate()`, `porcentaje()`.

---

## 1. Reportes de Taller / Órdenes de Trabajo

> **Modelo base**: `WorkOrder` (relaciones: `client`, `assignedTechnician`, `quote`).
> **Estado actual**: ya existe `work_orders/reports.blade.php` con resumen + filtros + tabla. Los reportes siguientes **extienden** ese módulo.

### 1.1 Productividad por Técnico

**Para qué sirve**
Mide el rendimiento individual de cada técnico: cuántas OT completa, cuánto tiempo le toma en promedio y cuántas tiene activas. Permite identificar técnicos sobrecargados, técnicos rezagados y asignar mejor la carga de trabajo.

**Objetivos**
- Cuantificar productividad individual y del equipo.
- Detectar técnicos con cuellos de botella.
- Incentivar/distribuir comisiones con base en datos objetivos.

**KPIs / Métricas**
- OT activas por técnico
- OT completadas en período
- Tiempo promedio de reparación (horas/días desde `en_reparacion` hasta `reparada`)
- Tasa de cumplimiento vs promesa de entrega
- Ranking de productividad

**Cómo se realizará**
- **Query**: `WorkOrder::selectRaw('assigned_to, count(*) as total, avg(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_time')->whereNotNull('assigned_to')->whereBetween('created_at', [...])->groupBy('assigned_to')`
- **Filtros**: rango de fechas, técnico específico, estado, prioridad.
- **Vista**: tabla ranking con barras de progreso (Tailwind) + gráfico de barras.
- **Extiende**: reutilizar consulta de `byTechnician` que ya existe en `WorkOrderController::reports`.

**Dependencias**: ninguna adicional.

---

### 1.2 Ciclo de Vida / Cuellos de Botella por Estado

**Para qué sirve**
Identifica en qué estado se estancan las órdenes. Si la mayoría del tiempo se pasa en `cotizacion_enviada`, hay un problema de proceso comercial, no técnico.

**Objetivos**
- Detectar estados con tiempo excesivo.
- Optimizar el flujo operativo del taller.
- Justificar cambios de proceso con datos.

**KPIs / Métricas**
- Tiempo promedio por estado (días)
- Distribución del tiempo total de una OT entre estados
- Cantidad de OT que exceden el tiempo esperado por estado
- % de OT "estancadas" (>N días sin cambio de estado)

**Cómo se realizará**
- **Requisito previo**: crear tabla `work_order_status_history` (migración nueva) que registre `work_order_id`, `from_status`, `to_status`, `changed_by`, `created_at`. Alimentada por un observer `WorkOrderObserver` en `updating`.
- **Query**: agregación sobre `work_order_status_history` con `TIMESTAMPDIFF` entre transiciones consecutivas.
- **Vista**: funnel chart + tabla de tiempo promedio por estado + heatmap estado×día.
- **Filtros**: rango de fechas, técnico, prioridad.

**Dependencias**: nueva migración + observer. Es el reporte más costoso de implementar pero de alto valor analítico.

---

### 1.3 SLA / Órdenes Atrasadas

**Para qué sirve**
Mide el cumplimiento de tiempos de entrega prometidos según prioridad. Permite actuar proactivamente antes de que un cliente reclame.

**Objetivos**
- Reducir reclamos por demora.
- Priorizar OT en riesgo.
- Establecer y validar SLAs realistas por tipo de reparación.

**KPIs / Métricas**
- % OT entregadas a tiempo vs atrasadas
- OT en riesgo (a X% del deadline)
- OT vencidas sin entregar
- Edad promedio de OT activas

**Cómo se realizará**
- **Requisito previo**: agregar columna `promised_at` (datetime) a `work_orders` si no existe (verificar migraciones).
- **Query**: comparar `promised_at` con `now()` para activas; con `updated_at` para terminadas.
- **Vista**: tabla con código de color (verde/amarillo/rojo) según cercanía al vencimiento, KPI cards de % cumplimiento.
- **Filtros**: rango, técnico, prioridad.

**Dependencias**: posible migración para `promised_at`. Configurar SLAs esperados por prioridad (settings existentes en `SettingsController`).

---

### 1.4 Conversión de Cotizaciones

**Para qué sirve**
Mide la efectividad comercial: de cada 100 cotizaciones enviadas, ¿cuántas se aprueban? Es el indicador clave del modelo de negocio del taller.

**Objetivos**
- Cuantificar tasa de cierre comercial.
- Identificar cotizaciones abandonadas (sin respuesta).
- Ajustar precios / estrategia de cotización.

**KPIs / Métricas**
- Cotizaciones enviadas en período
- % aprobadas, % rechazadas, % pendientes de respuesta
- Tiempo promedio de respuesta del cliente
- Monto total cotizado vs monto aprobado
- Tasa de aprobación por técnico

**Cómo se realizará**
- **Modelo base**: `Quote` con estados (`enviada`, `aprobada`, `rechazada`).
- **Query**: `Quote::selectRaw('status, count(*) as total, sum(total) as monto')->whereBetween('created_at', [...])->groupBy('status')`.
- **Vista**: donut chart de distribución de estados + embudo (enviadas → aprobadas → convertidas en OT reparadas) + tabla por técnico.
- **Filtros**: rango, técnico, estado.

**Dependencias**: ninguna (modelo `Quote` ya existe).

---

### 1.5 Dispositivos más Reparados

**Para qué sirve**
Conocer qué marcas y modelos se reparan más para anticipar repuestos, capacitar técnicos y negociar con proveedores.

**Objetivos**
- Optimizar compra de repuestos por demanda real.
- Identificar modelos problemáticos (más fallas).
- Guiar marketing especializado.

**KPIs / Métricas**
- Top N marcas por volumen de OT
- Top N modelos por volumen de OT
- Tipo de problema más frecuente por modelo
- Ticket promedio por marca/modelo

**Cómo se realizará**
- **Query**: `WorkOrder::selectRaw('device_brand, device_model, count(*) as total, avg(quote.total) as ticket')->groupBy('device_brand', 'device_model')`.
- **Vista**: tabla top 10 + gráfico de barras horizontales.
- **Filtros**: rango, marca, estado.

**Dependencias**: ninguna.

---

## 2. Reportes de POS / Ventas

> **Modelos base**: `Sale`, `SaleItem`, `Product`, `Category`. La columna de pagos mixtos ya existe.

### 2.1 Ventas por Período

**Para qué sirve**
Reporte maestro de ventas: ingresos totales, evolución diaria, comparativa con período anterior. Es el reporte más usado por el dueño/gerente.

**Objetivos**
- Monitorear ingresos y tendencia.
- Comparar períodos (mes vs mes, año vs año).
- Detectar estacionalidad.

**KPIs / Métricas**
- Ingreso total del período
- Número de ventas
- Ticket promedio
- Crecimiento vs período anterior (%)
- Serie diaria de ingresos (para gráfico)

**Cómo se realizará**
- **Query**: `Sale::whereBetween('created_at', [...])->selectRaw('DATE(created_at) as dia, sum(total) as total, count(*) as n')->groupBy('dia')`.
- **Vista**: KPIs + gráfico de líneas de evolución + tabla diaria + botones de rango rápido.
- **Filtros**: rango, método de pago, usuario/cajero.
- **Comparativo**: calcular período anterior equivalente y mostrar Δ%.

**Dependencias**: ninguna.

---

### 2.2 Top Productos y Ventas por Categoría

**Para qué sirve**
Identifica productos estrella y categorías que más venden para optimizar catálogo y compras.

**Objetivos**
- Detectar productos de alta rotación.
- Identificar categorías que aportan más margen.
- Decidir qué promocionar o descontinuar.

**KPIs / Métricas**
- Top N productos por unidades vendidas
- Top N productos por ingreso
- Ingreso por categoría
- % de contribución al total

**Cómo se realizará**
- **Query**: `SaleItem::join('products', ...)->selectRaw('product_id, sum(quantity) as units, sum(subtotal) as revenue')->groupBy('product_id')->orderByDesc('units')`.
- **Vista**: tabla top 20 + gráfico de barras + treemap de categorías.
- **Filtros**: rango, categoría.

**Dependencias**: ninguna.

---

### 2.3 Ventas por Método de Pago

**Para qué sirve**
Conoce la composición de los cobros (efectivo, tarjeta, transferencia, mixto). Útil para conciliación y proyección de caja.

**Objetivos**
- Conciliar con caja y bancos.
- Negociar comisiones con proveedores de tarjeta.
- Detectar cambios en hábitos de pago.

**KPIs / Métricas**
- Ingreso por método de pago
- % de cada método
- Número de ventas mixtas y monto por componente
- Ticket promedio por método

**Cómo se realizará**
- **Query**: aggregate sobre columnas de pago mixto de `sales` (ver migración `2026_06_06_070000`).
- **Vista**: donut chart + tabla desglose.
- **Filtros**: rango.

**Dependencias**: ninguna.

---

### 2.4 Ticket Promedio y Devoluciones / Mermas

**Para qué sirve**
Mide el valor promedio por venta y el impacto de devoluciones y mermas en el ingreso neto. Indicador de salud comercial.

**Objetivos**
- Mejorar estrategias de up-selling/cross-selling.
- Cuantificar pérdidas por devoluciones y mermas.
- Calcular ingreso neto real.

**KPIs / Métricas**
- Ticket promedio del período
- % devoluciones sobre ventas
- Monto de mermas (`WasteRecord`)
- Ingreso neto = ventas − devoluciones − mermas

**Cómo se realizará**
- **Query**: combinar `Sale`, `SalesReturn`, `WasteRecord` agregados por período.
- **Vista**: KPIs + gráfico de tendencia del ingreso neto + tabla de devoluciones recientes.
- **Filtros**: rango, motivo.

**Dependencias**: ninguna (modelos ya existen).

---

## 3. Reportes de Inventario / Kardex

> **Modelos base**: `Product`, `KardexMovement`, `Category`. Existe `reserved_stock` para cotizaciones aprobadas.

### 3.1 Valorización de Stock y Stock Crítico

**Para qué sirve**
Conocer el valor monetario del inventario actual y detectar productos por debajo del stock mínimo para reposición.

**Objetivos**
- Planificar compras.
- Evitar quiebres de stock.
- Tener valorización para contabilidad/impuestos.

**KPIs / Métricas**
- Valor total del inventario (`sum(stock * cost)`)
- Valor por categoría
- N° de productos bajo mínimo
- N° de productos sin stock
- Lista priorizada de reposición

**Cómo se realizará**
- **Query**: `Product::selectRaw('*, (stock + reserved_stock) as total, stock * cost as valor')->where('is_active', true)`.
- **Vista**: KPIs + tabla de productos bajo mínimo con botón "Generar orden de compra" (opcional) + gráfico de valor por categoría.
- **Filtros**: categoría, umbral de stock mínimo.

**Dependencias**: requiere columna `cost` y `min_stock` en `products` (verificar migraciones; si no, agregar).

---

### 3.2 Rotación de Inventario y Productos Obsoletos

**Para qué sirve**
Mide qué tan rápido se mueve el inventario. Detecta capital inmovilizado en productos que no se venden.

**Objetivos**
- Optimizar capital de trabajo.
- Liquidar productos obsoletos.
- Ajustar cantidades de compra.

**KPIs / Métricas**
- Rotación = unidades vendidas / stock promedio
- Días de inventario = 365 / rotación
- Productos sin movimiento en N días
- Valor inmovilizado en obsoletos

**Cómo se realizará**
- **Query**: join `SaleItem` (ventas del período) con `Product` (stock actual); calcular rotación; LEFT JOIN para detectar productos sin ventas.
- **Vista**: tabla con rotación + tabla de obsoletos con antigüedad y valor inmovilizado.
- **Filtros**: rango, categoría, umbral de "sin movimiento".

**Dependencias**: ninguna.

---

### 3.3 Kardex Consolidado

**Para qué sirve**
Vista unificada de movimientos de inventario (entradas, salidas, reservas, devoluciones, mermas) por producto o rango. Es el reporte de auditoría de inventario.

**Objetivos**
- Trazabilidad completa de inventario.
- Auditoría contable.
- Detectar discrepancias.

**KPIs / Métricas**
- Movimientos por tipo
- Saldo inicial, entradas, salidas, saldo final
- Productos con discrepancias (saldo físico vs sistema)

**Cómo se realizará**
- **Query**: `KardexMovement::with('product')->whereBetween('created_at', [...])->orderBy('created_at')` agrupado por producto.
- **Vista**: tabla tipo libro mayor con saldo corrido por producto + filtros por tipo de movimiento.
- **Filtros**: rango, producto, tipo de movimiento.

**Dependencias**: ninguna (modelo ya existe).

---

## 4. Reportes de Caja / Finanzas

> **Modelos base**: `CashRegister`, `CashRegisterMovement`, `CashRegisterIncident`.

### 4.1 Cuadre de Caja por Turno / Usuario

**Para qué sirve**
Verifica que el efectivo reportado coincida con el efectivo esperado al cierre de cada turno. Detecta descuadres y su responsable.

**Objetivos**
- Detectar descuadres al instante.
- Asignar responsabilidad por usuario.
- Reducir pérdidas de efectivo.

**KPIs / Métricas**
- Saldo inicial, ingresos, egresos, saldo final esperado
- Saldo final declarado
- Diferencia (descuadre)
- N° de incidentes por turno

**Cómo se realizará**
- **Query**: agregaciones de `CashRegisterMovement` por `cash_register_id` + `CashRegisterIncident` por turno.
- **Vista**: una tarjeta por turno del día + tabla detallada de movimientos + sección de incidentes.
- **Filtros**: fecha, usuario.

**Dependencias**: ninguna.

---

### 4.2 Flujo de Efectivo Diario e Ingresos vs Devoluciones

**Para qué sirve**
Visión consolidada del flujo de caja diario: entradas, salidas, devoluciones. Indicador de liquidez.

**Objetivos**
- Anticipar faltantes de liquidez.
- Cuantificar impacto de devoluciones en caja.
- Reportar a administración.

**KPIs / Métricas**
- Flujo neto diario
- Ingresos por ventas
- Egresos (movimientos de salida)
- Devoluciones en efectivo
- Saldo acumulado

**Cómo se realizará**
- **Query**: union de `CashRegisterMovement` (tipo entrada/salida) + `SalesReturn` que afectaron caja.
- **Vista**: gráfico de líneas del flujo neto diario + tabla diaria con desglose.
- **Filtros**: rango, tipo de movimiento.

**Dependencias**: ninguna.

---

## 5. Reportes de Clientes

> **Modelo base**: `Client` (relaciones: `workOrders`, `sales`).

### 5.1 Clientes Nuevos vs Recurrentes (Retención)

**Para qué sirve**
Mide cuántos clientes nuevos llegan y cuántos vuelven. Indicador de salud del negocio y de la efectividad del servicio.

**Objetivos**
- Medir retención / fidelización.
- Evaluar campañas de adquisición.
- Identificar clientes VIP.

**KPIs / Métricas**
- Clientes nuevos en período
- Clientes recurrentes (≥2 OT o ventas en período)
- Tasa de retención
- Cohortes mensuales (opcional)

**Cómo se realizará**
- **Query**: `Client::withCount(['workOrders' => fn($q) => $q->whereBetween(...), 'sales' => fn($q) => $q->whereBetween(...)])` + clasificación según recuento.
- **Vista**: KPIs + gráfico de cohortes (simple) + tabla de clientes nuevos vs recurrentes.
- **Filtros**: rango.

**Dependencias**: ninguna.

---

### 5.2 Top Clientes por Gasto y por OT

**Para qué sirve**
Identifica los clientes más valiosos para fidelización, beneficios y marketing dirigido.

**Objetivos**
- Segmentar clientes VIP.
- Diseñar programa de lealtad.
- Personalizar atención.

**KPIs / Métricas**
- Top N clientes por gasto total (ventas + cotizaciones aprobadas)
- Top N clientes por número de OT
- Frecuencia de visita promedio
- Ticket promedio por cliente

**Cómo se realizará**
- **Query**: join `Client` + `Sale` + `WorkOrder` agregando `sum(total)` y `count(*)`.
- **Vista**: tabla ranking con barras + badge VIP (top 10%).
- **Filtros**: rango.

**Dependencias**: ninguna.

---

## 6. Reportes de Cotizaciones

> **Modelos base**: `Quote`, `QuoteItem`. Ya hay interacción pública (aprobar/rechazar) desde tracking.

### 6.1 Tasa de Aprobación de Cotizaciones

**Para qué sirve**
KPI comercial central: mide qué porcentaje de cotizaciones se aprueban y por cuánto. Ya mencionado en §1.4; aquí se detalla el reporte completo dedicado a cotizaciones.

**Objetivos**
- Optimizar pricing y tiempos de respuesta.
- Detectar cotizaciones "olvidadas" por el cliente.
- Medir efectividad por técnico que cotiza.

**KPIs / Métricas**
- Total cotizaciones enviadas
- Tasa de aprobación (% y monto)
- Tasa de rechazo (%)
- Tasa sin respuesta (%)
- Tiempo promedio cliente → respuesta
- Ticket cotizado vs ticket aprobado

**Cómo se realizará**
- **Query**: `Quote::whereBetween('sent_at', [...])->selectRaw('status, count(*) as n, sum(total) as monto, avg(TIMESTAMPDIFF(HOUR, sent_at, responded_at)) as tiempo')->groupBy('status')`.
- **Vista**: cards de tasas + donut + tabla por técnico + lista de cotizaciones sin respuesta > N días.
- **Filtros**: rango, técnico, estado.

**Dependencias**: verificar columnas `sent_at` y `responded_at` en `quotes`; si no, inferir de `created_at` / `updated_at`.

---

## 7. Reportes Multi-tenant / SuperAdmin

> **Modelos base**: `Tenant`, `TenantSubscription`, `Plan`, `User`, `WorkOrder`, `Sale`. Ya existe `SuperAdminController`.

### 7.1 MRR / ARR y Suscripciones Activas

**Para qué sirve**
Indicador financiero SaaS: ingresos recurrentes mensuales y anuales proyectados. Vital para gestión del producto como servicio.

**Objetivos**
- Monitorear salud financiera del SaaS.
- Proyectar ingresos.
- Detectar planes con mayor adopción.

**KPIs / Métricas**
- MRR = Σ(plan_price × suscripciones activas)
- ARR = MRR × 12
- Suscripciones activas / inactivas / en prueba
- Distribución por plan

**Cómo se realizará**
- **Query**: `TenantSubscription::where('status', 'activa')->with('plan')->get()` agrupado por plan; sumar precio.
- **Vista**: KPIs MRR/ARR + gráfico de evolución del MRR + tabla de distribución por plan.
- **Filtros**: rango, estado, plan.

**Dependencias**: ninguna.

---

### 7.2 Crecimiento, Churn y Tenants Activos

**Para qué sirve**
Mide adopción y abandono del SaaS: cuántos tenants se activan, cuántos cancelan, cuántos están realmente usando el sistema.

**Objetivos**
- Detectar fugas de clientes.
- Identificar cohortes con mejor/worst retención.
- Guiar esfuerzos de onboarding/soporte.

**KPIs / Métricas**
- Tenants nuevos en período
- Tenants cancelados (churn)
- Tasa de churn (%)
- Tenants activos con uso real (≥1 OT o venta en últimos 30 días)
- Tenants inactivos en riesgo

**Cómo se realizará**
- **Query**: `Tenant::with('subscription')->whereBetween('created_at', [...])` + subqueries de actividad (`WorkOrder`/`Sale` por tenant en últimos 30 días).
- **Vista**: KPIs + gráfico de churn + tabla de tenants en riesgo (sin actividad reciente).
- **Filtros**: rango, plan, estado.

**Dependencias**: ninguna (requiere que las consultas respeten el contexto multi-tenant, sin scope global).

---

### 7.3 Uso por Tenant

**Para qué sirve**
Detecta cuentas que subutilizan el producto (en riesgo de churn) o que lo sobreutilizan (oportunidad de upsell).

**Objetivos**
- Alertar sobre tenants inactivos.
- Identificar oportunidades de upsell.
- Dimensionar carga por cliente.

**KPIs / Métricas**
- OT / ventas por tenant en el período
- Usuarios activos por tenant
- Almacenamiento / archivos usados (si aplica)
- % de tenants que superan límites de su plan

**Cómo se realizará**
- **Query**: agregaciones agrupadas por `tenant_id` en `WorkOrder`, `Sale`, `User`.
- **Vista**: tabla por tenant con columnas de uso + badges de plan + alertas.
- **Filtros**: rango, plan.

**Dependencias**: ninguna.

---

## 8. Plan de Acción / Hoja de Ruta

### Criterios de priorización
1. **Valor de negocio**: alto impacto en decisiones operativas/comerciales.
2. **Esfuerzo**: datos ya disponibles vs requiere migraciones.
3. **Reusabilidad**: extiende módulos existentes vs. nuevo desde cero.

### Fases de implementación

#### Fase 0 — Fundaciones (1–2 días)
- [ ] Crear permisos `reportes.*` con `spatie/laravel-permission`.
- [ ] Crear `ReportController` (o carpeta `Reportes/`).
- [ ] Crear componente Blade `<x-reportes.filtros>` reutilizable.
- [ ] Helper de formato de moneda/fecha.
- [ ] Decidir librería de gráficos (ApexCharts vía CDN recomendado).
- [ ] Exportación PDF/CSV genérica (trait `ExportableReport`).

#### Fase 1 — Reportes de alto valor, bajo esfuerzo (1–2 semanas)
Reportes que usan datos existentes y patrones ya presentes en el código:

1. **2.1 Ventas por período** ⭐ (inicio recomendado)
2. **1.1 Productividad por técnico** (extiende reporte OT existente)
3. **1.4 / 6.1 Conversión de cotizaciones**
4. **3.1 Valorización de stock y stock crítico**
5. **4.1 Cuadre de caja por turno**

#### Fase 2 — Reportes de profundización (2–3 semanas)
6. **2.2 Top productos y ventas por categoría**
7. **2.4 Ticket promedio y devoluciones/mermas**
8. **1.5 Dispositivos más reparados**
9. **3.3 Kardex consolidado**
10. **4.2 Flujo de efectivo diario**
11. **5.2 Top clientes**

#### Fase 3 — Reportes con requisito de datos nuevos (3–4 semanas)
12. **1.3 SLA / OT atrasadas** (requiere `promised_at`)
13. **1.2 Ciclo de vida / cuellos de botella** (requiere `work_order_status_history` + observer) ⚠️ más costoso
14. **3.2 Rotación de inventario y obsoletos**
15. **5.1 Clientes nuevos vs recurrentes**

#### Fase 4 — Reportes SaaS / SuperAdmin (1–2 semanas)
16. **7.1 MRR / ARR**
17. **7.2 Crecimiento, churn y tenants activos**
18. **7.3 Uso por tenant**

#### Fase 5 — Pulido (1 semana)
- [ ] Dashboard de reportes (landing `/reportes` con cards hacia cada uno).
- [ ] Programación de envío por email (usando sistema de notificaciones existente + jobs).
- [ ] Permisos finos por rol.
- [ ] Tests Playwright de los reportes clave.
- [ ] Documentación de usuario en `docs/`.

### Tabla resumen de prioridades

| # | Reporte | Área | Esfuerzo | Valor | Fase |
|---|---------|------|----------|-------|------|
| 2.1 | Ventas por período | POS | Bajo | Alto | 1 |
| 1.1 | Productividad por técnico | Taller | Bajo | Alto | 1 |
| 1.4 | Conversión de cotizaciones | Taller | Bajo | Alto | 1 |
| 3.1 | Valorización / stock crítico | Inventario | Bajo | Alto | 1 |
| 4.1 | Cuadre de caja | Caja | Bajo | Alto | 1 |
| 2.2 | Top productos / categorías | POS | Medio | Medio | 2 |
| 2.4 | Ticket promedio / devoluciones | POS | Medio | Medio | 2 |
| 1.5 | Dispositivos más reparados | Taller | Bajo | Medio | 2 |
| 3.3 | Kardex consolidado | Inventario | Bajo | Medio | 2 |
| 4.2 | Flujo de efectivo | Caja | Medio | Medio | 2 |
| 5.2 | Top clientes | Clientes | Bajo | Medio | 2 |
| 1.3 | SLA / OT atrasadas | Taller | Medio | Alto | 3 |
| 1.2 | Ciclo de vida / cuellos de botella | Taller | Alto | Alto | 3 |
| 3.2 | Rotación / obsoletos | Inventario | Medio | Medio | 3 |
| 5.1 | Clientes nuevos vs recurrentes | Clientes | Medio | Medio | 3 |
| 2.3 | Ventas por método de pago | POS | Bajo | Medio | 2 |
| 7.1 | MRR / ARR | SaaS | Bajo | Alto | 4 |
| 7.2 | Crecimiento / churn | SaaS | Medio | Alto | 4 |
| 7.3 | Uso por tenant | SaaS | Medio | Alto | 4 |

---

## 9. Consideraciones técnicas transversales

### Performance
- **Indexes**: asegurar índices en `created_at`, `tenant_id`, `status`, `assigned_to` en tablas de reportes.
- **Cache**: para reportes de períodos cerrados (ej. ventas del mes anterior), cachear resultados 1 hora con `Cache::remember`.
- **Paginación**: nunca `->get()` sin paginar en reportes de listado; usar `->paginate(25)` o `->simplePaginate()`.
- **Límites**: top N con `->take(N)` para evitar consultas enormes.

### Multi-tenant
- Reportes de tenant deben aplicar scope automático (`tenant_id` del usuario autenticado).
- Reportes de SuperAdmin deben **eliminar** el scope (`withoutGlobalScope`) y agrupar por `tenant_id`.
- Validar permisos con `permission` middleware + policy.

### Accesibilidad / UX
- Cards de KPI grandes y legibles.
- Código de color consistente (verde=bueno, amarillo=alerta, rojo=crítico).
- Botones de rango rápido (Hoy / 7d / 30d / Mes / Trimestre / Año).
- Filtros persistentes en la URL (`request()->only(...)`).
- Exportación PDF con cabecera de logo del tenant (usando `barryvdh/laravel-dompdf`).

### Seguridad
- No exponer datos cruzados entre tenants (validar en policy, no solo middleware).
- Sanitizar inputs de fecha.
- Auditoría de accesos a reportes sensibles (log de acceso a reportes financieros).

### Tests
- PHPUnit: pruebas unitarias de cada agregación (factories + aserción de totales).
- Playwright: al menos un test E2E por reporte de Fase 1 (login → navegar → filtrar → ver KPI).

---

## 10. Próximos pasos inmediatos

1. **Confirmar** este plan con el equipo / stakeholder.
2. **Revisar** el listado de migraciones y marcar cuáles columnas faltan (`promised_at`, `cost`, `min_stock`, `sent_at`, `responded_at`).
3. **Aprobar** el set de la Fase 1 (5 reportes) para arrancar.
4. **Implementar Fase 0** (fundaciones) antes que cualquier reporte.
5. **Arrancar** con el reporte **2.1 Ventas por período** por ser el de mayor impacto y menor esfuerzo.

---

_Última actualización: 2026-06-25_
_Autor: Análisis del repositorio RepCellPOS_
