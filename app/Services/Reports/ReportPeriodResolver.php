<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Resolves and validates report date periods.
 *
 * Rules:
 *  - Any date range must not exceed config('reports.max_period_days') (default 31).
 *  - Weekly automatic periods run Monday 00:00 → Friday 23:59 of the current week.
 *  - Monthly periods are clamped to one calendar month.
 */
class ReportPeriodResolver
{
    public function __construct(
        protected int $maxDays,
        protected string $timezone,
    ) {}

    public static function make(): static
    {
        return new static(
            maxDays:  config('reports.max_period_days', 31),
            timezone: config('reports.timezone', 'Africa/Casablanca'),
        );
    }

    /**
     * Return the current-week period: Monday 00:00 → Friday 23:59 (Casablanca).
     */
    public function currentWeek(): array
    {
        $now  = Carbon::now($this->timezone);
        $from = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $to   = $now->copy()->startOfWeek(Carbon::MONDAY)->addDays(4)->endOfDay(); // Friday

        return ['from' => $from, 'to' => $to];
    }

    /**
     * Return the last completed calendar month period.
     */
    public function lastMonth(): array
    {
        $now  = Carbon::now($this->timezone);
        $from = $now->copy()->subMonthNoOverflow()->startOfMonth()->startOfDay();
        $to   = $now->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay();

        return ['from' => $from, 'to' => $to];
    }

    /**
     * Validate a custom range. Throws InvalidArgumentException when > max days.
     *
     * @param  string|Carbon  $from
     * @param  string|Carbon  $to
     * @return array{from: Carbon, to: Carbon}
     */
    public function custom(string|Carbon $from, string|Carbon $to): array
    {
        $from = $from instanceof Carbon
            ? $from->copy()->setTimezone($this->timezone)->startOfDay()
            : Carbon::parse($from, $this->timezone)->startOfDay();

        $to = $to instanceof Carbon
            ? $to->copy()->setTimezone($this->timezone)->endOfDay()
            : Carbon::parse($to, $this->timezone)->endOfDay();

        if ($from->greaterThan($to)) {
            throw new InvalidArgumentException('La date de début doit être antérieure à la date de fin.');
        }

        $days = $from->diffInDays($to) + 1;

        if ($days > $this->maxDays) {
            throw new InvalidArgumentException(
                "La période sélectionnée ({$days} jours) dépasse la limite autorisée de {$this->maxDays} jours."
            );
        }

        return ['from' => $from, 'to' => $to];
    }

    /**
     * Validate a single calendar month (year + month integer).
     *
     * @return array{from: Carbon, to: Carbon}
     */
    public function singleMonth(int $year, int $month): array
    {
        $from = Carbon::create($year, $month, 1, 0, 0, 0, $this->timezone)->startOfMonth();
        $to   = $from->copy()->endOfMonth()->endOfDay();

        return ['from' => $from, 'to' => $to];
    }

    /**
     * Format a period as a human-readable label.
     */
    public function label(Carbon $from, Carbon $to): string
    {
        return $from->format('d/m/Y') . ' — ' . $to->format('d/m/Y');
    }
}
