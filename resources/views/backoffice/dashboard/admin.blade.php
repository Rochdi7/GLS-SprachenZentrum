@extends('layouts.main')

@section('title', 'Tableau de bord')
@section('breadcrumb-item', 'Backoffice')
@section('breadcrumb-item-active', 'Tableau de bord')

@section('content')
@php
    $u = auth()->user();
    $fmtMinutes = function ($m) {
        $m = (int) $m; $h = intdiv($m, 60); $r = $m % 60;
        return sprintf('%dh %02dm', $h, $r);
    };
    $reportsPct = $activeGroups > 0 ? min(100, round(($reportsReceived / $activeGroups) * 100)) : 0;
    $coveragePct = $staffCount > 0 ? min(100, round(($staffScheduledThisWeek / $staffCount) * 100)) : 0;
@endphp

{{-- ── Greeting + centre picker ── --}}
<div class="row align-items-center mb-3 g-2">
    <div class="col-12 col-md">
        <h4 class="mb-1">Bonjour, {{ $u->name }} 👋</h4>
        <p class="text-muted mb-0">
            <i class="ti ti-shield-check me-1"></i> Pilotage de
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
    <div class="alert alert-warning">Aucun centre ne vous est affecté. Contactez un Super Admin.</div>
@endif

{{-- ── Management stat cards (Light Able statistics-card-1) ── --}}
<div class="row g-3 align-items-stretch mb-3">
    {{-- Groupes actifs --}}
    <div class="col-md-6 col-xl-3">
        <div class="card statistics-card-1 overflow-hidden h-100">
            <div class="card-body">
                <img src="{{ URL::asset('build/images/widget/img-status-4.svg') }}" alt="img" class="img-fluid img-bg" />
                <div class="d-flex align-items-center">
                    <div class="avtar bg-light-primary"><i class="ti ti-users-group f-24"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0 text-muted">Groupes actifs</p>
                        <h4 class="f-w-300 m-b-0">{{ $activeGroupsTotal }}</h4>
                    </div>
                </div>
                <div class="row g-3 mt-4 text-center">
                    <div class="col-6"><p class="mb-0 text-muted">Actifs</p><h5 class="mb-0">{{ $activeGroupsTotal }}</h5></div>
                    <div class="col-6 border-start"><p class="mb-0 text-muted">Total</p><h5 class="mb-0">{{ $groupsTotal }}</h5></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Enseignants --}}
    <div class="col-md-6 col-xl-3">
        <div class="card statistics-card-1 overflow-hidden h-100">
            <div class="card-body">
                <img src="{{ URL::asset('build/images/widget/img-status-5.svg') }}" alt="img" class="img-fluid img-bg" />
                <div class="d-flex align-items-center">
                    <div class="avtar bg-light-info"><i class="ti ti-school f-24"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0 text-muted">Enseignants</p>
                        <h4 class="f-w-300 m-b-0">{{ $teachersTotal }}</h4>
                    </div>
                </div>
                <div class="row g-3 mt-4 text-center">
                    <div class="col-6"><p class="mb-0 text-muted">Enseignants</p><h5 class="mb-0">{{ $teachersTotal }}</h5></div>
                    <div class="col-6 border-start"><p class="mb-0 text-muted">Groupes</p><h5 class="mb-0">{{ $activeGroupsTotal }}</h5></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Suivis en attente --}}
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('backoffice.level_followups.index') }}" class="card statistics-card-1 overflow-hidden h-100 text-decoration-none">
            <div class="card-body">
                <img src="{{ URL::asset('build/images/widget/img-status-6.svg') }}" alt="img" class="img-fluid img-bg" />
                <div class="d-flex align-items-center">
                    <div class="avtar bg-light-warning"><i class="ti ti-bell-ringing f-24"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0 text-muted">Suivis en attente</p>
                        <div class="d-inline-flex align-items-center">
                            <h4 class="f-w-300 m-b-0">{{ $pendingFollowups }}</h4>
                            <span class="badge {{ $levelFollowupsDue->count() ? 'bg-light-danger' : 'bg-light-success' }} ms-2">
                                {{ $levelFollowupsDue->count() ? $levelFollowupsDue->count().' échu(s)' : 'À jour' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-4 text-center">
                    <div class="col-6"><p class="mb-0 text-muted">Échus</p><h5 class="mb-0">{{ $levelFollowupsDue->count() }}</h5></div>
                    <div class="col-6 border-start"><p class="mb-0 text-muted">En attente</p><h5 class="mb-0">{{ $pendingFollowups }}</h5></div>
                </div>
            </div>
        </a>
    </div>

    {{-- Demandes d'attestation --}}
    <div class="col-md-6 col-xl-3">
        <a href="{{ route('backoffice.attestation_requests.index') }}" class="card statistics-card-1 overflow-hidden h-100 text-decoration-none">
            <div class="card-body">
                <img src="{{ URL::asset('build/images/widget/img-status-7.svg') }}" alt="img" class="img-fluid img-bg" />
                <div class="d-flex align-items-center">
                    <div class="avtar bg-light-success"><i class="ti ti-mail-forward f-24"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0 text-muted">Demandes d'attestation</p>
                        <div class="d-inline-flex align-items-center">
                            <h4 class="f-w-300 m-b-0">{{ $attestationRequestsTotal }}</h4>
                            <span class="badge {{ $attestationRequestsPending ? 'bg-light-warning' : 'bg-light-success' }} ms-2">
                                {{ $attestationRequestsPending ? $attestationRequestsPending.' en attente' : 'À jour' }}
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
</div>

{{-- ── Weekly health bars ── --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0"><i class="ti ti-report me-2 text-primary"></i>Rapports hebdo reçus</h6>
                    <span class="fw-bold">{{ $reportsReceived }}/{{ $activeGroups }}</span>
                </div>
                <div class="progress" style="height:7px;">
                    <div class="progress-bar {{ $reportsPct >= 80 ? 'bg-success' : ($reportsPct >= 40 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $reportsPct }}%"></div>
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
                    <div class="progress-bar {{ $coveragePct >= 80 ? 'bg-success' : ($coveragePct >= 40 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $coveragePct }}%"></div>
                </div>
                <p class="text-muted text-sm mt-2 mb-0">{{ $staffScheduledThisWeek }} employé(s) planifié(s). <a href="{{ route('backoffice.schedules.manage') }}">Gérer le planning</a></p>
            </div>
        </div>
    </div>
</div>

{{-- ── Per-centre breakdown ── --}}
@if ($siteBreakdown->count() > 1)
    <div class="card">
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

{{-- ── Suivi niveau ── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="ti ti-bell-ringing me-2 text-danger"></i> Suivi niveau — rappels profs</h5>
        <span class="badge bg-light-danger">{{ $levelFollowupsDue->count() }}</span>
    </div>
    <div class="card-body p-0">
        @if ($levelFollowupsDue->isEmpty())
            <div class="text-center py-5 px-3">
                <div class="avtar avtar-l bg-light-success mx-auto mb-3"><i class="ti ti-circle-check f-24"></i></div>
                <h6 class="mb-1">Tout est à jour</h6>
                <p class="text-muted mb-0">Aucun rappel de suivi de niveau en attente.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Échéance</th><th>Groupe</th><th>Niveau</th><th>Enseignant</th><th>Centre</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($levelFollowupsDue as $f)
                            @php $isLate = $f->due_date && $f->due_date->isPast(); @endphp
                            <tr>
                                <td><span class="badge {{ $isLate ? 'bg-light-danger' : 'bg-light-warning' }}">{{ $f->due_date?->format('d/m/Y') }}</span></td>
                                <td>{{ $f->group?->name ?? '-' }}</td>
                                <td><span class="badge bg-light-primary">{{ $f->level }}</span></td>
                                <td>{{ $f->group?->teacher?->name ?? '-' }}</td>
                                <td><span class="badge bg-light-info">{{ $f->group?->site?->name ?? '-' }}</span></td>
                                <td class="text-end">
                                    @if ($f->group)
                                        <a href="{{ route('backoffice.groups.edit', $f->group->id) }}" class="btn btn-link-primary btn-sm">Détails</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
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
