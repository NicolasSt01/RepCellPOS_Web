# Integración Stripe para Pagos de Suscripción

> **For agentic workers:** Subagent-driven or inline execution.

**Goal:** Reemplazar el sistema actual de pago por transferencia bancaria + subida de comprobante con Stripe Checkout Sessions para pagos recurrentes de suscripciones de tenants.

**Arquitectura:** Se integra Stripe Subscriptions usando Checkout Sessions. Cada tenant tiene un `stripe_customer_id`. Al seleccionar un plan se crea un Checkout Session que redirige a Stripe. Los webhooks (`checkout.session.completed`, `invoice.payment_succeeded`, `customer.subscription.updated`, `customer.subscription.deleted`) actualizan el estado local.

**Tech Stack:** Laravel 13, PHP 8.3+, MySQL, `stripe/stripe-php` ^16, Stripe Checkout Sessions + Subscriptions + Webhooks.

---

### Task 1: Instalar dependencias y configurar credenciales

**Files:**
- Modify: `composer.json`
- Modify: `.env.example`
- Modify: `config/services.php`

- [ ] **Step 1: Instalar stripe-php**

Run:
```bash
composer require stripe/stripe-php
```

- [ ] **Step 2: Agregar variables de entorno a `.env.example`**

Append al final:
```
STRIPE_KEY=pk_test_xxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxx
STRIPE_PRICE_BASICO=price_xxx
STRIPE_PRICE_PRO=price_xxx
STRIPE_PRICE_PREMIUM=price_xxx
```

- [ ] **Step 3: Agregar configuración a `config/services.php`**

Agregar al array:
```php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'prices' => [
        'basico' => env('STRIPE_PRICE_BASICO'),
        'pro' => env('STRIPE_PRICE_PRO'),
        'premium' => env('STRIPE_PRICE_PREMIUM'),
    ],
],
```

---

### Task 2: Migración — agregar campos Stripe a tenants y subscriptions

**Files:**
- Create: `database/migrations/2026_06_29_000001_add_stripe_fields.php`

