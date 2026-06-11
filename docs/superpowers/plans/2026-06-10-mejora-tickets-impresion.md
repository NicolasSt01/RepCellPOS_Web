# Mejora de Tickets/Impresión — INC-MOD06-017

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Revisar y mejorar la generación de tickets/comprobantes de venta en POS.

**Architecture:** Los cambios abarcan seguridad (tenant authorization en rutas de impresión), corrección del flag `preview`, integración de cláusulas `print_on_receipt`, inclusión del cliente en tickets, reglas `@media print` para tickets térmicos, y cobertura de tests.

**Tech Stack:** Laravel 13, Blade, PHP 8.4, MySQL/SQLite (tests), Tailwind (vista A4)

---

### Task 1: Seguridad — Tenant authorization en rutas de impresión

**Files:**
- Modify: `app/Http/Controllers/PosController.php:298-316`

- [ ] **Step 1: Agregar `authorizeTenant()` en `PosController::print()` y `printPreview()`**

```php
public function print(Sale $sale): View
{
    $this->authorizeTenant($sale);

    $sale->load(['saleItems', 'user']);
    $tenant = $sale->tenant;
    $format = $tenant->print_format ?? 'ticket_58mm';

    return view("pos.print.{$format}", compact('sale', 'tenant'));
}

public function printPreview(Sale $sale): View
{
    $this->authorizeTenant($sale);

    $sale->load(['saleItems', 'user']);
    $tenant = $sale->tenant;
    $format = $tenant->print_format ?? 'ticket_58mm';

    return view("pos.print.{$format}", compact('sale', 'tenant'))->with('preview', true);
}
```

- [ ] **Step 2: Verificar que `authorizeTenant` existe en el Controller base**

Leer `app/Http/Controllers/Controller.php` para confirmar que `authorizeTenant` está disponible.

- [ ] **Step 3: Quitar `client` del eager load** (ya no se necesita, se carga bajo demanda o se pasa desde sale)

- [ ] **Step 4: Ejecutar tests para confirmar que no se rompe nada**

Run: `php artisan test --filter="print" --compact`
Expected: PASS

---

### Task 2: Corregir flag `$preview` — suprimir auto-print en preview

**Files:**
- Modify: `resources/views/pos/print/ticket_58mm.blade.php`
- Modify: `resources/views/pos/print/ticket_80mm.blade.php`
- Modify: `resources/views/pos/print/a4.blade.php`

- [ ] **Step 1: En cada vista, cambiar `onload` condicional al flag `$preview`**

```blade
<body onload="{{ $preview ?? false ? '' : 'window.print()' }}">
```

- [ ] **Step 2: En preview, mostrar botón "Imprimir" visible para que el usuario decida**

Agregar antes del `@media print` o al final del body:
```blade
@if($preview ?? false)
    <div style="text-align:center;margin-top:20px;font-family:sans-serif;">
        <button onclick="window.print()" style="padding:10px 30px;font-size:16px;cursor:pointer;">🖨 Imprimir</button>
    </div>
@endif
```

- [ ] **Step 3: Verificar que el preview modal en `pos/index.blade.php` funciona correctamente**

El modal ya carga `previewHtml` vía fetch a `/pos/print/{id}/preview`. Con el cambio, ya no se disparará el diálogo de impresión automáticamente.

---

### Task 3: Consistencia — Default format

**Files:**
- Modify: `app/Http/Controllers/PosController.php:303` (cambiar fallback a `ticket_80mm`)
- Modify: `database/migrations/2026_06_06_010000_create_tenants_table.php:23` (ya es `ticket_80mm` el default)

- [ ] **Step 1: Alinear fallback del controlador con el default de migración**

```php
$format = $tenant->print_format ?? 'ticket_80mm';
```

---

### Task 4: Renderizar `TenantClause.print_on_receipt` en todas las vistas

**Files:**
- Modify: `app/Http/Controllers/PosController.php:298-316` (cargar clauses)
- Modify: `resources/views/pos/print/ticket_58mm.blade.php`
- Modify: `resources/views/pos/print/ticket_80mm.blade.php`
- Modify: `resources/views/pos/print/a4.blade.php`

