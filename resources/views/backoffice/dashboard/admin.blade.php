@extends('layouts.main')

@section('title', 'Tableau de bord')
@section('breadcrumb-item', 'Backoffice')
@section('breadcrumb-item-active', 'Tableau de bord')

@section('css')
@endsection

@section('content')
@php
    $u = auth()->user();
    $fmtMinutes = function ($m) {
        $m = (int) $m; $h = intdiv($m, 60); $r = $m % 60;
        return sprintf('%dh %02dm', $h, $r);
    };
    $reportsPct  = $activeGroups > 0 ? min(100, round(($reportsReceived / $activeGroups) * 100)) : 0;
    $coveragePct = $staffCount   > 0 ? min(100, round(($staffScheduledThisWeek / $staffCount)  * 100)) : 0;

    // Centre label for the greeting
    if ($activeSiteId) {
        $current = $accessibleSites->firstWhere('id', $activeSiteId);
        $centreLabel = $current?->name ?? 'centre';
    } elseif ($accessibleSites->count() > 1) {
        $centreLabel = 'vos ' . $accessibleSites->count() . ' centres';
    } elseif ($accessibleSites->count() === 1) {
        $centreLabel = $accessibleSites->first()->name;
    } else {
        $centreLabel = null;
    }
@endphp

{{-- ── Greeting + centre picker ── --}}
<div class="row align-items-center mb-3 g-2">
    <div class="col-12 col-md">
        <h4 class="mb-1">Bonjour, {{ $u->name }} 👋</h4>
        <p class="text-muted mb-0">
            <i class="ti ti-shield-check me-1"></i> Pilotage de
            @if ($centreLabel)
                <span class="fw-medium text-dark">{{ $centreLabel }}</span>
            @else
                <span class="fw-medium text-warning">aucun centre affecté</span>
            @endif
        </p>
    </div>
    @if ($accessibleSites->count() > 1)
        <div class="col-12 col-md-auto">
            <form method="GET">
                <select name="site_id" class="form-select" onchange="this.form.submit()">
                    <option value="">— Tous mes centres —</option>
                    @foreach ($accessibleSites as $s)
                        <option value="{{ $s->id }}" {{ $activeSiteId === $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif
</div>

@if ($accessibleSites->isEmpty())
    <div class="alert alert-warning">Aucun centre ne vous est affecté. Contactez un Super Admin.</div>
@endif

{{-- =========================
TOP STAT CARDS — ROW 1
========================= --}}
<div class="row">

    {{-- Groupes actifs --}}
    <div class="col-md-4 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="users" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>
                <h5 class="mb-4">Groupes actifs</h5>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $activeGroupsTotal }}</h3>
                    <span class="bar" data-peity='{ "height": 24 }'>
                        {{ implode(',', $adminGroupsTrend) }}
                    </span>
                </div>
                <span class="badge bg-light-primary mt-2">
                    {{ $groupsTotal }} groupes total
                </span>
                <p class="text-muted text-sm mt-3">Groupes du centre</p>
                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3"
                         style="width:{{ $groupsTotal > 0 ? round(($activeGroupsTotal/$groupsTotal)*100) : 0 }}%">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Enseignants --}}
    <div class="col-md-4 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="book-open" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>
                <h5 class="mb-4">Enseignants</h5>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $teachersTotal }}</h3>
                    <span class="bar" data-peity='{ "height": 24 }'>
                        {{ implode(',', $adminTeachersTrend) }}
                    </span>
                </div>
                <span class="badge bg-light-primary mt-2">
                    {{ $activeGroupsTotal }} groupes actifs
                </span>
                <p class="text-muted text-sm mt-3">Équipe pédagogique</p>
                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:50%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Certificats --}}
    <div class="col-md-4 col-sm-12">
        <div class="card statistics-card-1 overflow-hidden bg-brand-color-3">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="award" class="text-white" style="font-size:2rem"></i>
                </div>
                <h5 class="mb-4 text-white">Certificats</h5>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="text-white f-w-300">{{ $certificatesTotal }}</h3>
                    <span class="line text-end" data-peity='{ "height": 24 }'>
                        {{ implode(',', $adminCertsTrend) }}
                    </span>
                </div>
                <p class="text-white text-opacity-75 text-sm mt-3">
                    +{{ $certificatesThisMonth }} ce mois
                </p>
                <div class="progress bg-white bg-opacity-10" style="height:7px">
                    <div class="progress-bar bg-white" style="width:65%"></div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- =========================
