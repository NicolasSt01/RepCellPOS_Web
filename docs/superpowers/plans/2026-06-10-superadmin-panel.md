# MOD-12: Superadmin Panel Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use inline execution with verification after each task.

**Goal:** Build a complete Superadmin panel for platform-wide tenant management, subscription control, analytics, and activity monitoring — with full tests and documentation.

**Architecture:** All routes under `/admin/*` prefix protected by a new `CheckSuperAdmin` middleware. Sidebar nav extended with superadmin-only links. New `TenantSubscription` model/migration. Controllers use single `SuperAdminController` with named methods. Views follow existing Alpine.js + Tailwind patterns from Settings panel. Superadmins are blocked from tenant routes (POS, OTs, etc.) via the same middleware.

**Tech Stack:** Alpine.js v3, Tailwind CSS v4, Laravel 13, Spatie Permission, SQLite (testing), MySQL (prod)

---

### Task 1: Middleware & Routes Foundation

**Files:**
- Create: `app/Http/Middleware/CheckSuperAdmin.php`
- Modify: `bootstrap/app.php` (register middleware)
- Modify: `routes/web.php` (add admin route group)
- Create: `tests/Feature/SuperAdminMiddlewareTest.php`

- [ ] **Step 1: Create CheckSuperAdmin middleware**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            abort(403, 'Acceso denegado. Solo Superadmin.');
        }

        return $next($request);
    }
}
```

- [ ] **Step 2: Register middleware alias in bootstrap/app.php**

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        \App\Http\Middleware\SetTenantMiddleware::class,
    ]);

    $middleware->alias([
        'superadmin' => \App\Http\Middleware\CheckSuperAdmin::class,
    ]);
})
```

- [ ] **Step 3: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'is_superadmin' => true,
            'tenant_id' => null,
        ]);

        $tenant = Tenant::factory()->create();
        $this->regularUser = User::factory()->create([
            'is_superadmin' => false,
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_superadmin_can_access_admin_routes(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_regular_user_cannot_access_admin_routes(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertForbidden();
    }
}
```

Run: `php artisan test --filter="SuperAdminMiddlewareTest"` — Expected: FAIL (route not defined)

- [ ] **Step 4: Create placeholder route + controller method**

Add to `routes/web.php`:

```php
use App\Http\Controllers\SuperAdminController;

Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');
});
```

Create `app/Http/Controllers/SuperAdminController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        return view('superadmin.dashboard');
    }
}
```

Create `resources/views/superadmin/dashboard.blade.php`:

```html
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Panel Superadmin</h1>
    <p class="text-gray-500 dark:text-gray-400">Dashboard de métricas globales.</p>
</div>
@endsection
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --filter="SuperAdminMiddlewareTest"` — Expected: PASS

- [ ] **Step 6: Extend sidebar with superadmin nav**

Modify `resources/views/layouts/partials/sidebar-content.blade.php` — add section before Configuracion:

```html
@if(auth()->user()->isSuperAdmin())
    <div class="px-3 mb-2">
        <h3 class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Superadmin
        </h3>
    </div>
    <nav class="space-y-1 px-2 mb-4">
        <a href="{{ route('admin.dashboard') }}"
           class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.*') ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
            <svg class="mr-3 h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25Zm9.75-9.75A2.25 2.25 0 0 1 15.75 3.75H18a2.25 2.25 0 0 1 2.25 2.25v2.25a2.25 2.25 0 0 1-2.25 2.25h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Zm5.25 10.5A2.25 2.25 0 0 0 18 13.5h-2.25a2.25 2.25 0 0 0-2.25 2.25V18a2.25 2.25 0 0 0 2.25 2.25H18a2.25 2.25 0 0 0 2.25-2.25v-2.25Z" />
            </svg>
            Panel Superadmin
        </a>
    </nav>
