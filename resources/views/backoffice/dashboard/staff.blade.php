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
            <div class="p-4 text-center text-muted">
                <i class="ti ti-check-circle f-22 text-success mb-2 d-block"></i>
                Aucun rappel en retard. 🎉
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
                                    @if($isLate)
                                        <small class="text-danger d-block">en retard</small>
                                    @endif
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
                    <div class="p-3 text-muted">Aucune entrée pour cette semaine.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Jour</th>
                                    <th>Heures</th>
                                    <th class="text-end">Travaillé</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myWeek as $s)
                                    <tr>
                                        <td>{{ $s->date->locale('fr')->isoFormat('ddd DD/MM') }}</td>
                                        <td>
                                            {{ \Illuminate\Support\Str::of($s->start_time)->limit(5,'') }}
                                            – {{ \Illuminate\Support\Str::of($s->end_time)->limit(5,'') }}
                                        </td>
                                        <td class="text-end">{{ $fmtMinutes($s->worked_minutes) }}</td>
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
                    <div class="p-3 text-muted">Aucun rapport pour cette semaine.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Enseignant</th>
                                    <th>Groupe</th>
                                    <th>Centre</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($weeklyReports as $r)
                                    <tr>
                                        <td class="text-nowrap">{{ $r->report_date->format('d/m') }}</td>
                                        <td class="text-nowrap">{{ $r->teacher?->name ?? '—' }}</td>
                                        <td>{{ $r->group?->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-light-info text-wrap" style="white-space: normal;">
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