SECOND ROW — MANAGEMENT KPIS
========================= --}}
<div class="row">

    {{-- Candidatures de groupes --}}
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('backoffice.applications.index') }}" class="text-decoration-none">
            <div class="card statistics-card-1 overflow-hidden">
                <div class="card-body">
                    <div class="float-end">
                        <i data-feather="clipboard" class="text-brand-color-3" style="font-size:2rem"></i>
                    </div>
                    <h6 class="mb-3">Candidatures</h6>
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="f-w-300 m-b-0">{{ $groupAppsTotal }}</h3>
                        <span class="donut" data-peity='{ "height": 26 }'>
                            {{ $groupAppsPending }}/{{ $groupAppsTotal ?: 1 }}
                        </span>
                    </div>
                    <span class="badge bg-light-warning mt-2">
                        {{ $groupAppsPending }} en attente
                    </span>
                    <p class="text-muted text-sm mt-3">Demandes d'intégration</p>
                    <div class="progress" style="height:7px">
                        <div class="progress-bar bg-brand-color-3" style="width:45%"></div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Suivis niveau --}}
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('backoffice.level_followups.index') }}" class="text-decoration-none">
            <div class="card statistics-card-1 overflow-hidden">
                <div class="card-body">
                    <div class="float-end">
                        <i data-feather="bell" class="text-brand-color-3" style="font-size:2rem"></i>
                    </div>
                    <h6 class="mb-3">Suivis niveau</h6>
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="f-w-300 m-b-0">{{ $pendingFollowups }}</h3>
                        <span class="donut" data-peity='{ "height": 26 }'>
                            {{ $levelFollowupsDue->count() }}/{{ $pendingFollowups ?: 1 }}
                        </span>
                    </div>
                    <span class="badge {{ $levelFollowupsDue->count() ? 'bg-light-danger' : 'bg-light-success' }} mt-2">
                        {{ $levelFollowupsDue->count() ? $levelFollowupsDue->count().' échu(s)' : 'À jour' }}
                    </span>
                    <p class="text-muted text-sm mt-3">Rappels profs</p>
                    <div class="progress" style="height:7px">
                        <div class="progress-bar bg-brand-color-3" style="width:60%"></div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Demandes d'attestation --}}
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('backoffice.attestation_requests.index') }}" class="text-decoration-none">
            <div class="card statistics-card-1 overflow-hidden">
                <div class="card-body">
                    <div class="float-end">
                        <i data-feather="mail" class="text-brand-color-3" style="font-size:2rem"></i>
                    </div>
                    <h6 class="mb-3">Attestations</h6>
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="f-w-300 m-b-0">{{ $attestationRequestsTotal }}</h3>
                        <span class="donut" data-peity='{ "height": 26 }'>
                            {{ $attestationRequestsPending }}/{{ $attestationRequestsTotal ?: 1 }}
                        </span>
                    </div>
                    <span class="badge {{ $attestationRequestsPending ? 'bg-light-warning' : 'bg-light-success' }} mt-2">
                        {{ $attestationRequestsPending ? $attestationRequestsPending.' en attente' : 'À jour' }}
                    </span>
                    <p class="text-muted text-sm mt-3">Demandes d'attestation</p>
                    <div class="progress" style="height:7px">
                        <div class="progress-bar bg-brand-color-3" style="width:55%"></div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Attestations délivrées --}}
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="file-text" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>
                <h6 class="mb-3">Attestations délivrées</h6>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $attestationsTotal }}</h3>
                </div>
                <span class="badge bg-light-success mt-2">
                    +{{ $attestationsThisMonth }} ce mois
                </span>
                <p class="text-muted text-sm mt-3">Attestations de cours</p>
                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:70%"></div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- =========================