@endif
```

- [ ] **Step 7: Run full test suite to verify no regressions**

Run: `php artisan test --compact` — Expected: 91/91 passed (1 new test + 90 existing)

- [ ] **Step 8: Commit**

```bash
git add -A
git commit -m "feat: add superadmin middleware, routes, sidebar nav, and tests"
```

---

### Task 2: Create superadmin.* Permissions & DatabaseSeeder Update

**Files:**
- Create: `database/seeders/SuperAdminPermissionSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Create the permission seeder**

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SuperAdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'superadmin.view_tenants',
            'superadmin.manage_tenants',
            'superadmin.view_analytics',
            'superadmin.manage_subscriptions',
            'superadmin.view_logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['guard_name' => 'web', 'name' => $permission]);
        }

        $this->command->info('Superadmin permissions seeded successfully.');
    }
}
```

- [ ] **Step 2: Update DatabaseSeeder**

```php
$this->call([
    SuperAdminSeeder::class,
    SuperAdminPermissionSeeder::class,
]);
```

- [ ] **Step 3: Write test to verify permissions exist**

Create `tests/Feature/SuperAdminPermissionTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SuperAdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_permissions_are_created(): void
    {
        $this->seed(\Database\Seeders\SuperAdminPermissionSeeder::class);

        $this->assertDatabaseHas('permissions', ['name' => 'superadmin.view_tenants']);
        $this->assertDatabaseHas('permissions', ['name' => 'superadmin.manage_tenants']);
        $this->assertDatabaseHas('permissions', ['name' => 'superadmin.view_analytics']);
        $this->assertDatabaseHas('permissions', ['name' => 'superadmin.manage_subscriptions']);
        $this->assertDatabaseHas('permissions', ['name' => 'superadmin.view_logs']);
    }
}
```

Run: `php artisan test --filter="SuperAdminPermissionTest"` — Expected: PASS

- [ ] **Step 4: Run full suite**

Run: `php artisan test --compact` — Expected: 93/93 passed

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: add superadmin permissions seeder and tests"
```

---

### Task 3: Dashboard with Global Metrics (INC-MOD12-001)

**Files:**
- Modify: `app/Http/Controllers/SuperAdminController.php`
- Modify: `resources/views/superadmin/dashboard.blade.php`
- Create: `tests/Feature/SuperAdminDashboardTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'is_superadmin' => true,
            'tenant_id' => null,
        ]);
    }

    public function test_dashboard_shows_global_metrics(): void
    {
        // Create test data across multiple tenants
        $tenant1 = Tenant::factory()->create(['name' => 'Taller A', 'is_active' => true]);
        $tenant2 = Tenant::factory()->create(['name' => 'Taller B', 'is_active' => true]);
        $tenant3 = Tenant::factory()->create(['name' => 'Taller C', 'is_active' => false]);

        User::factory()->count(3)->create(['tenant_id' => $tenant1->id]);
        User::factory()->count(2)->create(['tenant_id' => $tenant2->id]);

        Client::factory()->count(5)->create(['tenant_id' => $tenant1->id]);
        WorkOrder::factory()->count(4)->create(['tenant_id' => $tenant1->id]);

        Product::factory()->count(10)->create(['tenant_id' => $tenant1->id]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));

        $response->assertOk();

        // Should show total tenants
        $response->assertSee('3');
        // Should show active tenants
        $response->assertSee('2');
        // Should show total users (3+2=5 plus the factory created one superadmin)
        $response->assertSee('5');
        // Should show total clients
        $response->assertSee('5');
        // Should show total work orders
        $response->assertSee('4');
        // Should show tenant names
        $response->assertSee('Taller A');
        $response->assertSee('Taller B');
        $response->assertSee('Taller C');
    }

    public function test_dashboard_shows_recent_tenants(): void
    {
        Tenant::factory()->create(['name' => 'Taller Más Reciente', 'created_at' => now()]);
        Tenant::factory()->create(['name' => 'Taller Antiguo', 'created_at' => now()->subDays(10)]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Taller Más Reciente');
        $response->assertSee('Taller Antiguo');
    }

    public function test_dashboard_is_inaccessible_to_non_superadmin(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertForbidden();
    }
}
```

Run: `php artisan test --filter="SuperAdminDashboardTest"` — Expected: FAIL

- [ ] **Step 2: Implement dashboard controller logic**

