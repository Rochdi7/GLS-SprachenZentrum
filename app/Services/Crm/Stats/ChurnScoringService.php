<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmChurnScore;
use App\Models\CrmPaymentSnapshot;
use App\Models\CrmRegistration;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChurnScoringService
{
    public function __construct(protected Crm $crm)
    {
    }

    /**
     * Compute churn scores for all active registrations in a store.
     * Returns the number of students scored.
     */
    /**
     * Fast batch scoring — NO per-student API calls.
     * Uses only registration fields + local crm_payment_snapshots.
     * Presence scoring is skipped here (too slow for batch); it fires on detail view.
     */
    public function computeForStore(int $strStoreId): int
    {
        $registrations = $this->fetchAllRegistrations($strStoreId);

        // Pre-load late-payment student IDs from local DB in one query.
        $latePaymentIds = CrmPaymentSnapshot::where('crm_store_id', $strStoreId)
            ->whereNotNull('date_creation')
            ->whereNotNull('date_update')
            ->whereRaw('date_update > DATE_ADD(date_creation, INTERVAL 24 HOUR)')
            ->pluck('student_id')
            ->flip()
            ->toArray();

        $upserts = [];
        $now     = now()->toDateTimeString();

        foreach ($registrations as $registration) {
            $studentId = $registration['STUDENT_ID'] ?? null;
            if (!$studentId) continue;

            $latePayment = isset($latePaymentIds[$studentId]);

            // No per-student API calls — pass null for presence/overdue (scored from registration only)
            $result = $this->scoreStudent($registration, null, null, $latePayment);

            $studentName = $registration['STUDENT_FULL_NAME']
                ?? trim(($registration['STUDENT_FIRST_NAME'] ?? '') . ' ' . ($registration['STUDENT_LAST_NAME'] ?? ''))
                ?: null;

            $upserts[] = [
                'crm_student_id' => $studentId,
                'crm_store_id'   => $strStoreId,
                'score'          => $result['score'],
                'risk_level'     => $result['risk_level'],
                'signals'        => json_encode($result['signals']),
                'student_name'   => $studentName,
                'registration_id'=> $registration['ID'] ?? null,
                'class_id'       => $registration['LEVEL_SESSION_ID'] ?? $registration['CLASS_ID'] ?? null,
                'computed_at'    => $now,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        // Bulk upsert — one DB round-trip per store
        foreach (array_chunk($upserts, 200) as $chunk) {
            CrmChurnScore::upsert(
                $chunk,
                ['crm_student_id', 'crm_store_id'],
                ['score', 'risk_level', 'signals', 'student_name', 'registration_id', 'class_id', 'computed_at', 'updated_at']
            );
        }

        return count($upserts);
    }

    /**
     * Score a single student registration.
     *
     * @param  array       $registration    Raw registration row from CRM API
     * @param  array|null  $presenceStats   ['total'=>int, 'absent'=>int, 'recent_absent'=>int, 'recent_total'=>int, 'prior_absent'=>int, 'prior_total'=>int]
     * @param  array|null  $overdueServices Array of service rows with REST_AMOUNT
     * @param  bool        $latePayment     Whether the student has late payment history
     * @return array{score: int, risk_level: string, signals: array}
     */
    public function scoreStudent(
        array $registration,
        ?array $presenceStats,
        ?array $overdueServices,
        bool $latePayment = false
    ): array {
        $score   = 0;
        $signals = [];

        // --- Absence rate ---
        if ($presenceStats && $presenceStats['total'] > 0) {
            $absenceRate = round(($presenceStats['absent'] / $presenceStats['total']) * 100);

            if ($absenceRate > 50) {
                $score   += 40;
                $signals[] = "Taux d'absence {$absenceRate}%";
            } elseif ($absenceRate > 30) {
                $score   += 25;
                $signals[] = "Taux d'absence {$absenceRate}%";
            }
        }

        // --- Attendance declining (last 14d vs prior 14d) ---
        if ($presenceStats && $presenceStats['recent_total'] > 0 && $presenceStats['prior_total'] > 0) {
            $recentRate = $presenceStats['recent_absent'] / $presenceStats['recent_total'];
            $priorRate  = $presenceStats['prior_absent']  / $presenceStats['prior_total'];
            if ($recentRate > $priorRate + 0.1) {
                $score   += 15;
                $signals[] = "Assiduité en baisse ces 14 derniers jours";
            }
        }

        // --- Overdue installments ---
        $overdueCount = 0;
        if (!empty($overdueServices)) {
            foreach ($overdueServices as $svc) {
                $rest = (float) ($svc['REST_AMOUNT'] ?? 0);
                if ($rest > 0) {
                    $overdueCount++;
                }
            }
        }

        if ($overdueCount >= 2) {
            $score   += 35;
            $signals[] = "Plusieurs impayés ({$overdueCount} tranches en retard)";
        } elseif ($overdueCount === 1) {
            $score   += 20;
            $signals[] = "Impayé détecté (tranche en retard)";
        }

        // --- Registration expiry ---
        $endDate = $registration['END_DATE'] ?? $registration['REGISTRATION_END_DATE'] ?? null;
        if ($endDate) {
            try {
                $expiresIn = (int) Carbon::now()->diffInDays(Carbon::parse($endDate), false);
                if ($expiresIn <= 7 && $expiresIn >= 0) {
                    $score   += 30;
                    $signals[] = "Inscription expire dans {$expiresIn} jour(s)";
                } elseif ($expiresIn <= 14 && $expiresIn > 7) {
                    $score   += 20;
                    $signals[] = "Inscription expire dans {$expiresIn} jours";
                }
            } catch (\Throwable) {
                // Skip invalid dates silently
            }
        }

        // --- Suspended/Cancelled status ---
        $statusId = (int) ($registration['REGISTRATION_STATUS_ID'] ?? $registration['STATUS_ID'] ?? 0);
        if (in_array($statusId, [9, 10], true)) {
            $score   += 50;
            $signals[] = "Statut inscription : suspendu ou annulé";
        }

        // --- Late payment history (from snapshots) ---
        if ($latePayment) {
            $score   += 10;
            $signals[] = "Historique de paiements en retard";
        }

        $score = min(100, $score);

        return [
            'score'      => $score,
            'risk_level' => $this->riskLevel($score),
            'signals'    => $signals,
        ];
    }

    /**
     * Map a score (0-100) to a risk level string.
     */
    public function riskLevel(int $score): string
    {
        foreach (CrmChurnScore::RISK_LEVELS as $level => [$min, $max]) {
            if ($score >= $min && $score <= $max) {
                return $level;
            }
        }
        return 'critical';
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function fetchAllRegistrations(int $strStoreId): array
    {
        $rows = CrmRegistration::where('crm_store_id', $strStoreId)
            ->whereNotNull('raw_data')
            ->get(['raw_data'])
            ->pluck('raw_data')
            ->toArray();

        if (empty($rows)) {
            Log::warning("ChurnScoringService: no local registrations for store #{$strStoreId}. Run crm:sync-registrations first.");
        }

        return $rows;
    }

    /**
     * Fetch presence stats for a student, cached 30 min per (store, studentId).
     */
    private function getPresenceStats(int $strStoreId, int $studentId, ?int $classId): ?array
    {
        $cacheKey = "churn.presence.{$strStoreId}.{$studentId}." . ($classId ?? 'all');

        return Cache::remember($cacheKey, 1800, function () use ($strStoreId, $studentId, $classId) {
            try {
                // All sessions for this student
                $all  = [];
                $page = 0;
                do {
                    $response = $this->crm->students()->sessionPresence(
                        page: $page,
                        size: 100,
                        strStoreId: $strStoreId,
                        studentId: $studentId,
                        classId: $classId,
                    );
                    $all  = array_merge($all, $response['data'] ?? []);
                    $page++;
                } while (
                    ($response['pagination']['hasMore'] ?? false) ||
                    ($response['pagination']['hasNext'] ?? false)
                );

                if (empty($all)) {
                    return null;
                }

                $cutoff14 = Carbon::now()->setTimezone('Africa/Casablanca')->subDays(14);
                $cutoff28 = Carbon::now()->setTimezone('Africa/Casablanca')->subDays(28);

                $total         = count($all);
                $absent        = 0;
                $recentAbsent  = 0;
                $recentTotal   = 0;
                $priorAbsent   = 0;
                $priorTotal    = 0;

                foreach ($all as $row) {
                    $isAbsent = ($row['ABSENCE'] ?? 'N') === 'Y'
                        || ($row['PRESENCE_STATUS'] ?? 1) === 0;

                    if ($isAbsent) {
                        $absent++;
                    }

                    try {
                        $sessionDate = Carbon::parse($row['SESSION_DATE'])->setTimezone('Africa/Casablanca');
                    } catch (\Throwable) {
                        continue;
                    }

                    if ($sessionDate->gte($cutoff14)) {
                        $recentTotal++;
                        if ($isAbsent) {
                            $recentAbsent++;
                        }
                    } elseif ($sessionDate->gte($cutoff28)) {
                        $priorTotal++;
                        if ($isAbsent) {
                            $priorAbsent++;
                        }
                    }
                }

                return compact('total', 'absent', 'recentAbsent', 'recentTotal', 'priorAbsent', 'priorTotal');
            } catch (\Throwable) {
                return null;
            }
        });
    }

    /**
     * Get overdue subscription services for a student (via CRM API).
     * Cached 30 min.
     */
    private function getOverdueServices(int $strStoreId, int $studentId): ?array
    {
        $cacheKey = "churn.services.{$strStoreId}.{$studentId}";

        return Cache::remember($cacheKey, 1800, function () use ($strStoreId, $studentId) {
            try {
                $response = $this->crm->subscriptionServices()->list(
                    page: 0,
                    size: 50,
                    strStoreId: $strStoreId,
                    studentId: $studentId,
                );
                return $response['data'] ?? [];
            } catch (\Throwable) {
                return null;
            }
        });
    }

    /**
     * Detect late payment history from the local CrmPaymentSnapshot table.
     */
    private function hasLatePaymentHistory(int $strStoreId, int $studentId): bool
    {
        return CrmPaymentSnapshot::where('crm_store_id', $strStoreId)
            ->where('student_id', $studentId)
            ->whereNotNull('date_creation')
            ->whereNotNull('date_update')
            ->whereRaw('date_update > DATE_ADD(date_creation, INTERVAL 24 HOUR)')
            ->exists();
    }
}