WEEKLY HEALTH BARS
========================= --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0"><i class="ti ti-report me-2 text-primary"></i>Rapports hebdo reçus</h6>
                    <span class="fw-bold">{{ $reportsReceived }}/{{ $activeGroups }}</span>
                </div>
                <div class="progress" style="height:7px;">
                    <div class="progress-bar {{ $reportsPct >= 80 ? 'bg-success' : ($reportsPct >= 40 ? 'bg-warning' : 'bg-danger') }}"
                         style="width: {{ $reportsPct }}%"></div>
                </div>
                <p class="text-muted text-sm mt-2 mb-0">{{ $reportsPct }}% des groupes actifs ont déposé leur rapport cette semaine.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0"><i class="ti ti-calendar-check me-2 text-primary"></i>Planning équipe (semaine)</h6>
                    <span class="fw-bold">{{ $staffScheduledThisWeek }}/{{ $staffCount }}</span>
                </div>
                <div class="progress" style="height:7px;">
                    <div class="progress-bar {{ $coveragePct >= 80 ? 'bg-success' : ($coveragePct >= 40 ? 'bg-warning' : 'bg-danger') }}"
                         style="width: {{ $coveragePct }}%"></div>
                </div>
                <p class="text-muted text-sm mt-2 mb-0">
                    {{ $staffScheduledThisWeek }} employé(s) planifié(s).
                    <a href="{{ route('backoffice.schedules.manage') }}">Gérer le planning</a>
                </p>
            </div>
        </div>
    </div>
</div>

