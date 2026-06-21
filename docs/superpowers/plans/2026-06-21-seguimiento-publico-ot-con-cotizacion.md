# Seguimiento Público de OT con Cotización

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Permitir que el cliente acceda al estado de su orden de trabajo mediante un enlace protegido por token, vea el timeline completo con notas, y si existe cotización pueda ver los items, aprobarla o rechazarla desde la misma página.

**Architecture:** Se extiende el controlador `TrackingController` existente con dos métodos nuevos para aprobar/rechazar cotización vía el token público. Se agregan rutas POST. Se modifica la vista `tracking.show` para incluir una sección de cotización con items, totales y botones de acción condicionales según el estado. No se modifica la lógica del modelo `Quote` porque usa `auth()->user()` — los nuevos métodos del controlador manejan el timeline con "Cliente" como usuario.

**Tech Stack:** Laravel 13, Blade, Alpine.js, Tailwind CSS v3

---

## Files to create/modify

### Modify
| File | Change |
|------|--------|
| `app/Http/Controllers/TrackingController.php` | Agregar `approveQuote()` y `rejectQuote()` |
| `resources/views/tracking/show.blade.php` | Agregar sección de cotización con items y botones |
| `routes/web.php` | Agregar 2 rutas POST para tracking |

---

### Task 1: Agregar rutas POST para aprobar/rechazar cotización desde tracking

**File:** `routes/web.php`

- [ ] **Step 1: Agregar rutas después de la ruta tracking.show existente**

Buscar la línea `Route::get('/seguimiento/{token}', ...)` y agregar las dos rutas POST debajo:

```php
Route::get('/seguimiento/{token}', [TrackingController::class, 'show'])->name('tracking.show');
Route::post('/seguimiento/{token}/approve-quote', [TrackingController::class, 'approveQuote'])->name('tracking.approve-quote');
Route::post('/seguimiento/{token}/reject-quote', [TrackingController::class, 'rejectQuote'])->name('tracking.reject-quote');
```

- [ ] **Step 2: Verificar que las rutas se registran**

Run: `php artisan route:list --name=tracking --path=seguimiento`
Expected: 3 rutas listadas (show, approve-quote, reject-quote)

---

### Task 2: Agregar métodos approveQuote y rejectQuote al TrackingController

**File:** `app/Http/Controllers/TrackingController.php`