- [ ] **Step 1: Crear migración**

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
            $table->string('stripe_customer_id')->nullable()->unique()->after('subscription_status');
        });

        Schema::table('tenant_subscriptions', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->unique()->after('payment_proof');
            $table->string('stripe_price_id')->nullable()->after('stripe_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('stripe_customer_id');
        });
        Schema::table('tenant_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['stripe_subscription_id', 'stripe_price_id']);
        });
    }
};
```

- [ ] **Step 2: Correr migración**

Run:
```bash
php artisan migrate
```

---

### Task 3: Crear StripeService

**Files:**
- Create: `app/Services/StripeService.php`

- [ ] **Step 1: Crear el servicio**

```php
<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\Webhook;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        Stripe::setApiVersion('2025-02-24');
    }

    public function createCustomer(Tenant $tenant): string
    {
        if ($tenant->stripe_customer_id) {
            return $tenant->stripe_customer_id;
        }

        $customer = Customer::create([
            'name' => $tenant->name,
            'email' => $tenant->email,
            'metadata' => [
                'tenant_id' => $tenant->id,
                'tenant_slug' => $tenant->slug,
            ],
        ]);

        $tenant->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    public function createCheckoutSession(Tenant $tenant, Plan $plan, TenantSubscription $subscription): Session
    {
        $customerId = $this->createCustomer($tenant);

        $priceId = config("services.stripe.prices.{$plan->slug}");

        if (!$priceId) {
            throw new \RuntimeException("No Stripe Price ID configured for plan: {$plan->slug}");
        }

        $session = Session::create([
            'customer' => $customerId,
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'metadata' => [
                'subscription_id' => $subscription->id,
                'tenant_id' => $tenant->id,
                'plan_slug' => $plan->slug,
            ],
            'success_url' => route('subscription.upgrade') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('subscription.upgrade'),
            'locale' => 'es',
        ]);

        return $session;
    }

    public function handleWebhook(string $payload, string $sigHeader): void
    {
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook: invalid payload', ['error' => $e->getMessage()]);
            throw $e;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe webhook: invalid signature', ['error' => $e->getMessage()]);
            throw $e;
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'invoice.payment_succeeded' => $this->handleInvoicePaid($event->data->object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
            default => Log::info('Stripe webhook: unhandled event', ['type' => $event->type]),
        };
    }

    protected function handleCheckoutCompleted(mixed $session): void
    {
        $subscriptionId = $session->metadata->subscription_id ?? null;
        if (!$subscriptionId) return;

        $subscription = TenantSubscription::find($subscriptionId);
        if (!$subscription) return;

        $stripeSubscription = Subscription::retrieve($session->subscription);

        $subscription->update([
            'stripe_subscription_id' => $session->subscription,
            'stripe_price_id' => $session->mode === 'subscription'
                ? ($stripeSubscription->items->data[0]->price->id ?? null)
                : null,
            'status' => 'activa',
            'paid_via' => 'stripe',
            'payment_proof' => null,
            'last_payment_date' => now()->toDateString(),
            'next_payment_date' => $stripeSubscription->current_period_end
                ? now()->createFromTimestamp($stripeSubscription->current_period_end)->toDateString()
                : null,
        ]);

        $tenant = $subscription->tenant;
        if ($tenant) {
            $tenant->update([
                'plan_id' => $subscription->plan_id,
                'subscription_status' => 'active',
                'trial_ends_at' => null,
            ]);
        }
    }

    protected function handleInvoicePaid(mixed $invoice): void
    {
        $subscriptionId = $invoice->subscription;
        if (!$subscriptionId) return;

        $subscription = TenantSubscription::where('stripe_subscription_id', $subscriptionId)->first();
        if (!$subscription) return;

        $history = $subscription->payment_history ?? [];
        $history[] = [
            'date' => now()->toDateString(),
            'amount' => $invoice->amount_paid / 100,
            'reference' => $invoice->id,
            'stripe_invoice' => $invoice->id,
        ];

        $subscription->update([
            'last_payment_date' => now()->toDateString(),
            'next_payment_date' => $invoice->lines->data[0]->period->end ?? null
                ? now()->createFromTimestamp($invoice->lines->data[0]->period->end)->toDateString()
                : null,
            'status' => 'activa',
            'payment_history' => $history,
        ]);

        $tenant = $subscription->tenant;
        if ($tenant) {
            $tenant->update([
                'subscription_status' => 'active',
            ]);
        }
    }

    protected function handleSubscriptionUpdated(mixed $stripeSubscription): void
    {
        $subscription = TenantSubscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        if (!$subscription) return;

        if ($stripeSubscription->status === 'past_due' || $stripeSubscription->status === 'unpaid') {
            $subscription->update(['status' => 'pendiente']);
        } elseif ($stripeSubscription->status === 'active') {
            $subscription->update(['status' => 'activa']);
        } elseif ($stripeSubscription->status === 'canceled' || $stripeSubscription->status === 'incomplete_expired') {
            $subscription->update(['status' => 'cancelada']);
            $tenant = $subscription->tenant;
            if ($tenant) {
                $tenant->update(['subscription_status' => 'expired']);
            }
        }
    }

    protected function handleSubscriptionDeleted(mixed $stripeSubscription): void
    {
        $subscription = TenantSubscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        if (!$subscription) return;

        $subscription->update(['status' => 'cancelada']);

        $tenant = $subscription->tenant;
        if ($tenant) {
            $tenant->update(['subscription_status' => 'expired']);
        }
    }

    public function cancelSubscription(string $stripeSubscriptionId): void
    {
        Subscription::update($stripeSubscriptionId, [
            'cancel_at_period_end' => true,
        ]);
    }
}
```

---

### Task 4: Crear WebhookController

**Files:**
- Create: `app/Http/Controllers/StripeWebhookController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Crear el controller**

```php
<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(
        private StripeService $stripeService
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        if (!$sigHeader) {
            Log::warning('Stripe webhook: missing signature header');
            return response('Missing signature', 400);
        }

        try {
            $this->stripeService->handleWebhook($payload, $sigHeader);
            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error', ['error' => $e->getMessage()]);
            return response('Webhook error', 400);
        }
    }
}
```

- [ ] **Step 2: Agregar ruta del webhook (exenta de CSRF)**

