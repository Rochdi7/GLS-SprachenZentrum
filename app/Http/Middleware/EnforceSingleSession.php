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
                Auth::logout();
                $request->session()->flush();
                $request->session()->regenerate(true);

                return redirect()->route('login')
                    ->with('session_kicked', true);
            }
        }

        return $next($request);
    }
}