- [ ] **Step 1: Cargar `tenantClauses` con `print_on_receipt = true` en ambos métodos**

```php
$clauses = $tenant->clauses()->where('print_on_receipt', true)->get();
```

Verificar que `Tenant` tiene relación `clauses()`. Si no, crearla:
```php
// app/Models/Tenant.php
public function clauses(): HasMany
{
    return $this->hasMany(TenantClause::class);
}
```

Pasar `$clauses` a la vista:
```php
return view("pos.print.{$format}", compact('sale', 'tenant', 'clauses'));
```

- [ ] **Step 2: Renderizar cláusulas en `ticket_58mm.blade.php`** (antes del footer)

```blade
@if(($clauses ?? null) && $clauses->count() > 0)
    <hr style="border-top:1px dashed #000;margin:8px 0;">
    @foreach($clauses as $clause)
        <p style="font-size:8px;margin:2px 0;">{{ $clause->content }}</p>
    @endforeach
@endif
```

- [ ] **Step 3: Renderizar cláusulas en `ticket_80mm.blade.php`** (mismo estilo, font-size 9px)

- [ ] **Step 4: Renderizar cláusulas en `a4.blade.php`** (estilo Tailwind, texto pequeño al final)

---

### Task 5: Mostrar nombre del cliente en tickets

**Files:**
- Modify: `resources/views/pos/print/ticket_58mm.blade.php`
- Modify: `resources/views/pos/print/ticket_80mm.blade.php`
- Modify: `resources/views/pos/print/a4.blade.php`

- [ ] **Step 1: Cargar `client` solo cuando sea necesario (work_order tiene cliente)**

El `Sale` ya eager-loada `saleItems` y `user`. Cargar `client` condicionalmente:
```php
$sale->load(['saleItems', 'user', 'saleItems.product', 'workOrder.client']);
```

O mejor, acceder al cliente desde `$sale->client` (si existe relación directa) o desde `$sale->workOrder->client`.

Verificar si `Sale` tiene relación `client()`:
```php
// app/Models/Sale.php
public function client(): BelongsTo
{
    return $this->belongsTo(Client::class);
}
```

Si no existe columna `client_id` en `sales`, el cliente se obtiene vía `$sale->workOrder->client`.

- [ ] **Step 2: Agregar cliente en `ticket_58mm.blade.php`** (después del encabezado)

```blade
@php
    $client = $sale->workOrder?->client;
@endphp
@if($client)
    <p style="font-size:9px;margin:2px 0;">Cliente: {{ $client->name }} {{ $client->phone ? '— Tel: '.$client->phone : '' }}</p>
@endif
```

- [ ] **Step 3: Agregar cliente en `ticket_80mm.blade.php`** (mismo estilo)

- [ ] **Step 4: Agregar cliente en `a4.blade.php`** (estilo Tailwind en la sección de encabezado)

---

### Task 6: Agregar `@media print` rules a vistas térmicas

**Files:**
- Modify: `resources/views/pos/print/ticket_58mm.blade.php`
- Modify: `resources/views/pos/print/ticket_80mm.blade.php`

- [ ] **Step 1: Agregar bloque `@media print` al `<style>` de `ticket_58mm.blade.php`**

```css
@media print {
    @page { margin: 0; size: 58mm auto; }
    body { margin: 0; padding: 0; }
    .no-print { display: none !important; }
}
```

- [ ] **Step 2: Agregar bloque similar a `ticket_80mm.blade.php`**

```css
@media print {
    @page { margin: 0; size: 80mm auto; }
    body { margin: 0; padding: 0; }
    .no-print { display: none !important; }
}
```

- [ ] **Step 3: Agregar clase `no-print` al botón de impresión manual** (Task 2, step 2)

```blade
<div class="no-print" style="...">
```

---

### Task 7: Agregar tests

**Files:**
- Create: `tests/Feature/PrintTest.php`

