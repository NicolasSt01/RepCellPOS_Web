<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $sessionToken = session('session_token');
        $dbToken = $user->session_token;

        // If both null, this is a first-time setup — allow and store a token
        if (!$sessionToken && !$dbToken) {
            $newToken = \Illuminate\Support\Str::random(60);
            $user->update(['session_token' => $newToken]);
            session(['session_token' => $newToken]);
            return $next($request);
        }

        if (!$sessionToken || $sessionToken !== $dbToken) {
            auth()->logout();
            $request->session()->flash('session_expired', 'Tu sesión fue cerrada porque iniciaste sesión en otro dispositivo.');

            return redirect()->route('login');
        }

        return $next($request);
    }
}
