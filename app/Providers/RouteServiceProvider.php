<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Set up rate limiting for API requests
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Define routes for API and web
        $this->routes(function () {
            // Sitemap: no web/session middleware — must be application/xml, not text/html
            Route::get('/sitemap.xml', function () {
                $path = public_path('sitemap.xml');

                if (! is_readable($path)) {
                    abort(404, 'Sitemap not found.');
                }

                return response(
                    file_get_contents($path),
                    200,
                    [
                        'Content-Type' => 'application/xml; charset=UTF-8',
                        'Cache-Control' => 'public, max-age=3600',
                    ]
                );
            })->name('sitemap');

            // API routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

}
