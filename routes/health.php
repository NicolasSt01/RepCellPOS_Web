<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json(['status' => 'ok', 'timestamp' => now()]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});

Route::get('/__e2e/read-log', function () {
    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) {
        return response('LOG_NOT_FOUND');
    }
    $lines = file($logFile);
    // Return the full log — E2E tests clear it before each run
    return response(implode('', $lines));
});

Route::get('/__e2e/cleanup', function () {
    // Disable FK checks to allow truncating tables with foreign keys
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
    \Illuminate\Support\Facades\DB::table('work_orders')->truncate();
    // notifications table references work_orders
    \Illuminate\Support\Facades\DB::table('notifications')->truncate();
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');
    // Reset work order sequence for all tenants
    \Illuminate\Support\Facades\DB::table('tenants')->update(['work_order_sequence' => 0]);
    // Clear log file to avoid detecting old errors
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        file_put_contents($logFile, '');
    }
    return response()->json(['status' => 'cleaned']);
});

Route::get('/__e2e/verify-email', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email');
    if (!$email) {
        return response()->json(['error' => 'Email parameter required'], 400);
    }
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    \Illuminate\Support\Facades\DB::table('users')
        ->where('id', $user->id)
        ->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);
    return response()->json(['status' => 'email_verified', 'email' => $user->email]);
});

Route::get('/__e2e/get-verification-token', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email');
    if (!$email) {
        return response()->json(['error' => 'Email parameter required'], 400);
    }
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    return response()->json(['token' => $user->email_verification_token]);
});

Route::get('/__e2e/expire-email-verification', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email');
    if (!$email) {
        return response()->json(['error' => 'Email parameter required'], 400);
    }
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    // Use raw DB update to bypass $fillable guard on created_at
    \Illuminate\Support\Facades\DB::table('users')
        ->where('id', $user->id)
        ->update(['created_at' => now()->subDays(3)]);
    return response()->json(['status' => 'verification_expired', 'email' => $user->email]);
});

Route::get('/__e2e/expire-trial', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email');
    if (!$email) {
        return response()->json(['error' => 'Email parameter required'], 400);
    }
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user || !$user->tenant) {
        return response()->json(['error' => 'User or tenant not found'], 404);
    }
    $user->tenant->update([
        'trial_ends_at' => now()->subDay(),
    ]);
    return response()->json(['status' => 'trial_expired', 'tenant' => $user->tenant->name]);
});

Route::get('/__e2e/fill-clients', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email');
    $count = (int) ($request->query('count', 50));
    if (!$email) {
        return response()->json(['error' => 'Email parameter required'], 400);
    }
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user || !$user->tenant) {
        return response()->json(['error' => 'User or tenant not found'], 404);
    }
    $tenant = $user->tenant;
    for ($i = 0; $i < $count; $i++) {
        \App\Models\Client::create([
            'tenant_id' => $tenant->id,
            'name' => "E2E Client {$i}",
            'phone' => '+52 55 9999 ' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
            'notification_preference' => 'call',
        ]);
    }
    return response()->json(['status' => 'clients_created', 'count' => $count, 'total' => $tenant->clients()->count()]);
});

Route::get('/__e2e/activate-plan', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email');
    $planSlug = $request->query('plan');
    if (!$email || !$planSlug) {
        return response()->json(['error' => 'Email and plan parameters required'], 400);
    }
    $user = \App\Models\User::where('email', $email)->first();
    $plan = \App\Models\Plan::where('slug', $planSlug)->first();
    if (!$user || !$user->tenant) {
        return response()->json(['error' => 'User or tenant not found'], 404);
    }
    if (!$plan) {
        return response()->json(['error' => 'Plan not found'], 404);
    }
    $user->tenant->update([
        'plan_id' => $plan->id,
        'subscription_status' => 'active',
        'trial_ends_at' => null,
    ]);
    return response()->json(['status' => 'plan_activated', 'tenant' => $user->tenant->name, 'plan' => $plan->name]);
});

Route::get('/__e2e/expire-subscription', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email');
    if (!$email) {
        return response()->json(['error' => 'Email parameter required'], 400);
    }
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user || !$user->tenant) {
        return response()->json(['error' => 'User or tenant not found'], 404);
    }
    $tenant = $user->tenant;

    $tenant->update([
        'subscription_status' => 'active',
        'trial_ends_at' => null,
    ]);

    \App\Models\TenantSubscription::updateOrCreate(
        ['tenant_id' => $tenant->id],
        [
            'plan_id' => $tenant->plan_id,
            'plan_type' => 'mensual',
            'amount' => 0,
            'start_date' => now()->subDays(30),
            'end_date' => now()->subDay(),
            'status' => 'activa',
        ]
    );

    return response()->json(['status' => 'subscription_expired', 'tenant' => $tenant->name]);
});

