<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmClass;
use App\Models\CrmGroupProfitability;
use App\Models\CrmPaymentAllocation;
use App\Models\CrmPresenceSummary;
use App\Models\Site;
use App\Models\SiteExpense;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Computes per-group profitability snapshots from local synced tables.
 *
 * Revenue   → crm_payment_allocations (class_id, allocation_month)
 * Salary    → site_expenses WHERE type = 'paiement_prof', label-matched to class
 * Expenses  → site_expenses (other types)
 * Attendance→ crm_presence_summary
 * Students  → crm_registrations (active count)
 *
 * Salary matching strategy (in priority order):
 *   1. Direct: site_expenses.label LIKE '%{class_name}%' → assign full amount
 *   2. Proportional: unmatched expenses split by class revenue share
 *   3. Equal: if no revenue data, split equally across active classes
 *
 * Called by: php artisan crm:build-group-profitability
 */
class GroupProfitabilityService
{
    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Build profitability rows for the given months and upsert into crm_group_profitability.
     *
     * @return array{rows_written: int, classes: int, months_processed: string[]}
     */
    public function build(int $months = 3, ?int $storeId = null, ?string $singleMonth = null): array
    {
        $monthList = $singleMonth
            ? [$singleMonth]
            : $this->buildMonthList($months);

        $totalRows = 0;

        foreach ($monthList as $month) {
            $written    = $this->buildForMonth($month, $storeId);
            $totalRows += $written;
        }

        return [
            'rows_written'     => $totalRows,
            'months_processed' => $monthList,
        ];
    }

    /**
     * Dashboard data — reads from crm_group_profitability only (no live API).
     *
     * @return array{rows: Collection, totals: array, period_month: string, available_months: array}
     */
    public function forPeriod(string $periodMonth, ?int $storeId = null): array
    {
        $rows = CrmGroupProfitability::query()
            ->forStore($storeId)
            ->forMonth($periodMonth)
            ->orderByDesc('revenue')
            ->get();

        $totals = [
            'revenue'        => $rows->sum('revenue'),
            'teacher_salary' => $rows->sum('teacher_salary'),
            'other_expenses' => $rows->sum('other_expenses'),
            'profit'         => $rows->sum('profit'),
            'avg_margin'     => $rows->where('revenue', '>', 0)->avg('margin_pct') ?? 0,
            'groups'         => $rows->count(),
            'avg_attendance' => $rows->whereNotNull('attendance_rate')->avg('attendance_rate') ?? 0,
        ];

        $availableMonths = CrmGroupProfitability::query()
            ->forStore($storeId)
            ->where('period_type', 'monthly')
            ->selectRaw('DISTINCT period_month')
            ->orderByDesc('period_month')
            ->limit(24)
            ->pluck('period_month')
            ->toArray();

        return compact('rows', 'totals', 'periodMonth', 'availableMonths');
    }

    // ── Private: build one month ──────────────────────────────────────────────

