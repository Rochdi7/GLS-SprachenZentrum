<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

/**
 * mcamara/laravel-localization issues 302 redirects when sending /about → /fr/about.
 * Google treats 302 as temporary and deprioritizes the target. For SEO we need 301.
 *
 * This middleware promotes any 302 redirect on a GET/HEAD request whose Location
 * points to a localized URL on the same host to a 301.
 */
class ConvertLocaleRedirectsTo301
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $request->isMethodSafe()) {
            return $response;
        }

        if ($response->getStatusCode() !== 302) {
            return $response;
        }

        $location = $response->headers->get('Location');
        if (! $location) {
            return $response;
        }

        $supported = ['fr', 'en', 'de', 'ar'];
        $targetPath = parse_url($location, PHP_URL_PATH) ?? '';
        $firstSegment = explode('/', ltrim($targetPath, '/'))[0] ?? '';

        if (! in_array($firstSegment, $supported, true)) {
            return $response;
        }

        $response->setStatusCode(301);

        return $response;
    }
}
