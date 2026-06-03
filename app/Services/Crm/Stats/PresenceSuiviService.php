<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmAttendance;
use App\Models\CrmClass;
use App\Models\Site;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PresenceSuiviService
{
    public const CACHE_TTL = 600;

    public function __construct(protected Crm $crm) {}

    /**
     * Build calendar data for a center + month.
     * Returns per-day session dots, tooltip metadata, and fraud summary.
     */
    public function buildMonth(?int $storeId, string $yearMonth): array
    {
        $cacheKey = "crm.presence_suivi.{$storeId}.{$yearMonth}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($storeId, $yearMonth) {
            $start = Carbon::parse($yearMonth . '-01')->startOfMonth();
            $end   = Carbon::parse($yearMonth . '-01')->endOfMonth();
            $today = Carbon::today('Africa/Casablanca');

            // All days
            $days = collect(CarbonPeriod::create($start, $end))
                ->map(fn ($d) => $d->toDateString())
                ->toArray();

            // Week rows for calendar grid
            $weeks = $this->buildWeeks($start, $end, $days);

            // Classes for this center
            $classes = CrmClass::query()
                ->when($storeId, fn ($q) => $q->where('site_id', $storeId))
                ->whereNotNull('class_id')
                ->get()
                ->keyBy('crm_id');

            if ($classes->isEmpty()) {
                return $this->emptyResult($days, $weeks, $yearMonth);
            }

            $crmIds = $classes->keys()->toArray();

            // All attendance rows for this month — one row per student per session day
            $rows = CrmAttendance::query()
                ->whereIn('crm_class_id', $crmIds)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get();

            // Group: day → [class_crm_id → session_info]
            // Each class can have at most one session per day
            $sessionsByDay = []; // [date][crm_class_id] = session_info array

            foreach ($rows as $row) {
                $date = $row->date instanceof Carbon
                    ? $row->date->toDateString()
                    : substr((string) $row->date, 0, 10);

                $cid = $row->crm_class_id;
                $raw = $row->raw_data ?? [];

                if (!isset($sessionsByDay[$date][$cid])) {
                    $sessionsByDay[$date][$cid] = [
                        'session_ref'   => $raw['SESSION_REFERENCE'] ?? null,
                        'session_id'    => $raw['SESSION_ID']        ?? null,
                        'class_name'    => $raw['CLASS_NAME']        ?? ($classes[$cid]->name ?? ''),
                        'teacher'       => $raw['EMPLOYEE_TEACHER_FULL_NAME'] ?? '—',
                        'start_time'    => $raw['SESSION_START_TIME'] ?? null,
                        'end_time'      => $raw['SESSION_END_TIME']   ?? null,
                        'date_creation' => $raw['DATE_CREATION']      ?? null,
                        'created_by'    => $raw['USER_CREATION_FULL_NAME'] ?? null,
                        'present'       => 0,
                        'absent'        => 0,
                        'total'         => 0,
                    ];
                }

                $sessionsByDay[$date][$cid]['total']++;
                if ($row->is_present) {
                    $sessionsByDay[$date][$cid]['present']++;
                } else {
                    $sessionsByDay[$date][$cid]['absent']++;
                }
            }

            // --- Derive expected session weekdays per class from attendance history ---
            $classExpectedDows = []; // crmId → [0..6 weekday array]
            $lookbackFrom = Carbon::today()->subDays(90)->toDateString();

            $pastDates = CrmAttendance::whereIn('crm_class_id', $crmIds)
                ->where('date', '>=', $lookbackFrom)
                ->selectRaw('crm_class_id, DAYOFWEEK(date) - 1 as dow, COUNT(DISTINCT date) as cnt')
                ->groupBy('crm_class_id', 'dow')
                ->get();

            foreach ($pastDates as $r) {
                if ((int) $r->cnt >= 2) {
                    $classExpectedDows[$r->crm_class_id][] = (int) $r->dow;
                }
            }

            // Build per-day dot list for calendar
            $calendarDays = [];
            $stats = ['saisie' => 0, 'draft' => 0];

            foreach ($days as $day) {
                $dayCarbon = Carbon::parse($day);
                $dow       = $dayCarbon->dayOfWeek; // 0=Sun
                $dots = [];

                // 1. Sessions actually recorded on this day
                $daySessions = $sessionsByDay[$day] ?? [];

                foreach ($daySessions as $cid => $info) {
                    $status = $this->resolveStatus($info);
                    $dots[] = $this->buildDot($status, $info);
                    $stats[$status]++;
                }

                // 2. Expected sessions with no attendance record (Brouillon / not saisied)
                foreach ($classes as $cid => $class) {
                    if (isset($daySessions[$cid])) continue; // already has a dot

                    $expectedDows = $classExpectedDows[$cid] ?? [];
                    if (!in_array($dow, $expectedDows)) continue;

                    // Check class active window
                    $classStart = $class->raw_data['START_DATE'] ?? null;
                    $classEnd   = $class->raw_data['END_DATE']   ?? null;
                    if ($classStart && $dayCarbon->lt(Carbon::parse($classStart)->startOfDay())) continue;
                    if ($classEnd   && $dayCarbon->gt(Carbon::parse($classEnd)->endOfDay()))   continue;

                    // Future days: show grey dot, don't count in stats
                    if ($dayCarbon->gt($today)) {
                        $dots[] = $this->buildDot('futur', [
                            'class_name'    => $class->name,
                            'teacher'       => $class->raw_data['EMPLOYEE_TEACHER_FULL_NAME'] ?? '—',
                            'session_ref'   => null, 'start_time' => null, 'end_time' => null,
                            'present' => 0, 'absent' => 0, 'total' => 0,
                            'created_by' => null, 'date_creation' => null,
                        ]);
                        continue;
                    }

                    $status = 'draft';
                    $dots[] = $this->buildDot($status, [
                        'class_name'    => $class->name,
                        'teacher'       => $class->raw_data['EMPLOYEE_TEACHER_FULL_NAME'] ?? '—',
                        'session_ref'   => null,
                        'start_time'    => null,
                        'end_time'      => null,
                        'present'       => 0,
                        'absent'        => 0,
                        'total'         => 0,
                        'created_by'    => null,
                        'date_creation' => null,
                    ]);
                    $stats[$status]++;
                }

                $calendarDays[$day] = [
                    'dots'          => $dots,
                    'is_today'      => $day === $today->toDateString(),
                    'is_weekend'    => $dayCarbon->isWeekend(),
                    'is_future'     => $dayCarbon->gt($today),
                    'session_count' => count($dots),
                ];
            }

            // Fraud summary: groups with unsaisied past sessions
            $fraudByClass = [];
            foreach ($classes as $cid => $class) {
                $draft = 0; $saisie = 0;
                foreach ($days as $day) {
                    if (isset($sessionsByDay[$day][$cid])) {
                        $st = $this->resolveStatus($sessionsByDay[$day][$cid]);
                        if ($st === 'draft')  $draft++;
                        if ($st === 'saisie') $saisie++;
                        continue;
                    }
                    $dow = Carbon::parse($day)->dayOfWeek;
                    $expectedDows = $classExpectedDows[$cid] ?? [];
                    if (!in_array($dow, $expectedDows)) continue;
                    if (Carbon::parse($day)->lte($today)) $draft++;
                }
                if ($draft > 0 || $saisie > 0) {
                    $fraudByClass[] = [
                        'name'    => $class->name,
                        'teacher' => $class->raw_data['EMPLOYEE_TEACHER_FULL_NAME'] ?? '—',
                        'draft'   => $draft,
                        'saisie'  => $saisie,
                    ];
                }
            }
            usort($fraudByClass, fn ($a, $b) => $b['draft'] <=> $a['draft']);

            return [
                'days'         => $days,
                'weeks'        => $weeks,
                'calendar'     => $calendarDays,
                'month'        => $yearMonth,
                'stats'        => $stats,
                'fraud'        => $fraudByClass,
            ];
        });
    }

    /**
     * Employee (saisie operator) leaderboard per center.
     * Ranked by sessions saisies, shows present/absent counts and taux.
     */
    public function employeeStats(?int $storeId): array
    {
        $cacheKey = "crm.presence_suivi.employees.{$storeId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($storeId) {
            $sites = Site::whereNotNull('crm_store_id')->pluck('name', 'crm_store_id');

            $classFilter = CrmClass::query()
                ->when($storeId, fn ($q) => $q->where('site_id', $storeId))
                ->whereNotNull('class_id')
                ->pluck('crm_id')
                ->toArray();

            if (empty($classFilter)) return [];

            $rows = DB::table('crm_attendance')
                ->selectRaw('
                    JSON_UNQUOTE(JSON_EXTRACT(raw_data, "$.USER_CREATION"))                   as user_id,
                    JSON_UNQUOTE(JSON_EXTRACT(raw_data, "$.USER_CREATION_FULL_NAME"))          as employee,
                    JSON_UNQUOTE(JSON_EXTRACT(raw_data, "$.STR_STORE_ID"))                     as store_id,
                    COUNT(DISTINCT CONCAT(crm_class_id, "_", date))                           as sessions,
                    COUNT(*)                                                                    as total_rows,
                    SUM(is_present)                                                             as total_present,
                    COUNT(*) - SUM(is_present)                                                  as total_absent
                ')
                ->whereIn('crm_class_id', $classFilter)
                ->groupBy('user_id', 'employee', 'store_id')
                ->orderBy('sessions', 'desc')
                ->get();

            $byCentre = [];
            foreach ($rows as $r) {
                if (empty(trim((string)$r->employee))) continue;
                $sid  = (int) $r->store_id;
                $name = $sites[$sid] ?? "Centre #{$sid}";
                if (!isset($byCentre[$sid])) {
                    $byCentre[$sid] = ['center' => $name, 'employees' => []];
                }
                $total   = (int) $r->total_rows;
                $present = (int) $r->total_present;
                $absent  = (int) $r->total_absent;
                $byCentre[$sid]['employees'][] = [
                    'name'          => trim($r->employee),
                    'sessions'      => (int) $r->sessions,
                    'present'       => $present,
                    'absent'        => $absent,
                    'taux_presence' => $total > 0 ? round($present / $total * 100, 1) : 0,
                    'taux_absence'  => $total > 0 ? round($absent  / $total * 100, 1) : 0,
                ];
            }

            foreach ($byCentre as &$c) {
                usort($c['employees'], fn ($a, $b) => $b['sessions'] <=> $a['sessions']);
            }

            return array_values($byCentre);
        });
    }

    /**
     * All-time totals: total saisie sessions and total draft (expected but missing) sessions.
     * Returns ['saisie' => int, 'draft' => int].
     */
    public function allTimeTotals(?int $storeId): array
    {
        $cacheKey = "crm.presence_suivi.totals.{$storeId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($storeId) {
            $today = Carbon::today('Africa/Casablanca');

            $classes = CrmClass::query()
                ->when($storeId, fn ($q) => $q->where('site_id', $storeId))
                ->whereNotNull('class_id')
                ->get()->keyBy('crm_id');

            if ($classes->isEmpty()) return ['saisie' => 0, 'draft' => 0];

            $crmIds = $classes->keys()->toArray();

            // Saisie: distinct (class, date) pairs that have DATE_CREATION set
            $saisieRows = CrmAttendance::whereIn('crm_class_id', $crmIds)
                ->where('date', '<=', $today->toDateString())
                ->selectRaw('crm_class_id, date,
                    MAX(JSON_UNQUOTE(JSON_EXTRACT(raw_data, \'$.DATE_CREATION\'))) as date_creation')
                ->groupBy('crm_class_id', 'date')
                ->get();

            $saisie = 0;
            $draftFromRows = 0;
            $seenSessions = []; // [cid_date] to avoid double-counting in DOW inference

            foreach ($saisieRows as $r) {
                $key = $r->crm_class_id . '_' . substr((string)$r->date, 0, 10);
                $seenSessions[$key] = true;
                $hasDc = !empty($r->date_creation) && $r->date_creation !== 'null';
                if ($hasDc) $saisie++;
                else        $draftFromRows++;
            }

            // Draft from DOW inference: expected past sessions with no rows at all
            $lookbackFrom = Carbon::today()->subDays(90)->toDateString();
            $pastDows = CrmAttendance::whereIn('crm_class_id', $crmIds)
                ->where('date', '>=', $lookbackFrom)
                ->selectRaw('crm_class_id, DAYOFWEEK(date) - 1 as dow, COUNT(DISTINCT date) as cnt')
                ->groupBy('crm_class_id', 'dow')
                ->get();

            $classExpectedDows = [];
            foreach ($pastDows as $r) {
                if ((int)$r->cnt >= 2) $classExpectedDows[$r->crm_class_id][] = (int)$r->dow;
            }

            // Walk all dates from earliest attendance to today
            $earliest = CrmAttendance::whereIn('crm_class_id', $crmIds)->min('date');
            if (!$earliest) return ['saisie' => $saisie, 'draft' => $draftFromRows];

            $draftInferred = 0;
            $period = CarbonPeriod::create(Carbon::parse($earliest)->startOfMonth(), $today);
            foreach ($period as $day) {
                $dayStr = $day->toDateString();
                $dow    = $day->dayOfWeek;
                foreach ($crmIds as $cid) {
                    $key = $cid . '_' . $dayStr;
                    if (isset($seenSessions[$key])) continue;
                    $expectedDows = $classExpectedDows[$cid] ?? [];
                    if (!in_array($dow, $expectedDows)) continue;
                    $classStart = $classes[$cid]->raw_data['START_DATE'] ?? null;
                    $classEnd   = $classes[$cid]->raw_data['END_DATE']   ?? null;
                    if ($classStart && $day->lt(Carbon::parse($classStart)->startOfDay())) continue;
                    if ($classEnd   && $day->gt(Carbon::parse($classEnd)->endOfDay()))   continue;
                    $draftInferred++;
                }
            }

            return ['saisie' => $saisie, 'draft' => $draftFromRows + $draftInferred];
        });
    }

    /**
     * Drill-down: all sessions per group for a given status ('saisie' or 'draft'), all time.
     */
    public function groupDetails(?int $storeId, string $status): array
    {
        $cacheKey = "crm.presence_suivi.details.{$storeId}.{$status}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($storeId, $status) {
            $today = Carbon::today('Africa/Casablanca');

            $classes = CrmClass::query()
                ->when($storeId, fn ($q) => $q->where('site_id', $storeId))
                ->whereNotNull('class_id')
                ->get()->keyBy('crm_id');

            if ($classes->isEmpty()) return [];

            $crmIds = $classes->keys()->toArray();

            $rows = CrmAttendance::whereIn('crm_class_id', $crmIds)
                ->where('date', '<=', $today->toDateString())
                ->selectRaw('crm_class_id, date,
                    MAX(JSON_UNQUOTE(JSON_EXTRACT(raw_data, \'$.DATE_CREATION\'))) as date_creation,
                    MAX(JSON_UNQUOTE(JSON_EXTRACT(raw_data, \'$.SESSION_REFERENCE\'))) as session_ref,
                    MAX(JSON_UNQUOTE(JSON_EXTRACT(raw_data, \'$.SESSION_START_TIME\'))) as start_time,
                    MAX(JSON_UNQUOTE(JSON_EXTRACT(raw_data, \'$.SESSION_END_TIME\'))) as end_time,
                    MAX(JSON_UNQUOTE(JSON_EXTRACT(raw_data, \'$.USER_CREATION_FULL_NAME\'))) as created_by,
                    SUM(is_present) as present_count,
                    COUNT(*) as total')
                ->groupBy('crm_class_id', 'date')
                ->orderBy('date', 'desc')
                ->get();

            $seenSessions = [];
            $byGroup = [];

            foreach ($rows as $r) {
                $hasDc   = !empty($r->date_creation) && $r->date_creation !== 'null';
                $rowStatus = $hasDc ? 'saisie' : 'draft';
                $dateStr = substr((string)$r->date, 0, 10);
                $seenSessions[$r->crm_class_id . '_' . $dateStr] = true;

                if ($rowStatus !== $status) continue;

                $cid  = $r->crm_class_id;
                $name = $classes[$cid]->name ?? "Groupe #{$cid}";
                $teacher = $classes[$cid]->raw_data['EMPLOYEE_TEACHER_FULL_NAME'] ?? '—';

                if (!isset($byGroup[$cid])) {
                    $byGroup[$cid] = ['name' => $name, 'teacher' => $teacher, 'sessions' => []];
                }
                $byGroup[$cid]['sessions'][] = [
                    'date'        => $dateStr,
                    'session_ref' => $r->session_ref,
                    'start_time'  => $r->start_time ? substr($r->start_time, 0, 5) : null,
                    'end_time'    => $r->end_time   ? substr($r->end_time,   0, 5) : null,
                    'present'     => (int)$r->present_count,
                    'absent'      => (int)$r->total - (int)$r->present_count,
                    'total'       => (int)$r->total,
                    'created_by'  => $r->created_by,
                    'date_creation' => $hasDc
                        ? Carbon::parse($r->date_creation)->format('d/m/Y H:i') : null,
                ];
            }

            // For draft status, also add DOW-inferred missing sessions
            if ($status === 'draft') {
                $lookbackFrom = Carbon::today()->subDays(90)->toDateString();
                $pastDows = CrmAttendance::whereIn('crm_class_id', $crmIds)
                    ->where('date', '>=', $lookbackFrom)
                    ->selectRaw('crm_class_id, DAYOFWEEK(date) - 1 as dow, COUNT(DISTINCT date) as cnt')
                    ->groupBy('crm_class_id', 'dow')->get();

                $classExpectedDows = [];
                foreach ($pastDows as $r) {
                    if ((int)$r->cnt >= 2) $classExpectedDows[$r->crm_class_id][] = (int)$r->dow;
                }

                $earliest = CrmAttendance::whereIn('crm_class_id', $crmIds)->min('date');
                if ($earliest) {
                    $period = CarbonPeriod::create(Carbon::parse($earliest)->startOfMonth(), $today);
                    foreach ($period as $day) {
                        $dayStr = $day->toDateString();
                        $dow    = $day->dayOfWeek;
                        foreach ($crmIds as $cid) {
                            if (isset($seenSessions[$cid . '_' . $dayStr])) continue;
                            $expectedDows = $classExpectedDows[$cid] ?? [];
                            if (!in_array($dow, $expectedDows)) continue;
                            $classStart = $classes[$cid]->raw_data['START_DATE'] ?? null;
                            $classEnd   = $classes[$cid]->raw_data['END_DATE']   ?? null;
                            if ($classStart && $day->lt(Carbon::parse($classStart)->startOfDay())) continue;
                            if ($classEnd   && $day->gt(Carbon::parse($classEnd)->endOfDay()))   continue;
                            $name    = $classes[$cid]->name ?? "Groupe #{$cid}";
                            $teacher = $classes[$cid]->raw_data['EMPLOYEE_TEACHER_FULL_NAME'] ?? '—';
                            if (!isset($byGroup[$cid])) {
                                $byGroup[$cid] = ['name' => $name, 'teacher' => $teacher, 'sessions' => []];
                            }
                            $byGroup[$cid]['sessions'][] = [
                                'date'        => $dayStr,
                                'session_ref' => null,
                                'start_time'  => null,
                                'end_time'    => null,
                                'present'     => 0,
                                'absent'      => 0,
                                'total'       => 0,
                                'created_by'  => null,
                                'date_creation' => null,
                            ];
                        }
                    }
                }
                // Sort sessions desc per group
                foreach ($byGroup as &$g) {
                    usort($g['sessions'], fn($a, $b) => strcmp($b['date'], $a['date']));
                }
            }

            // Sort groups by session count desc
            uasort($byGroup, fn($a, $b) => count($b['sessions']) <=> count($a['sessions']));
            return array_values($byGroup);
        });
    }

    /**
     * Global fraud summary across all centers for the given month.
     */
    public function globalFraud(string $yearMonth): array
    {
        $cacheKey = "crm.presence_suivi.global.{$yearMonth}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($yearMonth) {
            $start = Carbon::parse($yearMonth . '-01')->startOfMonth()->toDateString();
            $end   = Carbon::parse($yearMonth . '-01')->endOfMonth()->toDateString();
            $today = Carbon::today('Africa/Casablanca');

            $sites = Site::whereNotNull('crm_store_id')->pluck('name', 'crm_store_id');

            $rows = CrmAttendance::query()
                ->join('crm_classes', 'crm_attendance.crm_class_id', '=', 'crm_classes.crm_id')
                ->whereBetween('crm_attendance.date', [$start, $end])
                ->selectRaw('crm_classes.site_id, crm_attendance.crm_class_id, crm_attendance.date,
                             MAX(JSON_UNQUOTE(JSON_EXTRACT(crm_attendance.raw_data, \'$.DATE_CREATION\'))) as date_creation,
                             SUM(crm_attendance.is_present) as present_count,
                             COUNT(*) as total')
                ->groupBy('crm_classes.site_id', 'crm_attendance.crm_class_id', 'crm_attendance.date')
                ->get();

            $byCenter = [];
            foreach ($rows as $r) {
                $sid = $r->site_id;
                if (!isset($byCenter[$sid])) {
                    $byCenter[$sid] = ['name' => $sites[$sid] ?? "Store #{$sid}", 'saisie' => 0, 'draft' => 0];
                }
                $dayCarbon = Carbon::parse($r->date);
                if ($dayCarbon->gt($today)) continue;

                $hasDc = !empty($r->date_creation) && $r->date_creation !== 'null';
                if ($hasDc) {
                    $byCenter[$sid]['saisie']++;
                } else {
                    $byCenter[$sid]['draft']++;
                }
            }

            uasort($byCenter, fn ($a, $b) => $b['draft'] <=> $a['draft']);
            return array_values($byCenter);
        });
    }

    private function buildDot(string $status, array $info): array
    {
        return [
            'status'        => $status,
            'class_name'    => $info['class_name'],
            'teacher'       => $info['teacher'],
            'session_ref'   => $info['session_ref'] ?? null,
            'start_time'    => isset($info['start_time']) && $info['start_time']
                                ? substr($info['start_time'], 0, 5) : null,
            'end_time'      => isset($info['end_time']) && $info['end_time']
                                ? substr($info['end_time'], 0, 5) : null,
            'present'       => $info['present'] ?? 0,
            'absent'        => $info['absent']  ?? 0,
            'total'         => $info['total']   ?? 0,
            'created_by'    => $info['created_by'] ?? null,
            'date_creation' => isset($info['date_creation']) && $info['date_creation']
                                ? Carbon::parse($info['date_creation'])->format('d/m/Y H:i')
                                : null,
        ];
    }

    private function resolveStatus(array $info): string
    {
        return !empty($info['date_creation']) ? 'saisie' : 'draft';
    }

    private function buildWeeks(Carbon $start, Carbon $end, array $days): array
    {
        $weeks = [];
        $week  = [];

        // Pad first week with nulls for days before month start
        $startDow = $start->dayOfWeek; // 0=Sun
        for ($i = 0; $i < $startDow; $i++) {
            $week[] = null;
        }

        foreach ($days as $day) {
            $week[] = $day;
            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }

        // Pad last week
        if (!empty($week)) {
            while (count($week) < 7) {
                $week[] = null;
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    private function emptyResult(array $days, array $weeks, string $yearMonth): array
    {
        return [
            'days'     => $days,
            'weeks'    => $weeks,
            'calendar' => [],
            'month'    => $yearMonth,
            'stats'    => ['saisie' => 0, 'draft' => 0],
            'fraud'    => [],
        ];
    }
}
