<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmAlert;
use App\Models\CrmAttendance;
use App\Models\CrmClass;
use App\Models\CrmCollectionRow;
use App\Models\CrmPaymentSnapshot;
use App\Models\CrmPresenceSummary;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Detects business alerts from local synced CRM tables and upserts into crm_alerts.
 *
 * Data sources (all local — no live API calls):
 *   absent_student   → crm_attendance + crm_classes
 *   unpaid_30d       → crm_collection_rows
 *   cheque_due_soon  → crm_payment_snapshots (payment_method_id = 2)
 *   weak_attendance  → crm_presence_summary
 *   group_near_end   → crm_classes (raw_data->END_DATE)
 *
 * Called by: php artisan crm:generate-alerts
 */
class AlertsService
{
    // Attendance threshold: alert if absence streak >= this
    private const ABSENT_THRESHOLD = 3;

    // Unpaid threshold in days
    private const UNPAID_DAYS = 30;

    // Cheque alert horizon in days
    private const CHEQUE_DAYS_AHEAD = 7;

    // Attendance rate alert threshold
    private const ATTENDANCE_THRESHOLD = 70.0;

    // Group near-end alert horizon in days
    private const GROUP_END_DAYS = 15;

    // payment_method_id for cheques in crm_payment_snapshots
    private const CHEQUE_METHOD_ID = 2;

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Run all detectors for the given store (or all stores if null) and upsert alerts.
     *
     * @return array{generated: int, skipped: int, types: array<string,int>}
     */
    public function generate(?int $storeId = null, bool $dryRun = false): array
    {
        $stats = ['generated' => 0, 'skipped' => 0, 'types' => []];

        $detectors = [
            'absent_student'  => fn () => $this->detectAbsentStudents($storeId),
            'unpaid_30d'      => fn () => $this->detectUnpaid30d($storeId),
            'cheque_due_soon' => fn () => $this->detectChequesDueSoon($storeId),
            'weak_attendance' => fn () => $this->detectWeakAttendanceGroups($storeId),
            'group_near_end'  => fn () => $this->detectGroupsNearEnd($storeId),
        ];

        foreach ($detectors as $type => $detector) {
            try {
                $rows = $detector();
                $stats['types'][$type] = count($rows);

                if (!$dryRun) {
                    ['generated' => $gen, 'skipped' => $skip] = $this->upsertAlerts($rows);
                    $stats['generated'] += $gen;
                    $stats['skipped']   += $skip;
                }
            } catch (\Throwable $e) {
                Log::warning("AlertsService: detector [{$type}] failed: " . $e->getMessage());
                $stats['types'][$type] = 0;
            }
        }

        return $stats;
    }

