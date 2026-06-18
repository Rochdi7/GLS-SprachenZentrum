@extends('layouts.main')

@section('title', 'Mon tableau de bord')
@section('breadcrumb-item', 'Backoffice')
@section('breadcrumb-item-active', 'Tableau de bord')

@section('content')
@php
    $u = auth()->user();
    $fmtMinutes = function ($m) {
        $m = (int) $m; $h = intdiv($m, 60); $r = $m % 60;
        return sprintf('%dh %02dm', $h, $r);
    };
@endphp

{{-- ── Greeting + centre picker ── --}}
<div class="row align-items-center mb-3 g-2">
    <div class="col-12 col-md">
        <h4 class="mb-1">Bonjour, {{ $u->name }} 👋</h4>
        <p class="text-muted mb-0">
            <i class="ti ti-headset me-1"></i> Réception ·
            @if ($activeSiteId)
                @php $current = $accessibleSites->firstWhere('id', $activeSiteId); @endphp
                <span class="fw-medium text-dark">{{ $current?->name ?? 'centre' }}</span>
            @elseif ($accessibleSites->count() > 1)
                <span class="fw-medium text-dark">vos {{ $accessibleSites->count() }} centres</span>
            @elseif ($accessibleSites->count() === 1)
                <span class="fw-medium text-dark">{{ $accessibleSites->first()->name }}</span>
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
    <div class="alert alert-warning">
        Aucun centre ne vous est affecté. Demandez à un administrateur de vous rattacher à un ou plusieurs centres.
    </div>
@endif

