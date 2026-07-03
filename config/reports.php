<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Automatic report sending
    |--------------------------------------------------------------------------
    | Set REPORTS_AUTO_SEND_ENABLED=true in .env to activate the scheduler.
    */
    'auto_send_enabled' => (bool) env('REPORTS_AUTO_SEND_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Recipients
    |--------------------------------------------------------------------------
    | Comma-separated list of email addresses for automatic reports.
    | In test mode only REPORTS_TEST_EMAIL receives mails.
    */
    'test_mode'  => (bool) env('REPORTS_TEST_MODE', true),
    'test_email' => env('REPORTS_TEST_EMAIL', 'rochdi.karouali1234@gmail.com'),
    'recipients' => array_filter(array_map('trim', explode(',', env('REPORTS_RECIPIENTS', '')))),

    /*
    |--------------------------------------------------------------------------
    | Scheduling
    |--------------------------------------------------------------------------
    | weeklyOn() day: 1=Monday … 5=Friday … 7=Sunday
    */
    'weekly_send_day'  => (int) env('REPORTS_WEEKLY_SEND_DAY', 5),   // Friday
    'weekly_send_time' => env('REPORTS_WEEKLY_SEND_TIME', '00:00'),
    'monthly_send_day'  => (int) env('REPORTS_MONTHLY_SEND_DAY', 1),   // 1st of month
    'monthly_send_time' => env('REPORTS_MONTHLY_SEND_TIME', '00:00'),
    'timezone'         => env('REPORTS_TIMEZONE', 'Africa/Casablanca'),

    /*
    |--------------------------------------------------------------------------
    | Period constraints
    |--------------------------------------------------------------------------
    | Maximum number of days for any custom date range.
    */
    'max_period_days' => (int) env('REPORTS_MAX_PERIOD_DAYS', 31),

];