    /**
     * Summary for the dashboard controller — reads only from local crm_alerts.
     *
     * @return array{counts: array, rows: \Illuminate\Contracts\Pagination\LengthAwarePaginator}
     */
    public function summary(
        ?int $storeId,
        string $status = 'open',
        ?string $type = null,
        ?string $severity = null,
        int $perPage = 25,
    ): array {
        $base = CrmAlert::query()
            ->forStore($storeId)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->ofType($type)
            ->ofSeverity($severity)
            ->orderByRaw("FIELD(severity,'critical','high','medium','low')")
            ->orderByDesc('detected_at');

        $rows = $base->paginate($perPage)->withQueryString();

        $counts = CrmAlert::query()
            ->forStore($storeId)
            ->active()
            ->selectRaw('
                COUNT(*) as total,
                SUM(severity IN ("high","critical")) as high_count,
                SUM(alert_type = "absent_student")  as absent_count,
                SUM(alert_type = "unpaid_30d")       as unpaid_count,
                SUM(alert_type = "cheque_due_soon")  as cheque_count,
                SUM(alert_type = "weak_attendance")  as attendance_count,
                SUM(alert_type = "group_near_end")   as group_count
            ')
            ->first()
            ?->toArray() ?? [];

        return compact('counts', 'rows');
    }

    /**
     * Delete resolved/dismissed alerts older than the given days.
     */
    public function prune(int $days = 30): int
    {
        return CrmAlert::whereIn('status', ['resolved', 'dismissed'])
            ->where('updated_at', '<', now()->subDays($days))
            ->delete();
    }

    // ── Detectors ─────────────────────────────────────────────────────────────

    private function detectAbsentStudents(?int $storeId): array
    {
        // Resolve class IDs that belong to the store (crm_classes.site_id → sites.crm_store_id)
        $classIds = $this->classIdsForStore($storeId);

        if (empty($classIds)) {
            return [];
        }

        // Find student+class combos with >= 3 absences in the last 30 days
        $rows = DB::table('crm_attendance as a')
            ->join('crm_classes as c', 'c.crm_id', '=', 'a.crm_class_id')
            ->leftJoin('crm_students as s', 's.crm_id', '=', 'a.crm_student_id')
            ->whereIn('a.crm_class_id', $classIds)
            ->where('a.is_present', false)
            ->where('a.date', '>=', now('Africa/Casablanca')->subDays(30)->toDateString())
            ->groupBy('a.crm_class_id', 'a.crm_student_id')
            ->havingRaw('COUNT(*) >= ?', [self::ABSENT_THRESHOLD])
            ->selectRaw('
                a.crm_class_id,
                a.crm_student_id,
                c.name as class_name,
                c.crm_store_id as store_id,
                COALESCE(s.first_name, "") as first_name,
                COALESCE(s.last_name, "")  as last_name,
                COUNT(*)                   as absent_count,
                MAX(a.date)                as last_absent_date
            ')
            ->get();

        $alerts = [];
        $month  = now('Africa/Casablanca')->format('Y-m');

        foreach ($rows as $r) {
            $studentName = trim("{$r->first_name} {$r->last_name}") ?: "Étudiant #{$r->crm_student_id}";
            $alerts[] = [
                'crm_store_id'   => $storeId ?? $r->store_id,
                'alert_type'     => 'absent_student',
                'severity'       => $r->absent_count >= 5 ? 'high' : 'medium',
                'title'          => "{$studentName} — {$r->absent_count} absences ({$r->class_name})",
                'message'        => "L'étudiant {$studentName} a été absent {$r->absent_count} fois lors des 30 derniers jours dans le groupe \"{$r->class_name}\".",
                'payload'        => [
                    'student_name'     => $studentName,
                    'class_name'       => $r->class_name,
                    'absent_count'     => $r->absent_count,
                    'last_absent_date' => $r->last_absent_date,
                ],
                'crm_student_id' => $r->crm_student_id,
                'crm_class_id'   => $r->crm_class_id,
                'dedup_key'      => "absent_student:{$r->crm_class_id}:{$r->crm_student_id}:{$month}",
                'detected_at'    => now(),
            ];
        }

        return $alerts;
    }

    private function detectUnpaid30d(?int $storeId): array
    {
        $cutoff = now('Africa/Casablanca')->subDays(self::UNPAID_DAYS)->toDateString();

        $rows = CrmCollectionRow::query()
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->where('rest_amount', '>', 0)
            ->where('due_date', '<', $cutoff)
            ->selectRaw('
                student_id,
                student_name,
                crm_store_id,
                class_id,
                MIN(due_date)           as oldest_due,
                SUM(rest_amount)        as total_unpaid,
                MAX(payment_delay_days) as max_delay_days
            ')
            ->groupBy('student_id', 'student_name', 'crm_store_id', 'class_id')
            ->get();

        $alerts = [];
        $month  = now('Africa/Casablanca')->format('Y-m');

        foreach ($rows as $r) {
            $studentName = $r->student_name ?: "Étudiant #{$r->student_id}";
            $entityKey   = "{$r->student_id}:{$r->class_id}";
            $alerts[] = [
                'crm_store_id'   => $r->crm_store_id,
                'alert_type'     => 'unpaid_30d',
                'severity'       => $r->total_unpaid >= 1000 ? 'critical' : 'high',
                'title'          => "{$studentName} — " . number_format($r->total_unpaid, 2, ',', ' ') . " DH impayé",
                'message'        => "L'étudiant {$studentName} a un solde impayé de " . number_format($r->total_unpaid, 2, ',', ' ') . " DH depuis le {$r->oldest_due} (retard : {$r->max_delay_days} jours).",
                'payload'        => [
                    'student_name'    => $studentName,
                    'total_unpaid'    => (float) $r->total_unpaid,
                    'oldest_due'      => $r->oldest_due,
                    'max_delay_days'  => $r->max_delay_days,
                    'class_id'        => $r->class_id,
                ],
                'crm_student_id' => $r->student_id,
                'crm_class_id'   => $r->class_id,
                'dedup_key'      => "unpaid_30d:{$r->crm_store_id}:{$entityKey}:{$month}",
                'detected_at'    => now(),
            ];
        }

        return $alerts;
    }

    private function detectChequesDueSoon(?int $storeId): array
    {
        $today    = now('Africa/Casablanca')->toDateString();
        $horizon  = now('Africa/Casablanca')->addDays(self::CHEQUE_DAYS_AHEAD)->toDateString();

        // Latest snapshot per payment, cheques only, due in next 7 days
        $rows = DB::table('crm_payment_snapshots as s')
            ->whereRaw('s.snapshot_date = (
                SELECT MAX(s2.snapshot_date)
                FROM crm_payment_snapshots s2
                WHERE s2.crm_payment_id = s.crm_payment_id
            )')
            ->where('s.payment_method_id', self::CHEQUE_METHOD_ID)
            ->whereBetween('s.due_date', [$today, $horizon])
            ->when($storeId, fn ($q) => $q->where('s.crm_store_id', $storeId))
            ->select('s.*')
            ->get();

        // Also flag overdue cheques with rest_amount > 0
        $overdue = DB::table('crm_payment_snapshots as s')
            ->whereRaw('s.snapshot_date = (
                SELECT MAX(s2.snapshot_date)
                FROM crm_payment_snapshots s2
                WHERE s2.crm_payment_id = s.crm_payment_id
            )')
            ->where('s.payment_method_id', self::CHEQUE_METHOD_ID)
            ->where('s.due_date', '<', $today)
            ->where('s.rest_amount', '>', 0)
            ->when($storeId, fn ($q) => $q->where('s.crm_store_id', $storeId))
            ->select('s.*')
            ->get();

        $alerts = [];

        foreach ($rows as $r) {
            $daysLeft    = Carbon::parse($today)->diffInDays(Carbon::parse($r->due_date));
            $studentName = $r->payload ? (json_decode($r->payload, true)['STUDENT_FULL_NAME'] ?? "Étudiant #{$r->student_id}") : "Étudiant #{$r->student_id}";
            $month       = now('Africa/Casablanca')->format('Y-m');

            $alerts[] = [
                'crm_store_id'   => $r->crm_store_id,
                'alert_type'     => 'cheque_due_soon',
                'severity'       => $daysLeft <= 2 ? 'high' : 'medium',
                'title'          => "Chèque échéance {$r->due_date} — {$studentName}",
                'message'        => "Un chèque de " . number_format($r->amount, 2, ',', ' ') . " DH (réf. {$r->reference}) arrive à échéance dans {$daysLeft} jour(s) le {$r->due_date}.",
                'payload'        => [
                    'student_name'   => $studentName,
                    'reference'      => $r->reference,
                    'amount'         => (float) $r->amount,
                    'due_date'       => $r->due_date,
                    'days_left'      => $daysLeft,
                    'crm_payment_id' => $r->crm_payment_id,
                ],
                'crm_student_id' => $r->student_id,
                'crm_class_id'   => null,
                'dedup_key'      => "cheque_due_soon:{$r->crm_store_id}:{$r->crm_payment_id}:{$month}",
                'detected_at'    => now(),
            ];
        }

        foreach ($overdue as $r) {
            $month       = now('Africa/Casablanca')->format('Y-m');
            $studentName = $r->payload ? (json_decode($r->payload, true)['STUDENT_FULL_NAME'] ?? "Étudiant #{$r->student_id}") : "Étudiant #{$r->student_id}";
            $daysLate    = Carbon::parse($r->due_date)->diffInDays(Carbon::parse($today));

            $alerts[] = [
                'crm_store_id'   => $r->crm_store_id,
                'alert_type'     => 'cheque_due_soon',
                'severity'       => 'critical',
                'title'          => "Chèque en retard ({$daysLate}j) — {$studentName}",
                'message'        => "Un chèque de " . number_format($r->amount, 2, ',', ' ') . " DH (réf. {$r->reference}) n'a pas été encaissé. Échéance dépassée de {$daysLate} jour(s).",
                'payload'        => [
                    'student_name'   => $studentName,
                    'reference'      => $r->reference,
                    'amount'         => (float) $r->amount,
                    'due_date'       => $r->due_date,
                    'days_late'      => $daysLate,
                    'rest_amount'    => (float) $r->rest_amount,
                    'crm_payment_id' => $r->crm_payment_id,
                ],
                'crm_student_id' => $r->student_id,
                'crm_class_id'   => null,
                'dedup_key'      => "cheque_overdue:{$r->crm_store_id}:{$r->crm_payment_id}:{$month}",
                'detected_at'    => now(),
            ];
        }

        return $alerts;
    }

    private function detectWeakAttendanceGroups(?int $storeId): array
    {
        $currentMonth = now('Africa/Casablanca')->startOfMonth()->toDateString();

        $rows = CrmPresenceSummary::query()
            ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
            ->where('month', $currentMonth)
            ->whereRaw('(total_present + total_absent) > 0')
            ->selectRaw('
                crm_class_id,
                crm_store_id,
                class_name,
                teacher_name,
                total_present,
                total_absent,
                total_students,
                ROUND(100 * total_present / (total_present + total_absent), 1) as attendance_rate
            ')
            ->get()
            ->filter(fn ($r) => $r->attendance_rate < self::ATTENDANCE_THRESHOLD);

        $alerts = [];
        $month  = now('Africa/Casablanca')->format('Y-m');

        foreach ($rows as $r) {
            $alerts[] = [
                'crm_store_id'   => $r->crm_store_id,
                'alert_type'     => 'weak_attendance',
                'severity'       => $r->attendance_rate < 50 ? 'high' : 'medium',
                'title'          => "{$r->class_name} — taux présence {$r->attendance_rate}%",
                'message'        => "Le groupe \"{$r->class_name}\" ({$r->teacher_name}) a un taux de présence de {$r->attendance_rate}% ce mois-ci ({$r->total_present} présent(s) / " . ($r->total_present + $r->total_absent) . " séances).",
                'payload'        => [
                    'class_name'      => $r->class_name,
                    'teacher_name'    => $r->teacher_name,
                    'attendance_rate' => (float) $r->attendance_rate,
                    'total_present'   => $r->total_present,
                    'total_absent'    => $r->total_absent,
                    'total_students'  => $r->total_students,
                    'month'           => $month,
                ],
                'crm_student_id' => null,
                'crm_class_id'   => $r->crm_class_id,
                'dedup_key'      => "weak_attendance:{$r->crm_store_id}:{$r->crm_class_id}:{$month}",
                'detected_at'    => now(),
            ];
        }

        return $alerts;
    }

    private function detectGroupsNearEnd(?int $storeId): array
    {
        $today   = now('Africa/Casablanca')->toDateString();
        $horizon = now('Africa/Casablanca')->addDays(self::GROUP_END_DAYS)->toDateString();

        // crm_classes.site_id = CRM store_id directly
        $query = CrmClass::query()
            ->when($storeId, fn ($q) => $q->where('site_id', $storeId))
            ->whereNotNull('raw_data');

        $alerts = [];
        $month  = now('Africa/Casablanca')->format('Y-m');

        // PHP-side filter since END_DATE is inside JSON raw_data
        $query->chunk(200, function ($classes) use ($today, $horizon, $storeId, $month, &$alerts) {
            foreach ($classes as $cls) {
                $raw     = $cls->raw_data;
                $endDate = $raw['END_DATE'] ?? null;

                if (!$endDate) continue;

                // Strip time portion if present
                $endDate = substr($endDate, 0, 10);

                if ($endDate < $today || $endDate > $horizon) continue;

                $daysLeft     = Carbon::parse($today)->diffInDays(Carbon::parse($endDate));
                $activeCount  = (int) ($raw['CLASS_COUNT_STUDENTS_ACTIVE'] ?? 0);
                $level        = $raw['SCHOOL_LEVEL_NAME'] ?? '';
                $teacher      = $raw['EMPLOYEE_TEACHER_FULL_NAME'] ?? '';
                $resolvedStore = $storeId ?? ($raw['STR_STORE_ID'] ?? null);

                $alerts[] = [
                    'crm_store_id'   => $resolvedStore,
                    'alert_type'     => 'group_near_end',
                    'severity'       => $daysLeft <= 5 ? 'high' : 'low',
                    'title'          => "{$cls->name} — fin dans {$daysLeft} jour(s)",
                    'message'        => "Le groupe \"{$cls->name}\" ({$level}, prof : {$teacher}) se termine le {$endDate} ({$daysLeft} jour(s)). {$activeCount} étudiant(s) actif(s).",
                    'payload'        => [
                        'class_name'    => $cls->name,
                        'level'         => $level,
                        'teacher'       => $teacher,
                        'end_date'      => $endDate,
                        'days_left'     => $daysLeft,
                        'active_count'  => $activeCount,
                    ],
                    'crm_student_id' => null,
                    'crm_class_id'   => $cls->crm_id,
                    'dedup_key'      => "group_near_end:{$resolvedStore}:{$cls->crm_id}:{$month}",
                    'detected_at'    => now(),
                ];
            }
        });

        return $alerts;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function classIdsForStore(?int $storeId): array
    {
        if (!$storeId) {
            return CrmClass::pluck('crm_id')->toArray();
        }

        // crm_classes.site_id stores the CRM store_id directly (not Laravel sites.id)
        return CrmClass::where('site_id', $storeId)->pluck('crm_id')->toArray();
    }

    /**
     * Upsert a batch of alert arrays into crm_alerts.
     * If dedup_key already exists AND status is open/in_progress, update payload + detected_at.
     * If status is resolved/dismissed, skip (don't re-open).
     *
     * @param  array[]  $rows
     * @return array{generated: int, skipped: int}
     */
    private function upsertAlerts(array $rows): array
    {
        $generated = 0;
        $skipped   = 0;

        foreach ($rows as $row) {
            $existing = CrmAlert::where('dedup_key', $row['dedup_key'])->first();

            if ($existing) {
                if (in_array($existing->status, ['resolved', 'dismissed'])) {
                    $skipped++;
                    continue;
                }
                // Update active alert with fresh data
                $existing->update([
                    'title'       => $row['title'],
                    'message'     => $row['message'],
                    'payload'     => $row['payload'],
                    'severity'    => $row['severity'],
                    'detected_at' => $row['detected_at'],
                ]);
                $skipped++;
            } else {
                CrmAlert::create(array_merge($row, ['status' => 'open']));
                $generated++;
            }
        }

        return compact('generated', 'skipped');
    }
}
