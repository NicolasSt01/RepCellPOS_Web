<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->isSuperAdmin()) {
            app()->instance('current_tenant_id', $request->user()->tenant_id);
        }

        return $next($request);
    }
}
