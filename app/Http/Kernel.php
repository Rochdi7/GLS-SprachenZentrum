<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * GLOBAL MIDDLEWARE
     * Executed for EVERY request
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\CacheWarmupHandler::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * MIDDLEWARE GROUPS
     */
    protected $middlewareGroups = [
        'web' => [\App\Http\Middleware\EncryptCookies::class, \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, \Illuminate\Session\Middleware\StartSession::class, \Illuminate\View\Middleware\ShareErrorsFromSession::class, \App\Http\Middleware\VerifyCsrfToken::class, \Illuminate\Routing\Middleware\SubstituteBindings::class],

        'api' => [\Illuminate\Routing\Middleware\ThrottleRequests::class . ':api', \Illuminate\Routing\Middleware\SubstituteBindings::class],
    ];

    /**
     * ROUTE MIDDLEWARE ALIASES
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // ⭐ Localization (necessaire pour /fr /ar)
        'localizationRedirect' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
        'localeSessionRedirect' => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
        'localeViewPath' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
        'localize' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,

        // Custom
        'locale.url' => \App\Http\Middleware\SetLocaleFromUrl::class,
    ];
}
