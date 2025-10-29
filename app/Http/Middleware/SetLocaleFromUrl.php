<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocaleFromUrl
{
    public function handle($request, Closure $next)
    {
        $locale = $request->route('locale');
        if (in_array($locale, ['en', 'de'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
