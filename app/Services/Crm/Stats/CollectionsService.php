<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmCollectionRow;
use App\Models\CrmPaymentSnapshot;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Aggregates payment-collection KPIs from the local crm_collection_rows table.
 *
 * All data comes from local DB — zero live API calls on page load.
 * Populate with: php artisan crm:sync-collections --all
 */
class CollectionsService
{
    public function __construct(protected Crm $crm)
    {
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    public function kpis(?int $strStoreId): array
    {
        $key = 'crm.collections.kpis:' . ($strStoreId ?: 'all');

        return Cache::remember($key, 900, function () use ($strStoreId) {
            $today        = Carbon::today();
            $todayStr     = $today->toDateString();
            $endWeek      = $today->copy()->endOfWeek()->toDateString(); // Sunday of current week
            $endMonth     = $today->copy()->endOfMonth()->toDateString();

            $base = $this->baseQuery($strStoreId);

            $outstandingTotal = (float) (clone $base)->sum('rest_amount');
            $dueToday         = (float) (clone $base)->whereDate('due_date', $todayStr)->sum('rest_amount');
            // "Dues" = upcoming échéances from TODAY forward. Anything before today
            // is not "due", it is overdue and lives in the overdue/Tout-en-retard cards.
            // Both week and month start at today so the cards are reconcilable:
            // dueWeek ⊆ dueMonth, and neither overlaps overdueAll.
            $dueWeek          = (float) (clone $base)->whereBetween('due_date', [$todayStr, $endWeek])->sum('rest_amount');
            $dueMonth         = (float) (clone $base)->whereBetween('due_date', [$todayStr, $endMonth])->sum('rest_amount');
            $overdue7         = (float) (clone $base)->where('due_date', '<=', $today->copy()->subDays(7)->toDateString())->sum('rest_amount');
            $overdue30        = (float) (clone $base)->where('due_date', '<=', $today->copy()->subDays(30)->toDateString())->sum('rest_amount');
            $overdue60        = (float) (clone $base)->where('due_date', '<=', $today->copy()->subDays(60)->toDateString())->sum('rest_amount');
            $overdue90        = (float) (clone $base)->where('due_date', '<=', $today->copy()->subDays(90)->toDateString())->sum('rest_amount');
            $overdueAll       = (float) (clone $base)->where('due_date', '<', $todayStr)->sum('rest_amount');

            return compact(
                'outstandingTotal', 'dueToday', 'dueWeek', 'dueMonth',
                'overdue7', 'overdue30', 'overdue60', 'overdue90', 'overdueAll',
            );
        });
    }

    public function topDebtors(?int $strStoreId, int $limit = 20): array
    {
        $key = 'crm.collections.top_debtors:' . ($strStoreId ?: 'all') . ':' . $limit;

        return Cache::remember($key, 900, function () use ($strStoreId, $limit) {
            $today = Carbon::today()->toDateString();

            return $this->baseQuery($strStoreId)
                ->selectRaw('student_id, student_name, store_name, crm_store_id,
                             SUM(rest_amount) as total_owed, MIN(due_date) as oldest_due')
                ->groupBy('student_id', 'student_name', 'store_name', 'crm_store_id')
                ->orderByDesc('total_owed')
                ->limit($limit)
                ->get()
                ->map(fn ($r) => [
                    'student_id'   => $r->student_id,
                    'student_name' => $r->student_name ?? '—',
                    'store_name'   => $r->store_name   ?? '—',
                    'store_id'     => $r->crm_store_id,
                    'total_owed'   => (float) $r->total_owed,
                    'oldest_due'   => $r->oldest_due,
                    'overdue_days' => $r->oldest_due && $r->oldest_due < $today
                        ? Carbon::parse($r->oldest_due)->diffInDays(Carbon::today())
                        : 0,
                ])
                ->toArray();
        });
    }

    public function upcomingDues(?int $strStoreId, int $days = 14): array
    {
        $key = 'crm.collections.upcoming:' . ($strStoreId ?: 'all') . ':' . $days;

        return Cache::remember($key, 900, function () use ($strStoreId, $days) {
            $today   = Carbon::today()->toDateString();
            $endDate = Carbon::today()->addDays($days)->toDateString();

            return $this->baseQuery($strStoreId)
                ->whereBetween('due_date', [$today, $endDate])
                ->orderBy('due_date')
                ->get(['student_id', 'student_name', 'store_name', 'rest_amount', 'due_date'])
                ->map(fn ($r) => [
                    'student_id'   => $r->student_id,
                    'student_name' => $r->student_name ?? '—',
                    'store_name'   => $r->store_name   ?? '—',
                    'amount'       => (float) $r->rest_amount,
                    'due_date'     => $r->due_date?->toDateString(),
                    'days_until'   => $r->due_date
                        ? Carbon::today()->diffInDays($r->due_date, false)
                        : null,
                ])
                ->toArray();
        });
    }

    public function agingBuckets(?int $strStoreId): array
    {
        $key = 'crm.collections.aging:' . ($strStoreId ?: 'all');

        return Cache::remember($key, 900, function () use ($strStoreId) {
            $today = Carbon::today();

            $buckets = [
                'current'  => ['label' => 'Retard 0–7 j',   'count' => 0, 'amount' => 0.0, 'color' => 'success'],
                'mild'     => ['label' => 'Retard 8–30 j',   'count' => 0, 'amount' => 0.0, 'color' => 'warning'],
                'serious'  => ['label' => 'Retard 31–60 j',  'count' => 0, 'amount' => 0.0, 'color' => 'orange'],
                'critical' => ['label' => 'Retard 61–90 j',  'count' => 0, 'amount' => 0.0, 'color' => 'danger'],
                'extreme'  => ['label' => 'Retard 90 j+',    'count' => 0, 'amount' => 0.0, 'color' => 'dark'],
            ];

            // diffInDays(today, false): positive = overdue, negative = not yet due.
            // Only bucket rows that are actually overdue (>= 0 days late).
            $this->baseQuery($strStoreId)
                ->whereNotNull('due_date')
                ->where('due_date', '<=', $today->toDateString())
                ->selectRaw('due_date, rest_amount')
                ->cursor()
                ->each(function ($row) use (&$buckets, $today) {
                    $diffDays = (int) Carbon::parse($row->due_date)->diffInDays($today, false);
                    $amount   = (float) $row->rest_amount;

                    $bucket = match (true) {
                        $diffDays <= 7  => 'current',
                        $diffDays <= 30 => 'mild',
                        $diffDays <= 60 => 'serious',
                        $diffDays <= 90 => 'critical',
                        default         => 'extreme',
                    };

                    $buckets[$bucket]['count']++;
                    $buckets[$bucket]['amount'] += $amount;
                });

            return $buckets;
        });
    }

    private function baseQuery(?int $strStoreId): \Illuminate\Database\Eloquent\Builder
    {
        // A debt is still owed even after the registration ends. We must NOT
        // restrict to status "Active" — that hides overdue tranches from
        // finished/archived 2025 registrations and made "Tout en retard" only
        // show the current month. Exclude only cancelled (status_id 10, Annulé),
        // matching the convention used in StatsController and caData().
        return CrmCollectionRow::query()
            ->where('registration_status_id', '!=', 10)
            ->whereNotNull('rest_amount')
            ->where('rest_amount', '>', 0)
            ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId));
    }

