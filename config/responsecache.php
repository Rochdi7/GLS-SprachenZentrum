<?php

use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;
use Spatie\ResponseCache\Hasher\DefaultHasher;
use Spatie\ResponseCache\Serializers\DefaultSerializer;
use Spatie\ResponseCache\Replacers\CsrfTokenReplacer;

return [

    /*
     * Enable / disable response cache globally
     */
    'enabled' => env('RESPONSE_CACHE_ENABLED', true),

    /*
     * Cache profile
     * → Cache uniquement les requêtes GET réussies (frontoffice)
     */
    'cache_profile' => CacheAllSuccessfulGetRequests::class,

    /*
     * Optional cache bypass header (useful for debugging)
     */
    'cache_bypass_header' => [
        'name' => env('CACHE_BYPASS_HEADER_NAME', null),
        'value' => env('CACHE_BYPASS_HEADER_VALUE', null),
    ],

    /*
     * Cache lifetime (default: 24h)
     */
    'cache_lifetime_in_seconds' => (int) env(
        'RESPONSE_CACHE_LIFETIME',
        60 * 60 * 24
    ),

    /*
     * Add cache time header (debug only)
     */
    'add_cache_time_header' => env('APP_DEBUG', false),

    /*
     * Cache time header name
     */
    'cache_time_header_name' => env(
        'RESPONSE_CACHE_HEADER_NAME',
        'x-response-cache-time'
    ),

    /*
     * Add cache age header (only if cache time header is enabled)
     */
    'add_cache_age_header' => env(
        'RESPONSE_CACHE_AGE_HEADER',
        false
    ),

    /*
     * Cache age header name
     */
    'cache_age_header_name' => env(
        'RESPONSE_CACHE_AGE_HEADER_NAME',
        'x-response-cache-age'
    ),

    /*
     * Cache store (file / redis recommended in prod)
     */
    'cache_store' => env(
        'RESPONSE_CACHE_DRIVER',
        'file'
    ),

    /*
     * Response replacers
     * → Important for CSRF tokens
     */
    'replacers' => [
        CsrfTokenReplacer::class,
    ],

    /*
     * Cache tag
     * → Useful for selective clearing
     */
    // 'cache_tag' => 'gls-response-cache',

    /*
     * Request hasher
     */
    'hasher' => DefaultHasher::class,

    /*
     * Response serializer
     */
    'serializer' => DefaultSerializer::class,
];
