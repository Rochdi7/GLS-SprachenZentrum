<?php

/*
|--------------------------------------------------------------------------
| CRM (Homeschool External API) Configuration
|--------------------------------------------------------------------------
|
| Settings for the third-party Homeschool CRM that GLS consumes through the
| External API v1. Keep this isolated from the local CRUD layer — nothing in
| this file should ever leak into Eloquent models or the backoffice DB.
|
*/

return [

    'base_url' => rtrim(env('CRM_API_BASE_URL', 'https://wimschool-test.wimsaas.com/school-service'), '/'),

    'token' => env('CRM_API_TOKEN'),

    'timeout' => (int) env('CRM_API_TIMEOUT', 45),

    'connect_timeout' => (int) env('CRM_API_CONNECT_TIMEOUT', 5),

    /*
    | TLS certificate verification for the CRM endpoint.
    |
    | Defaults to true (secure). Set CRM_API_VERIFY_SSL=false in .env for local
    | dev on Windows when PHP has no CA bundle configured and cURL fails with
    | "self-signed certificate in certificate chain". NEVER ship this as false
    | to production.
    */
    'verify_ssl' => filter_var(env('CRM_API_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN),

    'retry' => [
        'times' => (int) env('CRM_API_RETRY_TIMES', 2),
        'sleep_ms' => (int) env('CRM_API_RETRY_SLEEP', 250),
    ],

    'logging' => [
        'enabled' => (bool) env('CRM_API_LOG', true),
        'channel' => env('CRM_API_LOG_CHANNEL', 'stack'),
    ],
];
