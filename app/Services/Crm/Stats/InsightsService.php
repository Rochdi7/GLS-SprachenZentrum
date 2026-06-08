<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmPaymentSnapshot;
use App\Models\Site;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmException;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Aggregated insight calculations for the CRM business pages.
 *
 * Reads from:
 *   - The local crm_payment_snapshots table (fast, daily-fresh)
 *   - The Wimschool CRM API for live registrations / classes data (15-min cache)
 *
 * Each public method is a single Blade-friendly DTO (associative array).
 */
class InsightsService
{
    public const CACHE_TTL = 900; // 15 minutes for live API calls

    public function __construct(protected Crm $crm)
    {
    }

    /* =================================================================
     * 1. CASH-HANDLER DASHBOARD
     * =================================================================
     * Per-employee weekly breakdown built from the local snapshot table.
     * No API hit needed once the snapshot has run.
     */
    public function cashHandlers(?int $strStoreId = null, ?Carbon $weekStart = null): array
    {
        $weekStart ??= Carbon::now()->startOfWeek();
        $weekEnd     = $weekStart->copy()->endOfWeek();

        // Filter by date_creation_date (when cashier recorded payment) and Réglement only.
        // snapshot_date is the nightly copy date — not the collection date.
        $base = CrmPaymentSnapshot::query()
            ->whereBetween('date_creation_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->where('payment_type_id', 1)
            ->whereNotNull('user_creation_full_name')
            ->whereRaw('snapshot_date = (
                SELECT MAX(s2.snapshot_date)
                FROM crm_payment_snapshots s2
                WHERE s2.crm_payment_id = crm_payment_snapshots.crm_payment_id
            )')
            ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId));

        // Per-employee aggregation
        $rows = (clone $base)
            ->selectRaw('
                user_creation_full_name as handler,
                user_creation_id        as handler_id,
                COUNT(*)                as total_payments,
                SUM(amount)             as total_amount,
                AVG(amount)             as avg_amount,
                SUM(CASE WHEN payment_method_id = 1 THEN 1 ELSE 0 END) as cash_count,
                SUM(CASE WHEN payment_method_id = 1 THEN amount ELSE 0 END) as cash_amount,
                SUM(CASE WHEN payment_method_id = 2 THEN 1 ELSE 0 END) as cheque_count,
                SUM(CASE WHEN payment_method_id = 2 THEN amount ELSE 0 END) as cheque_amount,
                SUM(CASE WHEN payment_method_id = 3 THEN 1 ELSE 0 END) as transfer_count,
                SUM(CASE WHEN payment_method_id = 3 THEN amount ELSE 0 END) as transfer_amount,
                SUM(CASE WHEN payment_method_id NOT IN (1,2,3) THEN 1 ELSE 0 END) as other_count,
                MIN(date_creation) as first_recorded,
                MAX(date_creation) as last_recorded
            ')
            ->groupBy('user_creation_id', 'user_creation_full_name')
            ->orderByDesc('total_amount')
            ->get();

        // Outlier flag: anyone whose total_amount is > mean + 2*stddev
        $amounts = $rows->pluck('total_amount')->map(fn ($v) => (float) $v)->toArray();
        $mean = count($amounts) ? array_sum($amounts) / count($amounts) : 0;
        $variance = count($amounts) ? array_sum(array_map(fn ($x) => ($x - $mean) ** 2, $amounts)) / count($amounts) : 0;
        $stddev = sqrt($variance);
        $threshold = $mean + 2 * $stddev;

        $rows->each(function ($r) use ($threshold) {
            $r->is_outlier = $stddev_threshold = $r->total_amount > $threshold && $threshold > 0;
            $r->cash_pct   = $r->total_payments > 0 ? round($r->cash_count    / $r->total_payments * 100) : 0;
            $r->cheque_pct = $r->total_payments > 0 ? round($r->cheque_count  / $r->total_payments * 100) : 0;
            $r->transfer_pct = $r->total_payments > 0 ? round($r->transfer_count / $r->total_payments * 100) : 0;
        });

        // Hour-of-day distribution for the whole window (used in the chart)
        $hourly = (clone $base)
            ->selectRaw('HOUR(date_creation) as hour, COUNT(*) as n')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('n', 'hour')
            ->toArray();
        $hourBuckets = [];
        for ($h = 0; $h < 24; $h++) $hourBuckets[$h] = $hourly[$h] ?? 0;

        return [
            'week_start' => $weekStart->toDateString(),
            'week_end'   => $weekEnd->toDateString(),
            'rows'       => $rows,
            'totals'     => [
                'payments' => (int) $rows->sum('total_payments'),
                'amount'   => (float) $rows->sum('total_amount'),
                'handlers' => $rows->count(),
            ],
            'hourly'     => $hourBuckets,
            'mean'       => $mean,
            'threshold'  => $threshold,
        ];
    }

