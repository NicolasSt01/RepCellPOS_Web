<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (!$tenant) {
            abort(403, 'Tenant no encontrado.');
        }

        if (!$tenant->hasFeature($feature)) {
            return redirect()->route('subscription.upgrade')
                ->with('error', 'Tu plan no incluye esta funcionalidad. Actualiza tu plan para acceder.');
        }

        return $next($request);
    }
}
