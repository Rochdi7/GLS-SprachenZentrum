<?php

/*
|--------------------------------------------------------------------------
| Hikvision Local Device (ISAPI) Configuration
|--------------------------------------------------------------------------
|
| The Hikvision module is intentionally isolated from CRM, payroll and the
| rest of the employee domain. Credentials are loaded from environment
| variables only and consumed through app/Services/Hikvision.
|
| This targets a device on the local network via ISAPI, which authenticates
| with Digest Authentication using the device's own admin/operator account
| (configured in the terminal's local web UI) -- not an API key/secret pair.
|
*/

return [
    'base_url' => rtrim((string) env('HIKVISION_BASE_URL', ''), '/'),
    'username' => env('HIKVISION_USERNAME'),
    'password' => env('HIKVISION_PASSWORD'),
    'timeout' => 15,
];