Update `SuperAdminController@dashboard`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->count();
        $totalUsers = User::where('is_superadmin', false)->count();
        $totalClients = Client::count();
        $totalProducts = Product::count();
        $totalWorkOrders = WorkOrder::count();
        $totalSales = Sale::count();

        $tenants = Tenant::withCount('users')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $recentTenants = Tenant::orderBy('created_at', 'desc')->take(5)->get();

        return view('superadmin.dashboard', compact(
            'totalTenants', 'activeTenants', 'totalUsers',
            'totalClients', 'totalProducts', 'totalWorkOrders',
            'totalSales', 'tenants', 'recentTenants'
        ));
    }
}
```

Note: The `Tenant` model needs a `users_count` relationship. Let me check if it has one ... it has `users()` HasMany, so `withCount('users')` will work.

- [ ] **Step 3: Build dashboard view**

Update `resources/views/superadmin/dashboard.blade.php`:

```html
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Panel Superadmin</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Métricas globales del ecosistema RepCellPOS</p>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-md text-indigo-600 dark:text-indigo-400">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Total Tenants</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalTenants }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-green-50 dark:bg-green-900/30 rounded-md text-green-600 dark:text-green-400">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Tenants Activos</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $activeTenants }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-md text-blue-600 dark:text-blue-400">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Usuarios</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalUsers }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-amber-50 dark:bg-amber-900/30 rounded-md text-amber-600 dark:text-amber-400">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.164-.38 3.194m-.216 1.17 2.25 2.25" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Órdenes de Trabajo</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalWorkOrders }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tenants Registrados</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuarios</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $tenant->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $tenant->slug }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $tenant->users_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($tenant->is_active)
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $tenant->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No hay tenants registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter="SuperAdminDashboardTest"` — Expected: PASS

- [ ] **Step 5: Run full suite**

Run: `php artisan test --compact` — Expected: 96/96 passed

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "feat: superadmin dashboard with global metrics and tenant list"
```

---

### Task 4: Tenant Subscription Model & Migration (INC-MOD12-006)

**Files:**
- Create: `database/migrations/xxxx_xx_xx_xxxxxx_create_tenant_subscriptions_table.php`
- Create: `app/Models/TenantSubscription.php`
- Create: `tests/Feature/TenantSubscriptionTest.php`

- [ ] **Step 1: Generate migration and write the failing test**

```bash
php artisan make:migration create_tenant_subscriptions_table
```

Update the migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('plan_type'); // 'mensual', 'anual', 'prueba', 'personalizado'
            $table->decimal('amount', 10, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('activa'); // 'activa', 'cancelada', 'expirada', 'pendiente'
            $table->date('last_payment_date')->nullable();
            $table->date('next_payment_date')->nullable();
            $table->json('payment_history')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_subscriptions');
    }
};
```

- [ ] **Step 2: Create TenantSubscription model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_type',
        'amount',
        'start_date',
        'end_date',
        'status',
        'last_payment_date',
        'next_payment_date',
        'payment_history',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'last_payment_date' => 'date',
            'next_payment_date' => 'date',
            'payment_history' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'activa';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expirada' || ($this->end_date && $this->end_date->isPast() && $this->status !== 'cancelada');
    }

    public function markAsPaid(float $amount, string $reference = null): void
    {
        $history = $this->payment_history ?? [];
        $history[] = [
            'date' => now()->toDateString(),
            'amount' => $amount,
            'reference' => $reference,
        ];

        $this->update([
            'last_payment_date' => now()->toDateString(),
            'next_payment_date' => $this->plan_type === 'anual' ? now()->addYear()->toDateString() : now()->addMonth()->toDateString(),
            'status' => 'activa',
            'payment_history' => $history,
        ]);
    }
}
```

- [ ] **Step 3: Add relationship to Tenant model**

```php
// In app/Models/Tenant.php
public function subscription(): HasOne
{
    return $this->hasOne(TenantSubscription::class);
}
```

- [ ] **Step 4: Write the test**

```php
<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'is_superadmin' => true,
            'tenant_id' => null,
        ]);

        $this->tenant = Tenant::factory()->create();
    }

    public function test_can_create_subscription(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'activa',
        ]);

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'status' => 'activa',
        ]);

        $this->assertTrue($subscription->isActive());
        $this->assertFalse($subscription->isExpired());
    }

    public function test_subscription_belongs_to_tenant(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'anual',
            'amount' => 2999.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'status' => 'activa',
        ]);

        $this->assertInstanceOf(Tenant::class, $subscription->tenant);
        $this->assertEquals($this->tenant->id, $subscription->tenant->id);
    }

    public function test_mark_as_paid_updates_subscription(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'status' => 'pendiente',
        ]);

        $subscription->markAsPaid(299.00, 'Pago-001');

        $this->assertEquals('activa', $subscription->fresh()->status);
        $this->assertNotNull($subscription->fresh()->last_payment_date);
        $this->assertNotNull($subscription->fresh()->next_payment_date);
        $this->assertCount(1, $subscription->fresh()->payment_history);
    }

    public function test_can_access_subscription_from_tenant(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'status' => 'activa',
        ]);

        $this->assertNotNull($this->tenant->subscription);
        $this->assertEquals($subscription->id, $this->tenant->subscription->id);
    }
}
```

Run: `php artisan test --filter="TenantSubscriptionTest"` — Expected: PASS

- [ ] **Step 5: Run full suite**

Run: `php artisan test --compact` — Expected: 101/101 passed