- [ ] **Step 1: Crear archivo `tests/Feature/PrintTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\Client;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\TenantClause;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Tenant $otherTenant;
    private User $user;
    private User $otherUser;
    private Sale $sale;
    private Sale $otherSale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['print_format' => 'ticket_58mm']);
        $this->otherTenant = Tenant::factory()->create();

        Permission::create(['guard_name' => 'web', 'name' => 'pos.access']);
        Permission::create(['guard_name' => 'web', 'name' => 'quotes.approve']);

        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->user->givePermissionTo('pos.access');
        $this->otherUser = User::factory()->create(['tenant_id' => $this->otherTenant->id]);

        $cashRegister = CashRegister::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        $this->sale = Sale::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'cash_register_id' => $cashRegister->id,
            'type' => 'venta_directa',
            'subtotal' => 100,
            'tax_total' => 16,
            'total' => 116,
            'payment_method' => 'efectivo',
            'cash_amount' => 116,
            'change_amount' => 0,
        ]);

        // Sale from another tenant
        $otherCashRegister = CashRegister::create([
            'tenant_id' => $this->otherTenant->id,
            'user_id' => $this->otherUser->id,
            'opening_amount' => 500,
            'opened_at' => now(),
            'status' => 'abierta',
        ]);

        $this->otherSale = Sale::create([
            'tenant_id' => $this->otherTenant->id,
            'user_id' => $this->otherUser->id,
            'cash_register_id' => $otherCashRegister->id,
            'type' => 'venta_directa',
            'subtotal' => 200,
            'tax_total' => 32,
            'total' => 232,
            'payment_method' => 'efectivo',
            'cash_amount' => 232,
            'change_amount' => 0,
        ]);
    }

    public function test_print_route_returns_ok(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pos.print', $this->sale));

        $response->assertOk();
        $response->assertSee('Ticket #' . $this->sale->id);
    }

    public function test_print_preview_returns_ok(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pos.print.preview', $this->sale));

        $response->assertOk();
    }

    public function test_print_denied_for_other_tenant(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('pos.print', $this->sale));

        $response->assertStatus(403);
    }

    public function test_print_preview_denied_for_other_tenant(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('pos.print.preview', $this->sale));

        $response->assertStatus(403);
    }

    public function test_print_renders_correct_format(): void
    {
        $this->tenant->update(['print_format' => 'ticket_80mm']);

        $response = $this->actingAs($this->user)
            ->get(route('pos.print', $this->sale));

        $response->assertOk();
        $response->assertSee('80mm');
    }

    public function test_print_renders_a4_format(): void
    {
        $this->tenant->update(['print_format' => 'a4']);

        $response = $this->actingAs($this->user)
            ->get(route('pos.print', $this->sale));

        $response->assertOk();
    }

    public function test_preview_does_not_auto_print(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pos.print.preview', $this->sale));

        $response->assertOk();
        // Preview should NOT have onload="window.print()"
        $this->assertStringNotContainsString('onload="window.print()"', $response->getContent());
    }

    public function test_print_renders_tenant_clauses(): void
    {
        $clause = TenantClause::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Garantía',
            'content' => 'Equipo garantizado por 30 días.',
            'print_on_receipt' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('pos.print', $this->sale));

        $response->assertOk();
        $response->assertSee('Equipo garantizado por 30 días.');
    }

    public function test_print_does_not_render_inactive_clauses(): void
    {
        TenantClause::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Garantía',
            'content' => 'No debe aparecer.',
            'print_on_receipt' => true,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('pos.print', $this->sale));

        $response->assertOk();
        $response->assertDontSee('No debe aparecer.');
    }

    public function test_print_shows_client_name_when_available(): void
    {
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $workOrder = WorkOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'user_id' => $this->user->id,
        ]);
        $this->sale->update(['work_order_id' => $workOrder->id]);

        $response = $this->actingAs($this->user)
            ->get(route('pos.print', $this->sale));

        $response->assertOk();
        $response->assertSee($client->name);
    }

    public function test_sales_print_redirects_to_pos_print(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('sales.print', $this->sale));

        $response->assertRedirect(route('pos.print', $this->sale));
    }

    public function test_sales_print_denied_for_other_tenant(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('sales.print', $this->sale));

        $response->assertStatus(403);
    }
}
```

