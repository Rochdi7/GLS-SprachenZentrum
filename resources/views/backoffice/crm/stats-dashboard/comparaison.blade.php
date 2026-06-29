@extends('layouts.main')

@section('title', 'Comparaison Centres — CRM')
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'Comparaison centres')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .filter-card { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 1.25rem; }
        .kpi-box { border-left: 4px solid; border-radius: 8px; padding: .9rem 1.1rem; background: #fff; }
        .kpi-box .kpi-label { font-size: .75rem; color: #6c757d; margin-bottom: .2rem; }
        .kpi-box .kpi-val   { font-size: 1.3rem; font-weight: 700; margin: 0; }
        .rank-bar { height: 8px; border-radius: 4px; transition: width .5s; }
        #chartLine, #chartBar, #chartPie { min-height: 380px; }
        .store-cb-label { cursor: pointer; user-select: none; }
        .store-cb-label input { margin-right: .4rem; }
    </style>
@endsection

@section('content')

@php
    $storeColors = ['#4680ff','#1cc88a','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14','#20c997'];
    $siteList = $sites->whereNotNull('crm_store_id')->values();
@endphp

{{-- Header --}}
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="ph-duotone ph-arrows-left-right text-primary"></i>
            Comparaison Encaissement — Centre vs Centre
            @include('backoffice.crm.partials._snapshot_badge', ['snapshotDate' => $snapshotDate ?? null])
        </h4>
    </div>
    <div class="col-auto">
        <a href="{{ route('backoffice.crm.statistiques') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ph-duotone ph-arrow-left me-1"></i> Rentabilité
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card mb-4">
    {{-- Row 1: dates + groupby + presets + search --}}
    <div class="row g-2 align-items-end mb-3">
        <div class="col-sm-auto">
            <label class="form-label fw-semibold mb-1 small">Date de début</label>
            <input type="date" id="comp-start" class="form-control form-control-sm" style="min-width:140px"
                   value="{{ now()->startOfMonth()->toDateString() }}">
        </div>
        <div class="col-sm-auto">
            <label class="form-label fw-semibold mb-1 small">Date de fin</label>
            <input type="date" id="comp-end" class="form-control form-control-sm" style="min-width:140px"
                   value="{{ now()->toDateString() }}">
        </div>
        <div class="col-sm-auto ms-sm-auto">
            <label class="form-label mb-1 d-block" style="visibility:hidden">x</label>
            <button id="comp-search" class="btn btn-primary btn-sm px-4">
                <i class="ph-duotone ph-magnifying-glass me-1"></i> Comparer
            </button>
        </div>
    </div>

    {{-- Row 2: centres checkboxes --}}
    <div class="border-top pt-2">
        <label class="form-label fw-semibold mb-2 small">Centres à comparer</label>
        <div class="d-flex gap-3 flex-wrap" id="store-checkboxes">
            @foreach ($siteList as $i => $site)
                <label class="store-cb-label d-flex align-items-center gap-1 small">
                    <input type="checkbox" class="store-cb form-check-input mt-0"
                           value="{{ $site->crm_store_id }}" checked
                           style="accent-color: {{ $storeColors[$i % count($storeColors)] }}">
                    <span style="color:{{ $storeColors[$i % count($storeColors)] }};font-weight:600">
                        {{ $site->name }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>
</div>

{{-- Loading --}}
<div id="comp-loading" class="text-center py-5 d-none">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="text-muted mt-2 small">Chargement…</p>
</div>
<div id="comp-error" class="alert alert-danger d-none"></div>

{{-- Results --}}
<div id="comp-results" class="d-none">

    {{-- KPI totaux --}}
    <div class="row g-3 mb-4" id="comp-kpis"></div>

    {{-- Charts row --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ph-duotone ph-chart-line me-2 text-primary"></i>Évolution encaissement par centre</h6>
                    <div class="btn-group btn-group-sm" id="chart-type-btns">
                        <button class="btn btn-outline-primary active" data-ct="line">Courbe</button>
                        <button class="btn btn-outline-primary" data-ct="bar">Barres groupées</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="chartLine"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Bar totaux --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ph-duotone ph-chart-bar me-2 text-warning"></i>Total par centre sur la période</h6>
                </div>
                <div class="card-body">
                    <div id="chartBar"></div>
                </div>
            </div>
        </div>
        {{-- Pie parts --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ph-duotone ph-chart-pie me-2 text-success"></i>Répartition (%)</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div id="chartPie" style="min-height:320px;width:100%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ranking table --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="ph-duotone ph-trophy me-2 text-warning"></i>Classement — totaux sur la période</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Centre</th>
                            <th class="text-end">Total encaissé</th>
                            <th class="text-end">Nb paiements</th>
                        </tr>
                    </thead>
                    <tbody id="comp-rank-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Empty --}}
<div id="comp-empty" class="text-center py-5 text-muted d-none">
    <i class="ph-duotone ph-arrows-left-right" style="font-size:3rem"></i>
    <p class="mt-2">Aucun encaissement sur cette période pour les centres sélectionnés.</p>
</div>

@endsection

@section('scripts')
<script type="application/json" id="crm-comparaison-data">
{
    "endpoint":    "{{ route('backoffice.crm.statistiques.comparaison.data') }}",
    "storeColors": @json($storeColors)
}
</script>
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/backoffice/crm-stats-comparaison.js') }}"></script>
@endsection