    /* =================================================================
     * 2. DAILY RECONCILIATION REPORT
     * =================================================================
     * For each center × day in the window: total cash / cheque / transfer.
     * Z-score per center signals anomalous days.
     */
    public function reconciliation(int $days = 14): array
    {
        $start = Carbon::today()->subDays($days - 1);

        // Group by date_creation_date (when cashier recorded the payment), Réglement only.
        // Use latest snapshot per payment to avoid double-counting nightly copies.
        $raw = CrmPaymentSnapshot::query()
            ->whereBetween('date_creation_date', [$start->toDateString(), Carbon::today()->toDateString()])
            ->where('payment_type_id', 1)
            ->whereRaw('snapshot_date = (
                SELECT MAX(s2.snapshot_date)
                FROM crm_payment_snapshots s2
                WHERE s2.crm_payment_id = crm_payment_snapshots.crm_payment_id
            )')
            ->selectRaw('
                date_creation_date as snapshot_date,
                crm_store_id,
                SUM(CASE WHEN payment_method_id = 1 THEN amount ELSE 0 END) as cash,
                SUM(CASE WHEN payment_method_id = 2 THEN amount ELSE 0 END) as cheque,
                SUM(CASE WHEN payment_method_id = 3 THEN amount ELSE 0 END) as transfer,
                SUM(amount) as total,
                COUNT(*) as n
            ')
            ->groupBy('date_creation_date', 'crm_store_id')
            ->orderBy('date_creation_date')
            ->get();

        $sites = Site::whereNotNull('crm_store_id')->orderBy('name')->get()->keyBy('crm_store_id');

        // Build a per-center series and compute z-score per center
        $byCenter = $raw->groupBy('crm_store_id');
        $series = [];
        foreach ($byCenter as $storeId => $rows) {
            $site = $sites[$storeId] ?? null;
            $totals = $rows->pluck('total')->map(fn ($v) => (float) $v)->all();
            $mean = count($totals) ? array_sum($totals) / count($totals) : 0;
            $std  = count($totals) > 1
                ? sqrt(array_sum(array_map(fn ($x) => ($x - $mean) ** 2, $totals)) / count($totals))
                : 0;

            $rows->each(function ($r) use ($mean, $std) {
                $r->z = $std > 0 ? round(((float) $r->total - $mean) / $std, 2) : 0;
                $r->is_anomaly = abs($r->z) > 2;
            });

            $series[] = [
                'site'  => $site,
                'rows'  => $rows->values(),
                'mean'  => $mean,
                'std'   => $std,
            ];
        }

        // Sort centers by name for stable display
        usort($series, fn ($a, $b) => strcmp($a['site']?->name ?? '', $b['site']?->name ?? ''));

        return [
            'start_date' => $start->toDateString(),
            'end_date'   => Carbon::today()->toDateString(),
            'days'       => $days,
            'series'     => $series,
        ];
    }

    /* =================================================================
     * 3. RETENTION FUNNEL
     * =================================================================
     * Cohorts by start month. For each cohort show:
     *   - Total registrations
     *   - Status-based: active / archived / cancelled / suspended
     *   - Date-based: passed end date / mid-formation
     *
     * Pulls live from the API (cached 15 min).
     */
    public function retention(int $monthsBack = 8, ?int $strStoreId = null): array
    {
        $cacheKey = 'crm.insights.retention:' . $monthsBack . ':' . ($strStoreId ?: 'all');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($monthsBack, $strStoreId) {
            $start = Carbon::now()->subMonths($monthsBack - 1)->startOfMonth();

            try {
                $all = $this->fetchAllRegistrations($strStoreId, $start);
            } catch (CrmException $e) {
                return ['error' => $e->getMessage(), 'cohorts' => []];
            }

            // Bucket by START_DATE month
            $cohorts = [];
            foreach ($all as $r) {
                $startDate = $r['START_DATE'] ?? null;
                if (!$startDate) continue;
                try { $month = Carbon::parse($startDate)->format('Y-m'); }
                catch (\Throwable $e) { continue; }

                $cohorts[$month] ??= [
                    'month'         => $month,
                    'total'         => 0,
                    'active'        => 0,
                    'archived'      => 0,
                    'cancelled'     => 0,
                    'suspended'     => 0,
                    'reached_end'   => 0,
                    'still_running' => 0,
                ];

                $cohorts[$month]['total']++;
                $statusId = (int) ($r['REGISTRATION_STATUS_ID'] ?? 0);
                if ($statusId === 8)        $cohorts[$month]['active']++;
                elseif ($statusId === 11)   $cohorts[$month]['archived']++;
                elseif ($statusId === 10)   $cohorts[$month]['cancelled']++;
                elseif ($statusId === 9)    $cohorts[$month]['suspended']++;

                // Date-based: END_DATE in the past?
                try {
                    $end = $r['END_DATE'] ? Carbon::parse($r['END_DATE']) : null;
                    if ($end && $end->isPast()) $cohorts[$month]['reached_end']++;
                    elseif ($end)               $cohorts[$month]['still_running']++;
                } catch (\Throwable $e) {}
            }

            // Compute retention % per cohort
            ksort($cohorts);
            foreach ($cohorts as &$c) {
                $c['retention_status_pct'] = $c['total'] > 0
                    ? round(($c['active'] + $c['archived']) / $c['total'] * 100)
                    : 0;
                $c['retention_date_pct'] = $c['total'] > 0
                    ? round($c['reached_end'] / $c['total'] * 100)
                    : 0;
                $c['dropout_pct'] = $c['total'] > 0
                    ? round(($c['cancelled'] + $c['suspended']) / $c['total'] * 100)
                    : 0;
            }
            unset($c);

            return ['cohorts' => array_values($cohorts), 'error' => null];
        });
    }