- [ ] **Step 2: Ejecutar tests para verificar que fallan (TDD)**

Run: `php artisan test tests/Feature/PrintTest.php --compact`
Expected: Todos fallan o errores (las features no están implementadas aún)

---

### Task 8: Implementar cambios en controlador y vistas

**Files:**
- Modify: `app/Http/Controllers/PosController.php:298-316` (tenant auth, clauses, cliente)
- Modify: `resources/views/pos/print/ticket_58mm.blade.php`
- Modify: `resources/views/pos/print/ticket_80mm.blade.php`
- Modify: `resources/views/pos/print/a4.blade.php`

- [ ] **Step 1: Actualizar métodos `print()` y `printPreview()` en PosController**

```php
use App\Models\TenantClause;

public function print(Sale $sale): View
{
    $this->authorizeTenant($sale);

    $sale->load(['saleItems', 'user', 'workOrder.client']);
    $tenant = $sale->tenant;
    $format = $tenant->print_format ?? 'ticket_80mm';
    $clauses = TenantClause::where('tenant_id', $tenant->id)
        ->where('print_on_receipt', true)
        ->where('is_active', true)
        ->get();

    return view("pos.print.{$format}", compact('sale', 'tenant', 'clauses'));
}

public function printPreview(Sale $sale): View
{
    $this->authorizeTenant($sale);

    $sale->load(['saleItems', 'user', 'workOrder.client']);
    $tenant = $sale->tenant;
    $format = $tenant->print_format ?? 'ticket_80mm';
    $clauses = TenantClause::where('tenant_id', $tenant->id)
        ->where('print_on_receipt', true)
        ->where('is_active', true)
        ->get();

    return view("pos.print.{$format}", compact('sale', 'tenant', 'clauses'))->with('preview', true);
}
```

- [ ] **Step 2: Actualizar `ticket_58mm.blade.php`** (onload condicional + @media print + cliente + cláusulas + botón imprimir)

Leer archivo actual y aplicar cambios combinados (Tasks 2, 4, 5, 6).

- [ ] **Step 3: Actualizar `ticket_80mm.blade.php`** (mismos cambios)

- [ ] **Step 4: Actualizar `a4.blade.php`** (onload condicional + cliente + cláusulas)

- [ ] **Step 5: Ejecutar todos los tests**

Run: `php artisan test tests/Feature/PrintTest.php tests/Feature/QuoteTest.php tests/Feature/PosMixedPaymentTest.php --compact`
Expected: Todos pasando

---

### Task 9: Corregir `window.open` dimensions según formato

**Files:**
- Modify: `resources/views/pos/index.blade.php` (método `printTicket()`)

- [ ] **Step 1: Hacer las dimensiones del popup dependientes del formato**

```javascript
printTicket() {
    if (this.previewSaleId) {
        const width = window.innerWidth < 500 ? 400 : 500;
        const height = window.innerHeight < 700 ? 600 : 700;
        window.open('/pos/print/' + this.previewSaleId, '_blank', 'width=' + width + ',height=' + height);
    }
}
```

En lugar de hardcodear `width=400,height=600`, usar dimensiones relativas a la ventana actual.

---

### Task 10: Commit final

- [ ] **Step 1: Commit**

```bash
git add -A
git commit -m "fix: revisión y mejora de tickets/impresión

- Seguridad: tenant authorization en pos.print y pos.print.preview
- Preview: suprime auto-print, muestra botón manual
- Cláusulas: renderiza TenantClause.print_on_receipt en tickets
- Cliente: nombre del cliente visible en todos los formatos
- @media print: reglas para tickets térmicos 58mm y 80mm
- Default format: alineado ticket_80mm entre migración y controlador
- Tests: cobertura completa de print (autorización, formatos, cláusulas, preview, cliente)"
```
