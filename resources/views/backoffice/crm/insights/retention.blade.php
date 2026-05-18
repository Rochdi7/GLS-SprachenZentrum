@extends('layouts.main')

@section('title', 'CRM — Rétention')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Funnel de rétention')

@section('content')
    @include('backoffice.crm.partials._center')

    <form method="GET" class="card mb-3">
        <div class="card-body py-3 d-flex align-items-end gap-2 flex-wrap">
            <div>
                <label class="form-label small text-muted mb-1">Cohortes (mois en arrière)</label>
                <input type="number" name="months" value="{{ $months }}" min="2" max="24" class="form-control form-control-sm" style="width: 100px;">
            </div>
            <button class="btn btn-sm btn-primary"><i class="ti ti-search me-1"></i> Afficher</button>
            <div class="ms-auto small text-muted">
                Cohorte = mois de <code>START_DATE</code>. Rétention « statut » = Active + Archive vs total.
                Rétention « date » = a atteint END_DATE.
            </div>
        </div>
    </form>

    @if(!empty($report['error']))
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-1"></i>
            Impossible de récupérer les inscriptions : {{ $report['error'] }}
        </div>
    @endif

    @if(empty($report['cohorts']))
        <div class="alert alert-info">Aucune cohorte disponible (l'API <code>/registrations</code> est peut-être en panne).</div>
    @else
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cohorte</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Active</th>
                                <th class="text-end">Archivée</th>
                                <th class="text-end">Annulée</th>
                                <th class="text-end">Suspendue</th>
                                <th class="text-end">A atteint END_DATE</th>
                                <th class="text-end">En cours</th>
                                <th>Rétention statut</th>
                                <th>Rétention date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report['cohorts'] as $c)
                                <tr>
                                    <td class="fw-medium">{{ $c['month'] }}</td>
                                    <td class="text-end">{{ $c['total'] }}</td>
                                    <td class="text-end"><span class="badge bg-light-success text-success">{{ $c['active'] }}</span></td>
                                    <td class="text-end"><span class="badge bg-light-info text-info">{{ $c['archived'] }}</span></td>
                                    <td class="text-end"><span class="badge bg-light-danger text-danger">{{ $c['cancelled'] }}</span></td>
                                    <td class="text-end"><span class="badge bg-light-warning text-warning">{{ $c['suspended'] }}</span></td>
                                    <td class="text-end">{{ $c['reached_end'] }}</td>
                                    <td class="text-end">{{ $c['still_running'] }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; min-width: 60px;">
                                                <div class="progress-bar bg-success" style="width: {{ $c['retention_status_pct'] }}%;"></div>
                                            </div>
                                            <small class="text-nowrap">{{ $c['retention_status_pct'] }}%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; min-width: 60px;">
                                                <div class="progress-bar bg-primary" style="width: {{ $c['retention_date_pct'] }}%;"></div>
                                            </div>
                                            <small class="text-nowrap">{{ $c['retention_date_pct'] }}%</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