    /* =================================================================
     * 4. REVENUE FORECAST
     * =================================================================
     * For each center, look at active registrations and the SERVICE_LIST
     * (when available on classes) to project next 6 months of revenue.
     *
     * Falls back to "active count × average monthly fee" when service list isn't available.
     */
    public function forecast(int $months = 6, ?int $strStoreId = null): array
    {
        $cacheKey = 'crm.insights.forecast:' . $months . ':' . ($strStoreId ?: 'all');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($months, $strStoreId) {
            $sites = Site::whereNotNull('crm_store_id')
                ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId))
                ->orderBy('name')
                ->get();

            // Build the month buckets going forward
            $buckets = [];
            $cursor = Carbon::now()->startOfMonth();
            for ($i = 0; $i < $months; $i++) {
                $buckets[] = [
                    'label' => $cursor->format('Y-m'),
                    'start' => $cursor->copy()->startOfMonth(),
                    'end'   => $cursor->copy()->endOfMonth(),
                ];
                $cursor->addMonth();
            }

            // For "actual" revenue per month, look at the snapshot table
            $actuals = DB::table('crm_payment_snapshots')
                ->selectRaw('
                    crm_store_id,
                    DATE_FORMAT(date_creation_date, "%Y-%m") as month_label,
                    SUM(amount) as total
                ')
                ->whereDate('snapshot_date', Carbon::today())
                ->whereNotNull('date_creation_date')
                ->groupBy('crm_store_id', 'month_label')
                ->get()
                ->groupBy('crm_store_id');

            $series = [];
            foreach ($sites as $site) {
                $siteActuals = ($actuals[$site->crm_store_id] ?? collect())->keyBy('month_label');

                // Average of the last 3 closed months as forecast baseline
                $closedMonths = [];
                for ($i = 1; $i <= 3; $i++) {
                    $m = Carbon::now()->subMonths($i)->format('Y-m');
                    $closedMonths[] = (float) ($siteActuals[$m]->total ?? 0);
                }
                $baseline = count(array_filter($closedMonths)) > 0
                    ? array_sum($closedMonths) / count(array_filter($closedMonths))
                    : 0;

                $monthlyValues = [];
                foreach ($buckets as $b) {
                    $actual = (float) ($siteActuals[$b['label']]->total ?? 0);
                    $monthlyValues[] = [
                        'month'    => $b['label'],
                        'actual'   => $actual,
                        'forecast' => $baseline,
                        'is_past'  => $b['end']->isPast(),
                    ];
                }

                $series[] = [
                    'site'      => $site,
                    'baseline'  => $baseline,
                    'months'    => $monthlyValues,
                    'total_forecast' => array_sum(array_map(fn ($m) => $m['forecast'], $monthlyValues)),
                    'total_actual'   => array_sum(array_map(fn ($m) => $m['actual'],   $monthlyValues)),
                ];
            }

            return [
                'months' => array_column($buckets, 'label'),
                'series' => $series,
            ];
        });
    }

    /* =================================================================
     * Helpers
     * =================================================================
     */

    /**
     * Pull every registration matching the filter into memory (paginated).
     * Uses 15-min cache to absorb repeated retention/forecast renders.
     *
     * @return array<int, array<string,mixed>>
     */
    protected function fetchAllRegistrations(?int $strStoreId, Carbon $startDate): array
    {
        $all = [];
        $page = 0;

        do {
            $resp = $this->crm->registrations()->list(
                page: $page,
                size: 100,
                strStoreId: $strStoreId,
                startDate: $startDate->toDateString(),
                endDate: Carbon::now()->endOfMonth()->toDateString(),
            );
            $batch = $resp['data'] ?? [];
            $all = array_merge($all, $batch);
            $hasNext = $resp['pagination']['hasNext'] ?? $resp['pagination']['hasMore'] ?? false;
            $page++;
        } while ($hasNext && $page < 100);

        return $all;
    }
}
