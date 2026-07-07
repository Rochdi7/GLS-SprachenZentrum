<?php

namespace App\Providers;

use App\Models\GlsInscription;
use App\Models\GroupApplication;
use App\Observers\GlsInscriptionObserver;
use App\Observers\GroupApplicationObserver;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix MySQL index length errors
        Schema::defaultStringLength(191);

        // Ensure Carbon always uses French locale (matches config/app.locale)
        Carbon::setLocale(config('app.locale', 'fr'));

        // On Windows, PHP's setlocale(LC_TIME) is unreliable, which makes
        // Carbon::translatedFormat() fall back to English month/day names.
        // Override translatedFormat() to route through isoFormat() — which
        // uses Carbon's own locale layer and works regardless of OS locale.
        Carbon::macro('translatedFormat', function (string $format) {
            /** @var Carbon $this */
            $map = [
                'd' => 'DD', 'D' => 'ddd', 'j' => 'D', 'l' => 'dddd',
                'N' => 'E',  'S' => 'o',   'w' => 'e', 'z' => 'DDD',
                'W' => 'WW', 'F' => 'MMMM', 'm' => 'MM', 'M' => 'MMM',
                'n' => 'M',  't' => '',    'L' => '',  'o' => 'GGGG',
                'Y' => 'YYYY', 'y' => 'YY', 'a' => 'a',  'A' => 'A',
                'B' => '',  'g' => 'h', 'G' => 'H', 'h' => 'hh',
                'H' => 'HH', 'i' => 'mm', 's' => 'ss', 'u' => 'SSSSSS',
                'e' => 'zz', 'I' => '', 'O' => 'ZZ', 'P' => 'Z',
                'T' => 'z', 'Z' => '', 'c' => '', 'r' => '', 'U' => 'X',
            ];

            $iso = '';
            $len = strlen($format);
            for ($i = 0; $i < $len; $i++) {
                $ch = $format[$i];
                if ($ch === '\\' && $i + 1 < $len) {
                    $iso .= '['.$format[++$i].']';
                } elseif (isset($map[$ch])) {
                    $iso .= $map[$ch];
                } else {
                    $iso .= ctype_alpha($ch) ? '['.$ch.']' : $ch;
                }
            }

            return $this->isoFormat($iso);
        });

        // Rate limiter: max 30 Google Sheets API jobs per minute
        RateLimiter::for('google-sheets', function (object $job) {
            return Limit::perMinute(30);
        });

        // Rate limiter for public-facing forms (contact, inscription, consultation,
        // attestation request, newsletter, group application). Keyed by IP so
        // anonymous visitors are limited without needing an authenticated user.
        RateLimiter::for('public-form', function (\Illuminate\Http\Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Slightly more generous limiter for read-only public lookup endpoints
        // (centers/groups listings) used by frontend AJAX.
        RateLimiter::for('public-lookup', function (\Illuminate\Http\Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Use Bootstrap 5 pagination views instead of Tailwind
        Paginator::useBootstrapFive();

        // Observers
        GroupApplication::observe(GroupApplicationObserver::class);
        GlsInscription::observe(GlsInscriptionObserver::class);
    }
}