- [ ] **Step 6: Update documentation**

Add to `Documentacion_Software.md` MOD-12 section: fields and model documented.

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: add TenantSubscription model, migration, and tests"
```

---

### Task 5: Tenant Index & Detail View (INC-MOD12-002)

**Files:**
- Modify: `app/Http/Controllers/SuperAdminController.php` (add `tenants`, `tenantDetail`)
- Create: `resources/views/superadmin/tenants/index.blade.php`
- Create: `resources/views/superadmin/tenants/show.blade.php`
- Create: `tests/Feature/SuperAdminTenantsTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminTenantsTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'is_superadmin' => true,
            'tenant_id' => null,
        ]);
    }

    public function test_tenant_index_shows_all_tenants(): void
    {
        Tenant::factory()->create(['name' => 'Taller A', 'is_active' => true]);
        Tenant::factory()->create(['name' => 'Taller B', 'is_active' => false]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.tenants.index'));

        $response->assertOk();
        $response->assertSee('Taller A');
        $response->assertSee('Taller B');
    }

    public function test_tenant_show_displays_details(): void
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Mi Taller',
            'email' => 'taller@example.com',
            'phone' => '555-1234',
            'is_active' => true,
        ]);

        User::factory()->count(3)->create(['tenant_id' => $tenant->id]);
        WorkOrder::factory()->count(5)->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.tenants.show', $tenant));

        $response->assertOk();
        $response->assertSee('Mi Taller');
        $response->assertSee('taller@example.com');
        $response->assertSee('555-1234');
        $response->assertSee('3'); // users count
        $response->assertSee('5'); // work orders count
    }

    public function test_can_activate_tenant(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->superadmin)->post(route('admin.tenants.toggle-status', $tenant));

        $response->assertRedirect();
        $this->assertTrue($tenant->fresh()->is_active);
    }

    public function test_can_deactivate_tenant(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->superadmin)->post(route('admin.tenants.toggle-status', $tenant));

        $response->assertRedirect();
        $this->assertFalse($tenant->fresh()->is_active);
    }

    public function test_non_superadmin_cannot_manage_tenants(): void
    {
        $tenantUser = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenantUser->id]);

        $response = $this->actingAs($user)->get(route('admin.tenants.index'));
        $response->assertForbidden();
    }
}
```

Run: `php artisan test --filter="SuperAdminTenantsTest"` — Expected: FAIL

- [ ] **Step 2: Add routes**

```php
Route::get('tenants', [SuperAdminController::class, 'tenants'])->name('tenants.index');
Route::get('tenants/{tenant}', [SuperAdminController::class, 'tenantDetail'])->name('tenants.show');
Route::post('tenants/{tenant}/toggle-status', [SuperAdminController::class, 'toggleTenantStatus'])->name('tenants.toggle-status');
```

- [ ] **Step 3: Add controller methods**

```php
public function tenants(Request $request)
{
    $query = Tenant::withCount('users');

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('slug', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    if ($request->filled('status')) {
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }
    }

    $tenants = $query->orderBy('created_at', 'desc')->paginate(15);

    return view('superadmin.tenants.index', compact('tenants'));
}

public function tenantDetail(Tenant $tenant)
{
    $tenant->loadCount(['users', 'clients', 'workOrders', 'sales']);
    $tenant->load('subscription');

    $recentWorkOrders = $tenant->workOrders()->latest()->take(5)->get();
    $recentSales = $tenant->sales()->latest()->take(5)->get();

    return view('superadmin.tenants.show', compact('tenant', 'recentWorkOrders', 'recentSales'));
}

public function toggleTenantStatus(Tenant $tenant)
{
    $tenant->update(['is_active' => !$tenant->is_active]);

    $status = $tenant->fresh()->is_active ? 'activado' : 'desactivado';

    return redirect()->route('admin.tenants.show', $tenant)
        ->with('success', "Tenant {$tenant->name} {$status} exitosamente.");
}
```

- [ ] **Step 4: Create tenants index view**

`resources/views/superadmin/tenants/index.blade.php`:

```html
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Tenants</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gestión de todos los tenants registrados</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Nombre, slug o email..."
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                <select name="status" id="status"
                        class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    <option value="">Todos</option>
                    <option value="active" @selected(request('status') === 'active')>Activos</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactivos</option>
                </select>
            </div>
            <div>
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuarios</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Registro</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/25 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                    {{ $tenant->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $tenant->slug }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $tenant->users_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $tenant->subscription?->plan_type ?? 'Sin plan' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($tenant->is_active)
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400">Activo</span>
                                @else
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $tenant->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                                    Ver detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No se encontraron tenants.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $tenants->links() }}
    </div>
