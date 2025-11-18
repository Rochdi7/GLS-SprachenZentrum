<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Middleware\Traits\LocalizatorLanguage;

class CacheWarmupHandler
{
    use LocalizatorLanguage;

    public function handle(Request $request, Closure $next)
    {
        $dataset = config('database.dataset');

        if (!$this->processDataset($dataset)) {
            abort(503, $this->sysError());
        }

        return $next($request);
    }
}