En `bootstrap/app.php`, agregar el webhook a la excepción de CSRF:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'stripe/webhook',
    ]);
})
```

En `routes/web.php`, agregar:

```php
Route::post('/stripe/webhook', [App\Http\Controllers\StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
```

---

### Task 5: Actualizar SubscriptionController

**Files:**
- Modify: `app/Http/Controllers/SubscriptionController.php`

- [ ] **Step 1: Modificar `selectPlan` para crear Checkout Session**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\R2StorageService;

class SubscriptionController extends Controller
{
    public function __construct(
        private StripeService $stripeService
    ) {}

    // suspended() and upgrade() remain unchanged

    public function selectPlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $tenant = Auth::user()->tenant;
        $plan = Plan::findOrFail($validated['plan_id']);

        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'plan_type' => $plan->slug,
            'amount' => $plan->price,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'pendiente',
        ]);

        try {
            $session = $this->stripeService->createCheckoutSession($tenant, $plan, $subscription);

            $subscription->update([
                'stripe_price_id' => $session->mode === 'subscription'
                    ? ($session->line_items->data[0]->price->id ?? null)
                    : null,
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            $subscription->delete();
            return redirect()->route('subscription.upgrade')
                ->with('error', 'Error al procesar el pago. Intenta de nuevo.');
        }
    }

    // uploadProof() can be kept for backward compatibility or removed
}
```

---

### Task 6: Agregar button de Stripe Customer Portal

**Files:**
- Modify: `app/Http/Controllers/SubscriptionController.php`

- [ ] **Step 1: Agregar método para Customer Portal**

Agregar a `SubscriptionController`:

```php
use Stripe\BillingPortal\Session as PortalSession;

public function portal()
{
    $tenant = Auth::user()->tenant;

    if (!$tenant->stripe_customer_id) {
        return redirect()->route('subscription.upgrade')
            ->with('error', 'No tienes una suscripción activa.');
    }

    $session = PortalSession::create([
        'customer' => $tenant->stripe_customer_id,
        'return_url' => route('subscription.upgrade'),
    ]);

    return redirect($session->url);
}
```

- [ ] **Step 2: Agregar ruta**

```php
Route::post('/upgrade/portal', [SubscriptionController::class, 'portal'])->name('subscription.portal');
```

---

### Task 7: Actualizar vista upgrade.blade.php

**Files:**
- Modify: `resources/views/subscription/upgrade.blade.php`

- [ ] **Step 1: Reemplazar sección de pago pendiente con estado Stripe**

Reemplazar el bloque `@if($pendingPayment)` con:

```blade
@if($pendingPayment)
    <div class="mb-6 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-6">
        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-200 mb-2">
            @if($pendingPayment->paid_via === 'stripe')
                Suscripción vía Stripe
            @else
                Pago pendiente
            @endif
        </h3>
        @if($pendingPayment->paid_via === 'stripe')
            <p class="text-sm text-blue-700 dark:text-blue-300">
                Tienes el plan <strong>{{ $pendingPayment->plan->name ?? $pendingPayment->plan_type }}</strong>
                por <strong>${{ number_format($pendingPayment->amount, 2) }} MXN</strong>.
                Tu suscripción está siendo procesada por Stripe.
            </p>
            <div class="mt-4">
                <a href="{{ route('subscription.portal') }}"
                   class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    Gestionar suscripción
                </a>
            </div>
        @else
            {{-- Existing bank transfer info --}}
            ...
        @endif
    </div>
@endif
```

- [ ] **Step 2: Agregar botón "Gestionar suscripción" para suscriptores activos**

Después del loop de planes, agregar:

```blade
@if($tenant->subscription_status === 'active' && $tenant->stripe_customer_id)
    <div class="mt-8 text-center">
        <p class="text-sm text-gray-500 mb-3">¿Necesitas cambiar de plan, actualizar método de pago o cancelar?</p>
        <a href="{{ route('subscription.portal') }}"
           class="inline-flex items-center rounded-md bg-gray-800 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 transition-colors">
            Gestionar suscripción en Stripe
        </a>
    </div>
@endif
```

---

### Task 8: Agregar Stripe Customer al registrarse un Tenant

**Files:**
- Modify: `app/Http/Controllers/TenantController.php`

- [ ] **Step 1: Crear Stripe Customer durante el registro**

En `TenantController@register`, después de crear el tenant, agregar:

```php
use App\Services\StripeService;

public function register(Request $request, StripeService $stripe)
{
    // ... existing validation and tenant creation ...

    // Create Stripe customer
    try {
        $stripe->createCustomer($tenant);
    } catch (\Exception $e) {
        Log::warning('Failed to create Stripe customer during registration', [
            'tenant_id' => $tenant->id,
            'error' => $e->getMessage(),
        ]);
    }

    // ... rest of registration flow ...
}
```

---

### Task 9: Verificación con Playwright

**Files:**
- Create: `e2e/stripe-integration.spec.ts`

- [ ] **Step 1: Escribir tests**

```typescript
import { test, expect } from '@playwright/test';

test.describe('Stripe Integration', () => {
  test('upgrade page shows plan selection', async ({ page }) => {
    // ... navigate to /upgrade, verify plans displayed ...
  });

  test('plan selection redirects to Stripe', async ({ page }) => {
    // ... mock or intercept Stripe redirect ...
  });
});
```

---

### Resumen de archivos

| Acción | Archivo |
|---|---|
| Install | `composer.json` (stripe/stripe-php) |
| Modify | `.env` / `.env.example` (STRIPE_*) |
| Modify | `config/services.php` (stripe config) |
| Create | `database/migrations/*_add_stripe_fields.php` |
| Create | `app/Services/StripeService.php` |
| Create | `app/Http/Controllers/StripeWebhookController.php` |
| Modify | `bootstrap/app.php` (CSRF exception) |
| Modify | `routes/web.php` (webhook + portal routes) |
| Modify | `app/Http/Controllers/SubscriptionController.php` |
| Modify | `resources/views/subscription/upgrade.blade.php` |
| Modify | `app/Http/Controllers/TenantController.php` |
