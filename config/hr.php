<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Contracted weekly target (minutes)
    |--------------------------------------------------------------------------
    |
    | Used by the personal planning dashboard to compute overtime
    | (heures supplémentaires). Set to 0 to disable the overtime KPI
    | entirely — when 0, no overtime card is shown and nothing is invented.
    |
    | Example: 45h/week → 45 * 60 = 2700.
    |
    */
    'weekly_target_minutes' => (int) env('HR_WEEKLY_TARGET_MINUTES', 0),

];