{{-- ── Stat cards (Light Able statistics-card-1) ── --}}
<div class="row g-3 align-items-stretch mb-3">
    {{-- Demandes d'attestation --}}
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('backoffice.attestation_requests.index') }}" class="card statistics-card-1 overflow-hidden h-100 text-decoration-none">
            <div class="card-body">
                <img src="{{ URL::asset('build/images/widget/img-status-4.svg') }}" alt="img" class="img-fluid img-bg" />
                <div class="d-flex align-items-center">
                    <div class="avtar bg-light-warning"><i class="ti ti-mail-forward f-24"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0 text-muted">Demandes d'attestation</p>
                        <div class="d-inline-flex align-items-center">
                            <h4 class="f-w-300 m-b-0">{{ $attestationRequestsTotal }}</h4>
                            <span class="badge {{ $attestationRequestsPending > 0 ? 'bg-light-warning' : 'bg-light-success' }} ms-2">
                                {{ $attestationRequestsPending > 0 ? $attestationRequestsPending.' en attente' : 'À jour' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-4 text-center">
                    <div class="col-6"><p class="mb-0 text-muted">En attente</p><h5 class="mb-0">{{ $attestationRequestsPending }}</h5></div>
                    <div class="col-6 border-start"><p class="mb-0 text-muted">Total</p><h5 class="mb-0">{{ $attestationRequestsTotal }}</h5></div>
                </div>
            </div>
        </a>
    </div>

    {{-- Traductions --}}
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('backoffice.translations.index') }}" class="card statistics-card-1 overflow-hidden h-100 text-decoration-none">
            <div class="card-body">
                <img src="{{ URL::asset('build/images/widget/img-status-5.svg') }}" alt="img" class="img-fluid img-bg" />
                <div class="d-flex align-items-center">
                    <div class="avtar bg-light-danger"><i class="ti ti-language f-24"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0 text-muted">Traductions</p>
                        <div class="d-inline-flex align-items-center">
                            <h4 class="f-w-300 m-b-0">{{ $translationsTotal }}</h4>
                            <span class="badge {{ $translationsActive > 0 ? 'bg-light-danger' : 'bg-light-success' }} ms-2">
                                {{ $translationsActive > 0 ? $translationsActive.' en cours' : 'À jour' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-4 text-center">
                    <div class="col-6"><p class="mb-0 text-muted">En cours</p><h5 class="mb-0">{{ $translationsActive }}</h5></div>
                    <div class="col-6 border-start"><p class="mb-0 text-muted">Total</p><h5 class="mb-0">{{ $translationsTotal }}</h5></div>
                </div>
            </div>
        </a>
    </div>

    {{-- Certificats --}}
    <div class="col-md-6 col-xl-3">
        <div class="card statistics-card-1 overflow-hidden h-100">
            <div class="card-body">
                <img src="{{ URL::asset('build/images/widget/img-status-6.svg') }}" alt="img" class="img-fluid img-bg" />
                <div class="d-flex align-items-center">
                    <div class="avtar bg-light-primary"><i class="ti ti-certificate f-24"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0 text-muted">Certificats</p>
                        <h4 class="f-w-300 m-b-0">{{ $certificatesTotal }}</h4>
                    </div>
                </div>
                <div class="row g-3 mt-4 text-center">
                    <div class="col-6"><p class="mb-0 text-muted">Ce mois</p><h5 class="mb-0">{{ $certificatesThisMonth }}</h5></div>
                    <div class="col-6 border-start"><p class="mb-0 text-muted">Total</p><h5 class="mb-0">{{ $certificatesTotal }}</h5></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Attestations --}}
    <div class="col-md-6 col-xl-3">
        <div class="card statistics-card-1 overflow-hidden h-100">
            <div class="card-body">
                <img src="{{ URL::asset('build/images/widget/img-status-7.svg') }}" alt="img" class="img-fluid img-bg" />
                <div class="d-flex align-items-center">
                    <div class="avtar bg-light-success"><i class="ti ti-file-certificate f-24"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0 text-muted">Attestations</p>
                        <h4 class="f-w-300 m-b-0">{{ $attestationsTotal }}</h4>
                    </div>
                </div>
                <div class="row g-3 mt-4 text-center">
                    <div class="col-6"><p class="mb-0 text-muted">Ce mois</p><h5 class="mb-0">{{ $attestationsThisMonth }}</h5></div>
                    <div class="col-6 border-start"><p class="mb-0 text-muted">Total</p><h5 class="mb-0">{{ $attestationsTotal }}</h5></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Enseignants --}}
    <div class="col-md-6 col-xl-3">
        <div class="card statistics-card-1 overflow-hidden h-100">
            <div class="card-body">
                <img src="{{ URL::asset('build/images/widget/img-status-8.svg') }}" alt="img" class="img-fluid img-bg" />
                <div class="d-flex align-items-center">
                    <div class="avtar bg-light-info"><i class="ti ti-school f-24"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0 text-muted">Enseignants</p>
                        <h4 class="f-w-300 m-b-0">{{ $teachersTotal }}</h4>
                    </div>
                </div>
                <div class="row g-3 mt-4 text-center">
                    <div class="col-6"><p class="mb-0 text-muted">Groupes actifs</p><h5 class="mb-0">{{ $activeGroupsTotal }}</h5></div>
                    <div class="col-6 border-start"><p class="mb-0 text-muted">Enseignants</p><h5 class="mb-0">{{ $teachersTotal }}</h5></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mon planning --}}
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('backoffice.schedules.week') }}" class="card statistics-card-1 overflow-hidden h-100 text-decoration-none">
            <div class="card-body">
                <img src="{{ URL::asset('build/images/widget/img-status-9.svg') }}" alt="img" class="img-fluid img-bg" />
                <div class="d-flex align-items-center">
                    <div class="avtar bg-light-secondary"><i class="ti ti-calendar-time f-24"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0 text-muted">Mon planning (semaine)</p>
                        <h4 class="f-w-300 m-b-0">{{ $fmtMinutes($myWorkedMinutes) }}</h4>
                    </div>
                </div>
                <div class="row g-3 mt-4 text-center">
                    <div class="col-6"><p class="mb-0 text-muted">Jours</p><h5 class="mb-0">{{ $myWeek->count() }}</h5></div>
                    <div class="col-6 border-start"><p class="mb-0 text-muted">Travaillé</p><h5 class="mb-0">{{ $fmtMinutes($myWorkedMinutes) }}</h5></div>
                </div>
            </div>
        </a>
    </div>

    {{-- Candidatures groupes --}}
    <div class="col-md-6 col-xl-3">
        @can('applications.view')
        <a href="{{ route('backoffice.applications.index') }}" class="card statistics-card-1 overflow-hidden h-100 text-decoration-none">
        @else
        <div class="card statistics-card-1 overflow-hidden h-100">
        @endcan
            <div class="card-body">
                <img src="{{ URL::asset('build/images/widget/img-status-1.svg') }}" alt="img" class="img-fluid img-bg" />
                <div class="d-flex align-items-center">
                    <div class="avtar bg-light-primary"><i class="ti ti-user-plus f-24"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0 text-muted">Candidatures groupes</p>
                        <div class="d-inline-flex align-items-center">
                            <h4 class="f-w-300 m-b-0">{{ $groupAppsTotal }}</h4>
                            <span class="badge {{ $groupAppsPending > 0 ? 'bg-light-warning' : 'bg-light-success' }} ms-2">
                                {{ $groupAppsPending > 0 ? $groupAppsPending.' en attente' : 'À jour' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-4 text-center">
                    <div class="col-6"><p class="mb-0 text-muted">En attente</p><h5 class="mb-0">{{ $groupAppsPending }}</h5></div>
                    <div class="col-6 border-start"><p class="mb-0 text-muted">Total</p><h5 class="mb-0">{{ $groupAppsTotal }}</h5></div>
                </div>
            </div>
        @can('applications.view')</a>@else</div>@endcan
    </div>

    {{-- Groupes --}}
    <div class="col-md-6 col-xl-3">
        <div class="card statistics-card-1 overflow-hidden h-100">
            <div class="card-body">
                <img src="{{ URL::asset('build/images/widget/img-status-2.svg') }}" alt="img" class="img-fluid img-bg" />
                <div class="d-flex align-items-center">
                    <div class="avtar bg-light-warning"><i class="ti ti-users-group f-24"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0 text-muted">Groupes</p>
                        <h4 class="f-w-300 m-b-0">{{ $groupsTotal }}</h4>
                    </div>
                </div>
                <div class="row g-3 mt-4 text-center">
                    <div class="col-6"><p class="mb-0 text-muted">Actifs</p><h5 class="mb-0">{{ $activeGroupsTotal }}</h5></div>
                    <div class="col-6 border-start"><p class="mb-0 text-muted">Total</p><h5 class="mb-0">{{ $groupsTotal }}</h5></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Activité (6 derniers mois) ── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="ti ti-chart-line me-2 text-primary"></i> Activité des 6 derniers mois</h5>
        <span class="text-muted small">Demandes &amp; traductions reçues</span>
    </div>
    <div class="card-body">
        <div id="staff-activity-chart"></div>
    </div>
</div>

{{-- ── Suivi niveau (rappels profs) — design riche avec niveaux + progression ── --}}
@php
    $snTotal = $levelFollowupsDue->count();
    $snRows  = $levelFollowupsDue->take(5);
@endphp
<style>
    .sn-card .sn-pill { display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:50%;font-size:.7rem;font-weight:700;border:2px solid transparent; }
    .sn-pill--done    { background:#e6faf0;color:#09b10f;border-color:#09b10f; }
    .sn-pill--current { background:#eaf3ff;color:#2f7ed8;border-color:#2f7ed8; }
    .sn-pill--pending { background:#f5f7fa;color:#a0a9b8;border-color:#d0d5de; }
    .sn-pill--inactive{ background:#f9f9f9;color:#ccc;border-color:#e8e8e8; }
    .sn-card .sn-bar { height:5px;border-radius:999px;background:#e9ecf1;overflow:hidden;margin-top:5px; }
    .sn-card .sn-bar__fill { height:100%;border-radius:999px;background:#09b10f; }
    .sn-card table td { padding:13px 12px;border-bottom:1px solid #f1f4f9;vertical-align:middle; }
    .sn-card table thead th { padding:10px 12px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#8a93a8;border-bottom:2px solid #eef2f7;background:#fafbfc; }
    .sn-card table tr:last-child td { border-bottom:none; }
    .sn-due { display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;font-size:.74rem;font-weight:600;background:#fff0f0;color:#c0392b; }
    .sn-complete-toggle { cursor:pointer; }
    .sn-notes-row textarea { font-size:.82rem; }
</style>
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
                                $order = ['A1', 'A2', 'B1', 'B2'];
                                $startLevel = $followup->group?->level;
                                $startIndex = is_string($startLevel) ? array_search($startLevel, $order, true) : 0;
                                $startIndex = ($startIndex === false) ? 0 : $startIndex;
                                $allForGroup = $levelFollowupsByGroup[$followup->group_id] ?? collect();
                                $doneLevels = $allForGroup->where('status', 'done')->pluck('level')->all();

                                $lastDoneIndex = -1;
                                foreach ($order as $idx => $lvl) {
                                    if ($idx < $startIndex) continue;
                                    if (in_array($lvl, $doneLevels, true)) { $lastDoneIndex = $idx; }
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
                                        {{ $followup->group?->date_fin ? \Carbon\Carbon::parse($followup->group->date_fin)->format('d/m/Y') : '—' }}
                                    </span>
                                </td>
                                <td class="text-end" style="min-width:170px">
                                    <form method="POST" action="{{ route('backoffice.level_followups.complete', $followup) }}">
                                        @csrf
                                        <div class="sn-notes-row d-none mb-2" id="sn-notes-{{ $followup->id }}">
                                            <textarea name="done_notes" class="form-control form-control-sm" rows="2" placeholder="Notes (facultatif)"></textarea>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                            <button type="button" class="btn btn-sm btn-light-secondary sn-complete-toggle" data-target="sn-notes-{{ $followup->id }}" title="Ajouter une note">
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

{{-- ── Planning + rapports ── --}}
<div class="row g-3">
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.querySelector('#staff-activity-chart');
        if (!el || typeof ApexCharts === 'undefined') return;

        var options = {
            chart: { type: 'area', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            colors: ['#4680ff', '#e58a00'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05, stops: [0, 90, 100] } },
            series: [
                { name: "Demandes d'attestation", data: @json($activityRequests) },
                { name: 'Traductions',            data: @json($activityTranslations) }
            ],
            xaxis: { categories: @json($activityLabels), axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { formatter: function (v) { return Math.round(v); } } },
            legend: { position: 'top', horizontalAlign: 'right' },
            grid: { borderColor: '#e9ecef', strokeDashArray: 4 },
            tooltip: { y: { formatter: function (v) { return Math.round(v); } } }
        };
        new ApexCharts(el, options).render();
    });

    // Suivi niveau — toggle the optional note textarea before completing.
    document.querySelectorAll('.sn-complete-toggle').forEach(function (b) {
        b.addEventListener('click', function () {
            var target = document.getElementById(this.dataset.target);
            if (target) target.classList.toggle('d-none');
        });
    });
</script>
@endsection
