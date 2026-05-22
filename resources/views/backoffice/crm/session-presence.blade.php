@extends('layouts.main')

@section('title', 'CRM — Présences')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Présences sessions')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice/crm-session-presence.css') }}">
@endsection

@php
    $classId = request()->filled('classId') ? (int) request()->query('classId') : null;
@endphp

@section('content')
    @include('backoffice.crm.partials._center')

    {{-- Custom filter bar — uses a real <select> for Groupe (the API only
         exposes class IDs, which users don't know by heart). Falls back to a
         free-text input if the classes endpoint isn't reachable. --}}
    @php
        $hasAnyFilter = collect(['classId', 'startDate', 'endDate', 'presence', 'absence', 'studentId', 'schoolYearId'])
            ->contains(fn ($k) => request()->filled($k));
        $currentClass = request('classId');
    @endphp

    <form method="GET" class="card mb-3">
        <div class="card-body py-3">
            <div class="d-flex align-items-center mb-2">
                <h6 class="mb-0 small text-muted text-uppercase">
                    <i class="ti ti-filter me-1"></i> Filtres
                </h6>
                @if($hasAnyFilter)
                    <span class="badge bg-light-primary text-primary ms-2">Filtres actifs</span>
                @endif
            </div>

            <div class="row g-2">
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="form-label small mb-1 text-muted">
                        Groupe <span class="text-danger">*</span>
                    </label>
                    @if(!empty($classOptions))
                        <select name="classId" class="form-select form-select-sm">
                            <option value="">— Choisir un groupe —</option>
                            @foreach($classOptions as $opt)
                                <option value="{{ $opt['id'] }}" {{ (string) $currentClass === (string) $opt['id'] ? 'selected' : '' }}>
                                    {{ $opt['name'] }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="number" name="classId" value="{{ $currentClass }}"
                               placeholder="Class ID (liste indisponible)"
                               class="form-control form-control-sm">
                    @endif
                </div>

                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                    <label class="form-label small mb-1 text-muted">Date de début</label>
                    <input type="date" name="startDate" value="{{ $startDate }}" class="form-control form-control-sm">
                </div>

                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                    <label class="form-label small mb-1 text-muted">Date de fin</label>
                    <input type="date" name="endDate" value="{{ $endDate }}" class="form-control form-control-sm">
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label small mb-1 text-muted">Présence</label>
                    <select name="presence" class="form-select form-select-sm">
                        <option value="">—</option>
                        <option value="Y" {{ request('presence') === 'Y' ? 'selected' : '' }}>Présents uniquement</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label small mb-1 text-muted">Absence</label>
                    <select name="absence" class="form-select form-select-sm">
                        <option value="">—</option>
                        <option value="Y" {{ request('absence') === 'Y' ? 'selected' : '' }}>Absents uniquement</option>
                    </select>
                </div>

                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label small mb-1 text-muted">Étudiant</label>
                    <div class="crm-student-ac position-relative">
                        <input type="hidden" name="studentId" value="{{ request('studentId') }}" data-role="value">
                        <input type="text"
                               class="form-control form-control-sm crm-student-ac-search"
                               placeholder="Tapez un nom…"
                               value="{{ request('studentId') ? '#' . request('studentId') : '' }}"
                               autocomplete="off"
                               data-role="search">
                        <div class="crm-student-ac-menu list-group shadow-sm" data-role="menu"
                             style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1050; max-height:280px; overflow-y:auto;"></div>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label small mb-1 text-muted">Année scolaire</label>
                    @if(!empty($lovSchoolYears))
                        <select name="schoolYearId" class="form-select form-select-sm">
                            <option value="">— Toutes les années —</option>
                            @foreach($lovSchoolYears as $opt)
                                <option value="{{ $opt['id'] }}" {{ (string) request('schoolYearId') === (string) $opt['id'] ? 'selected' : '' }}>
                                    {{ $opt['name'] }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="number" name="schoolYearId" value="{{ request('schoolYearId') }}"
                               placeholder="Liste indisponible — saisir l'ID"
                               class="form-control form-control-sm">
                    @endif
                </div>
            </div>

            <div class="mt-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="ti ti-search me-1"></i> Appliquer
                </button>
                @if($hasAnyFilter)
                    <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-x me-1"></i> Réinitialiser
                    </a>
                @endif
            </div>
        </div>
    </form>

    @include('backoffice.crm.partials._student_autocomplete')

    @if($error)
        <div class="card"><div class="card-body">
            @include('backoffice.crm.partials._table')
        </div></div>
    @elseif($classId === null)
        <div class="alert alert-info border-0 shadow-sm">
            <div class="d-flex align-items-start gap-3">
                <div class="display-6 text-info lh-1"><i class="ti ti-info-circle"></i></div>
                <div>
                    <h6 class="alert-heading mb-1">Choisissez un groupe</h6>
                    <p class="small mb-0 text-muted">
                        L'API présence ne renvoie pas l'identifiant du groupe sur chaque ligne :
                        pour afficher le détail par groupe + les totaux par étudiant, sélectionnez
                        un <strong>Groupe</strong> dans le filtre ci-dessus puis cliquez sur Appliquer.
                    </p>
                </div>
            </div>
        </div>
    @elseif(!$payload || empty($payload['data']))
        <div class="alert alert-warning border-0 shadow-sm mb-0">
            <i class="ti ti-alert-triangle me-1"></i>
            Aucune session de présence trouvée pour ce groupe avec les filtres actuels.
        </div>
    @else
        @php
            $rows = $payload['data'];
            $meta = $payload['meta'] ?? [];

            // Group rows by SESSION_ID + SESSION_DATE (one session = one class meeting)
            // and compute per-student totals across the full set (controller walks all pages).
            $sessions = []; // sessionKey => [...]
            $students = []; // studentKey => [...]

            $totalPresent = 0;
            $totalAbsent  = 0;

            foreach ($rows as $row) {
                $sid       = $row['SESSION_ID']   ?? null;
                $sdate     = $row['SESSION_DATE'] ?? null;
                $studentId = $row['STUDENT_ID']   ?? null;
                $first     = trim((string) ($row['FIRST_NAME'] ?? ''));
                $last      = trim((string) ($row['LAST_NAME']  ?? ''));
                $name      = trim($first . ' ' . $last) ?: ('#' . ($studentId ?? '?'));

                // Presence detection: prefer PRESENCE_STATUS, fall back to PRESENCE/ABSENCE Y flags.
                $status = $row['PRESENCE_STATUS'] ?? null;
                if ($status === null || $status === '') {
                    if (($row['PRESENCE'] ?? null) === 'Y')    $status = 1;
                    elseif (($row['ABSENCE'] ?? null) === 'Y') $status = 0;
                }
                $isPresent = $status === 1 || $status === '1';
                $isAbsent  = $status === 0 || $status === '0';

                $sessionKey = ($sid ?? 'none') . '|' . ($sdate ?? 'na');
                if (!isset($sessions[$sessionKey])) {
                    $sessions[$sessionKey] = [
                        'session_id'   => $sid,
                        'session_date' => $sdate,
                        'rows'         => [],
                        'present'      => 0,
                        'absent'       => 0,
                    ];
                }
                $sessions[$sessionKey]['rows'][] = $row;
                if ($isPresent) $sessions[$sessionKey]['present']++;
                if ($isAbsent)  $sessions[$sessionKey]['absent']++;

                $sKey = $studentId !== null ? (string) $studentId : ('name:' . $name);
                if (!isset($students[$sKey])) {
                    $students[$sKey] = [
                        'student_id' => $studentId,
                        'name'       => $name,
                        'total'      => 0,
                        'present'    => 0,
                        'absent'     => 0,
                    ];
                }
                $students[$sKey]['total']++;
                if ($isPresent) { $students[$sKey]['present']++; $totalPresent++; }
                if ($isAbsent)  { $students[$sKey]['absent']++;  $totalAbsent++;  }
            }

            uasort($sessions, fn ($a, $b) => strcmp((string) ($b['session_date'] ?? ''), (string) ($a['session_date'] ?? '')));
            uasort($students, function ($a, $b) {
                $aDen = $a['present'] + $a['absent'];
                $bDen = $b['present'] + $b['absent'];
                $aPct = $aDen > 0 ? $a['present'] / $aDen : 0;
                $bPct = $bDen > 0 ? $b['present'] / $bDen : 0;
                if ($aPct === $bPct) return strcmp($a['name'], $b['name']);
                return $bPct <=> $aPct;
            });

            $totalRows     = count($rows);
            $totalSessions = count($sessions);
            $totalStudents = count($students);
            $denomAll      = $totalPresent + $totalAbsent;
            $overallPct    = $denomAll > 0 ? round(($totalPresent / $denomAll) * 100) : 0;
        @endphp

        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
                    <div class="small text-muted text-uppercase">Sessions</div>
                    <div class="h4 mb-0">{{ $totalSessions }}</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
                    <div class="small text-muted text-uppercase">Étudiants</div>
                    <div class="h4 mb-0">{{ $totalStudents }}</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
                    <div class="small text-muted text-uppercase">Présences</div>
                    <div class="h4 mb-0 text-success">{{ $totalPresent }}</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
                    <div class="small text-muted text-uppercase">Absences</div>
                    <div class="h4 mb-0 text-danger">{{ $totalAbsent }}</div>
                </div></div>
            </div>
        </div>

        <div class="mb-3 small text-muted">
            <i class="ti ti-database me-1"></i>
            {{ $totalRows }} ligne(s) chargée(s)
            @if(!empty($meta['rowsBefore']) && $meta['rowsBefore'] !== $totalRows)
                <span class="text-muted">(filtrées depuis {{ $meta['rowsBefore'] }})</span>
            @endif
            — taux de présence global : <strong>{{ $overallPct }}%</strong>
        </div>

        {{-- ============================ PRESENCE MATRIX ============================
             Students × days grid (matches the "Absence par groupe" mockup).
             Cells render P (presence, green) / Q (absence, red) / blank (no session).
        --}}
        @php
            // Build the X axis from the **full date range** (every consecutive
            // day between Date début and Date fin), not just the days with
            // sessions. This matches the reference CRM that shows "off days"
            // as grey cells. Falls back to the union of session dates if the
            // range isn't both bounded.
            $matrixDates = [];
            try {
                $rangeStart = $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : null;
                $rangeEnd   = $endDate   ? \Carbon\Carbon::parse($endDate)->startOfDay()   : null;
            } catch (\Throwable) {
                $rangeStart = $rangeEnd = null;
            }
            if ($rangeStart && $rangeEnd && $rangeEnd->gte($rangeStart)) {
                $cursor = $rangeStart->copy();
                while ($cursor->lte($rangeEnd)) {
                    $matrixDates[] = $cursor->format('Y-m-d');
                    $cursor->addDay();
                }
            } else {
                $dateBuckets = [];
                foreach ($rows as $row) {
                    $sdate = $row['SESSION_DATE'] ?? null;
                    if ($sdate) {
                        try {
                            $dateBuckets[\Carbon\Carbon::parse($sdate)->format('Y-m-d')] = true;
                        } catch (\Throwable) {}
                    }
                }
                ksort($dateBuckets);
                $matrixDates = array_keys($dateBuckets);
            }

            // Build the cell map: studentKey => date => 'P' | 'Q'
            // Only rows with a resolvable PRESENCE_STATUS (Y/1 or N/0) make it
            // into the matrix. Students that appear in the raw API response
            // without any actual attendance data for the filtered range are
            // dropped — otherwise the table fills up with empty grey rows.
            $matrix = [];
            foreach ($rows as $row) {
                $studentId = $row['STUDENT_ID']   ?? null;
                $first     = trim((string) ($row['FIRST_NAME'] ?? ''));
                $last      = trim((string) ($row['LAST_NAME']  ?? ''));
                $name      = trim($first . ' ' . $last) ?: ('#' . ($studentId ?? '?'));
                $sKey      = $studentId !== null ? (string) $studentId : ('name:' . $name);
                $sdate     = $row['SESSION_DATE'] ?? null;
                if (!$sdate) continue;
                // The API returns SESSION_DATE in UTC (e.g. "2026-04-09T23:00:00.000Z"
                // for a 19h Casablanca evening session). Convert to Africa/Casablanca
                // before formatting — otherwise late-evening sessions get filed under
                // the previous calendar day and show up as empty cells in the matrix.
                try { $dKey = \Carbon\Carbon::parse($sdate)->setTimezone('Africa/Casablanca')->format('Y-m-d'); } catch (\Throwable) { continue; }

                $status = $row['PRESENCE_STATUS'] ?? null;
                if ($status === null || $status === '') {
                    if (($row['PRESENCE'] ?? null) === 'Y')    $status = 1;
                    elseif (($row['ABSENCE'] ?? null) === 'Y') $status = 0;
                }
                $cell = ($status === 1 || $status === '1') ? 'P'
                      : (($status === 0 || $status === '0') ? 'Q' : null);

                // Skip rows with no clear status — they're just placeholder
                // entries from the API and don't belong in the grid.
                if ($cell === null) continue;

                if (!isset($matrix[$sKey])) {
                    $matrix[$sKey] = [
                        'name'              => $name,
                        'student_id'        => $studentId,
                        'cells'             => [],
                        'registration_status' => $row['REGISTRATION_STATUS_NAME'] ?? null,
                    ];
                } else {
                    // Keep the most recent non-null status — guards against the
                    // first row missing the field on a rare edge case.
                    if (empty($matrix[$sKey]['registration_status']) && !empty($row['REGISTRATION_STATUS_NAME'])) {
                        $matrix[$sKey]['registration_status'] = $row['REGISTRATION_STATUS_NAME'];
                    }
                }
                $matrix[$sKey]['cells'][$dKey] = $cell;
            }

            // Drop students that ended up with zero cells (shouldn't happen
            // given the skip above, but defensive).
            $matrix = array_filter($matrix, fn ($s) => !empty($s['cells']));

            // Sort by registration status (Active → Archivé → Annulé) then
            // alphabetically inside each bucket — matches the reference CRM
            // where active students stay at the top and cancellations sink.
            $statusRank = function (?string $name): int {
                $k = mb_strtolower((string) $name, 'UTF-8');
                if (str_contains($k, 'annul'))                              return 2; // bottom
                if (str_contains($k, 'archiv') || str_contains($k, 'inact')) return 1; // middle
                return 0; // active / unknown — top
            };
            uasort($matrix, function ($a, $b) use ($statusRank) {
                $rankA = $statusRank($a['registration_status'] ?? null);
                $rankB = $statusRank($b['registration_status'] ?? null);
                if ($rankA !== $rankB) return $rankA <=> $rankB;
                return strcmp($a['name'], $b['name']);
            });

            // Decide whether day labels can be just "1, 2, 3" (single month)
            // or need DD/MM (spans multiple months).
            $monthsCovered = [];
            foreach ($matrixDates as $d) {
                $monthsCovered[substr($d, 0, 7)] = true;
            }
            $singleMonth = count($monthsCovered) <= 1;
        @endphp

        @if(!empty($matrixDates))
            <div class="card mb-3"><div class="card-body">
                <h6 class="mb-3"><i class="ti ti-layout-grid me-1"></i> Absence par groupe</h6>


                <div class="table-responsive" style="max-height: 600px;">
                    <table class="presence-matrix">
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                @foreach($matrixDates as $d)
                                    @php
                                        $label = $singleMonth
                                            ? (int) \Carbon\Carbon::parse($d)->format('d')
                                            : \Carbon\Carbon::parse($d)->format('d/m');
                                    @endphp
                                    <th title="{{ $d }}">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matrix as $sKey => $s)
                                @php
                                    // Map the API's REGISTRATION_STATUS_NAME to a row tint.
                                    // The API uses "Annulé" / "Active" / "Archivé" today;
                                    // strtolower + contains keeps us safe if labels drift.
                                    $statusName = $s['registration_status'] ?? '';
                                    $statusKey  = mb_strtolower($statusName, 'UTF-8');
                                    $rowClass   = '';
                                    if (str_contains($statusKey, 'annul')) {
                                        $rowClass = 'pm-row-canceled';
                                    } elseif (str_contains($statusKey, 'archiv') || str_contains($statusKey, 'inactif') || str_contains($statusKey, 'inactive')) {
                                        $rowClass = 'pm-row-archived';
                                    }
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td title="{{ $statusName ?: 'Active' }}">
                                        <span class="pm-student">
                                            <span class="pm-avatar"><i class="ti ti-user"></i></span>
                                            {{ $s['name'] }}
                                        </span>
                                    </td>
                                    @foreach($matrixDates as $d)
                                        @php
                                            $cell = $s['cells'][$d] ?? null;
                                            $cls  = $cell === 'P' ? 'pm-P' : ($cell === 'Q' ? 'pm-Q' : 'pm-none');
                                        @endphp
                                        <td>
                                            <div class="pm-cell {{ $cls }}">{{ $cell ?? '' }}</div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex gap-3 flex-wrap small text-muted">
                    <span><span class="d-inline-block pm-cell pm-P" style="width:16px;height:16px;line-height:16px;vertical-align:middle;"></span> Présent</span>
                    <span><span class="d-inline-block pm-cell pm-Q" style="width:16px;height:16px;line-height:16px;vertical-align:middle;"></span> Absent</span>
                    <span><span class="d-inline-block pm-cell pm-none" style="width:16px;height:16px;line-height:16px;vertical-align:middle;"></span> Pas de séance</span>
                    <span class="ms-2"><span class="d-inline-block" style="width:16px;height:16px;background:#f17b75;border-radius:4px;vertical-align:middle;"></span> Étudiant annulé</span>
                    <span><span class="d-inline-block" style="width:16px;height:16px;background:#c8ced4;border-radius:4px;vertical-align:middle;"></span> Étudiant archivé / inactif</span>
                </div>
            </div></div>
        @endif

        <div class="card mb-4"><div class="card-body">
            <h6 class="mb-3"><i class="ti ti-users me-1"></i> Totaux par étudiant</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Étudiant</th>
                            <th class="small text-nowrap">Student ID</th>
                            <th class="small text-end">Sessions</th>
                            <th class="small text-end text-success">Présences</th>
                            <th class="small text-end text-danger">Absences</th>
                            <th class="small" style="width: 200px;">Taux</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $s)
                            @php
                                $denom = $s['present'] + $s['absent'];
                                $pct = $denom > 0 ? round(($s['present'] / $denom) * 100) : 0;
                                $bar = $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger');
                            @endphp
                            <tr>
                                <td class="small">{{ $s['name'] }}</td>
                                <td class="small text-muted">{{ $s['student_id'] ?? '—' }}</td>
                                <td class="small text-end">{{ $s['total'] }}</td>
                                <td class="small text-end text-success">{{ $s['present'] }}</td>
                                <td class="small text-end text-danger">{{ $s['absent'] }}</td>
                                <td class="small">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar {{ $bar }}" role="progressbar" style="width: {{ $pct }}%;"></div>
                                        </div>
                                        <span class="text-nowrap small fw-medium" style="min-width: 38px; text-align: right;">{{ $pct }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div></div>

        <div class="card"><div class="card-body">
            <h6 class="mb-3"><i class="ti ti-calendar-event me-1"></i> Détail par session</h6>

            <div class="accordion" id="presenceSessions">
                @foreach($sessions as $sessionKey => $sess)
                    @php
                        $collapseId = 'sess-' . md5($sessionKey);
                        $sessDen = $sess['present'] + $sess['absent'];
                        $sessPct = $sessDen > 0 ? round($sess['present'] / $sessDen * 100) : 0;
                        try { $d = $sess['session_date'] ? \Carbon\Carbon::parse($sess['session_date']) : null; }
                        catch (\Throwable $e) { $d = null; }
                    @endphp
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                    aria-expanded="false" aria-controls="{{ $collapseId }}">
                                <div class="d-flex flex-wrap align-items-center gap-2 w-100 pe-3">
                                    <span class="badge bg-light-primary text-primary">
                                        <i class="ti ti-calendar me-1"></i>
                                        {{ $d ? $d->format('Y-m-d') : ($sess['session_date'] ?? '—') }}
                                    </span>
                                    <span class="text-muted small">Session #{{ $sess['session_id'] ?? '—' }}</span>
                                    <span class="ms-auto d-flex gap-1 flex-wrap">
                                        <span class="badge bg-light-success text-success">
                                            <i class="ti ti-user-check me-1"></i>{{ $sess['present'] }} présent(s)
                                        </span>
                                        <span class="badge bg-light-danger text-danger">
                                            <i class="ti ti-user-x me-1"></i>{{ $sess['absent'] }} absent(s)
                                        </span>
                                        <span class="badge bg-light text-muted">{{ $sessPct }}%</span>
                                    </span>
                                </div>
                            </button>
                        </h2>
                        <div id="{{ $collapseId }}" class="accordion-collapse collapse" data-bs-parent="#presenceSessions">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="small">Étudiant</th>
                                                <th class="small">Student ID</th>
                                                <th class="small text-end" style="width: 140px;">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sess['rows'] as $row)
                                                @php
                                                    $status = $row['PRESENCE_STATUS'] ?? null;
                                                    if ($status === null || $status === '') {
                                                        if (($row['PRESENCE'] ?? null) === 'Y')    $status = 1;
                                                        elseif (($row['ABSENCE'] ?? null) === 'Y') $status = 0;
                                                    }
                                                    $isPresent = $status === 1 || $status === '1';
                                                    $isAbsent  = $status === 0 || $status === '0';
                                                    $name = trim(($row['FIRST_NAME'] ?? '') . ' ' . ($row['LAST_NAME'] ?? '')) ?: '—';
                                                @endphp
                                                <tr>
                                                    <td class="small">{{ $name }}</td>
                                                    <td class="small text-muted">{{ $row['STUDENT_ID'] ?? '—' }}</td>
                                                    <td class="small text-end">
                                                        @if($isPresent)
                                                            <span class="badge bg-light-success text-success">
                                                                <i class="ti ti-check me-1"></i>Présent
                                                            </span>
                                                        @elseif($isAbsent)
                                                            <span class="badge bg-light-danger text-danger">
                                                                <i class="ti ti-x me-1"></i>Absent
                                                            </span>
                                                        @else
                                                            <span class="badge bg-light text-muted">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div></div>
    @endif
@endsection
