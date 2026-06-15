<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (!$tenant) {
            abort(403, 'Tenant no encontrado.');
        }

        if (!$tenant->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ]);
        }

        if ($tenant->subscription_status === 'trial') {
            if ($tenant->trial_ends_at && now()->gt($tenant->trial_ends_at)) {
                $tenant->update(['subscription_status' => 'expired']);
                return redirect()->route('subscription.upgrade')
                    ->with('error', 'Tu período de prueba ha terminado. Elige un plan para continuar.');
            }
            return $next($request);
        }

        if ($tenant->subscription_status === 'active') {
            return $next($request);
        }

        return redirect()->route('subscription.upgrade')
            ->with('error', 'Tu suscripción no está activa. Elige un plan para continuar.');
    }
}