    public function recoveryByCenter(?int $strStoreId): array
    {
        $key = 'crm.collections.recovery_by_center:' . ($strStoreId ?: 'all');

        return Cache::remember($key, 900, function () use ($strStoreId) {
            // Outstanding (unpaid rest_amount per center, active registrations)
            $outstanding = CrmCollectionRow::query()
                ->whereNotNull('rest_amount')
                ->where('rest_amount', '>', 0)
                ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId))
                ->selectRaw('crm_store_id, store_name, SUM(rest_amount) as outstanding, COUNT(*) as dossiers')
                ->groupBy('crm_store_id', 'store_name')
                ->get()
                ->keyBy('crm_store_id');

            // Collected this month (Réglement only, date_creation_date, latest snapshot)
            $thisMonthStart = Carbon::today()->startOfMonth()->toDateString();
            $today          = Carbon::today()->toDateString();

            $collected = CrmPaymentSnapshot::query()
                ->whereBetween('date_creation_date', [$thisMonthStart, $today])
                ->where('payment_type_id', 1)
                ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId))
                ->whereRaw('snapshot_date = (
                    SELECT MAX(s2.snapshot_date)
                    FROM crm_payment_snapshots s2
                    WHERE s2.crm_payment_id = crm_payment_snapshots.crm_payment_id
                )')
                ->selectRaw('crm_store_id, SUM(amount) as collected_month, COUNT(*) as payments_month')
                ->groupBy('crm_store_id')
                ->get()
                ->keyBy('crm_store_id');

            // Collected last 3 months for trend
            $threeMonthsAgo = Carbon::today()->subMonths(3)->startOfMonth()->toDateString();
            $collected3m = CrmPaymentSnapshot::query()
                ->whereBetween('date_creation_date', [$threeMonthsAgo, $today])
                ->where('payment_type_id', 1)
                ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId))
                ->whereRaw('snapshot_date = (
                    SELECT MAX(s2.snapshot_date)
                    FROM crm_payment_snapshots s2
                    WHERE s2.crm_payment_id = crm_payment_snapshots.crm_payment_id
                )')
                ->selectRaw('crm_store_id, SUM(amount) as collected_3m')
                ->groupBy('crm_store_id')
                ->get()
                ->keyBy('crm_store_id');

            // Merge all store IDs
            $storeIds = $outstanding->keys()
                ->merge($collected->keys())
                ->unique();

            $result = [];
            foreach ($storeIds as $sid) {
                $o    = $outstanding[$sid]   ?? null;
                $c    = $collected[$sid]     ?? null;
                $c3m  = $collected3m[$sid]   ?? null;

                $outstandingAmt  = (float) ($o?->outstanding     ?? 0);
                $collectedMonth  = (float) ($c?->collected_month  ?? 0);
                $collected3mAmt  = (float) ($c3m?->collected_3m  ?? 0);
                $totalExposure   = $outstandingAmt + $collectedMonth;

                $result[] = [
                    'store_id'        => (int) $sid,
                    'store_name'      => $o?->store_name ?? 'Store #' . $sid,
                    'outstanding'     => $outstandingAmt,
                    'dossiers'        => (int) ($o?->dossiers ?? 0),
                    'collected_month' => $collectedMonth,
                    'collected_3m'    => $collected3mAmt,
                    'payments_month'  => (int) ($c?->payments_month ?? 0),
                    'recovery_rate'   => $totalExposure > 0
                        ? round($collectedMonth / $totalExposure * 100, 1)
                        : 0.0,
                ];
            }

            // Sort by collected_month desc (best collectors first)
            usort($result, fn ($a, $b) => $b['collected_month'] <=> $a['collected_month']);

            return $result;
        });
    }

    public function performanceByCenter(): array
    {
        $key = 'crm.collections.perf_by_center';

        return Cache::remember($key, 900, function () {
            $threeMonthsAgo = Carbon::today()->subMonths(3)->startOfMonth()->toDateString();

            $rows = CrmPaymentSnapshot::query()
                ->selectRaw("crm_store_id, DATE_FORMAT(date_creation_date, '%Y-%m') AS month, SUM(amount) AS total")
                ->whereRaw('date_creation_date >= ?', [$threeMonthsAgo])
                ->where('payment_type_id', 1) // Réglement only — exclude inter-caisse transfers
                ->groupBy('crm_store_id', 'month')
                ->orderBy('month')
                ->get();

            $pivot = [];
            foreach ($rows as $row) {
                $pivot[$row->crm_store_id][$row->month] = (float) $row->total;
            }

            return $pivot;
        });
    }
}
