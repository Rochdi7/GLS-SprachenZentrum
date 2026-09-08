<?php

return [
    // reCAPTCHA v3 (score-based, invisible — no design impact)
    'enabled'    => env('RECAPTCHA_ENABLED', true),
    'site_key'   => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),

    // Minimum score to accept without suspicion (0.0 = bot, 1.0 = human).
    // 0.5 is Google's default. Submissions below this are NOT refused — they are
    // accepted and flagged (recaptcha_suspicious) so no real student is lost.
    'min_score'  => (float) env('RECAPTCHA_MIN_SCORE', 0.5),

    // Hard floor: only submissions at or below this score are actually refused.
    // Google returns exactly 0.0 for traffic it is confident is automated, so the
    // default blocks bots while letting low-but-plausible humans through.
    'reject_below' => (float) env('RECAPTCHA_REJECT_BELOW', 0.0),

    // If Google is unreachable / times out, let the submission through (true)
    // so a Google outage never blocks real students.
    'fail_open'  => env('RECAPTCHA_FAIL_OPEN', true),

    'timeout'    => (int) env('RECAPTCHA_TIMEOUT', 5),

    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
];