    private function buildForMonth(string $month, ?int $storeId): int
    {
        // crm_classes.site_id stores the CRM store_id (e.g. 50995), NOT Laravel sites.id
        // Build a map: crm_store_id => site name for display
        $siteNameByStore = Site::whereNotNull('crm_store_id')
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->pluck('name', 'crm_store_id');

        $storeIds = $storeId
            ? [$storeId]
            : $siteNameByStore->keys()->toArray();

        if (empty($storeIds)) {
            Log::warning("GroupProfitabilityService: no sites found for storeId={$storeId}");
            return 0;
        }

        // Load all classes — crm_classes.site_id = crm_store_id (confirmed from data)
        $classes = CrmClass::whereIn('site_id', $storeIds)->get()->keyBy('crm_id');

        if ($classes->isEmpty()) {
            return 0;
        }

        $classIds = $classes->keys()->toArray();

        // site_expenses uses Laravel sites.id — resolve for salary lookup
        $laravelSiteIds = Site::whereIn('crm_store_id', $storeIds)->pluck('id')->toArray();

        // --- Data sources ---
        $revenue    = $this->revenueByClass($classIds, $storeIds, $month);
        $salaries   = $this->teacherSalaryByClass($classes, $laravelSiteIds, $month, $revenue);
        $attendance = $this->attendanceByClass($storeIds, $month);
        $students   = $this->activeStudentsByClass($classIds);

        $written = 0;

        foreach ($classes as $crmId => $cls) {
            $crmStoreId  = $cls->site_id; // crm_classes.site_id IS the crm_store_id
            $raw         = $cls->raw_data ?? [];

            $rev         = (float) ($revenue[$crmId]['amount'] ?? 0);
            $payStudents = (int)   ($revenue[$crmId]['paying_students'] ?? 0);
            $sal         = (float) ($salaries[$crmId]['amount'] ?? 0);
            $salMethod   = $salaries[$crmId]['method'] ?? null;
            $otherExp    = 0.0;
            $profit      = $rev - $sal - $otherExp;
            $margin      = $rev > 0 ? round($profit / $rev * 100, 2) : 0;

            $att = $attendance[$crmId] ?? null;

            CrmGroupProfitability::updateOrCreate(
                [
                    'crm_class_id' => $crmId,
                    'period_month' => $month,
                    'period_type'  => 'monthly',
                ],
                [
                    'class_name'          => $cls->name,
                    'crm_store_id'        => $crmStoreId,
                    'site_name'           => $siteNameByStore->get($crmStoreId),
                    'teacher_name'        => $raw['EMPLOYEE_TEACHER_FULL_NAME'] ?? null,
                    'level_name'          => $raw['SCHOOL_LEVEL_NAME'] ?? null,
                    'revenue'             => $rev,
                    'paying_students'     => $payStudents,
                    'teacher_salary'      => $sal,
                    'salary_match_method' => $salMethod,
                    'other_expenses'      => $otherExp,
                    'profit'              => $profit,
                    'margin_pct'          => $margin,
                    'attendance_rate'     => $att ? round($att['rate'], 2) : null,
                    'total_sessions'      => $att['sessions'] ?? 0,
                    'total_present'       => $att['present'] ?? 0,
                    'total_absent'        => $att['absent'] ?? 0,
                    'active_students'     => $students[$crmId] ?? ($raw['CLASS_COUNT_STUDENTS_ACTIVE'] ?? 0),
                    'computed_at'         => now(),
                ]
            );
            $written++;
        }

        return $written;
    }

    // ── Data Sources ──────────────────────────────────────────────────────────

