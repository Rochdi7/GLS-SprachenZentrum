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
<div class="row mt-3">
    <div class="col-12">
        <div class="card" id="suivi-niveau">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Suivi niveau (rappels profs)</h5>
                <span class="badge bg-light-primary">{{ $levelFollowupsDue->count() }} rappel(s) dû(s)</span>
            </div>

            <div class="card-body">
                @if($levelFollowupsDue->isEmpty())
                    <div class="alert alert-info mb-0">
                        Aucun rappel de suivi niveau pour aujourd'hui.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Groupe</th>
                                    <th>Prof</th>
                                    <th>Niveaux</th>
                                    <th>Échéance</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($levelFollowupsDue as $followup)
                                    @php
                                        $order = ['A1', 'A2', 'B1', 'B2'];
                                        $startLevel = $followup->group?->level;
                                        $startIndex = is_string($startLevel) ? array_search($startLevel, $order, true) : 0;
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
                                        <td class="fw-semibold">{{ $followup->group?->name }}</td>
                                        <td>{{ $followup->group?->teacher?->name ?? '-' }}</td>
                                        <td style="min-width:260px;">
                                            <div class="d-flex flex-column gap-2">
                                                <div class="progress" style="height:6px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $percent }}%;">
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center gap-2">
                                                    @foreach($order as $idx => $lvl)
                                                        @php
                                                            $inactive = $idx < $startIndex;
                                                            $isDone = (!$inactive && $idx <= $lastDoneIndex);
                                                            $circleClass = $inactive
                                                                ? 'bg-light-secondary'
                                                                : ($isDone ? 'bg-light-success' : 'bg-light-warning');
                                                        @endphp
                                                        <span class="avtar rounded-circle {{ $circleClass }}"
                                                            style="min-width:44px;text-align:center;">
                                                            {{ $lvl }}
                                                        </span>
                                                    @endforeach
                                                </div>

                                                <div class="text-muted text-sm">
                                                    Niveau actuel : <strong>{{ $followup->level }}</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($followup->due_date)->format('d/m/Y') }}
                                        </td>
                                        <td class="text-end">
                                            <form method="POST"
                                                action="{{ route('backoffice.level_followups.complete', $followup) }}">
                                                @csrf
                                                <textarea name="done_notes"
                                                    class="form-control form-control-sm"
                                                    rows="2"
                                                    placeholder="Notes (facultatif)"></textarea>
                                                <button type="submit" class="btn btn-success btn-sm mt-2">
                                                    Marquer terminé
                                                </button>
                                            </form>
                                        </td>
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
{{-- ApexCharts THEME --}}
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script>
// chart-apex.js is a theme demo file targeting ~20 chart IDs; only 3 exist here.
// Wrap the constructor so missing elements are silently skipped.
(function () {
    var _Real = window.ApexCharts;
    window.ApexCharts = function (el, opts) {
        if (!el) return { render: function () { return Promise.resolve(); } };
        return new _Real(el, opts);
    };
    window.ApexCharts.prototype = _Real.prototype;
    Object.assign(window.ApexCharts, _Real);
})();
</script>
<script src="{{ URL::asset('build/js/chart_maps/chart-apex.js') }}"></script>

{{-- Peity THEME --}}
<script src="{{ URL::asset('build/js/plugins/peity-vanilla.min.js') }}"></script>

<script>
/* =========================
PEITY INIT (OK)
========================= */
peity.defaults.line = {
    delimiter: ",",
    fill: "#e0f5fe",
    height: 24,
    min: 0,
    stroke: "#04a9f5",
    strokeWidth: 1,
    width: 80,
};
peity.defaults.bar = {
    delimiter: ",",
    fill: ["#04a9f5"],
    height: 24,
    min: 0,
    padding: 0.1,
    width: 80,
};
peity.defaults.donut = {
    delimiter: null,
    fill: ["#ffffff", "rgba(255,255,255,.3)"],
    height: 26,
    innerRadius: 8,
    radius: 12,
    width: 26,
};

document.querySelectorAll(".line").forEach((e) => peity(e, "line"));
document.querySelectorAll(".bar").forEach((e) => peity(e, "bar"));
document.querySelectorAll(".donut").forEach((e) => peity(e, "donut"));

if (typeof feather !== 'undefined') feather.replace();


/* =========================
APEX DASHBOARD INIT
(ton thème ne lit pas data-series, donc on force ici)
========================= */
(function () {
    if (typeof ApexCharts === 'undefined') return;

    // Stockage des instances pour destroy si reload (turbo/vite/hot)
    window.__glsApex = window.__glsApex || {};

    function safeParseJson(str, fallback) {
        try { return JSON.parse(str); } catch (e) { return fallback; }
    }

    function getData(el) {
        const series = safeParseJson(el.getAttribute('data-series') || '[]', []);
        const labels = safeParseJson(el.getAttribute('data-labels') || '[]', []);
        return { series, labels };
    }

    function destroyIfExists(key) {
        if (window.__glsApex[key] && typeof window.__glsApex[key].destroy === 'function') {
            window.__glsApex[key].destroy();
        }
        window.__glsApex[key] = null;
    }

    function renderBar(elId, key, name) {
        const el = document.getElementById(elId);
        if (!el) return;

        const { series, labels } = getData(el);

        // Si pas de data, on évite un chart cassé
        if (!Array.isArray(series) || series.length === 0) return;

        destroyIfExists(key);

        const options = {
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false }
            },
            series: [{ name: name, data: series }],
            xaxis: { categories: labels },
            dataLabels: { enabled: false },
            grid: { strokeDashArray: 4 }
        };

        window.__glsApex[key] = new ApexCharts(el, options);
        window.__glsApex[key].render();
    }

    function renderDonut(elId, key) {
        const el = document.getElementById(elId);
        if (!el) return;

        const { series, labels } = getData(el);

        if (!Array.isArray(series) || series.length === 0) return;

        destroyIfExists(key);

        const options = {
            chart: {
                type: 'donut',
                height: 300
            },
            series: series,
            labels: labels,
            legend: { position: 'bottom' }
        };

        window.__glsApex[key] = new ApexCharts(el, options);
        window.__glsApex[key].render();
    }

    // Render dashboard charts
    renderBar('bar-chart-1', 'bar1', 'Articles');
    renderBar('bar-chart-2', 'bar2', 'Certificats');
    renderBar('bar-chart-3', 'bar3', 'Inscriptions');
    renderBar('bar-chart-4', 'bar4', 'Consultations');
    renderDonut('pie-chart-2', 'donut1');
})();

(function () {
    const suivi = document.getElementById('suivi-niveau');
    if (!suivi) return;

    const row = suivi.closest('.row');
    const separator = document.getElementById('suivi-niveau-separator');
    const content = document.querySelector('.pc-container .pc-content');

    if (!row || !content) return;

    if (separator) {
        content.appendChild(separator);
    }

    content.appendChild(row);
})();
</script>
@endsection
 