- [ ] **Step 1: Reescribir el controlador con los nuevos métodos**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TrackingController extends Controller
{
    public function show(string $token): View
    {
        $workOrder = WorkOrder::where('tracking_token', $token)
            ->with(['client', 'tenant', 'quote.quoteItems.product'])
            ->firstOrFail();

        return view('tracking.show', compact('workOrder'));
    }

    public function approveQuote(string $token): RedirectResponse|JsonResponse
    {
        $workOrder = WorkOrder::where('tracking_token', $token)
            ->with(['quote.quoteItems.product'])
            ->firstOrFail();

        $quote = $workOrder->quote;

        if (!$quote || $quote->status !== 'enviada') {
            return request()->wantsJson()
                ? response()->json(['message' => 'La cotización no está disponible para aprobación.'], 422)
                : back()->with('error', 'La cotización no está disponible para aprobación.');
        }

        try {
            DB::transaction(function () use ($quote, $workOrder) {
                $quote->refresh();

                foreach ($quote->quoteItems as $item) {
                    if ($item->product_id && $item->product) {
                        if ($item->product->availableStock() < $item->quantity) {
                            throw new \RuntimeException(
                                "Stock insuficiente para {$item->product->name}. Disponible: {$item->product->availableStock()}, requerido: {$item->quantity}."
                            );
                        }
                    }
                }

                $quote->reserveStock();
                $quote->update(['status' => 'aprobada']);
                $workOrder->update(['status' => 'cotizacion_aprobada']);
                $workOrder->addTimelineEvent('cotizacion_aprobada', 'Cliente', 'Cotización aprobada por el cliente — stock reservado');
            });

            return request()->wantsJson()
                ? response()->json(['message' => 'Cotización aprobada correctamente.', 'redirect' => route('tracking.show', $token)])
                : redirect()->route('tracking.show', $token)->with('success', 'Cotización aprobada correctamente.');
        } catch (\RuntimeException $e) {
            return request()->wantsJson()
                ? response()->json(['message' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage());
        }
    }

    public function rejectQuote(Request $request, string $token): RedirectResponse|JsonResponse
    {
        $workOrder = WorkOrder::where('tracking_token', $token)
            ->with(['quote'])
            ->firstOrFail();

        $quote = $workOrder->quote;

        if (!$quote || $quote->status !== 'enviada') {
            return request()->wantsJson()
                ? response()->json(['message' => 'La cotización no está disponible para rechazo.'], 422)
                : back()->with('error', 'La cotización no está disponible para rechazo.');
        }

        $reason = $request->input('reason', '');

        DB::transaction(function () use ($quote, $workOrder, $reason) {
            $quote->update(['status' => 'rechazada', 'cancellation_reason' => $reason]);
            $workOrder->addTimelineEvent('cotizacion_enviada', 'Cliente', 'Cotización rechazada por el cliente. ' . ($reason ? "Motivo: {$reason}" : ''));
        });

        return request()->wantsJson()
            ? response()->json(['message' => 'Cotización rechazada.', 'redirect' => route('tracking.show', $token)])
            : redirect()->route('tracking.show', $token)->with('info', 'Cotización rechazada.');
    }
}
```

---

### Task 3: Mejorar la vista tracking.show con sección de cotización

**File:** `resources/views/tracking/show.blade.php`

- [ ] **Step 1: Cargar la relación quote en el controlador (ya hecho en Task 2)**

El método `show()` ya carga `quote.quoteItems.product`.

- [ ] **Step 2: Agregar sección de cotización después del timeline**

Insertar después del cierre del div `</div>` del timeline (línea ~95) y antes del bloque de cancelación:

```blade
                    @if($workOrder->quote && in_array($workOrder->quote->status, ['enviada', 'aprobada', 'rechazada']))
                    @php $quote = $workOrder->quote; @endphp
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Cotización</h3>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-2 pr-2 font-medium text-gray-500">Descripción</th>
                                        <th class="text-center py-2 px-2 font-medium text-gray-500">Tipo</th>
                                        <th class="text-center py-2 px-2 font-medium text-gray-500">Cant.</th>
                                        <th class="text-right py-2 px-2 font-medium text-gray-500">P. Unit.</th>
                                        <th class="text-right py-2 pl-2 font-medium text-gray-500">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($quote->quoteItems as $item)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-2 pr-2 text-gray-900">{{ $item->description }}</td>
                                        <td class="py-2 px-2 text-center">
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium
                                                @if($item->type === 'producto') bg-blue-100 text-blue-700
                                                @else bg-gray-100 text-gray-700 @endif">
                                                {{ $item->type === 'producto' ? 'Producto' : 'Servicio' }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-2 text-center text-gray-900">{{ $item->quantity }}</td>
                                        <td class="py-2 px-2 text-right text-gray-900">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="py-2 pl-2 text-right text-gray-900">${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-gray-400">Sin items</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 space-y-1 text-sm text-right">
                            <p class="text-gray-500">Subtotal: <span class="font-medium text-gray-900">${{ number_format($quote->subtotal, 2) }}</span></p>
                            @if($quote->tax_total > 0)
                            <p class="text-gray-500">Impuestos: <span class="font-medium text-gray-900">${{ number_format($quote->tax_total, 2) }}</span></p>
                            @endif
                            <p class="text-lg font-bold text-gray-900">Total: ${{ number_format($quote->total, 2) }}</p>
                        </div>

                        @if($quote->status === 'enviada')
                        <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-end">
                            <form method="POST" action="{{ route('tracking.reject-quote', $workOrder->tracking_token) }}"
                                  x-data="{ open: false, reason: '' }"
                                  @submit.prevent="if(open) $el.submit(); else open = true">
                                @csrf
                                <div x-show="open" x-cloak class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo del rechazo (opcional)</label>
                                    <textarea x-model="reason" name="reason" rows="2"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-red-500 sm:text-sm sm:leading-6"
                                        placeholder="Ej: Presupuesto muy elevado"></textarea>
                                </div>
                                <div class="flex gap-3 justify-end">
                                    <button type="submit"
                                        class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                                        <span x-show="!open">Rechazar Cotización</span>
                                        <span x-show="open">Confirmar Rechazo</span>
                                    </button>
                                    <button type="button" @click="open = false" x-show="open"
                                        class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('tracking.approve-quote', $workOrder->tracking_token) }}"
                                  onsubmit="return confirm('¿Estás seguro de aprobar esta cotización? Al hacerlo, se reservará el stock necesario.')">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center rounded-md bg-green-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                                    Aprobar Cotización
                                </button>
                            </form>
                        </div>
                        @elseif($quote->status === 'aprobada')
                        <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                            <p class="text-sm font-medium text-green-800">✓ Cotización aprobada</p>
                            <p class="text-xs text-green-600 mt-1">El taller ya tiene tu aprobación y está procesando la reparación.</p>
                        </div>
                        @elseif($quote->status === 'rechazada')
                        <div class="mt-4 p-3 bg-red-50 rounded-lg border border-red-200">
                            <p class="text-sm font-medium text-red-800">✗ Cotización rechazada</p>
                            @if($quote->cancellation_reason)
                            <p class="text-xs text-red-600 mt-1">Motivo: {{ $quote->cancellation_reason }}</p>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
```

- [ ] **Step 3: Agregar Alpine.js al layout para el rechazo**

Verificar que la página ya carga Alpine.js (lo hace vía `@vite(['resources/css/app.css', 'resources/js/app.js'])` donde `app.js` importa Alpine). El `x-data` en el formulario de rechazo es autónomo, no necesita componente padre.

- [ ] **Step 4: Verificar que el `@vite` carga Alpine correctamente**

```bash
grep -n "x-data\|x-show\|x-cloak\|x-model\|@submit" resources/views/tracking/show.blade.php
```
Expected: Las directivas Alpine existen (x-data, x-show, x-cloak, x-model, @submit.prevent)

---

### Task 4: E2E test — aprobar cotización desde tracking

**File:** `e2e/work-order-flow.spec.ts`

- [ ] **Step 1: Agregar escenario de aprobación al test existente**

Insertar después de la verificación del log de correo y antes del cierre del test:

```typescript
    // ── 7. Cliente aprueba cotización desde tracking ──
    // Obtener el token de tracking del work order
    await page.goto('/work_orders');
    // Hacer clic en la primera OT de la lista para ver el detalle
    await page.locator('a').filter({ hasText: /OT-/i }).first().click();
    await page.waitForURL(/\/work_orders\/\d+/, { timeout: 5000 });

    // Extraer el tracking token de la página
    const trackingUrl = await page.evaluate(() => {
      const link = document.querySelector('a[href*="/seguimiento/"]');
      return link ? link.getAttribute('href') : null;
    });

    if (trackingUrl) {
      // Navegar al tracking público
      await page.goto(trackingUrl);
      await expect(page.getByText(/Seguimiento de Orden/i)).toBeVisible();

      // Aprobar cotización
      await page.getByRole('button', { name: /Aprobar Cotización/i }).click();

      // Confirmar el diálogo
      page.once('dialog', dialog => dialog.accept());

      // Verificar que se muestra como aprobada
      await expect(page.getByText(/Cotización aprobada/i)).toBeVisible();
    }
```

---

### Task 5: Verificar tests existentes

- [ ] **Step 1: Ejecutar E2E tests**

```bash
: > storage/logs/laravel.log && curl -s http://localhost:8080/__e2e/cleanup > /dev/null && npx playwright test e2e/ --headed
```
Expected: 12 passed (los 10 de registro + 1 POS + 1 WorkOrder)

- [ ] **Step 2: Ejecutar PHPUnit tests para asegurar que no se rompió nada**

```bash
php artisan test --env=e2e --filter="WorkOrder|Quote|Tracking"
```
Expected: Los tests preexistentes pasan (los que fallan por 419/tabla vacía son preexistentes, no por estos cambios)
