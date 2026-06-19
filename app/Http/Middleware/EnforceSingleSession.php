<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceSingleSession
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (Auth::check()) {
            $user = Auth::user();
            $sessionToken = $request->session()->get('session_token');

            if ($user->session_token !== $sessionToken) {
                // Don't kick — redirect to conflict page so user can decide
                if ($request->routeIs('session.conflict') || $request->routeIs('session.keep') || $request->routeIs('session.logout')) {
                    return $next($request);
                }

                return redirect()->route('session.conflict');
            }
        }

        return $next($request);
    }
}