    /**
     * Revenue per class from crm_payment_allocations.
     * Uses allocation_month (already converted to Casablanca TZ by the sync command).
     *
     * @return array<int, array{amount: float, paying_students: int}>
     */
    private function revenueByClass(array $classIds, array $storeIds, string $month): array
    {
        $rows = CrmPaymentAllocation::query()
            ->whereIn('class_id', $classIds)
            ->whereIn('crm_store_id', $storeIds)
            ->where('allocation_month', $month . '-01')
            ->selectRaw('
                class_id,
                SUM(amount)                   as total_amount,
                COUNT(DISTINCT student_id)    as paying_students
            ')
            ->groupBy('class_id')
            ->get();

        $result = [];
        foreach ($rows as $r) {
            $result[(int) $r->class_id] = [
                'amount'          => (float) $r->total_amount,
                'paying_students' => (int)   $r->paying_students,
            ];
        }
        return $result;
    }

    /**
     * Teacher salary per class from site_expenses WHERE type = 'paiement_prof'.
     *
     * Matching priority:
     *   1. label LIKE '%class_name%' → direct match, method='direct'
     *   2. unmatched expenses distributed by revenue share → method='proportional'
     *   3. if no revenue, split equally → method='equal'
     *
     * @return array<int, array{amount: float, method: string}>
     */
    private function teacherSalaryByClass(
        Collection $classes,
        array $siteIds,
        string $month,
        array $revenue,
    ): array {
        $expenses = SiteExpense::query()
            ->whereIn('site_id', $siteIds)
            ->where('type', 'paiement_prof')
            ->whereRaw('DATE_FORMAT(month, "%Y-%m") = ?', [$month])
            ->get();

        if ($expenses->isEmpty()) {
            return [];
        }

        // Build class name lookup (normalised lowercase)
        $classNames = $classes->mapWithKeys(fn ($c) => [$c->crm_id => mb_strtolower(trim($c->name))]);

        $result       = [];
        $unmatched    = [];

        foreach ($expenses as $exp) {
            $labelLower = mb_strtolower(trim($exp->label ?? ''));
            $matched    = false;

            // Pass 1 — direct label match
            foreach ($classNames as $crmId => $name) {
                if ($name && str_contains($labelLower, $name)) {
                    $result[$crmId] = [
                        'amount' => ($result[$crmId]['amount'] ?? 0) + (float) $exp->amount,
                        'method' => 'direct',
                    ];
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $unmatched[] = ['site_id' => $exp->site_id, 'amount' => (float) $exp->amount];
            }
        }

        if (empty($unmatched)) {
            return $result;
        }

        // Pass 2 — proportional or equal distribution of unmatched expenses.
        // site_expenses.site_id = Laravel sites.id
        // crm_classes.site_id   = CRM store_id (e.g. 50995)
        // Build a bridge: Laravel site.id → crm_store_id → classes
        $laravelSiteToCrmStore = Site::whereNotNull('crm_store_id')
            ->whereIn('id', collect($unmatched)->pluck('site_id')->unique()->toArray())
            ->pluck('crm_store_id', 'id');

        // Group unmatched by Laravel site_id
        $unmatchedBySite = collect($unmatched)->groupBy('site_id');

        // Build per-crm_store_id class lists (crm_classes.site_id = crm_store_id)
        $classesByStore = $classes->groupBy('site_id');

        foreach ($unmatchedBySite as $laravelSiteId => $siteExpenses) {
            $crmStoreId  = $laravelSiteToCrmStore->get($laravelSiteId);
            $siteTotal   = $siteExpenses->sum('amount');
            $siteClasses = $crmStoreId ? $classesByStore->get($crmStoreId, collect()) : collect();

            if ($siteClasses->isEmpty()) {
                continue;
            }

            // Compute site revenue total (only from classes in this store)
            $siteClassIds   = $siteClasses->pluck('crm_id')->toArray();
            $siteRevTotal   = array_sum(array_map(
                fn ($id) => $revenue[$id]['amount'] ?? 0,
                $siteClassIds
            ));

            $classCount = count($siteClassIds);

            foreach ($siteClassIds as $crmId) {
                if ($siteRevTotal > 0) {
                    $share  = ($revenue[$crmId]['amount'] ?? 0) / $siteRevTotal;
                    $method = 'proportional';
                } else {
                    $share  = 1 / $classCount;
                    $method = 'equal';
                }

                // Don't override a direct match method
                $currentMethod = $result[$crmId]['method'] ?? null;
                $result[$crmId] = [
                    'amount' => ($result[$crmId]['amount'] ?? 0) + ($siteTotal * $share),
                    'method' => $currentMethod === 'direct' ? 'direct' : $method,
                ];
            }
        }

        return $result;
    }

    /**
     * Attendance rate per class from crm_presence_summary for the given month.
     *
     * @return array<int, array{rate: float, sessions: int, present: int, absent: int}>
     */
    private function attendanceByClass(array $storeIds, string $month): array
    {
        $monthDate = $month . '-01';

        $rows = CrmPresenceSummary::query()
            ->whereIn('crm_store_id', $storeIds)
            ->where('month', $monthDate)
            ->get();

        $result = [];
        foreach ($rows as $r) {
            $total = $r->total_present + $r->total_absent;
            $result[(int) $r->crm_class_id] = [
                'rate'     => $total > 0 ? round(100 * $r->total_present / $total, 1) : 0,
                'sessions' => (int) ($r->saisie_sessions ?? 0),
                'present'  => (int) $r->total_present,
                'absent'   => (int) $r->total_absent,
            ];
        }
        return $result;
    }

    /**
     * Active student count per class from crm_registrations for the given month.
     * "Active" = registration_status_name LIKE 'actif' OR 'active'.
     *
     * @return array<int, int>
     */
    private function activeStudentsByClass(array $classIds): array
    {
        $rows = DB::table('crm_registrations')
            ->whereIn('crm_class_id', $classIds)
            ->whereRaw("LOWER(status_label) REGEXP 'actif|active|inscrit'")
            ->groupBy('crm_class_id')
            ->selectRaw('crm_class_id, COUNT(DISTINCT crm_student_id) as cnt')
            ->get();

        $result = [];
        foreach ($rows as $r) {
            $result[(int) $r->crm_class_id] = (int) $r->cnt;
        }
        return $result;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildMonthList(int $months): array
    {
        $list = [];
        for ($i = 0; $i < $months; $i++) {
            $list[] = now('Africa/Casablanca')->subMonths($i)->format('Y-m');
        }
        return $list;
    }
}
