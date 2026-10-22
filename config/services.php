<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'semrush' => [
        'key' => env('SEMRUSH_API_KEY'),
        'database' => env('SEMRUSH_DATABASE', 'fr'),
        // Persistent cache for identical reports. 0 disables. --fresh bypasses.
        'cache_ttl_hours' => (int) env('SEMRUSH_CACHE_TTL_HOURS', 24),
        // Per-run safeguards (one artisan invocation = one run). Not Semrush pricing.
        'budget_units' => (int) env('SEMRUSH_BUDGET_UNITS', 1000),
        'max_calls' => (int) env('SEMRUSH_MAX_CALLS', 10),
        // JSONL log of every live call: report, db, rows, units. Never credentials.
        'usage_log' => env('SEMRUSH_USAGE_LOG', storage_path('app/seo/semrush-usage.jsonl')),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
