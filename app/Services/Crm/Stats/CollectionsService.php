<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmPaymentSnapshot;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Aggregates payment-collection data from the CRM API into dashboard KPIs,
 * aging buckets, top-debtor rankings and upcoming due-date lists.
 *
 * All heavy API calls are cached 15 minutes.  Local-DB aggregations
 * (performanceByCenter) bypass the API entirely.
 */
class CollectionsService
{
    public function __construct(protected Crm $crm)
    {
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * 8 headline KPI values for the dashboard summary row.
     */
    public function kpis(?int $strStoreId): array
    {
        $key = 'crm.collections.kpis:' . ($strStoreId ?: 'all');

        return Cache::remember($key, 900, function () use ($strStoreId) {
            $today   = Carbon::today();
            $endWeek = $today->copy()->addDays(7)->toDateString();
            $endMonth = $today->copy()->endOfMonth()->toDateString();
            $todayStr = $today->toDateString();

            // Outstanding total — all rows, no due-date filter
            $all = $this->fetchAllCollectionRows($strStoreId);
            $outstandingTotal = array_sum(array_column($all, 'REST_AMOUNT'));

            // Due today
            $dueToday = $this->sumCollectionFilter($strStoreId, $todayStr, $todayStr);

            // Due this week
            $dueWeek = $this->sumCollectionFilter($strStoreId, $todayStr, $endWeek);

            // Due this month
            $dueMonth = $this->sumCollectionFilter($strStoreId, $todayStr, $endMonth);

            // Overdue buckets (past due — dueDateEndDate in the past)
            $overdue7  = $this->sumCollectionFilter($strStoreId, null, $today->copy()->subDays(7)->toDateString());
            $overdue30 = $this->sumCollectionFilter($strStoreId, null, $today->copy()->subDays(30)->toDateString());
            $overdue60 = $this->sumCollectionFilter($strStoreId, null, $today->copy()->subDays(60)->toDateString());
            $overdue90 = $this->sumCollectionFilter($strStoreId, null, $today->copy()->subDays(90)->toDateString());

            return compact(
                'outstandingTotal',
                'dueToday',
                'dueWeek',
                'dueMonth',
                'overdue7',
                'overdue30',
                'overdue60',
                'overdue90',
            );
        });
    }

    /**
     * Top $limit debtors by total REST_AMOUNT.
     */
    public function topDebtors(?int $strStoreId, int $limit = 20): array
    {
        $key = 'crm.collections.top_debtors:' . ($strStoreId ?: 'all') . ':' . $limit;

        return Cache::remember($key, 900, function () use ($strStoreId, $limit) {
            $rows = $this->fetchAllCollectionRows($strStoreId);

            // Group by STUDENT_ID, accumulate REST_AMOUNT and keep latest metadata
            $grouped = [];
            foreach ($rows as $row) {
                $sid = $row['STUDENT_ID'] ?? 'unknown';
                if (!isset($grouped[$sid])) {
                    $grouped[$sid] = [
                        'student_id'   => $sid,
                        'student_name' => $row['STUDENT_FULL_NAME'] ?? '—',
                        'store_name'   => $row['STR_STORE_NAME'] ?? '—',
                        'store_id'     => $row['STR_STORE_ID'] ?? null,
                        'total_owed'   => 0.0,
                        'oldest_due'   => null,
                    ];
                }
                $grouped[$sid]['total_owed'] += (float) ($row['REST_AMOUNT'] ?? 0);

                // Track oldest due date for "days overdue" calc
                $due = $row['DUE_DATE'] ?? null;
                if ($due && ($grouped[$sid]['oldest_due'] === null || $due < $grouped[$sid]['oldest_due'])) {
                    $grouped[$sid]['oldest_due'] = $due;
                }
            }

            // Sort descending by total_owed
            usort($grouped, fn ($a, $b) => $b['total_owed'] <=> $a['total_owed']);

            // Annotate overdue days
            $today = Carbon::today()->toDateString();
            foreach ($grouped as &$d) {
                $d['overdue_days'] = $d['oldest_due'] && $d['oldest_due'] < $today
                    ? Carbon::parse($d['oldest_due'])->diffInDays(Carbon::today())
                    : 0;
            }

            return array_values(array_slice($grouped, 0, $limit));
        });
    }

    /**
     * Collection rows with DUE_DATE between today and today+$days.
     */
    public function upcomingDues(?int $strStoreId, int $days = 14): array
    {
        $key = 'crm.collections.upcoming:' . ($strStoreId ?: 'all') . ':' . $days;

        return Cache::remember($key, 900, function () use ($strStoreId, $days) {
            $today  = Carbon::today()->toDateString();
            $endDate = Carbon::today()->addDays($days)->toDateString();

            $rows = $this->fetchAllCollectionRows($strStoreId, $today, $endDate);

            $result = [];
            foreach ($rows as $row) {
                $due = $row['DUE_DATE'] ?? null;
                $result[] = [
                    'student_id'   => $row['STUDENT_ID'] ?? null,
                    'student_name' => $row['STUDENT_FULL_NAME'] ?? '—',
                    'store_name'   => $row['STR_STORE_NAME'] ?? '—',
                    'amount'       => (float) ($row['REST_AMOUNT'] ?? 0),
                    'due_date'     => $due,
                    'days_until'   => $due ? Carbon::today()->diffInDays(Carbon::parse($due), false) : null,
                ];
            }

            // Sort soonest first
            usort($result, fn ($a, $b) => $a['days_until'] <=> $b['days_until']);

            return $result;
        });
    }

    /**
     * Aging bucket counts and amounts.
     *
     * Buckets:
     *   current  — 0 to 7 days overdue
     *   mild     — 8 to 30 days overdue
     *   serious  — 31 to 60 days overdue
     *   critical — 61 to 90 days overdue
     *   extreme  — more than 90 days overdue
     */
    public function agingBuckets(?int $strStoreId): array
    {
        $key = 'crm.collections.aging:' . ($strStoreId ?: 'all');

        return Cache::remember($key, 900, function () use ($strStoreId) {
            $today = Carbon::today();

            $buckets = [
                'current'  => ['label' => '0–7 j',    'count' => 0, 'amount' => 0.0, 'color' => 'success'],
                'mild'     => ['label' => '8–30 j',   'count' => 0, 'amount' => 0.0, 'color' => 'warning'],
                'serious'  => ['label' => '31–60 j',  'count' => 0, 'amount' => 0.0, 'color' => 'orange'],
                'critical' => ['label' => '61–90 j',  'count' => 0, 'amount' => 0.0, 'color' => 'danger'],
                'extreme'  => ['label' => '90 j+',    'count' => 0, 'amount' => 0.0, 'color' => 'dark'],
            ];

            $rows = $this->fetchAllCollectionRows($strStoreId);

            foreach ($rows as $row) {
                $due = $row['DUE_DATE'] ?? null;
                if (!$due) continue;

                $dueDate = Carbon::parse($due);
                $diffDays = $dueDate->diffInDays($today, false); // positive = past due
                $amount = (float) ($row['REST_AMOUNT'] ?? 0);

                if ($diffDays <= 7) {
                    $buckets['current']['count']++;
                    $buckets['current']['amount'] += $amount;
                } elseif ($diffDays <= 30) {
                    $buckets['mild']['count']++;
                    $buckets['mild']['amount'] += $amount;
                } elseif ($diffDays <= 60) {
                    $buckets['serious']['count']++;
                    $buckets['serious']['amount'] += $amount;
                } elseif ($diffDays <= 90) {
                    $buckets['critical']['count']++;
                    $buckets['critical']['amount'] += $amount;
                } else {
                    $buckets['extreme']['count']++;
                    $buckets['extreme']['amount'] += $amount;
                }
            }

            return $buckets;
        });
    }

    /**
     * Local-DB performance aggregation: sum(amount) per store per month,
     * last 3 months.  No API call.
     */
    public function performanceByCenter(): array
    {
        $key = 'crm.collections.perf_by_center';

        return Cache::remember($key, 900, function () {
            $threeMonthsAgo = Carbon::today()->subMonths(3)->startOfMonth();

            $rows = CrmPaymentSnapshot::query()
                ->selectRaw("crm_store_id, DATE_FORMAT(effective_date, '%Y-%m') AS month, SUM(amount) AS total")
                ->where('effective_date', '>=', $threeMonthsAgo)
                ->groupBy('crm_store_id', 'month')
                ->orderBy('month')
                ->get();

            // Pivot: [ storeId => [ 'month' => total, ... ] ]
            $pivot = [];
            foreach ($rows as $row) {
                $pivot[$row->crm_store_id][$row->month] = (float) $row->total;
            }

            return $pivot;
        });
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Fetch ALL pages of collection rows.  Uses page size of 25 (API max).
     */
    private function fetchAllCollectionRows(
        ?int $strStoreId,
        ?string $dueDateStart = null,
        ?string $dueDateEnd = null,
    ): array {
        $rows = [];
        $page = 0;

        do {
            $response = $this->crm->payments()->collection(
                page: $page,
                size: 25,
                strStoreId: $strStoreId,
                dueDateStartDate: $dueDateStart,
                dueDateEndDate: $dueDateEnd,
            );

            $data = $response['data'] ?? [];
            $rows = array_merge($rows, $data);
            $page++;
        } while (!empty($response['pagination']['hasMore']));

        return $rows;
    }

    /**
     * Sum REST_AMOUNT for all collection rows matching the given due-date range.
     */
    private function sumCollectionFilter(?int $strStoreId, ?string $start, ?string $end): float
    {
        $rows = $this->fetchAllCollectionRows($strStoreId, $start, $end);
        return (float) array_sum(array_column($rows, 'REST_AMOUNT'));
    }
}
