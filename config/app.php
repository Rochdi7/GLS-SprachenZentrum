<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    'name' => env('APP_NAME', 'Laravel'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL'),

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | DEFAULT LANGUAGE = FRENCH
    |
    */
    'locale' => 'fr',

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | FALLBACK LANGUAGE = FRENCH
    |
    */
    'fallback_locale' => 'fr',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    */
    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    */
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',

    'maintenance' => [
        'driver' => 'file',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    */
    'providers' => ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    */
    'aliases' => Facade::defaultAliases()->merge([])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Supported Locales (YOUR APP LANGUAGES)
    |--------------------------------------------------------------------------
    |
    | ONLY FRENCH AND ARABIC are supported.
    | REMOVE EN to avoid Localizator exception.
    |
    */
    'supported_locales' => ['fr', 'ar'],

];