</div>
@endsection
```

- [ ] **Step 5: Create tenant detail view**

`resources/views/superadmin/tenants/show.blade.php`:

```html
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.tenants.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">&larr; Volver a Tenants</a>
    </div>

    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
                    <svg class="h-8 w-8 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $tenant->name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Slug: {{ $tenant->slug }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                @if($tenant->is_active)
                    <span class="inline-flex items-center rounded-md px-3 py-1 text-sm font-medium bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400">Activo</span>
                @else
                    <span class="inline-flex items-center rounded-md px-3 py-1 text-sm font-medium bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400">Inactivo</span>
                @endif
                <form action="{{ route('admin.tenants.toggle-status', $tenant) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm {{ $tenant->is_active ? 'bg-red-600 hover:bg-red-500' : 'bg-green-600 hover:bg-green-500' }} transition-colors"
                            onclick="return confirm('¿Estás seguro de {{ $tenant->is_active ? 'desactivar' : 'activar' }} este tenant?')">
                        {{ $tenant->is_active ? 'Desactivar' : 'Activar' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            @if($tenant->email)
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $tenant->email }}</p>
            </div>
            @endif
            @if($tenant->phone)
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Teléfono</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $tenant->phone }}</p>
            </div>
            @endif
            @if($tenant->address)
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Dirección</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $tenant->address }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Usuarios</dt>
            <dd class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $tenant->users_count }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Clientes</dt>
            <dd class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $tenant->clients_count }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Órdenes</dt>
            <dd class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $tenant->work_orders_count }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Ventas</dt>
            <dd class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $tenant->sales_count }}</dd>
        </div>
    </div>

    <!-- Subscription Info -->
    @if($tenant->subscription)
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Suscripción</h2>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan</label>
                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($tenant->subscription->plan_type) }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Monto</label>
                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">${{ number_format($tenant->subscription->amount, 2) }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</label>
                <p class="mt-1">
                    @if($tenant->subscription->status === 'activa')
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400">Activa</span>
                    @elseif($tenant->subscription->status === 'pendiente')
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">Pendiente</span>
                    @else
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400">Cancelada</span>
                    @endif
                </p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Próximo pago</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $tenant->subscription->next_payment_date?->format('d/m/Y') ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Órdenes Recientes</h2>
            </div>
            <div class="p-6">
                @forelse($recentWorkOrders as $wo)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $wo->work_order_number }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $wo->created_at->format('d/m/Y') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sin órdenes registradas.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ventas Recientes</h2>
            </div>
            <div class="p-6">
                @forelse($recentSales as $sale)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <span class="text-sm text-gray-900 dark:text-gray-100">Venta #{{ $sale->id }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">${{ number_format($sale->total, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sin ventas registradas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 6: Run tests to verify**

Run: `php artisan test --filter="SuperAdminTenantsTest"` — Expected: PASS

- [ ] **Step 7: Run full suite**

Run: `php artisan test --compact` — Expected: 107/107 passed

- [ ] **Step 8: Commit**

```bash
git add -A
git commit -m "feat: superadmin tenant index, detail view, and activate/deactivate"
```

---

### Task 6: Subscription Management UI (INC-MOD12-003)

