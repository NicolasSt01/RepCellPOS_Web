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
