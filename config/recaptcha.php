<?php

return [
    // reCAPTCHA v3 (score-based, invisible — no design impact)
    'enabled'    => env('RECAPTCHA_ENABLED', true),
    'site_key'   => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),

    // Minimum score to accept (0.0 = bot, 1.0 = human). 0.5 is Google's default.
    'min_score'  => (float) env('RECAPTCHA_MIN_SCORE', 0.5),

    // If Google is unreachable / times out, let the submission through (true)
    // so a Google outage never blocks real students.
    'fail_open'  => env('RECAPTCHA_FAIL_OPEN', true),

    'timeout'    => (int) env('RECAPTCHA_TIMEOUT', 5),

    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
];