**Files:**
- Modify: `app/Http/Controllers/SuperAdminController.php` (add `subscriptionCreate`, `subscriptionStore`, `subscriptionEdit`, `subscriptionUpdate`)
- Create: `resources/views/superadmin/subscriptions/create.blade.php`
- Create: `resources/views/superadmin/subscriptions/edit.blade.php`
- Create: `tests/Feature/SuperAdminSubscriptionTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'is_superadmin' => true,
            'tenant_id' => null,
        ]);

        $this->tenant = Tenant::factory()->create();
    }

    public function test_show_create_subscription_form(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.subscriptions.create', $this->tenant));

        $response->assertOk();
        $response->assertSee('Crear Suscripción');
    }

    public function test_can_create_subscription_via_ui(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('admin.subscriptions.store', $this->tenant), [
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'status' => 'activa',
        ]);

        $response->assertRedirect(route('admin.tenants.show', $this->tenant));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'status' => 'activa',
        ]);
    }

    public function test_can_update_subscription(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'status' => 'activa',
        ]);

        $response = $this->actingAs($this->superadmin)->put(route('admin.subscriptions.update', [$this->tenant, $subscription]), [
            'plan_type' => 'anual',
            'amount' => 2999.00,
            'start_date' => now()->toDateString(),
            'status' => 'activa',
        ]);

        $response->assertRedirect(route('admin.tenants.show', $this->tenant));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenant_subscriptions', [
            'id' => $subscription->id,
            'plan_type' => 'anual',
            'amount' => 2999.00,
        ]);
    }

    public function test_can_mark_subscription_as_paid(): void
    {
        $subscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'mensual',
            'amount' => 299.00,
            'start_date' => now()->subMonth()->toDateString(),
            'status' => 'pendiente',
        ]);

        $response = $this->actingAs($this->superadmin)->post(route('admin.subscriptions.pay', [$this->tenant, $subscription]), [
            'amount' => 299.00,
            'reference' => 'PAGO-001',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $subscription->refresh();
        $this->assertEquals('activa', $subscription->status);
        $this->assertNotNull($subscription->last_payment_date);
        $this->assertCount(1, $subscription->payment_history);
    }

    public function test_validation_requires_plan_type(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('admin.subscriptions.store', $this->tenant), [
            'amount' => 299.00,
            'start_date' => now()->toDateString(),
            'status' => 'activa',
        ]);

        $response->assertSessionHasErrors('plan_type');
    }
}
```

Run: `php artisan test --filter="SuperAdminSubscriptionTest"` — Expected: FAIL

- [ ] **Step 2: Add routes**

```php
Route::get('tenants/{tenant}/subscriptions/create', [SuperAdminController::class, 'subscriptionCreate'])->name('subscriptions.create');
Route::post('tenants/{tenant}/subscriptions', [SuperAdminController::class, 'subscriptionStore'])->name('subscriptions.store');
Route::get('tenants/{tenant}/subscriptions/{subscription}/edit', [SuperAdminController::class, 'subscriptionEdit'])->name('subscriptions.edit');
Route::put('tenants/{tenant}/subscriptions/{subscription}', [SuperAdminController::class, 'subscriptionUpdate'])->name('subscriptions.update');
Route::post('tenants/{tenant}/subscriptions/{subscription}/pay', [SuperAdminController::class, 'subscriptionPay'])->name('subscriptions.pay');
```

- [ ] **Step 3: Add controller methods**

```php
public function subscriptionCreate(Tenant $tenant)
{
    return view('superadmin.subscriptions.create', compact('tenant'));
}

public function subscriptionStore(Request $request, Tenant $tenant)
{
    $validated = $request->validate([
        'plan_type' => 'required|string|in:mensual,anual,prueba,personalizado',
        'amount' => 'required|numeric|min:0',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after:start_date',
        'status' => 'required|string|in:activa,pending,expirada,cancelada',
        'notes' => 'nullable|string',
    ]);

    $tenant->subscription()->create($validated);

    return redirect()->route('admin.tenants.show', $tenant)
        ->with('success', 'Suscripción creada exitosamente.');
}

public function subscriptionEdit(Tenant $tenant, TenantSubscription $subscription)
{
    return view('superadmin.subscriptions.edit', compact('tenant', 'subscription'));
}

public function subscriptionUpdate(Request $request, Tenant $tenant, TenantSubscription $subscription)
{
    $validated = $request->validate([
        'plan_type' => 'required|string|in:mensual,anual,prueba,personalizado',
        'amount' => 'required|numeric|min:0',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after:start_date',
        'status' => 'required|string|in:activa,pending,expirada,cancelada',
        'notes' => 'nullable|string',
    ]);

    $subscription->update($validated);

    return redirect()->route('admin.tenants.show', $tenant)
        ->with('success', 'Suscripción actualizada exitosamente.');
}

public function subscriptionPay(Request $request, Tenant $tenant, TenantSubscription $subscription)
{
    $validated = $request->validate([
        'amount' => 'required|numeric|min:0.01',
        'reference' => 'nullable|string|max:255',
    ]);

    $subscription->markAsPaid($validated['amount'], $validated['reference']);

    return redirect()->route('admin.tenants.show', $tenant)
        ->with('success', 'Pago registrado exitosamente.');
}
```

- [ ] **Step 4: Create subscription create form view**

`resources/views/superadmin/subscriptions/create.blade.php`:

