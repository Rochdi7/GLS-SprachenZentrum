<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmAttendance;
use App\Models\CrmCollectionRow;
use App\Models\CrmRegistration;
use App\Models\CrmStudent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Computes churn / risk scores from local DB only — no API calls.
 *
 * SCORING FORMULA (0–100, higher = more at risk)
 * ─────────────────────────────────────────────
 * Attendance signals  (max 55 pts):
 *   +25  consecutive absences in last 3 sessions
 *   +20  consecutive absences in last 5 sessions (cumulative with above: 45)
 *   +10  attendance rate < 50 % over full history
 *   +10  no presence recorded in last 30 days (stopped attending)
 *   + 5  attendance rate 50–70 % (partial decay signal)
 *   + 5  absent in last 2 of 3 sessions
 *
 * Payment signals     (max 45 pts):
 *   +30  outstanding balance (rest_amount > 0) AND consecutive absences ≥ 3
 *   +25  outstanding balance alone (rest_amount > 0, no absences)
 *   +15  payment overdue > 30 days
 *   +10  payment overdue 8–30 days
 *
 * Risk thresholds:
 *   low      0–24
 *   medium  25–49
 *   high    50–74
 *   critical 75+
 *
 * The score is additive and capped at 100.
 */
class ChurnScoringService
{
    /**
     * Window sizes used for attendance analysis.
     */
    private const RECENT_SESSIONS   = 5;
    private const CRITICAL_ABSENCES = 3;
    private const STOPPED_DAYS      = 30;

