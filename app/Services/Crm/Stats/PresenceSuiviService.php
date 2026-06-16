<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmAttendance;
use App\Models\CrmClass;
use App\Models\CrmPresenceSummary;
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
                        'session_ref'    => $row->session_reference ?? $raw['SESSION_REFERENCE'] ?? null,
                        'session_id'     => $raw['SESSION_ID']        ?? null,
                        'class_name'     => $raw['CLASS_NAME']        ?? ($classes[$cid]->name ?? ''),
                        'teacher'        => $raw['EMPLOYEE_TEACHER_FULL_NAME'] ?? '—',
                        'start_time'     => $raw['SESSION_START_TIME'] ?? null,
                        'end_time'       => $raw['SESSION_END_TIME']   ?? null,
                        'date_creation'  => $row->date_creation ?? $raw['DATE_CREATION'] ?? null,
                        'created_by'     => $raw['USER_CREATION_FULL_NAME'] ?? null,
                        // PRESENCE_STATUS=0 means session exists but no attendance entered
                        'presence_status'=> $raw['PRESENCE_STATUS'] ?? null,
                        'present'        => 0,
                        'absent'         => 0,
                        'total'          => 0,
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
     * All-time totals: saisie + draft session counts.
     *
     * Reads from crm_presence_summary (precomputed by crm:build-presence-summary).
     * Was previously a CarbonPeriod loop: 700+ days × 30 classes = 21,000+ iterations.
     * Now: one SUM query on an indexed aggregate table.
     */
    public function allTimeTotals(?int $storeId): array
    {
        $cacheKey = "crm.presence_suivi.totals.{$storeId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($storeId) {
            $row = CrmPresenceSummary::query()
                ->when($storeId, fn ($q) => $q->where('crm_store_id', $storeId))
                ->selectRaw('SUM(saisie_sessions) as saisie, SUM(draft_sessions) as draft')
                ->first();

            return [
                'saisie' => (int) ($row?->saisie ?? 0),
                'draft'  => (int) ($row?->draft  ?? 0),
            ];
        });
    }

    /**
     * Drill-down: all sessions per group for a given status ('saisie' or 'draft'), all time.
     *
     * Uses the normalized date_creation column instead of JSON_EXTRACT in the WHERE clause.
     * This allows MySQL to use the idx_att_date_creation index instead of full table scan.
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

            // Use normalized date_creation column — no JSON_EXTRACT in WHERE
            // NULL = draft, NOT NULL = saisie
            $query = DB::table('crm_attendance as a')
                ->select([
                    'a.crm_class_id',
                    'a.date',
                    'a.date_creation',           // normalized column (indexed)
                    'a.session_reference',        // normalized column
                    DB::raw('MAX(JSON_UNQUOTE(JSON_EXTRACT(a.raw_data, "$.SESSION_START_TIME"))) as start_time'),
                    DB::raw('MAX(JSON_UNQUOTE(JSON_EXTRACT(a.raw_data, "$.SESSION_END_TIME"))) as end_time'),
                    DB::raw('MAX(JSON_UNQUOTE(JSON_EXTRACT(a.raw_data, "$.USER_CREATION_FULL_NAME"))) as created_by'),
                    DB::raw('SUM(a.is_present) as present_count'),
                    DB::raw('COUNT(*) as total'),
                ])
                ->whereIn('a.crm_class_id', $crmIds)
                ->where('a.date', '<=', $today->toDateString())
                ->groupBy('a.crm_class_id', 'a.date', 'a.date_creation', 'a.session_reference')
                ->orderBy('a.date', 'desc');

            // Filter by PRESENCE_STATUS: 0 = brouillon/not entered, !=0 = saisie
            if ($status === 'saisie') {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(a.raw_data, '$.PRESENCE_STATUS')) != '0'");
            } else {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(a.raw_data, '$.PRESENCE_STATUS')) = '0'");
            }

            $rows    = $query->get();
            $byGroup = [];

            foreach ($rows as $r) {
                $cid     = $r->crm_class_id;
                $dateStr = substr((string) $r->date, 0, 10);
                $name    = $classes[$cid]->name ?? "Groupe #{$cid}";
                $teacher = $classes[$cid]->raw_data['EMPLOYEE_TEACHER_FULL_NAME'] ?? '—';

                if (!isset($byGroup[$cid])) {
                    $byGroup[$cid] = ['name' => $name, 'teacher' => $teacher, 'sessions' => []];
                }

                $byGroup[$cid]['sessions'][] = [
                    'date'          => $dateStr,
                    'session_ref'   => $r->session_reference,
                    'start_time'    => $r->start_time ? substr($r->start_time, 0, 5) : null,
                    'end_time'      => $r->end_time   ? substr($r->end_time,   0, 5) : null,
                    // Draft/Brouillon = attendance not entered yet → 0/0, not raw absent counts
                    'present'       => $status === 'draft' ? 0 : (int) $r->present_count,
                    'absent'        => $status === 'draft' ? 0 : (int) $r->total - (int) $r->present_count,
                    'total'         => (int) $r->total,
                    'created_by'    => $r->created_by,
                    'date_creation' => $r->date_creation
                        ? Carbon::parse($r->date_creation)->format('d/m/Y H:i')
                        : null,
                ];
            }

            uasort($byGroup, fn ($a, $b) => count($b['sessions']) <=> count($a['sessions']));
            return array_values($byGroup);
        });
    }

    /**
     * Statistiques de présence par SÉANCE.
     *
     * Lists every recorded session in a date window (one card per séance), with
     * its present / absent counts and presence rate. Each row carries the
     * SESSION_ID so the UI can drill into the per-student present/absent lists
     * via {@see sessionDetail()}.
     */
    public function sessionStats(?int $storeId, string $startDate, string $endDate, ?int $classId = null): array
    {
        $cacheKey = "crm.presence_stats.sessions.{$storeId}.{$startDate}.{$endDate}." . ($classId ?: 'all');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($storeId, $startDate, $endDate, $classId) {
            $classes = CrmClass::query()
                ->when($storeId, fn ($q) => $q->where('site_id', $storeId))
                ->whereNotNull('class_id')
                ->get()->keyBy('crm_id');

            if ($classes->isEmpty()) {
                return ['sessions' => [], 'charts' => $this->emptyCharts(), 'totals' => $this->emptyStatTotals()];
            }

            $crmIds = $classId
                ? array_values(array_filter($classes->keys()->toArray(), fn ($c) => $c === $classId))
                : $classes->keys()->toArray();

            if (empty($crmIds)) {
                return ['sessions' => [], 'charts' => $this->emptyCharts(), 'totals' => $this->emptyStatTotals()];
            }

            // One row per (class, session day). Aggregate present/absent across
            // the per-student attendance rows of that séance.
            $rows = DB::table('crm_attendance as a')
                ->select([
                    'a.crm_class_id',
                    'a.date',
                    'a.session_reference',
                    DB::raw('MAX(JSON_UNQUOTE(JSON_EXTRACT(a.raw_data, "$.SESSION_ID")))                  as session_id'),
                    DB::raw('MAX(JSON_UNQUOTE(JSON_EXTRACT(a.raw_data, "$.SESSION_START_TIME")))          as start_time'),
                    DB::raw('MAX(JSON_UNQUOTE(JSON_EXTRACT(a.raw_data, "$.SESSION_END_TIME")))            as end_time'),
                    DB::raw('MAX(JSON_UNQUOTE(JSON_EXTRACT(a.raw_data, "$.EMPLOYEE_TEACHER_FULL_NAME")))  as teacher'),
                    DB::raw('MAX(JSON_UNQUOTE(JSON_EXTRACT(a.raw_data, "$.PRESENCE_STATUS")))             as presence_status'),
                    DB::raw('SUM(a.is_present)  as present_count'),
                    DB::raw('COUNT(*)           as total'),
                ])
                ->whereIn('a.crm_class_id', $crmIds)
                ->whereBetween('a.date', [$startDate, $endDate])
                ->groupBy('a.crm_class_id', 'a.date', 'a.session_reference')
                ->orderBy('a.date', 'desc')
                ->orderBy('start_time', 'desc')
                ->get();

            $sessions = [];
            $tPresent = 0; $tAbsent = 0; $tSessions = 0;
            $valide = 0; $brouillon = 0; $annule = 0;
            $trend = [];   // [date => ['present'=>, 'absent'=>]]
            $byClass = []; // [cid => ['name'=>, 'present'=>, 'total'=>]]

            foreach ($rows as $r) {
                $ps = $r->presence_status;

                // PRESENCE_STATUS=0 / null = brouillon (attendance never entered).
                // 1 = valide (saisie). There is no cancellation flag in the
                // attendance feed, so "annulé" stays 0.
                if ($ps === null || (int) $ps === 0) {
                    $brouillon++;
                    continue;
                }
                $valide++;

                $present = (int) $r->present_count;
                $total   = (int) $r->total;
                $absent  = $total - $present;
                $cid     = $r->crm_class_id;
                $date    = substr((string) $r->date, 0, 10);
                $cname   = $classes[$cid]->name ?? "Groupe #{$cid}";

                $sessions[] = [
                    'session_id'   => $r->session_id ? (int) $r->session_id : null,
                    'session_ref'  => $r->session_reference,
                    'class_id'     => (int) $cid,
                    'class_name'   => $cname,
                    'teacher'      => $r->teacher ?: '—',
                    'date'         => $date,
                    'start_time'   => $r->start_time ? substr($r->start_time, 0, 5) : null,
                    'end_time'     => $r->end_time   ? substr($r->end_time,   0, 5) : null,
                    'present'      => $present,
                    'absent'       => $absent,
                    'total'        => $total,
                    'taux'         => $total > 0 ? round($present / $total * 100, 1) : 0,
                ];

                $tPresent += $present;
                $tAbsent  += $absent;
                $tSessions++;

                // Chart 1: présence/absence trend per day
                $trend[$date] ??= ['present' => 0, 'absent' => 0];
                $trend[$date]['present'] += $present;
                $trend[$date]['absent']  += $absent;

                // Chart 2: taux de présence par groupe
                $byClass[$cid] ??= ['name' => $cname, 'present' => 0, 'total' => 0];
                $byClass[$cid]['present'] += $present;
                $byClass[$cid]['total']   += $total;
            }

            $tTotal = $tPresent + $tAbsent;

            // ── Chart series ────────────────────────────────────────────────
            ksort($trend);
            // array_values() strips the date keys — otherwise present/absent
            // serialize as JSON objects ({"2026-06-01":…}) and the chart's
            // numeric-index lookup (present[i]) returns undefined → flat chart.
            $trendSeries = [
                'labels'  => array_keys($trend),
                'present' => array_values(array_map(fn ($d) => $d['present'], $trend)),
                'absent'  => array_values(array_map(fn ($d) => $d['absent'],  $trend)),
            ];

            // Top groups by présence rate (min 1 session), limit 12 for readability
            $groupSeries = collect($byClass)
                ->map(fn ($g) => [
                    'name' => $g['name'],
                    'taux' => $g['total'] > 0 ? round($g['present'] / $g['total'] * 100, 1) : 0,
                ])
                ->sortByDesc('taux')
                ->take(12)
                ->values();

            return [
                'sessions' => $sessions,
                'charts'   => [
                    'trend'  => $trendSeries,
                    'groups' => [
                        'labels' => $groupSeries->pluck('name')->toArray(),
                        'taux'   => $groupSeries->pluck('taux')->toArray(),
                    ],
                ],
                'totals'   => [
                    'sessions'      => $tSessions,
                    'valide'        => $valide,
                    'brouillon'     => $brouillon,
                    'annule'        => $annule,
                    'present'       => $tPresent,
                    'absent'        => $tAbsent,
                    'total'         => $tTotal,
                    'taux_presence' => $tTotal > 0 ? round($tPresent / $tTotal * 100, 1) : 0,
                    'taux_absence'  => $tTotal > 0 ? round($tAbsent  / $tTotal * 100, 1) : 0,
                ],
            ];
        });
    }

    /**
     * Per-student présence / absence detail for a single séance
     * (one class on one date). Returns two name lists.
     */
    public function sessionDetail(int $classCrmId, string $date): array
    {
        $rows = CrmAttendance::query()
            ->where('crm_class_id', $classCrmId)
            ->whereDate('date', $date)
            ->get();

        $present = [];
        $absent  = [];

        foreach ($rows as $row) {
            $raw  = $row->raw_data ?? [];
            $name = trim(($raw['FIRST_NAME'] ?? '') . ' ' . ($raw['LAST_NAME'] ?? ''))
                ?: ('#' . ($raw['STUDENT_ID'] ?? '?'));

            $entry = [
                'name'  => $name,
                'phone' => $raw['PHONE_NUMBER'] ?? $raw['WHATSAPP_NUMBER'] ?? null,
                'excuse'=> ($raw['EXCUSE'] ?? null) === 'Y',
                'delay' => ($raw['DELAY'] ?? null) === 'Y',
            ];

            if ($row->is_present) {
                $present[] = $entry;
            } else {
                $absent[] = $entry;
            }
        }

        $byName = fn ($a, $b) => strcmp($a['name'], $b['name']);
        usort($present, $byName);
        usort($absent, $byName);

        $first = $rows->first();
        $raw   = $first->raw_data ?? [];

        return [
            'class_name'  => $raw['CLASS_NAME'] ?? null,
            'teacher'     => $raw['EMPLOYEE_TEACHER_FULL_NAME'] ?? '—',
            'date'        => $date,
            'session_ref' => $raw['SESSION_REFERENCE'] ?? null,
            'start_time'  => isset($raw['SESSION_START_TIME']) ? substr($raw['SESSION_START_TIME'], 0, 5) : null,
            'end_time'    => isset($raw['SESSION_END_TIME'])   ? substr($raw['SESSION_END_TIME'],   0, 5) : null,
            'present'     => $present,
            'absent'      => $absent,
        ];
    }

    private function emptyStatTotals(): array
    {
        return [
            'sessions'      => 0,
            'valide'        => 0,
            'brouillon'     => 0,
            'annule'        => 0,
            'present'       => 0,
            'absent'        => 0,
            'total'         => 0,
            'taux_presence' => 0,
            'taux_absence'  => 0,
        ];
    }

    private function emptyCharts(): array
    {
        return [
            'trend'  => ['labels' => [], 'present' => [], 'absent' => []],
            'groups' => ['labels' => [], 'taux' => []],
        ];
    }

    /**
     * Class list (for the filter dropdown) scoped to a center.
     */
    public function classOptions(?int $storeId): array
    {
        return CrmClass::query()
            ->when($storeId, fn ($q) => $q->where('site_id', $storeId))
            ->whereNotNull('class_id')
            ->orderBy('name')
            ->get(['crm_id', 'name'])
            ->map(fn ($c) => ['id' => (int) $c->crm_id, 'name' => $c->name])
            ->toArray();
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
                             MAX(crm_attendance.date_creation) as date_creation,
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
        $ps = $info['presence_status'] ?? null;
        return ($ps !== null && (int) $ps !== 0) ? 'saisie' : 'draft';
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
