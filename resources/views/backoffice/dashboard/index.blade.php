@extends('layouts.main')

@section('title', 'Tableau de bord GLS')
@section('breadcrumb-item', 'Accueil')
@section('breadcrumb-item-active', 'Dashboard')

@section('css')
@endsection

@section('content')

{{-- =========================
TOP STATISTICS (DESIGN IDENTIQUE)
========================= --}}
<div class="row">

    {{-- Centres GLS --}}
    <div class="col-md-4 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="map-pin" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>

                <h5 class="mb-4">Centres GLS</h5>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $stats['totalSites'] }}</h3>

                    {{-- Peity sparkline --}}
                    <span class="line text-end" data-peity='{ "height": 24 }'>
                        {{ implode(',', $analytics['sitesTrend']) }}
                    </span>
                </div>

                <span class="badge bg-light-success mt-2">
                    {{ $stats['activeGroups'] }} groupes actifs
                </span>

                <p class="text-muted text-sm mt-3">Centres de formation GLS</p>

                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:85%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Enseignants --}}
    <div class="col-md-4 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="users" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>

                <h5 class="mb-4">Enseignants</h5>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $stats['totalTeachers'] }}</h3>

                    {{-- Peity bar --}}
                    <span class="bar" data-peity='{ "height": 24 }'>
                        {{ implode(',', $analytics['teachersTrend']) }}
                    </span>
                </div>

                <span class="badge bg-light-primary mt-2">
                    {{ $stats['totalGroups'] }} groupes
                </span>

                <p class="text-muted text-sm mt-3">Équipe pédagogique GLS</p>

                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:50%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Articles --}}
    <div class="col-md-4 col-sm-12">
        <div class="card statistics-card-1 overflow-hidden bg-brand-color-3">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="file-text" class="text-white" style="font-size:2rem"></i>
                </div>

                <h5 class="mb-4 text-white">Articles publiés</h5>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="text-white f-w-300">{{ $stats['publishedPosts'] }}</h3>

                    {{-- Peity donut --}}
                    <span class="donut" data-peity='{ "height": 26 }'>
                        {{ $stats['publishedPosts'] }}/{{ $stats['totalPosts'] }}
                    </span>
                </div>

                <p class="text-white text-opacity-75 text-sm mt-3">
                    Blog GLS
                </p>

                <div class="progress bg-white bg-opacity-10" style="height:7px">
                    <div class="progress-bar bg-white" style="width:65%"></div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- =========================
SECOND ROW (ACADEMIC)
========================= --}}
<div class="row">

    {{-- Certificats --}}
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="award" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>

                <h6 class="mb-3">Certificats</h6>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $stats['totalCertificates'] }}</h3>
                </div>

                <span class="badge bg-light-success mt-2">
                    Certificats délivrés
                </span>

                <p class="text-muted text-sm mt-3">Attestations d'examen</p>

                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:75%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Studienkollegs --}}
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="book-open" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>

                <h6 class="mb-3">Studienkollegs</h6>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $stats['totalStudienkollegs'] }}</h3>

                    <span class="donut" data-peity='{ "height": 26 }'>
                        {{ $stats['featuredStudienkollegs'] }}/{{ $stats['totalStudienkollegs'] }}
                    </span>
                </div>

                <span class="badge bg-light-primary mt-2">
                    {{ $stats['featuredStudienkollegs'] }} en vedette
                </span>

                <p class="text-muted text-sm mt-3">Programmes universitaires</p>

                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:60%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quizzes --}}
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="help-circle" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>

                <h6 class="mb-3">Quiz</h6>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $stats['totalQuizzes'] }}</h3>

                    <span class="donut" data-peity='{ "height": 26 }'>
                        {{ $stats['activeQuizzes'] }}/{{ $stats['totalQuizzes'] }}
                    </span>
                </div>

                <span class="badge bg-light-success mt-2">
                    {{ $stats['totalQuestions'] }} questions
                </span>

                <p class="text-muted text-sm mt-3">Tests de niveau</p>

                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:65%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Utilisateurs --}}
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="user" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>

                <h6 class="mb-3">Utilisateurs</h6>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $stats['totalUsers'] }}</h3>
                </div>

                <span class="badge bg-light-primary mt-2">
                    Comptes enregistrés
                </span>

                <p class="text-muted text-sm mt-3">Gestion des accès</p>

                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:40%"></div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- =========================
