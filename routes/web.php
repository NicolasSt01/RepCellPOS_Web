<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/seguimiento/{token}', [TrackingController::class, 'show'])->name('tracking.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [TenantController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [TenantController::class, 'register']);
});

// Logout must be accessible to all authenticated users including superadmin
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'not-superadmin'])->group(function () {
    Route::get('/r2/{path}', function ($path) {
        if (!\Illuminate\Support\Facades\Storage::disk('r2')->exists($path)) {
            abort(404);
        }
        $content = \Illuminate\Support\Facades\Storage::disk('r2')->get($path);
        $mimeType = \Illuminate\Support\Facades\Storage::disk('r2')->mimeType($path);
        return response($content, 200, ['Content-Type' => $mimeType]);
    })->where('path', '.*')->name('r2.serve');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('clients', ClientController::class);

    Route::get('/work_orders/search-clients', [WorkOrderController::class, 'searchClients'])->name('work_orders.search_clients');
    Route::post('/work_orders/store-client', [WorkOrderController::class, 'storeClient'])->name('work_orders.store_client');
    Route::resource('work_orders', WorkOrderController::class);
    Route::get('/work_orders/{work_order}/print', [WorkOrderController::class, 'print'])->name('work_orders.print');
    Route::get('/work_orders/{work_order}/print-pdf', [WorkOrderController::class, 'printPdf'])->name('work_orders.print.pdf');
    Route::get('/work_orders/reports', [WorkOrderController::class, 'reports'])->name('work_orders.reports');
    Route::post('/work_orders/{work_order}/change_status', [WorkOrderController::class, 'changeStatus'])->name('work_orders.change_status');
    Route::post('/work_orders/{work_order}/set_priority', [WorkOrderController::class, 'setPriority'])->name('work_orders.set_priority');
    Route::post('/work_orders/{work_order}/add_note', [WorkOrderController::class, 'addNote'])->name('work_orders.add_note');
    Route::post('/work_orders/{work_order}/assign_technician', [WorkOrderController::class, 'assignTechnician'])->name('work_orders.assign_technician');
    Route::post('/work_orders/{work_order}/unassign_technician', [WorkOrderController::class, 'unassignTechnician'])->name('work_orders.unassign_technician');
    Route::post('/work_orders/{work_order}/images', [WorkOrderController::class, 'addImages'])->name('work_orders.images.store');

    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/adjust_stock', [ProductController::class, 'adjustStock'])->name('products.adjust_stock');
    Route::post('/products/{product}/print-labels', [ProductController::class, 'printLabels'])->name('products.print_labels');

    Route::get('/work_orders/{work_order}/quote', [QuoteController::class, 'show'])->name('quotes.show');
    Route::post('/quotes/{quote}/add_item', [QuoteController::class, 'addItem'])->name('quotes.add_item');
    Route::delete('/quote_items/{quoteItem}', [QuoteController::class, 'removeItem'])->name('quotes.remove_item');
    Route::post('/quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');
    Route::post('/quotes/{quote}/approve', [QuoteController::class, 'approve'])->name('quotes.approve');
    Route::post('/quotes/{quote}/reject', [QuoteController::class, 'reject'])->name('quotes.reject');

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/print/{sale}', [PosController::class, 'print'])->name('pos.print');
    Route::get('/pos/print/{sale}/preview', [PosController::class, 'printPreview'])->name('pos.print.preview');

    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');

    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::get('/returns/create', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('/returns/search-sale', [ReturnController::class, 'searchSale'])->name('returns.search_sale');
    Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');

    Route::get('/cash_registers', [CashRegisterController::class, 'index'])->name('cash_registers.index');
    Route::post('/cash_registers/open', [CashRegisterController::class, 'open'])->name('cash_registers.open');
    Route::post('/cash_registers/{cashRegister}/close', [CashRegisterController::class, 'close'])->name('cash_registers.close');
    Route::post('/cash_registers/{cashRegister}/withdraw', [CashRegisterController::class, 'withdraw'])->name('cash_registers.withdraw');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('can:settings.company');
    Route::get('/settings/company', [SettingsController::class, 'company'])->name('settings.company')->middleware('can:settings.company');
    Route::put('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company.update')->middleware('can:settings.company');

    Route::get('/settings/users', [SettingsController::class, 'users'])->name('settings.users')->middleware('can:settings.users');
    Route::post('/settings/users', [SettingsController::class, 'createUser'])->name('settings.users.store')->middleware('can:settings.users');
    Route::put('/settings/users/{user}', [SettingsController::class, 'updateUser'])->name('settings.users.update')->middleware('can:settings.users');
    Route::delete('/settings/users/{user}', [SettingsController::class, 'deleteUser'])->name('settings.users.destroy')->middleware('can:settings.users');

    Route::get('/settings/roles', [SettingsController::class, 'roles'])->name('settings.roles')->middleware('can:settings.roles');
    Route::post('/settings/roles', [SettingsController::class, 'createRole'])->name('settings.roles.store')->middleware('can:settings.roles');
    Route::put('/settings/roles/{role}', [SettingsController::class, 'updateRole'])->name('settings.roles.update')->middleware('can:settings.roles');
    Route::delete('/settings/roles/{role}', [SettingsController::class, 'deleteRole'])->name('settings.roles.destroy')->middleware('can:settings.roles');

    Route::get('/settings/clauses', [SettingsController::class, 'clauses'])->name('settings.clauses')->middleware('can:settings.clauses');
    Route::post('/settings/clauses', [SettingsController::class, 'storeClause'])->name('settings.clauses.store')->middleware('can:settings.clauses');
    Route::put('/settings/clauses/{clause}', [SettingsController::class, 'updateClause'])->name('settings.clauses.update')->middleware('can:settings.clauses');
    Route::delete('/settings/clauses/{clause}', [SettingsController::class, 'deleteClause'])->name('settings.clauses.destroy')->middleware('can:settings.clauses');

    Route::get('/settings/taxes', [SettingsController::class, 'taxes'])->name('settings.taxes')->middleware('can:settings.taxes');
    Route::put('/settings/taxes', [SettingsController::class, 'updateTaxes'])->name('settings.taxes.update')->middleware('can:settings.taxes');
});

Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');

    Route::get('tenants', [App\Http\Controllers\SuperAdminController::class, 'tenants'])->name('tenants.index');
    Route::get('tenants/{tenant}', [App\Http\Controllers\SuperAdminController::class, 'tenantDetail'])->name('tenants.show');
    Route::post('tenants/{tenant}/toggle-status', [App\Http\Controllers\SuperAdminController::class, 'toggleTenantStatus'])->name('tenants.toggle-status');

    Route::get('tenants/{tenant}/subscriptions/create', [App\Http\Controllers\SuperAdminController::class, 'subscriptionCreate'])->name('subscriptions.create');
    Route::post('tenants/{tenant}/subscriptions', [App\Http\Controllers\SuperAdminController::class, 'subscriptionStore'])->name('subscriptions.store');
    Route::get('tenants/{tenant}/subscriptions/{subscription}/edit', [App\Http\Controllers\SuperAdminController::class, 'subscriptionEdit'])->name('subscriptions.edit');
    Route::put('tenants/{tenant}/subscriptions/{subscription}', [App\Http\Controllers\SuperAdminController::class, 'subscriptionUpdate'])->name('subscriptions.update');
    Route::post('tenants/{tenant}/subscriptions/{subscription}/pay', [App\Http\Controllers\SuperAdminController::class, 'subscriptionPay'])->name('subscriptions.pay');
});
