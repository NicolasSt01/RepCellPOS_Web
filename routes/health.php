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
    return response()->json(['status' => 'cleaned']);
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