{{-- ── Per-centre breakdown ── --}}
@if ($siteBreakdown->count() > 1)
    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0"><i class="ti ti-building me-2"></i>Détail par centre</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Centre</th><th class="text-center">Groupes actifs</th><th class="text-center">Enseignants</th></tr></thead>
                    <tbody>
                        @foreach ($siteBreakdown as $row)
                            <tr>
                                <td class="fw-medium">{{ $row['name'] }}</td>
                                <td class="text-center"><span class="badge bg-light-primary">{{ $row['groups'] }}</span></td>
                                <td class="text-center"><span class="badge bg-light-info">{{ $row['teachers'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

{{-- =========================
SUIVI NIVEAU (Rappels profs)
========================= --}}
@php
    $snTotal = $levelFollowupsDue->count();
    $snRows  = $levelFollowupsDue->take(5);
@endphp
<style>
    .sn-card .sn-pill {
        display:inline-flex;align-items:center;justify-content:center;
        width:30px;height:30px;border-radius:50%;font-size:.7rem;font-weight:700;
        border:2px solid transparent;
    }
    .sn-pill--done    { background:#e6faf0;color:#09b10f;border-color:#09b10f; }
    .sn-pill--current { background:#eaf3ff;color:#2f7ed8;border-color:#2f7ed8; }
    .sn-pill--pending { background:#f5f7fa;color:#a0a9b8;border-color:#d0d5de; }
    .sn-pill--inactive{ background:#f9f9f9;color:#ccc;border-color:#e8e8e8; }
    .sn-card .sn-bar { height:5px;border-radius:999px;background:#e9ecf1;overflow:hidden;margin-top:5px; }
    .sn-card .sn-bar__fill { height:100%;border-radius:999px;background:#09b10f; }
    .sn-card table td { padding:13px 12px;border-bottom:1px solid #f1f4f9;vertical-align:middle; }
    .sn-card table thead th { padding:10px 12px;font-size:.7rem;font-weight:700;text-transform:uppercase;
        letter-spacing:.05em;color:#8a93a8;border-bottom:2px solid #eef2f7;background:#fafbfc; }
    .sn-card table tr:last-child td { border-bottom:none; }
    .sn-due { display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;
        font-size:.74rem;font-weight:600;background:#fff0f0;color:#c0392b; }
    .sn-complete-toggle { cursor:pointer; }
    .sn-notes-row textarea { font-size:.82rem; }
</style>
<div class="row mt-3">
    <div class="col-12">
        <div class="card sn-card" id="suivi-niveau">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0">Suivi niveau <small class="text-muted fw-normal">(rappels profs)</small></h5>
                <div class="d-flex align-items-center gap-2">
                    @if($snTotal)
                        <span class="badge bg-danger">{{ $snTotal }} rappel(s) dû(s)</span>
                    @endif
                    <a href="{{ route('backoffice.level_followups.index') }}" class="btn btn-sm btn-light-primary">
                        Voir tout <i class="ti ti-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                @if($snRows->isEmpty())
                    <p class="text-muted p-4 mb-0">
                        <i class="ti ti-circle-check text-success me-1"></i>
                        Aucun rappel de suivi niveau pour aujourd'hui.
                    </p>
                @else
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Groupe · Prof</th>
                                    <th>Niveaux</th>
                                    <th>Progression</th>
                                    <th>Fin formation</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($snRows as $followup)
                                    @php
                                        $order      = ['A1', 'A2', 'B1', 'B2'];
                                        $startLevel = $followup->group?->level;
                                        $startIndex = is_string($startLevel) ? array_search($startLevel, $order, true) : 0;
                                        $startIndex = ($startIndex === false) ? 0 : $startIndex;
                                        $allForGroup = $levelFollowupsByGroup[$followup->group_id] ?? collect();
                                        $doneLevels  = $allForGroup->where('status', 'done')->pluck('level')->all();

                                        $lastDoneIndex = -1;
                                        foreach ($order as $idx => $lvl) {
                                            if ($idx < $startIndex) continue;
                                            if (in_array($lvl, $doneLevels, true)) $lastDoneIndex = $idx;
                                        }

                                        $totalSteps = max(1, count(array_slice($order, $startIndex)));
                                        $doneSteps  = max(0, ($lastDoneIndex - $startIndex + 1));
                                        $percent    = (int) round(($doneSteps / $totalSteps) * 100);
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="fw-semibold" style="color:#243042">{{ $followup->group?->name ?? '—' }}</div>
                                            <div class="text-muted" style="font-size:.82rem">
                                                <i class="ti ti-user f-12 me-1"></i>{{ $followup->group?->teacher?->name ?? '—' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @foreach($order as $idx => $lvl)
                                                    @php
                                                        $inactive  = $idx < $startIndex;
                                                        $isDone    = (!$inactive && $idx <= $lastDoneIndex);
                                                        $isCurrent = (!$inactive && $idx === $lastDoneIndex + 1);
                                                        $state     = $inactive ? 'inactive' : ($isDone ? 'done' : ($isCurrent ? 'current' : 'pending'));
                                                    @endphp
                                                    <span class="sn-pill sn-pill--{{ $state }}">{{ $lvl }}</span>
                                                @endforeach
                                            </div>
                                            <div class="text-muted mt-1" style="font-size:.75rem">
                                                Actuel : <strong>{{ $followup->level }}</strong>
                                            </div>
                                        </td>
                                        <td style="min-width:110px">
                                            <div style="font-size:.8rem;font-weight:700;color:#243042">{{ $percent }}%</div>
                                            <div class="sn-bar"><div class="sn-bar__fill" style="width:{{ $percent }}%"></div></div>
                                        </td>
                                        <td>
                                            <span class="sn-due">
                                                <i class="ti ti-calendar-event f-13"></i>
                                                {{ $followup->group?->date_fin ? \Carbon\Carbon::parse($followup->group->date_fin)->format('d/m/Y') : '—' }}
                                            </span>
                                        </td>
                                        <td class="text-end" style="min-width:170px">
                                            <form method="POST" action="{{ route('backoffice.level_followups.complete', $followup) }}">
                                                @csrf
                                                <div class="sn-notes-row d-none mb-2" id="sn-notes-{{ $followup->id }}">
                                                    <textarea name="done_notes" class="form-control form-control-sm"
                                                              rows="2" placeholder="Notes (facultatif)"></textarea>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-end gap-1">
                                                    <button type="button"
                                                            class="btn btn-sm btn-light-secondary sn-complete-toggle"
                                                            data-target="sn-notes-{{ $followup->id }}"
                                                            title="Ajouter une note">
                                                        <i class="ti ti-note"></i>
                                                    </button>
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="ti ti-check me-1"></i>Terminé
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($snTotal > 5)
                        <div class="text-center border-top py-2">
                            <a href="{{ route('backoffice.level_followups.index') }}" class="text-decoration-none">
                                + {{ $snTotal - 5 }} autre(s) rappel(s) — voir tout
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.sn-complete-toggle').forEach(function (b) {
    b.addEventListener('click', function () {
        var el = document.getElementById(this.dataset.target);
        if (el) el.classList.toggle('d-none');
    });
});
</script>

<hr id="suivi-niveau-separator">

{{-- =========================
APEX CHARTS — centre-scoped data
IDs: admin-bar-1 … admin-bar-4, admin-donut-1
========================= --}}
<div class="row">

    {{-- Bar 1: Groupes par mois --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Groupes par mois</h5>
            </div>
            <div class="card-body">
                <div id="admin-bar-1"
                     data-series='@json(array_values($adminGroupsByMonth->toArray()))'
                     data-labels='@json(array_keys($adminGroupsByMonth->toArray()))'>
                </div>
            </div>
        </div>
    </div>

    {{-- Bar 2: Certificats par mois --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Certificats par mois</h5>
            </div>
            <div class="card-body">
                <div id="admin-bar-2"
                     data-series='@json(array_values($adminCertsByMonth->toArray()))'
                     data-labels='@json(array_keys($adminCertsByMonth->toArray()))'>
                </div>
            </div>
        </div>
    </div>

    {{-- Bar 3: Inscriptions par mois --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Inscriptions par mois</h5>
            </div>
            <div class="card-body">
                <div id="admin-bar-3"
                     data-series='@json(array_values($adminInscriptionsByMonth->toArray()))'
                     data-labels='@json(array_keys($adminInscriptionsByMonth->toArray()))'>
                </div>
            </div>
        </div>
    </div>

    {{-- Bar 4: Candidatures par mois --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Candidatures par mois</h5>
            </div>
            <div class="card-body">
                <div id="admin-bar-4"
                     data-series='@json(array_values($adminGroupAppsByMonth->toArray()))'
                     data-labels='@json(array_keys($adminGroupAppsByMonth->toArray()))'>
                </div>
            </div>
        </div>
    </div>

    {{-- Donut: Candidatures (statuts) --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Candidatures (statuts)</h5>
            </div>
            <div class="card-body">
                <div id="admin-donut-1"
                     data-series='@json(array_values($adminGroupAppsByStatus))'
                     data-labels='@json(array_keys($adminGroupAppsByStatus))'
                     style="width:100%">
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Planning + rapports ── --}}
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="ti ti-calendar me-2"></i> Mon planning de la semaine</h5>
                <a href="{{ route('backoffice.schedules.week') }}" class="btn btn-link-primary btn-sm">Détails</a>
            </div>
            <div class="card-body p-0">
                @if ($myWeek->isEmpty())
                    <div class="text-center py-5 px-3">
                        <div class="avtar avtar-l bg-light-primary mx-auto mb-3"><i class="ti ti-calendar-off f-24"></i></div>
                        <h6 class="mb-1">Aucune entrée cette semaine</h6>
                        <p class="text-muted mb-0">Votre planning sera affiché ici dès qu'il sera saisi.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Jour</th><th>Heures</th><th class="text-end">Travaillé</th></tr></thead>
                            <tbody>
                                @foreach ($myWeek as $s)
                                    <tr>
                                        <td class="fw-medium text-capitalize">{{ $s->date->locale('fr')->isoFormat('ddd DD/MM') }}</td>
                                        <td><span class="badge bg-light-secondary"><i class="ti ti-clock me-1"></i>{{ substr($s->start_time,0,5) }} - {{ substr($s->end_time,0,5) }}</span></td>
                                        <td class="text-end fw-bold text-primary">{{ $fmtMinutes($s->worked_minutes) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="ti ti-report me-2"></i> Rapports hebdo de la semaine</h5>
                <a href="{{ route('backoffice.weekly_reports.index') }}" class="btn btn-link-primary btn-sm">Tous</a>
            </div>
            <div class="card-body p-0">
                @if ($weeklyReports->isEmpty())
                    <div class="text-center py-5 px-3">
                        <div class="avtar avtar-l bg-light-warning mx-auto mb-3"><i class="ti ti-file-off f-24"></i></div>
                        <h6 class="mb-1">Aucun rapport déposé</h6>
                        <p class="text-muted mb-0">Les rapports hebdomadaires des enseignants apparaîtront ici.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th style="width:90px">Date</th><th>Enseignant</th><th>Groupe</th><th>Centre</th></tr></thead>
                            <tbody>
                                @foreach ($weeklyReports as $r)
                                    <tr>
                                        <td><span class="badge bg-light-primary">{{ $r->report_date->format('d/m') }}</span></td>
                                        <td class="text-nowrap fw-medium">{{ $r->teacher?->name ?? '-' }}</td>
                                        <td class="text-muted">{{ $r->group?->name ?? '-' }}</td>
                                        <td><span class="badge bg-light-info">{{ $r->teacher?->site?->name ?? '-' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('build/js/plugins/peity-vanilla.min.js') }}"></script>
<script>
'use strict';

// ── ApexCharts null-element guard ────────────────────────────────────────────
(function () {
    var _Real = window.ApexCharts;
    if (!_Real) return;
    window.ApexCharts = function (el, opts) {
        if (!el) return { render: function () { return Promise.resolve(); } };
        return new _Real(el, opts);
    };
    window.ApexCharts.prototype = _Real.prototype;
    Object.assign(window.ApexCharts, _Real);
})();

// ── Peity sparklines ─────────────────────────────────────────────────────────
(function () {
    if (typeof peity === 'undefined') return;
    peity.defaults.line  = { delimiter: ',', fill: '#e0f5fe', height: 24, min: 0, stroke: '#04a9f5', strokeWidth: 1, width: 80 };
    peity.defaults.bar   = { delimiter: ',', fill: ['#04a9f5'], height: 24, min: 0, padding: 0.1, width: 80 };
    peity.defaults.donut = { delimiter: null, fill: ['#ffffff', 'rgba(255,255,255,.3)'], height: 26, innerRadius: 8, radius: 12, width: 26 };
    document.querySelectorAll('.line').forEach(e  => peity(e, 'line'));
    document.querySelectorAll('.bar').forEach(e   => peity(e, 'bar'));
    document.querySelectorAll('.donut').forEach(e => peity(e, 'donut'));
})();

if (typeof feather !== 'undefined') feather.replace();

// ── ApexCharts admin dashboard ───────────────────────────────────────────────
(function () {
    if (typeof ApexCharts === 'undefined') return;

    window.__glsApex = window.__glsApex || {};

    function safeJson(str, fb) { try { return JSON.parse(str); } catch (e) { return fb; } }
    function getData(el) {
        return {
            series: safeJson(el.getAttribute('data-series') || '[]', []),
            labels: safeJson(el.getAttribute('data-labels') || '[]', []),
        };
    }
    function destroy(key) {
        if (window.__glsApex[key]?.destroy) window.__glsApex[key].destroy();
        window.__glsApex[key] = null;
    }

    function renderBar(elId, key, name) {
        const el = document.getElementById(elId);
        if (!el) return;
        const { series, labels } = getData(el);
        if (!series.length) return;
        destroy(key);
        window.__glsApex[key] = new ApexCharts(el, {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{ name, data: series }],
            xaxis: { categories: labels },
            dataLabels: { enabled: false },
            grid: { strokeDashArray: 4 },
        });
        window.__glsApex[key].render();
    }

    function renderDonut(elId, key) {
        const el = document.getElementById(elId);
        if (!el) return;
        const { series, labels } = getData(el);
        if (!series.length) return;
        destroy(key);
        window.__glsApex[key] = new ApexCharts(el, {
            chart: { type: 'donut', height: 300 },
            series,
            labels,
            legend: { position: 'bottom' },
        });
        window.__glsApex[key].render();
    }

    renderBar('admin-bar-1', 'adminBar1', 'Groupes');
    renderBar('admin-bar-2', 'adminBar2', 'Certificats');
    renderBar('admin-bar-3', 'adminBar3', 'Inscriptions');
    renderBar('admin-bar-4', 'adminBar4', 'Candidatures');
    renderDonut('admin-donut-1', 'adminDonut1');
})();

// ── Suivi-niveau widget reorder ───────────────────────────────────────────────
(function () {
    const suivi = document.getElementById('suivi-niveau');
    if (!suivi) return;
    const row       = suivi.closest('.row');
    const separator = document.getElementById('suivi-niveau-separator');
    const content   = document.querySelector('.pc-container .pc-content');
    if (!row || !content) return;
    if (separator) content.appendChild(separator);
    content.appendChild(row);
})();
</script>
@endsection
