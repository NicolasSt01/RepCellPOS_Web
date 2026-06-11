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
