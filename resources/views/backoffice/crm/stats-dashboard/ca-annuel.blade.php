@extends('layouts.main')

@section('title', 'CA Annuel — CRM')
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'CA Annuel')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .kpi-card { border-radius: 10px; padding: 1rem 1.2rem; color: #fff; }
        .kpi-card .kpi-label { font-size: .72rem; opacity: .85; margin-bottom: .15rem; }
        .kpi-card .kpi-val   { font-size: 1.25rem; font-weight: 700; margin: 0; }
        #crm-ca-chart { min-height: 400px; }
    </style>
@endsection

@section('content')

{{-- Header --}}
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="ph-duotone ph-chart-line-up text-primary"></i>
            Résumé annuel CA
        </h4>
    </div>
    <div class="col-auto d-flex align-items-center gap-2">
        {{-- Site picker --}}
        <form method="GET" action="{{ route('backoffice.crm.statistiques.ca-annuel') }}" id="site-form" class="d-flex gap-2 align-items-center">
            <select name="store_id" id="ca-store-select" class="form-select form-select-sm" style="min-width:170px;">
                <option value="">— Tous les centres —</option>
                @foreach ($sites as $site)
                    <option value="{{ $site->crm_store_id }}" {{ $storeId == $site->crm_store_id ? 'selected' : '' }}>
                        {{ $site->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-primary">OK</button>
            {{-- Year arrows --}}
            <a href="{{ route('backoffice.crm.statistiques.ca-annuel', array_filter(['year' => $year - 1, 'store_id' => $storeId])) }}"
               class="btn btn-sm btn-outline-secondary px-2">
                <i class="ph ph-caret-left"></i>
            </a>
            <span class="fw-bold fs-6" style="min-width:48px;text-align:center;">{{ $year }}</span>
            <a href="{{ route('backoffice.crm.statistiques.ca-annuel', array_filter(['year' => $year + 1, 'store_id' => $storeId])) }}"
               class="btn btn-sm btn-outline-secondary px-2">
                <i class="ph ph-caret-right"></i>
            </a>
            <input type="hidden" name="year" value="{{ $year }}">
        </form>
    </div>
</div>

{{-- KPI cards --}}
@php
    $totalCA            = array_sum($seriesCA);
    $totalCollecte      = array_sum($seriesCollecte);
    $totalReste         = array_sum($seriesReste);
    $totalDep           = array_sum($seriesDepenses);
    $totalEncaissements = array_sum($seriesEncaissements);
@endphp
<div class="row g-3 mb-4 row-cols-2 row-cols-md-5">
    <div class="col">
        <div class="kpi-card" style="background:#4680ff;">
            <div class="kpi-label">Chiffre d'affaires</div>
            <div class="kpi-val">{{ number_format($totalCA, 0, ',', ' ') }} DH</div>
        </div>
    </div>
    <div class="col">
        <div class="kpi-card" style="background:#1cc88a;">
            <div class="kpi-label">Collecté (Encaissements)</div>
            <div class="kpi-val">{{ number_format($totalCollecte, 0, ',', ' ') }} DH</div>
        </div>
    </div>
    <div class="col">
        <div class="kpi-card" style="background:#ffc107;">
            <div class="kpi-label">Reste à payer</div>
            <div class="kpi-val">{{ number_format($totalReste, 0, ',', ' ') }} DH</div>
        </div>
    </div>
    <div class="col">
        <div class="kpi-card" style="background:#dc3545;">
            <div class="kpi-label">Dépenses</div>
            <div class="kpi-val">{{ number_format($totalDep, 0, ',', ' ') }} DH</div>
        </div>
    </div>
    <div class="col">
        <div class="kpi-card" style="background:#6f42c1;">
            <div class="kpi-label">Encaissements</div>
            <div class="kpi-val">{{ number_format($totalEncaissements, 0, ',', ' ') }} DH</div>
        </div>
    </div>
</div>

{{-- Area chart --}}
<div class="card">
    <div class="card-header py-2 d-flex align-items-center justify-content-between">
        <h6 class="mb-0">
            <i class="ph-duotone ph-chart-area text-primary me-1"></i>
            Résumé des frais annuels — {{ $year }}
        </h6>
    </div>
    <div class="card-body">
        <div id="crm-ca-chart"></div>
    </div>
</div>

{{-- Data island --}}
<script type="application/json" id="crm-ca-chart-data">
{!! json_encode([
    'months'              => $months,
    'seriesCA'            => $seriesCA,
    'seriesCollecte'      => $seriesCollecte,
    'seriesReste'         => $seriesReste,
    'seriesDepenses'      => $seriesDepenses,
    'seriesEncaissements' => $seriesEncaissements,
    'year'                => $year,
]) !!}
</script>

@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/backoffice/crm-ca-annuel.js') }}?v=2"></script>
    <script>
    // Choices.js wraps the native select — listen on the underlying element via 'change'
    // which Choices dispatches after its own update.
    document.getElementById('ca-store-select').addEventListener('change', function () {
        document.getElementById('site-form').submit();
    });
    </script>
@endsection
