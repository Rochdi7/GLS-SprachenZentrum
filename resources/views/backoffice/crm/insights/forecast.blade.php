@extends('layouts.main')

@section('title', 'CRM — Prévisions revenus')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Prévisions revenus')

@section('content')
    @include('backoffice.crm.partials._center')

    <form method="GET" class="card mb-3">
        <div class="card-body py-3 d-flex align-items-end gap-2 flex-wrap">
            <div>
                <label class="form-label small text-muted mb-1">Horizon (mois)</label>
                <input type="number" name="months" value="{{ $months }}" min="2" max="12" class="form-control form-control-sm" style="width: 100px;">
            </div>
            <button class="btn btn-sm btn-primary"><i class="ti ti-search me-1"></i> Afficher</button>
            <div class="ms-auto small text-muted">
                Prévision = moyenne des paiements des 3 derniers mois clos par centre.
            </div>
        </div>
    </form>

    @php
        $allMonths = $report['months'] ?? [];
        $series    = $report['series'] ?? [];
        $grandForecast = array_sum(array_column($series, 'total_forecast'));
        $grandActual   = array_sum(array_column($series, 'total_actual'));
    @endphp

    {{-- Aggregate KPIs --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="small text-muted mb-1">Prévision totale ({{ count($allMonths) }} mois)</p>
                    <h3 class="mb-0">{{ number_format($grandForecast, 0, ',', ' ') }} <small class="text-muted">DH</small></h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="small text-muted mb-1">Actuel cumulé (mêmes mois)</p>
                    <h3 class="mb-0">{{ number_format($grandActual, 0, ',', ' ') }} <small class="text-muted">DH</small></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Centre</th>
                            <th class="text-end">Baseline /mois</th>
                            @foreach($allMonths as $m)
                                <th class="text-end small">{{ $m }}</th>
                            @endforeach
                            <th class="text-end">Total prévu</th>
                            <th class="text-end">Total actuel</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($series as $row)
                            @php $site = $row['site']; @endphp
                            <tr>
                                <td class="fw-medium">{{ $site->name }}</td>
                                <td class="text-end small text-muted">
                                    {{ number_format($row['baseline'], 0, ',', ' ') }}
                                </td>
                                @foreach($row['months'] as $m)
                                    <td class="text-end small text-nowrap">
                                        @if($m['is_past'])
                                            <span class="fw-medium">{{ number_format($m['actual'], 0, ',', ' ') }}</span>
                                            <div class="text-muted" style="font-size: 10px;">(actuel)</div>
                                        @else
                                            <span class="text-muted">{{ number_format($m['forecast'], 0, ',', ' ') }}</span>
                                            <div class="text-muted" style="font-size: 10px;">(prévu)</div>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-end fw-medium">{{ number_format($row['total_forecast'], 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($row['total_actual'], 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mt-3 mb-0">
                <i class="ti ti-info-circle me-1"></i>
                Les chiffres « actuel » viennent des paiements snapshotés (champ <code>EFFECTIVE_DATE</code>) ;
                la prévision est la moyenne des 3 derniers mois clos pour chaque centre.
            </p>
        </div>
    </div>
@endsection
