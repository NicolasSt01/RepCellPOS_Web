<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantSubscription
{
    protected function isAdmin(User $user): bool
    {
        return $user->hasRole('admin_tenant');
    }

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

        if ($user->email_verified_at === null && $user->created_at->diffInDays(now()) >= 2) {
            auth()->logout();
            $request->session()->invalidate();
            return redirect()->route('login')->withErrors([
                'email' => 'Tu correo electrónico no ha sido verificado dentro del plazo de 2 días. Contacta al administrador para reactivar tu cuenta.',
            ]);
        }

        $expiredRedirect = function () use ($user, $tenant) {
            if ($this->isAdmin($user)) {
                return redirect()->route('subscription.upgrade');
            }
            return redirect()->route('subscription.suspended');
        };

        if ($tenant->subscription_status === 'trial') {
            if ($tenant->trial_ends_at && now()->gt($tenant->trial_ends_at)) {
                $tenant->update(['subscription_status' => 'expired']);
                return $expiredRedirect()->with('error', 'Tu período de prueba ha terminado.');
            }
            return $next($request);
        }

        if ($tenant->subscription_status === 'active') {
            if ($tenant->subscription && $tenant->subscription->end_date && now()->gt($tenant->subscription->end_date)) {
                $tenant->update(['subscription_status' => 'expired']);
                return $expiredRedirect()->with('error', 'Tu suscripción ha expirado.');
            }
            return $next($request);
        }

        return $expiredRedirect()->with('error', 'Tu suscripción no está activa.');
    }
}