Route::get('/__e2e/create-user', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email');
    $newUserEmail = $request->query('new_email');
    $newUserName = $request->query('new_name');
    $role = $request->query('role', 'tecnico');
    if (!$email || !$newUserEmail || !$newUserName) {
        return response()->json(['error' => 'email, new_email, new_name parameters required'], 400);
    }
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user || !$user->tenant) {
        return response()->json(['error' => 'User or tenant not found'], 404);
    }
    $newUser = \App\Models\User::create([
        'name' => $newUserName,
        'email' => $newUserEmail,
        'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        'tenant_id' => $user->tenant_id,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $newUser->assignRole($role);
    return response()->json(['status' => 'user_created', 'user' => $newUser->name, 'role' => $role]);
});

Route::get('/__e2e/create-sale', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email');
    if (!$email) {
        return response()->json(['error' => 'Email parameter required'], 400);
    }
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user || !$user->tenant) {
        return response()->json(['error' => 'User or tenant not found'], 404);
    }
    $tenant = $user->tenant;

    $register = \App\Models\CashRegister::where('tenant_id', $tenant->id)->where('status', 'abierta')->first();
    if (!$register) {
        return response()->json(['error' => 'No open cash register'], 400);
    }

    \App\Models\Sale::create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'cash_register_id' => $register->id,
        'type' => 'venta_directa',
        'subtotal' => 100,
        'tax_total' => 16,
        'total' => 116,
        'payment_method' => 'efectivo',
        'cash_amount' => 116,
        'card_amount' => 0,
        'change_amount' => 0,
    ]);

    return response()->json(['status' => 'sale_created', 'expected_cash' => $register->getExpectedCash()]);
});

Route::get('/__e2e/create-work-order', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email');
    if (!$email) {
        return response()->json(['error' => 'Email parameter required'], 400);
    }
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user || !$user->tenant) {
        return response()->json(['error' => 'User or tenant not found'], 404);
    }
    $tenant = $user->tenant;
    $client = \App\Models\Client::where('tenant_id', $tenant->id)->first();
    if (!$client) {
        return response()->json(['error' => 'No client found, create clients first'], 400);
    }

    $wo = \App\Models\WorkOrder::create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'user_id' => $user->id,
        'device_brand' => $request->query('brand', 'Samsung'),
        'device_model' => $request->query('model', 'Galaxy S24'),
        'device_serial' => 'SN-E2E-' . uniqid(),
        'problem_description' => $request->query('problem', 'Test problem'),
        'status' => 'recibida',
        'priority' => 'media',
        'work_order_number' => $tenant->work_order_prefix . ($tenant->work_order_sequence + 1),
    ]);

    $tenant->increment('work_order_sequence');

    if ($request->has('assigned_to')) {
        $wo->update(['assigned_to' => $request->query('assigned_to')]);
    }

    return response()->json(['status' => 'work_order_created', 'id' => $wo->id, 'number' => $wo->work_order_number]);
});

Route::get('/__e2e/set-work-order-status', function (\Illuminate\Http\Request $request) {
    $id = $request->query('id');
    $status = $request->query('status');
    if (!$id || !$status) {
        return response()->json(['error' => 'id and status required'], 400);
    }
    $workOrder = \App\Models\WorkOrder::find($id);
    if (!$workOrder) {
        return response()->json(['error' => 'Work order not found'], 404);
    }
    $workOrder->update(['status' => $status]);
    return response()->json(['status' => 'ok', 'new_status' => $status]);
});

Route::get('/__e2e/create-superadmin', function () {
    $user = \App\Models\User::where('email', 'superadmin@repcellpos.com')->first();
    if ($user) {
        return response()->json(['status' => 'already_exists', 'email' => $user->email]);
    }
    $user = \App\Models\User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@repcellpos.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'is_superadmin' => true,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    return response()->json(['status' => 'superadmin_created', 'email' => $user->email]);
});

Route::get('/__e2e/simulate-pickup-reminder', function (\Illuminate\Http\Request $request) {
    $id = $request->query('id');
    if (!$id) {
        return response()->json(['error' => 'work_order_id required'], 400);
    }

    $workOrder = \App\Models\WorkOrder::find($id);
    if (!$workOrder) {
        return response()->json(['error' => 'Work order not found'], 404);
    }

    // Backdate to 4 days ago so it exceeds the 3-day threshold
    $workOrder->update(['updated_at' => now()->subDays(4)]);

    // Mark all ready_for_pickup notifications as sent
    \App\Models\Notification::where('work_order_id', $id)
        ->where('event', 'ready_for_pickup')
        ->update(['status' => 'sent']);

    // Run the reminder command with --days=0 so it catches our backdated order
    try {
        \Illuminate\Support\Facades\Artisan::call('pickup:remind', ['--days' => 0]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return response()->json(['status' => 'reminder_simulated', 'artisan_output' => $output]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
});

Route::get('/__e2e/seed-plans', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'PlansSeeder', '--force' => true]);
        return response()->json(['status' => 'plans_seeded', 'output' => \Illuminate\Support\Facades\Artisan::output()]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
});
