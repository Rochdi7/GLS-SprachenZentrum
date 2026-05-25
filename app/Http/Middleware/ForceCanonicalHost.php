<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects to the canonical host from config (APP_URL / SEO_CANONICAL_HOST).
 * Prevents www/non-www duplicate content without affecting local dev.
 */
class ForceCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production')) {
            return $next($request);
        }

        $canonicalHost = config('seo.canonical_host');

        if (! $canonicalHost || $request->getHost() === $canonicalHost) {
            return $next($request);
        }

        $scheme = config('seo.force_https', true) ? 'https' : $request->getScheme();
        $target = $scheme.'://'.$canonicalHost.$request->getRequestUri();

        return redirect()->to($target, 301);
    }
}
