@extends('layouts.main')

@section('title', 'Suivi Présences — ' . $monthLabel)
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'Suivi Présences')

@section('css')
<link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
<style>
/* ── Reset & base ─────────────────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box}

/* ── Calendar card ─────────────────────────────────────────────── */
.cal-card{background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.08);overflow:hidden}
.cal-nav{display:flex;align-items:center;justify-content:space-between;padding:20px 24px 0}
.cal-nav h5{font-size:1.1rem;font-weight:700;color:#1a1a2e;margin:0}
.cal-nav-btn{background:none;border:none;cursor:pointer;color:#6c757d;font-size:1.1rem;padding:4px 8px;border-radius:6px;transition:background .15s}
.cal-nav-btn:hover{background:#f0f0f0;color:#1a1a2e}

/* ── Grid ──────────────────────────────────────────────────────── */
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);padding:16px 12px 20px}
.cal-day-name{text-align:center;font-size:.72rem;font-weight:600;color:#9ca3af;padding:0 0 12px;text-transform:uppercase;letter-spacing:.06em}

/* ── Day cell ──────────────────────────────────────────────────── */
.cal-cell{min-height:110px;border-radius:12px;padding:6px;transition:background .15s;position:relative}
.cal-cell:hover{background:#f8f9fa}
.cal-cell.other-month{opacity:.25}
.cal-cell.is-today{background:#f0f4ff}
.cal-cell.is-today .cal-date{background:#4680ff;color:#fff;border-radius:50%}
.cal-cell.is-weekend .cal-date{color:#dc3545}

.cal-date{
    display:inline-flex;align-items:center;justify-content:center;
    width:28px;height:28px;font-size:.82rem;font-weight:600;color:#374151;
    margin-bottom:6px;
}

/* ── Dot wrapper ───────────────────────────────────────────────── */
.dots-wrap{display:flex;flex-wrap:wrap;gap:4px;padding:0 2px}

/* ── Session dot ───────────────────────────────────────────────── */
.sdot{
    width:13px;height:13px;border-radius:50%;cursor:pointer;
    position:relative;flex-shrink:0;
    transition:transform .15s,box-shadow .15s;
}
.sdot:hover{transform:scale(1.5);z-index:10;box-shadow:0 2px 8px rgba(0,0,0,.25)}
.sdot.saisie {background:#1cc88a}
.sdot.draft  {background:#e74c3c}
.sdot.futur  {background:#d1d5db;cursor:default}
.sdot.futur:hover{transform:none;box-shadow:none}

/* ── Tooltip ───────────────────────────────────────────────────── */
.sdot-tooltip{
    position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%);
    background:#1a1a2e;color:#fff;border-radius:10px;padding:10px 14px;
    width:220px;font-size:.75rem;line-height:1.5;z-index:9999;
    box-shadow:0 8px 24px rgba(0,0,0,.25);pointer-events:none;
    opacity:0;transition:opacity .15s;white-space:normal;
}
.sdot-tooltip::after{
    content:'';position:absolute;top:100%;left:50%;transform:translateX(-50%);
    border:5px solid transparent;border-top-color:#1a1a2e;
}
.sdot-wrap:hover .sdot-tooltip{opacity:1}
.sdot-wrap{position:relative}
.tt-ref{color:#9ca3af;font-size:.68rem}
.tt-status{display:inline-block;border-radius:4px;padding:1px 6px;font-size:.68rem;font-weight:600;margin-bottom:4px}
.tt-saisie {background:#1cc88a22;color:#1cc88a}
.tt-draft  {background:#e74c3c22;color:#e74c3c}

/* ── KPI cards ─────────────────────────────────────────────────── */
.kpi-pill{border-radius:12px;padding:16px 20px;display:flex;align-items:center;gap:14px}
.kpi-pill .kpi-dot{width:16px;height:16px;border-radius:50%;flex-shrink:0}
.kpi-pill .kpi-num{font-size:1.7rem;font-weight:800;line-height:1}
.kpi-pill .kpi-lbl{font-size:.75rem;color:#6c757d;margin-top:2px}

/* ── Fraud table ───────────────────────────────────────────────── */
.fraud-table th{font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6c757d}
.pct-bar{height:6px;border-radius:3px;overflow:hidden;background:#f0f0f0}
.pct-fill{height:100%;border-radius:3px;transition:width .4s}

/* ── Legend ─────────────────────────────────────────────────────── */
.leg-item{display:flex;align-items:center;gap:6px;font-size:.8rem;color:#374151}
.leg-dot{width:11px;height:11px;border-radius:50%}
</style>
@endsection

@section('content')

@php
    $today    = \Carbon\Carbon::today('Africa/Casablanca')->toDateString();
    $dayNames = ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'];
    $totalS   = $stats['saisie'] ?? 0;
    $totalD   = $stats['draft']  ?? 0;
    $totalAll = $totalS + $totalD;
@endphp

{{-- ── Header ─────────────────────────────────────────────────── --}}
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="ph-duotone ph-calendar-check text-primary"></i>
            Suivi Présences
        </h4>
        <small class="text-muted">Détection de fraude · séances saisies vs non saisies</small>
    </div>
    <div class="col-auto d-flex gap-2 align-items-center flex-wrap">
        @if ($crmCenters->isNotEmpty())
        <form method="GET" action="{{ route('backoffice.crm.presence-suivi') }}" class="d-flex gap-1">
            <input type="hidden" name="month" value="{{ $yearMonth }}">
            <select name="strStoreId" class="form-select form-select-sm" style="width:175px" onchange="this.form.submit()">
                <option value="">— Tous les centres —</option>
                @foreach ($crmCenters->whereNotNull('crm_store_id') as $c)
                    <option value="{{ $c->crm_store_id }}" {{ $storeId == $c->crm_store_id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </form>
        @endif
        <a href="{{ route('backoffice.crm.presence-suivi', array_merge(request()->query(), ['refresh'=>1])) }}"
           class="btn btn-sm btn-outline-secondary" title="Rafraîchir cache">
            <i class="ph-duotone ph-arrows-clockwise"></i>
        </a>
        @include('backoffice.crm.partials._sync_badge')
    </div>
</div>

{{-- ── Global fraud by center ─────────────────────────────────── --}}
@if (!empty($globalFraud))
<div class="row g-3 mb-4">
    @foreach ($globalFraud as $gc)
    @php
        $gcTotal = $gc['saisie'] + $gc['draft'];
        $gcPct   = $gcTotal > 0 ? round($gc['saisie'] / $gcTotal * 100) : 100;
        $gcColor = $gcPct >= 90 ? '#1cc88a' : ($gcPct >= 60 ? '#ffc107' : '#e74c3c');
    @endphp
    <div class="col-md-4 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <strong class="small">{{ $gc['name'] }}</strong>
                    <span class="badge" style="background:{{ $gcColor }}22;color:{{ $gcColor }};font-size:.7rem">{{ $gcPct }}% OK</span>
                </div>
                <div class="pct-bar mb-2">
                    <div class="pct-fill" style="width:{{ $gcPct }}%;background:{{ $gcColor }}"></div>
                </div>
                <div class="d-flex gap-3" style="font-size:.75rem">
                    <span style="color:#1cc88a"><strong>{{ $gc['saisie'] }}</strong> ✓</span>
                    <span style="color:#e74c3c"><strong>{{ $gc['draft'] }}</strong> ⚠</span>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── All-time totals (clickable) ─────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-4">
        <div class="kpi-pill bg-white shadow-sm" role="button" onclick="openDetails('saisie')" style="cursor:pointer;border:2px solid transparent;transition:border .15s" onmouseenter="this.style.borderColor='#1cc88a'" onmouseleave="this.style.borderColor='transparent'">
            <div class="kpi-dot" style="background:#1cc88a"></div>
            <div>
                <div class="kpi-num" style="color:#1cc88a">{{ number_format($totals['saisie']) }}</div>
                <div class="kpi-lbl">Total séances saisies <small class="text-muted">(toutes années)</small></div>
            </div>
            <i class="ph-duotone ph-arrow-square-out ms-auto text-muted f-18"></i>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi-pill bg-white shadow-sm" role="button" onclick="openDetails('draft')" style="cursor:pointer;border:2px solid transparent;transition:border .15s" onmouseenter="this.style.borderColor='#e74c3c'" onmouseleave="this.style.borderColor='transparent'">
            <div class="kpi-dot" style="background:#e74c3c"></div>
            <div>
                <div class="kpi-num" style="color:#e74c3c">{{ number_format($totals['draft']) }}</div>
                <div class="kpi-lbl">Annulé ou Brouillon — vérifier ! <small class="text-muted">(toutes années)</small></div>
            </div>
            <i class="ph-duotone ph-arrow-square-out ms-auto text-muted f-18"></i>
        </div>
    </div>
    <div class="col-6 col-md-4">
        @php $gt = $totals['saisie'] + $totals['draft']; @endphp
        <div class="kpi-pill bg-white shadow-sm">
            <div class="kpi-dot" style="background:#9ca3af"></div>
            <div>
                <div class="kpi-num" style="color:#9ca3af">{{ $gt > 0 ? round($totals['saisie']/$gt*100) : 0 }}%</div>
                <div class="kpi-lbl">Taux saisie global <small class="text-muted">(toutes années)</small></div>
            </div>
        </div>
    </div>
</div>

{{-- ── KPI row (current month) ──────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="kpi-pill bg-white shadow-sm" style="border:1px solid #e9ecef">
            <div class="kpi-dot" style="background:#1cc88a"></div>
            <div><div class="kpi-num" style="color:#1cc88a">{{ $totalS }}</div><div class="kpi-lbl">Saisies <small class="text-muted">ce mois</small></div></div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi-pill bg-white shadow-sm" style="border:1px solid #e9ecef">
            <div class="kpi-dot" style="background:#e74c3c"></div>
            <div><div class="kpi-num" style="color:#e74c3c">{{ $totalD }}</div><div class="kpi-lbl">Annulé / Brouillon <small class="text-muted">ce mois</small></div></div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi-pill bg-white shadow-sm" style="border:1px solid #e9ecef">
            <div class="kpi-dot" style="background:#9ca3af"></div>
            <div><div class="kpi-num" style="color:#9ca3af">{{ $totalAll > 0 ? round($totalS/$totalAll*100) : 0 }}%</div><div class="kpi-lbl">Taux saisie <small class="text-muted">ce mois</small></div></div>
        </div>
    </div>
</div>

{{-- ── Calendar card ───────────────────────────────────────────── --}}
<div class="cal-card mb-4">

    {{-- Navigation --}}
    <div class="cal-nav mb-2">
        <a href="{{ route('backoffice.crm.presence-suivi', array_merge(request()->query(), ['month'=>$prevMonth])) }}"
           class="cal-nav-btn"><i class="ph-duotone ph-caret-left f-18"></i></a>
        <h5>{{ $monthLabel }}</h5>
        <a href="{{ route('backoffice.crm.presence-suivi', array_merge(request()->query(), ['month'=>$nextMonth])) }}"
           class="cal-nav-btn"><i class="ph-duotone ph-caret-right f-18"></i></a>
    </div>

    {{-- Legend --}}
    <div class="d-flex gap-4 px-4 pb-2 flex-wrap align-items-center">
        <div class="leg-item"><div class="leg-dot" style="background:#1cc88a"></div>Saisie (Validé)</div>
        <div class="leg-item"><div class="leg-dot" style="background:#e74c3c"></div>Annulé ou Brouillon — vérifier !</div>
    </div>

    {{-- Day names --}}
    <div class="cal-grid" style="padding-bottom:0">
        @foreach ($dayNames as $dn)
            <div class="cal-day-name">{{ $dn }}</div>
        @endforeach
    </div>

    {{-- Weeks --}}
    <div class="cal-grid" style="padding-top:0">
        @foreach ($weeks as $week)
            @foreach ($week as $day)
            @if ($day === null)
                <div class="cal-cell other-month"></div>
            @else
            @php
                $ci   = $calendar[$day] ?? null;
                $dots = $ci['dots'] ?? [];
                $isT  = ($day === $today);
                $isW  = \Carbon\Carbon::parse($day)->isWeekend();
                $isFut= \Carbon\Carbon::parse($day)->gt(\Carbon\Carbon::today('Africa/Casablanca'));
            @endphp
            <div class="cal-cell {{ $isT ? 'is-today' : '' }} {{ $isW ? 'is-weekend' : '' }} {{ $isFut ? 'is-future' : '' }}">
                <div class="cal-date">{{ (int) substr($day, 8, 2) }}</div>
                <div class="dots-wrap">
                    @foreach ($dots as $dot)
                    @php
                        $ttStatus = $dot['status'] === 'saisie' ? 'Saisie ✓' : 'Annulé ou Brouillon — vérifier !';
                        $ttClass = $dot['status'] === 'saisie' ? 'tt-saisie' : 'tt-draft';
                    @endphp
                    <div class="sdot-wrap">
                        <div class="sdot {{ $dot['status'] }}"
                             title="{{ $dot['class_name'] }}"></div>
                        <div class="sdot-tooltip">
                            <div class="tt-ref">{{ $dot['session_ref'] ?? '' }}</div>
                            <div class="fw-semibold" style="font-size:.8rem;margin-bottom:3px">{{ $dot['class_name'] }}</div>
                            <div style="color:#9ca3af;font-size:.72rem;margin-bottom:5px">{{ $dot['teacher'] }}</div>
                            <span class="tt-status {{ $ttClass }}">{{ $ttStatus }}</span>
                            @if ($dot['start_time'])
                            <div style="margin-top:4px">🕐 {{ $dot['start_time'] }}–{{ $dot['end_time'] }}</div>
                            @endif
                            @if ($dot['status'] === 'saisie' && $dot['created_by'])
                            <div style="margin-top:4px;color:#9ca3af">Par: {{ $dot['created_by'] }}</div>
                            @endif
                            <div style="margin-top:4px">
                                <span style="color:#1cc88a">{{ $dot['present'] }} présents</span>
                                · <span style="color:#dc3545">{{ $dot['absent'] }} absents</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach
        @endforeach
    </div>
</div>

{{-- ── Fraud detail table ───────────────────────────────────────── --}}
@if (!empty($fraud))
<div class="card border-0 shadow-sm mb-4" style="border-radius:14px">
    <div class="card-header bg-white border-bottom" style="border-radius:14px 14px 0 0">
        <h5 class="mb-0 d-flex align-items-center gap-2">
            <i class="ph-duotone ph-warning-circle text-warning"></i>
            Groupes avec séances non saisies — {{ count($fraud) }} groupe(s) à relancer
        </h5>
    </div>
    <div class="card-body p-0">
        <table class="table fraud-table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Groupe</th>
                    <th>Enseignant</th>
                    <th class="text-center" style="color:#1cc88a">Saisies</th>
                    <th class="text-center" style="color:#e74c3c">Annulé ou Brouillon</th>
                    <th style="width:180px">Taux saisie</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($fraud as $f)
                @php
                    $ft    = $f['saisie'] + $f['draft'];
                    $fpct  = $ft > 0 ? round($f['saisie'] / $ft * 100) : 0;
                    $fcolor= $fpct >= 90 ? '#1cc88a' : ($fpct >= 60 ? '#ffc107' : '#e74c3c');
                @endphp
                <tr>
                    <td class="ps-4 fw-semibold">{{ $f['name'] }}</td>
                    <td class="text-muted small">{{ $f['teacher'] }}</td>
                    <td class="text-center fw-bold" style="color:#1cc88a">{{ $f['saisie'] }}</td>
                    <td class="text-center fw-bold" style="color:#e74c3c">{{ $f['draft'] }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="pct-bar flex-grow-1">
                                <div class="pct-fill" style="width:{{ $fpct }}%;background:{{ $fcolor }}"></div>
                            </div>
                            <small style="color:{{ $fcolor }};min-width:32px">{{ $fpct }}%</small>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Employee leaderboard — one full-width card per center ────── --}}
@if (!empty($teacherStats))
<div class="mb-4">
    <h5 class="mb-3 d-flex align-items-center gap-2">
        <i class="ph-duotone ph-trophy text-warning"></i>
        Classement employés par centre — séances saisies
    </h5>
    @foreach ($teacherStats as $center)
    <div class="card border-0 shadow-sm mb-3" style="border-radius:14px">
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 px-4 py-3" style="border-radius:14px 14px 0 0">
            <i class="ph-duotone ph-building-office text-primary f-18"></i>
            <strong>{{ $center['center'] }}</strong>
            <span class="badge bg-light text-muted ms-auto">{{ count($center['employees']) }} employé(s)</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="font-size:.83rem;table-layout:fixed;width:100%">
                <colgroup>
                    <col style="width:48px">
                    <col>{{-- Employé --}}
                    <col style="width:90px">{{-- Séances --}}
                    <col style="width:100px">{{-- Présence --}}
                    <col style="width:100px">{{-- Absence --}}
                    <col style="width:160px">{{-- Taux --}}
                </colgroup>
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-center">#</th>
                        <th>Employé</th>
                        <th class="text-center">Séances</th>
                        <th class="text-center" style="color:#1cc88a">Présence</th>
                        <th class="text-center" style="color:#e74c3c">Absence</th>
                        <th>Taux présence</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($center['employees'] as $i => $t)
                    @php
                        $rank   = $i + 1;
                        $medal  = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => $rank };
                        $pcolor = $t['taux_presence'] >= 70 ? '#1cc88a' : ($t['taux_presence'] >= 50 ? '#ffc107' : '#e74c3c');
                    @endphp
                    <tr>
                        <td class="ps-4 text-center fw-bold" style="font-size:.9rem">{{ $medal }}</td>
                        <td class="fw-semibold" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t['name'] }}</td>
                        <td class="text-center">
                            <span class="badge rounded-pill bg-light text-dark border">{{ number_format($t['sessions']) }}</span>
                        </td>
                        <td class="text-center fw-bold" style="color:#1cc88a">{{ number_format($t['present']) }}</td>
                        <td class="text-center fw-bold" style="color:#e74c3c">{{ number_format($t['absent']) }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="flex:1;height:7px;border-radius:4px;background:#f0f0f0;overflow:hidden;min-width:0">
                                    <div style="width:{{ $t['taux_presence'] }}%;height:100%;border-radius:4px;background:{{ $pcolor }}"></div>
                                </div>
                                <small style="color:{{ $pcolor }};width:42px;font-weight:700;flex-shrink:0;text-align:right">{{ $t['taux_presence'] }}%</small>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── Details modal ───────────────────────────────────────────── --}}
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="w-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span id="modal-dot" style="width:14px;height:14px;border-radius:50%;flex-shrink:0;display:inline-block"></span>
                        <h5 class="modal-title mb-0" id="modal-title">Détails séances</h5>
                        <span id="modal-total-badge" class="badge bg-secondary ms-1" style="font-size:.75rem"></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                    </div>
                    <input type="text" id="modal-search" class="form-control form-control-sm mb-2"
                           placeholder="Filtrer par groupe ou enseignant…" oninput="filterGroups(this.value)">
                </div>
            </div>
            <div class="modal-body p-0" id="modal-body">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm me-2"></div> Chargement…
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
const detailsUrl = "{{ route('backoffice.crm.presence-suivi.details') }}{{ request()->has('strStoreId') ? '?strStoreId='.request('strStoreId') : '' }}";

function openDetails(status) {
    const colors = { saisie: '#1cc88a', draft: '#e74c3c' };
    const labels = { saisie: 'Séances Saisies — toutes années', draft: 'Annulé ou Brouillon — toutes années' };
    document.getElementById('modal-dot').style.background = colors[status];
    document.getElementById('modal-title').textContent    = labels[status];
    document.getElementById('modal-total-badge').textContent = '';
    document.getElementById('modal-search').value = '';

    const modalBody = document.getElementById('modal-body');
    modalBody.innerHTML = '<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div> Chargement…</div>';

    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    modal.show();

    const sep = detailsUrl.includes('?') ? '&' : '?';
    fetch(detailsUrl + sep + 'status=' + status)
        .then(r => r.json())
        .then(data => renderDetails(data.groups, status, colors[status]))
        .catch(() => { modalBody.innerHTML = '<p class="text-danger p-4">Erreur de chargement.</p>'; });
}

function renderDetails(groups, status, color) {
    const modalBody = document.getElementById('modal-body');
    if (!groups || groups.length === 0) {
        modalBody.innerHTML = '<p class="text-muted p-4">Aucune séance trouvée.</p>';
        return;
    }

    // Total sessions count
    const totalSessions = groups.reduce((s, g) => s + g.sessions.length, 0);
    document.getElementById('modal-total-badge').textContent = totalSessions + ' séances · ' + groups.length + ' groupes';

    let html = '';
    groups.forEach((g, gi) => {
        const cnt = g.sessions.length;
        html += `
        <div class="group-block border-bottom" data-group="${(g.name+' '+g.teacher).toLowerCase()}">
            <div class="d-flex align-items-center justify-content-between px-4 py-3" style="background:#f8f9fa;cursor:pointer" onclick="toggleGroup(${gi})">
                <div>
                    <strong>${g.name}</strong>
                    <span class="text-muted small ms-2">${g.teacher}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill" style="background:${color}22;color:${color};font-size:.75rem">${cnt} séance${cnt>1?'s':''}</span>
                    <i class="ph ph-caret-down" id="caret-${gi}" style="transition:transform .2s;font-size:1rem;color:#6c757d"></i>
                </div>
            </div>
            <div id="group-rows-${gi}" class="table-responsive" style="display:none">
                <table class="table table-sm table-hover mb-0" style="font-size:.8rem">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Réf</th>
                            <th>Horaire</th>
                            <th class="text-center" style="color:#1cc88a">Présents</th>
                            <th class="text-center" style="color:#e74c3c">Absents</th>
                            <th class="text-center">Total</th>
                            ${status==='saisie'?'<th>Saisi par</th><th>Saisi le</th>':''}
                        </tr>
                    </thead>
                    <tbody>`;
        g.sessions.forEach(s => {
            const dt = s.date ? new Date(s.date+'T00:00:00').toLocaleDateString('fr-FR',{weekday:'short',day:'2-digit',month:'short',year:'numeric'}) : '—';
            const hr = s.start_time ? `${s.start_time}–${s.end_time}` : '—';
            html += `<tr>
                <td class="ps-4 fw-semibold">${dt}</td>
                <td class="text-muted">${s.session_ref??'—'}</td>
                <td>${hr}</td>
                <td class="text-center" style="color:#1cc88a"><strong>${s.present}</strong></td>
                <td class="text-center" style="color:#e74c3c"><strong>${s.absent}</strong></td>
                <td class="text-center">${s.total}</td>
                ${status==='saisie'?`<td class="text-muted small">${s.created_by??'—'}</td><td class="text-muted small">${s.date_creation??'—'}</td>`:''}
            </tr>`;
        });
        html += `</tbody></table></div></div>`;
    });

    modalBody.innerHTML = html;
}

function toggleGroup(gi) {
    const rows  = document.getElementById('group-rows-'+gi);
    const caret = document.getElementById('caret-'+gi);
    const open  = rows.style.display !== 'none';
    rows.style.display  = open ? 'none' : 'block';
    caret.style.transform = open ? '' : 'rotate(180deg)';
}

function filterGroups(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.group-block').forEach(el => {
        el.style.display = el.dataset.group.includes(q) ? '' : 'none';
    });
}
</script>
@endsection

@endsection