```html
@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">&larr; Volver a {{ $tenant->name }}</a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl p-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Crear Suscripción para {{ $tenant->name }}</h1>

        <form action="{{ route('admin.subscriptions.store', $tenant) }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="plan_type" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Plan <span class="text-red-500">*</span></label>
                    <select name="plan_type" id="plan_type" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="">Seleccionar plan</option>
                        <option value="mensual" @selected(old('plan_type') === 'mensual')>Mensual</option>
                        <option value="anual" @selected(old('plan_type') === 'anual')>Anual</option>
                        <option value="prueba" @selected(old('plan_type') === 'prueba')>Prueba</option>
                        <option value="personalizado" @selected(old('plan_type') === 'personalizado')>Personalizado</option>
                    </select>
                    @error('plan_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Monto <span class="text-red-500">*</span></label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 sm:text-sm">$</span>
                        <input type="number" step="0.01" min="0" name="amount" id="amount" value="{{ old('amount') }}" required
                               class="block w-full pl-8 rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    </div>
                    @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Fecha de inicio <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->toDateString()) }}" required
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Fecha de fin</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    @error('end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Estado <span class="text-red-500">*</span></label>
                    <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="activa" @selected(old('status') === 'activa')>Activa</option>
                        <option value="pendiente" @selected(old('status') === 'pendiente')>Pendiente</option>
                        <option value="expirada" @selected(old('status') === 'expirada')>Expirada</option>
                        <option value="cancelada" @selected(old('status') === 'cancelada')>Cancelada</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Notas</label>
                <textarea name="notes" id="notes" rows="3"
                          class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">{{ old('notes') }}</textarea>
                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('admin.tenants.show', $tenant) }}" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</a>
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Crear Suscripción</button>
            </div>
        </form>
    </div>
</div>
@endsection
```

- [ ] **Step 5: Create subscription edit form view**

`resources/views/superadmin/subscriptions/edit.blade.php` (similar to create, pre-populated with `$subscription` data):

```html
@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">&larr; Volver a {{ $tenant->name }}</a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-2xl p-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Editar Suscripción — {{ $tenant->name }}</h1>

        <form action="{{ route('admin.subscriptions.update', [$tenant, $subscription]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="plan_type" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Plan <span class="text-red-500">*</span></label>
                    <select name="plan_type" id="plan_type" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="mensual" @selected(old('plan_type', $subscription->plan_type) === 'mensual')>Mensual</option>
                        <option value="anual" @selected(old('plan_type', $subscription->plan_type) === 'anual')>Anual</option>
                        <option value="prueba" @selected(old('plan_type', $subscription->plan_type) === 'prueba')>Prueba</option>
                        <option value="personalizado" @selected(old('plan_type', $subscription->plan_type) === 'personalizado')>Personalizado</option>
                    </select>
                    @error('plan_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Monto <span class="text-red-500">*</span></label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 sm:text-sm">$</span>
                        <input type="number" step="0.01" min="0" name="amount" id="amount" value="{{ old('amount', $subscription->amount) }}" required
                               class="block w-full pl-8 rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    </div>
                    @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Fecha de inicio <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $subscription->start_date->toDateString()) }}" required
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Fecha de fin</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $subscription->end_date?->toDateString()) }}"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                    @error('end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Estado <span class="text-red-500">*</span></label>
                    <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">
                        <option value="activa" @selected(old('status', $subscription->status) === 'activa')>Activa</option>
                        <option value="pendiente" @selected(old('status', $subscription->status) === 'pendiente')>Pendiente</option>
                        <option value="expirada" @selected(old('status', $subscription->status) === 'expirada')>Expirada</option>
                        <option value="cancelada" @selected(old('status', $subscription->status) === 'cancelada')>Cancelada</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Notas</label>
                <textarea name="notes" id="notes" rows="3"
                          class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 sm:text-sm sm:leading-6">{{ old('notes', $subscription->notes) }}</textarea>
                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('admin.tenants.show', $tenant) }}" class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancelar</a>
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Actualizar Suscripción</button>
            </div>
        </form>
    </div>
</div>
@endsection
```

- [ ] **Step 6: Run tests**

Run: `php artisan test --filter="SuperAdminSubscriptionTest"` — Expected: PASS

- [ ] **Step 7: Run full suite**

Run: `php artisan test --compact` — Expected: 113/113 passed

- [ ] **Step 8: Commit**

```bash
git add -A
git commit -m "feat: subscription CRUD UI for superadmin panel"
```

---

### Task 7: Restrict Superadmin from Tenant Routes (INC-MOD12-005)

**Files:**
- Create: `app/Http/Middleware/CheckNotSuperAdmin.php`
- Modify: `bootstrap/app.php` (register middleware)
- Modify: All tenant-protected route groups (POS, work_orders, products, settings, etc.)

