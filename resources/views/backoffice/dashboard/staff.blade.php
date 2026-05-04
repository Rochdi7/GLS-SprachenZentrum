@extends('layouts.main')

@section('title', 'Mon tableau de bord')
@section('breadcrumb-item', 'Backoffice')
@section('breadcrumb-item-active', 'Tableau de bord')

@section('content')
@php
    use Illuminate\Support\Carbon;
    $u = auth()->user();
    $fmtMinutes = function ($m) {
        $m = (int) $m;
        $h = intdiv($m, 60); $r = $m % 60;
        return sprintf('%dh %02dm', $h, $r);
    };
@endphp

<div class="row mb-3">
    <div class="col-md-8">
        <h4 class="mb-1">Bonjour, {{ $u->name }} 👋</h4>
        <p class="text-muted mb-0">
            Vue d'ensemble de
            @if($activeSiteId)
                @php $current = $accessibleSites->firstWhere('id', $activeSiteId); @endphp
                <strong>{{ $current?->name ?? 'centre' }}</strong>
            @elseif($accessibleSites->count() > 1)
                <strong>vos {{ $accessibleSites->count() }} centres</strong>
            @elseif($accessibleSites->count() === 1)
                <strong>{{ $accessibleSites->first()->name }}</strong>
            @else
                <strong class="text-warning">aucun centre affecté</strong>
            @endif
        </p>
    </div>

    @if($accessibleSites->count() > 1)
        <div class="col-md-4">
            <form method="GET" class="d-flex gap-2 justify-content-end">
                <select name="site_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— Tous mes centres —</option>
                    @foreach($accessibleSites as $s)
                        <option value="{{ $s->id }}" {{ $activeSiteId === $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif
</div>

@if($accessibleSites->isEmpty())
    <div class="alert alert-warning">
        Aucun centre ne vous est affecté. Demandez à un administrateur de vous rattacher à un ou plusieurs centres
        pour voir les données associées.
    </div>
@endif

{{-- ── KPIs (Certificats / Attestations / Enseignants / Groupes) ──── --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <p class="text-muted small text-uppercase mb-1">Certificats</p>
                    <i class="ti ti-certificate text-primary f-22"></i>
                </div>
                <h4 class="mb-0">{{ $certificatesTotal }}</h4>
                <small class="text-muted">{{ $certificatesThisMonth }} ce mois-ci</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <p class="text-muted small text-uppercase mb-1">Attestations</p>
                    <i class="ti ti-file-certificate text-success f-22"></i>
                </div>
                <h4 class="mb-0">{{ $attestationsTotal }}</h4>
                <small class="text-muted">{{ $attestationsThisMonth }} ce mois-ci</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <p class="text-muted small text-uppercase mb-1">Enseignants</p>
                    <i class="ti ti-school text-info f-22"></i>
                </div>
                <h4 class="mb-0">{{ $teachersTotal }}</h4>
                <small class="text-muted">{{ $activeGroupsTotal }} groupe(s) actif(s)</small>
            </div>
        </div>
    </div>

    {{-- Demandes d'attestation --}}
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('backoffice.attestation_requests.index') }}" class="text-decoration-none">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <p class="text-muted small text-uppercase mb-1">Demandes d'attestation</p>
                    <i class="ti ti-mail-forward text-warning f-22"></i>
                </div>
                <h4 class="mb-0 text-dark">{{ $attestationRequestsTotal }}</h4>
                <small class="text-warning fw-semibold">
                    @if($attestationRequestsPending > 0)
                        <i class="ti ti-bell"></i> {{ $attestationRequestsPending }} en attente
                    @else
                        <span class="text-muted">Aucune en attente</span>
                    @endif
                </small>
            </div>
        </div>
        </a>
    </div>

    {{-- Traductions Maroc–Allemagne --}}
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('backoffice.translations.index') }}" class="text-decoration-none">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <p class="text-muted small text-uppercase mb-1">Traductions</p>
                    <i class="ti ti-language text-danger f-22"></i>
                </div>
                <h4 class="mb-0 text-dark">{{ $translationsTotal }}</h4>
                <small class="text-danger fw-semibold">
                    @if($translationsActive > 0)
                        <i class="ti ti-progress"></i> {{ $translationsActive }} en cours
                    @else
                        <span class="text-muted">Aucune en cours</span>
                    @endif
                </small>
            </div>
        </div>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <p class="text-muted small text-uppercase mb-1">Mon planning (semaine)</p>
                    <i class="ti ti-calendar-time text-warning f-22"></i>
                </div>
                <h4 class="mb-0">{{ $fmtMinutes($myWorkedMinutes) }}</h4>
                <small class="text-muted">{{ $myWeek->count() }} jour(s) planifié(s)</small>
            </div>
        </div>
    </div>
</div>

{{-- ── Suivi niveau (rappels profs) ───────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="ti ti-bell-ringing me-2 text-danger"></i>
            Suivi niveau — rappels profs
            <span class="badge bg-danger ms-2">{{ $levelFollowupsDue->count() }}</span>
        </h5>
    </div>
    <div class="card-body p-0">
        @if($levelFollowupsDue->isEmpty())
            <div class="d-flex flex-column align-items-center justify-content-center text-center py-5 px-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                      style="width:60px;height:60px;background:#e6f7ec;color:#198754;">
                    <i class="ti ti-circle-check f-22"></i>
                </span>
                <h6 class="mb-1">Tout est à jour</h6>
                <small class="text-muted">Aucun rappel de suivi de niveau en attente.</small>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Échéance</th>
                            <th>Groupe</th>
                            <th>Niveau</th>
                            <th>Enseignant</th>
                            <th>Centre</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($levelFollowupsDue as $f)
                            @php
                                $isLate = $f->due_date && $f->due_date->isPast();
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge {{ $isLate ? 'bg-danger' : 'bg-warning' }}">
                                        {{ $f->due_date?->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td>{{ $f->group?->name ?? '—' }}</td>
                                <td><span class="badge bg-light-primary">{{ $f->level }}</span></td>
                                <td>{{ $f->group?->teacher?->name ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-light-info">
                                        {{ $f->group?->site?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if($f->group)
                                        <a href="{{ route('backoffice.groups.edit', $f->group->id) }}"
                                           class="btn btn-sm btn-link" title="Voir le groupe">
                                            Détails →
                                        </a>
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

<div class="row g-3">
    {{-- ── My weekly schedule ────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="ti ti-calendar me-1"></i> Mon planning de la semaine
                </h6>
                <a href="{{ route('backoffice.schedules.week') }}" class="btn btn-sm btn-link">Détails →</a>
            </div>
            <div class="card-body p-0">
                @if($myWeek->isEmpty())
                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-5 px-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                              style="width:60px;height:60px;background:#e7f1ff;color:#0d6efd;">
                            <i class="ti ti-calendar-off f-22"></i>
                        </span>
                        <h6 class="mb-1">Aucune entrée cette semaine</h6>
                        <small class="text-muted">Votre planning sera affiché ici dès qu'il sera saisi.</small>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Jour</th>
                                    <th>Heures</th>
                                    <th class="text-end pe-3">Travaillé</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myWeek as $s)
                                    <tr>
                                        <td class="ps-3 fw-semibold text-capitalize">
                                            {{ $s->date->locale('fr')->isoFormat('ddd DD/MM') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light-secondary text-dark">
                                                <i class="ti ti-clock"></i>
                                                {{ \Illuminate\Support\Str::of($s->start_time)->limit(5,'') }}
                                                – {{ \Illuminate\Support\Str::of($s->end_time)->limit(5,'') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3 fw-bold text-primary">
                                            {{ $fmtMinutes($s->worked_minutes) }}
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

    {{-- ── Weekly reports for accessible centres ────────────────── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="ti ti-report me-1"></i> Rapports hebdo de la semaine
                </h6>
                <a href="{{ route('backoffice.weekly_reports.index') }}" class="btn btn-sm btn-link">Tous →</a>
            </div>
            <div class="card-body p-0">
                @if($weeklyReports->isEmpty())
                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-5 px-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                              style="width:60px;height:60px;background:#fff4e5;color:#a06700;">
                            <i class="ti ti-file-off f-22"></i>
                        </span>
                        <h6 class="mb-1">Aucun rapport déposé</h6>
                        <small class="text-muted">Les rapports hebdomadaires des enseignants apparaîtront ici.</small>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width:90px">Date</th>
                                    <th>Enseignant</th>
                                    <th>Groupe</th>
                                    <th class="pe-3">Centre</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($weeklyReports as $r)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge bg-light-primary text-dark">
                                                {{ $r->report_date->format('d/m') }}
                                            </span>
                                        </td>
                                        <td class="text-nowrap fw-semibold">{{ $r->teacher?->name ?? '—' }}</td>
                                        <td class="text-muted">{{ $r->group?->name ?? '—' }}</td>
                                        <td class="pe-3">
                                            <span class="badge bg-light-info text-dark">
                                                <i class="ti ti-building"></i>
                                                {{ $r->teacher?->site?->name ?? '—' }}
                                            </span>
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
@endsection