THIRD ROW (MODULES GLS)
========================= --}}
<div class="row">

    {{-- Inscriptions --}}
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="edit-3" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>

                <h6 class="mb-3">Inscriptions</h6>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $stats['totalInscriptions'] }}</h3>

                    <span class="line text-end" data-peity='{ "height": 24 }'>
                        {{ implode(',', $analytics['inscriptionsTrend']) }}
                    </span>
                </div>

                <span class="badge bg-light-success mt-2">
                    +{{ $stats['inscriptionsThisMonth'] }} ce mois
                </span>

                <p class="text-muted text-sm mt-3">Demandes d’inscription</p>

                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:70%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Consultations --}}
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="phone-call" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>

                <h6 class="mb-3">Consultations</h6>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $stats['totalConsultations'] }}</h3>

                    <span class="bar" data-peity='{ "height": 24 }'>
                        {{ implode(',', $analytics['consultationsTrend']) }}
                    </span>
                </div>

                <span class="badge bg-light-primary mt-2">
                    +{{ $stats['consultationsThisMonth'] }} ce mois
                </span>

                <p class="text-muted text-sm mt-3">Demandes de rappel</p>

                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:55%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Newsletter --}}
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="mail" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>

                <h6 class="mb-3">Newsletter</h6>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $stats['totalSubscribers'] }}</h3>

                    <span class="line text-end" data-peity='{ "height": 24 }'>
                        {{ implode(',', $analytics['newsletterTrend']) }}
                    </span>
                </div>

                <span class="badge bg-light-success mt-2">
                    +{{ $stats['subscribersThisMonth'] }} ce mois
                </span>

                <p class="text-muted text-sm mt-3">Abonnés newsletter</p>

                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:60%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Candidatures groupes --}}
    <div class="col-md-3 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="clipboard" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>

                <h6 class="mb-3">Candidatures</h6>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $stats['totalGroupApps'] }}</h3>

                    <span class="donut" data-peity='{ "height": 26 }'>
                        {{ $stats['pendingGroupApps'] }}/{{ $stats['totalGroupApps'] }}
                    </span>
                </div>

                <span class="badge bg-light-warning mt-2">
                    {{ $stats['pendingGroupApps'] }} en attente
                </span>

                <p class="text-muted text-sm mt-3">Demandes d’intégration</p>

                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:45%"></div>
                </div>
            </div>
        </div>
    </div>

</div>

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
                                    <th>Échéance</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($snRows as $followup)
                                    @php
                                        $order = ['A1', 'A2', 'B1', 'B2'];
                                        $startLevel = $followup->group?->level;
                                        $startIndex = is_string($startLevel) ? array_search($startLevel, $order, true) : 0;
                                        $startIndex = ($startIndex === false) ? 0 : $startIndex;
                                        $allForGroup = $levelFollowupsByGroup[$followup->group_id] ?? collect();
                                        $doneLevels = $allForGroup->where('status', 'done')->pluck('level')->all();

                                        $lastDoneIndex = -1;
                                        foreach ($order as $idx => $lvl) {
                                            if ($idx < $startIndex) continue;
                                            if (in_array($lvl, $doneLevels, true)) {
                                                $lastDoneIndex = $idx;
                                            }
                                        }

                                        $totalSteps = max(1, count(array_slice($order, $startIndex)));
                                        $doneSteps = max(0, ($lastDoneIndex - $startIndex + 1));
                                        $percent = (int) round(($doneSteps / $totalSteps) * 100);
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
                                                        $inactive = $idx < $startIndex;
                                                        $isDone    = (!$inactive && $idx <= $lastDoneIndex);
                                                        $isCurrent = (!$inactive && $idx === $lastDoneIndex + 1);
                                                        $state = $inactive ? 'inactive' : ($isDone ? 'done' : ($isCurrent ? 'current' : 'pending'));
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
                                                {{ \Carbon\Carbon::parse($followup->due_date)->format('d/m/Y') }}
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
APEX CHARTS (USING THEME chart-apex.js)
IDs: bar-chart-1, bar-chart-2, pie-chart-2
========================= --}}
<div class="row">

    {{-- Bar 1: Articles par mois --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Articles par mois</h5>
            </div>
            <div class="card-body">
                <div id="bar-chart-1"
                    data-series='@json(array_values($postsByMonth->toArray()))'
                    data-labels='@json(array_keys($postsByMonth->toArray()))'>
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
                <div id="bar-chart-2"
                    data-series='@json(array_values($certificatesByMonth->toArray()))'
                    data-labels='@json(array_keys($certificatesByMonth->toArray()))'>
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
                <div id="bar-chart-3"
                    data-series='@json(array_values($inscriptionsByMonth->toArray()))'
                    data-labels='@json(array_keys($inscriptionsByMonth->toArray()))'>
                </div>
            </div>
        </div>
    </div>

    {{-- Bar 4: Consultations par mois --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Consultations par mois</h5>
            </div>
            <div class="card-body">
                <div id="bar-chart-4"
                    data-series='@json(array_values($consultationsByMonth->toArray()))'
                    data-labels='@json(array_keys($consultationsByMonth->toArray()))'>
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
                <div id="pie-chart-2"
                    data-series='@json(array_values($groupAppsByStatus))'
                    data-labels='@json(array_keys($groupAppsByStatus))'
                    style="width:100%">
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('build/js/chart_maps/chart-apex.js') }}"></script>
<script src="{{ URL::asset('build/js/plugins/peity-vanilla.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/backoffice/dashboard-index.js') }}"></script>
@endsection
 