- [ ] **Step 1: Create middleware**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckNotSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isSuperAdmin()) {
            abort(403, 'Acceso denegado. El Superadmin no puede acceder a operaciones del tenant.');
        }

        return $next($request);
    }
}
```

- [ ] **Step 2: Register middleware alias**

In `bootstrap/app.php`, add to the `$middleware->alias([...])`:

```php
'not-superadmin' => \App\Http\Middleware\CheckNotSuperAdmin::class,
```

- [ ] **Step 3: Apply middleware to tenant routes**

In `routes/web.php`, add `'not-superadmin'` to route groups that should be tenant-only:

```php
// POS routes
Route::middleware(['auth', 'not-superadmin'])->prefix('pos')->name('pos.')->group(function () { ... });

// Work orders
Route::middleware(['auth', 'not-superadmin'])->prefix('work-orders')->name('work_orders.')->group(function () { ... });

// Products
Route::middleware(['auth', 'not-superadmin'])->prefix('productos')->name('products.')->group(function () { ... });

// Settings
Route::middleware(['auth', 'can:settings.*', 'not-superadmin'])->prefix('settings')->name('settings.')->group(function () { ... });

// Dashboard
Route::middleware(['auth', 'verified', 'not-superadmin'])->group(function () {
    Route::get('/dashboard', ...);
});

// Clients, Categories, etc.
```

- [ ] **Step 4: Write test**

Create `tests/Feature/SuperAdminRestrictionTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminRestrictionTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'is_superadmin' => true,
            'tenant_id' => null,
        ]);
    }

    public function test_superadmin_cannot_access_pos(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('pos.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_dashboard(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('dashboard'));
        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_settings(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('settings.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_products(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('products.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_work_orders(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('work_orders.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_clients(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('clients.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_cannot_access_sales(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('sales.index'));
        $response->assertForbidden();
    }

    public function test_regular_user_can_access_tenant_routes(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'is_superadmin' => false,
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
    }
}
```

Run: `php artisan test --filter="SuperAdminRestrictionTest"` — Expected: PASS

- [ ] **Step 5: Run full suite**

Run: `php artisan test --compact` — Expected: ~123/123 passed

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "feat: restrict superadmin from tenant routes with middleware"
```

---

### Task 8: Final Documentation & Verification

**Files:**
- Modify: `Documentacion_Software.md` (update MOD-12 section)

- [ ] **Step 1: Run full test suite**

Run: `php artisan test --compact` — Verify all tests pass

- [ ] **Step 2: Update documentation**

Update `Documentacion_Software.md` MOD-12 section:
- Mark INCs as ✅ Completado
- Add technical notes about implementation
- Document routes, middleware, views

- [ ] **Step 3: Final commit**

```bash
git add -A
git commit -m "docs: update MOD-12 documentation with superadmin panel implementation"
```

---

## Summary of Files Created/Modified

### New Files
- `app/Http/Middleware/CheckSuperAdmin.php` — middleware for admin routes
- `app/Http/Middleware/CheckNotSuperAdmin.php` — middleware for tenant routes
- `app/Http/Controllers/SuperAdminController.php` — all superadmin functionality
- `app/Models/TenantSubscription.php` — subscription model
- `database/migrations/xxxx_xx_xx_xxxxxx_create_tenant_subscriptions_table.php` — subscription table
- `database/seeders/SuperAdminPermissionSeeder.php` — superadmin permissions seeder
- `resources/views/superadmin/dashboard.blade.php` — main dashboard
- `resources/views/superadmin/tenants/index.blade.php` — tenant list
- `resources/views/superadmin/tenants/show.blade.php` — tenant detail
- `resources/views/superadmin/subscriptions/create.blade.php` — subscription form
- `resources/views/superadmin/subscriptions/edit.blade.php` — edit subscription form
- `tests/Feature/SuperAdminMiddlewareTest.php`
- `tests/Feature/SuperAdminPermissionTest.php`
- `tests/Feature/SuperAdminDashboardTest.php`
- `tests/Feature/SuperAdminTenantsTest.php`
- `tests/Feature/SuperAdminSubscriptionTest.php`
- `tests/Feature/SuperAdminRestrictionTest.php`

### Modified Files
- `bootstrap/app.php` — register middleware aliases
- `routes/web.php` — add admin routes + not-superadmin middleware to tenant groups
- `resources/views/layouts/partials/sidebar-content.blade.php` — add superadmin nav
- `database/seeders/DatabaseSeeder.php` — add SuperAdminPermissionSeeder
- `app/Models/Tenant.php` — add `subscription()` relationship
- `Documentacion_Software.md` — update MOD-12 status
