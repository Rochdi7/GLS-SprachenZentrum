<?php

/*
|--------------------------------------------------------------------------
| Hikvision Third-Party Partner Account Configuration
|--------------------------------------------------------------------------
|
| The Hikvision module is intentionally isolated from CRM, payroll and the
| rest of the employee domain. Credentials are loaded from environment
| variables only and consumed through app/Services/Hikvision.
|
*/

return [
    'base_url' => rtrim((string) env('HIKVISION_BASE_URL', ''), '/'),
    'api_key' => env('HIKVISION_API_KEY'),
    'api_secret' => env('HIKVISION_API_SECRET'),
    'timeout' => 15,
];