    /**
     * Compute scores for all active students, optionally filtered by store.
     * Returns an array of upsert-ready rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function computeAll(?int $storeId = null): array
    {
        $now = Carbon::now('Africa/Casablanca');

        // 1. Load all active registrations (+ student + class name) in one query
        $registrations = CrmRegistration::query()
            ->when($storeId, fn($q) => $q->where('crm_store_id', $storeId))
            ->with(['student', 'class'])
            ->select([
                'crm_registrations.id',
                'crm_registrations.crm_id',
                'crm_registrations.crm_student_id',
                'crm_registrations.crm_class_id',
                'crm_registrations.crm_store_id',
                'crm_registrations.status_label',
                'crm_registrations.date_creation',
            ])
            ->get();

        if ($registrations->isEmpty()) {
            return [];
        }

        // 2. Load attendance for all relevant students in bulk
        $studentIds = $registrations->pluck('crm_student_id')->unique()->values()->all();
        $classIds   = $registrations->pluck('crm_class_id')->unique()->values()->all();

        $attendanceByStudent = $this->loadAttendance($studentIds, $classIds);

        // 3. Load collection rows (unpaid) for all students
        $collectionByStudent = $this->loadCollections($studentIds, $storeId);

        // 4. Score each student — deduplicate: one score per student per store
        $scored  = [];
        $seen    = []; // student_id → true, prevents duplicate rows

        foreach ($registrations as $reg) {
            $sid = (int) $reg->crm_student_id;
            $key = $sid . '_' . (int) $reg->crm_store_id;

            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $attendance  = $attendanceByStudent[$sid] ?? collect();
            $collections = $collectionByStudent[$sid] ?? collect();

            $result = $this->scoreStudent(
                student:    $reg->student,
                reg:        $reg,
                attendance: $attendance,
                collections: $collections,
                now:        $now,
            );

            $result['crm_store_id']   = $reg->crm_store_id;
            $result['registration_id'] = $reg->crm_id;
            $result['class_id']        = $reg->crm_class_id;
            $result['computed_at']     = $now->toDateTimeString();

            $scored[] = $result;
        }

        return $scored;
    }

    /**
     * Score a single student given their attendance and collection records.
     *
     * @param  Collection<CrmAttendance>  $attendance
     * @param  Collection<CrmCollectionRow>  $collections
     * @return array<string, mixed>
     */
    public function scoreStudent(
        ?CrmStudent $student,
        CrmRegistration $reg,
        Collection $attendance,
        Collection $collections,
        Carbon $now,
    ): array {
        $score   = 0;
        $signals = [];

        // ── Attendance analysis ──────────────────────────────────────────────
        $sortedAtt  = $attendance->sortBy('date')->values();
        $totalSess  = $sortedAtt->count();
        $presentCnt = $sortedAtt->where('is_present', true)->count();
        $absentCnt  = $totalSess - $presentCnt;

        // Recent sessions (last N)
        $recentSess     = $sortedAtt->slice(-self::RECENT_SESSIONS)->values();
        $recentAbsent   = $recentSess->where('is_present', false)->count();
        $consecutiveAbs = $this->countTrailingAbsences($sortedAtt);

        $attPct = $totalSess > 0 ? round(($presentCnt / $totalSess) * 100) : null;

        $lastPresence = $sortedAtt->where('is_present', true)->sortByDesc('date')->first();
        $daysSinceLast = $lastPresence
            ? (int) Carbon::parse($lastPresence->date)->startOfDay()->diffInDays($now->copy()->startOfDay())
            : null;

        // Consecutive absences — most critical signal
        if ($consecutiveAbs >= self::CRITICAL_ABSENCES) {
            $score += 45;
            $signals[] = "{$consecutiveAbs} absences consécutives récentes";
        } elseif ($consecutiveAbs >= 2) {
            $score += 25;
            $signals[] = "{$consecutiveAbs} absences consécutives";
        }

        // Stopped attending (no presence in 30+ days)
        if ($daysSinceLast !== null && $daysSinceLast >= self::STOPPED_DAYS) {
            $score += 10;
            $signals[] = "Absent depuis {$daysSinceLast} jours";
        } elseif ($daysSinceLast === null && $totalSess > 0) {
            $score += 10;
            $signals[] = "Aucune présence enregistrée";
        }

        // Low attendance rate
        if ($attPct !== null) {
            if ($attPct < 50) {
                $score += 10;
                $signals[] = "Taux de présence faible ({$attPct}%)";
            } elseif ($attPct < 70) {
                $score += 5;
                $signals[] = "Taux de présence en baisse ({$attPct}%)";
            }
        }

        // Recent session pattern (absent 2-of-last-3)
        if ($consecutiveAbs < 2 && $recentSess->count() >= 3 && $recentAbsent >= 2) {
            $score += 5;
            $signals[] = "{$recentAbsent} absences sur les {$recentSess->count()} dernières séances";
        }

        // ── Payment analysis ─────────────────────────────────────────────────
        $unpaidRows    = $collections->where('rest_amount', '>', 0);
        $totalUnpaid   = $unpaidRows->sum('rest_amount');
        $maxOverdue    = $unpaidRows->max('payment_delay_days') ?? 0;

        if ($totalUnpaid > 0) {
            if ($consecutiveAbs >= self::CRITICAL_ABSENCES) {
                // Absent + unpaid = highest priority
                $score += 30;
                $signals[] = sprintf("Impayé %.2f DH + absent", $totalUnpaid);
            } else {
                $score += 25;
                $signals[] = sprintf("Impayé %.2f DH", $totalUnpaid);
            }

            if ($maxOverdue > 30) {
                $score += 15;
                $signals[] = "Retard de paiement {$maxOverdue} jours";
            } elseif ($maxOverdue > 8) {
                $score += 10;
                $signals[] = "Retard de paiement {$maxOverdue} jours";
            }
        }

        // Cap score at 100
        $score = min(100, $score);

        $riskLevel       = $this->riskLevel($score);
        $recommendedAction = $this->recommendedAction($riskLevel);

        $studentName = $student
            ? trim("{$student->first_name} {$student->last_name}")
            : 'Inconnu';

        return [
            'crm_student_id'     => $reg->crm_student_id,
            'score'              => $score,
            'risk_level'         => $riskLevel,
            'student_name'       => $studentName,
            'signals'            => [
                'reasons'              => $signals,
                'total_sessions'       => $totalSess,
                'present_count'        => $presentCnt,
                'absent_count'         => $absentCnt,
                'attendance_pct'       => $attPct,
                'consecutive_absences' => $consecutiveAbs,
                'days_since_last_presence' => $daysSinceLast,
                'last_presence_date'   => $lastPresence ? Carbon::parse($lastPresence->date)->toDateString() : null,
                'total_unpaid'         => $totalUnpaid > 0 ? round($totalUnpaid, 2) : null,
                'max_overdue_days'     => $maxOverdue > 0 ? $maxOverdue : null,
                'recommended_action'   => $recommendedAction,
            ],
        ];
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function loadAttendance(array $studentIds, array $classIds): Collection
    {
        return CrmAttendance::query()
            ->whereIn('crm_student_id', $studentIds)
            ->whereIn('crm_class_id', $classIds)
            ->orderBy('date')
            ->get(['crm_student_id', 'crm_class_id', 'date', 'is_present'])
            ->groupBy('crm_student_id');
    }

    private function loadCollections(array $studentIds, ?int $storeId): Collection
    {
        return CrmCollectionRow::query()
            ->whereIn('student_id', $studentIds)
            ->when($storeId, fn($q) => $q->where('crm_store_id', $storeId))
            ->get(['student_id', 'rest_amount', 'due_date', 'payment_delay_days'])
            ->groupBy('student_id');
    }

    /**
     * Count how many trailing records (most recent first) are absences.
     */
    private function countTrailingAbsences(Collection $sortedAsc): int
    {
        $count    = 0;
        $reversed = $sortedAsc->reverse()->values();
        foreach ($reversed as $row) {
            if (!$row->is_present) {
                $count++;
            } else {
                break;
            }
        }
        return $count;
    }

    private function riskLevel(int $score): string
    {
        if ($score >= 75) return 'critical';
        if ($score >= 50) return 'high';
        if ($score >= 25) return 'medium';
        return 'low';
    }

    private function recommendedAction(string $riskLevel): string
    {
        return match ($riskLevel) {
            'critical' => 'Appeler immédiatement',
            'high'     => 'Appeler en priorité',
            'medium'   => 'Faire un suivi',
            default    => 'Aucune action requise',
        };
    }
}
