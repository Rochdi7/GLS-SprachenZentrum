@extends('layouts.main')

@section('title', 'Statistiques CRM — Rentabilité par centre')
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'Statistiques')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .stat-kpi { border-left: 4px solid; border-radius: 6px; }
        .stat-kpi .kpi-label { font-size: .78rem; color: #6c757d; margin-bottom: .2rem; }
        .stat-kpi h4 { font-size: 1.4rem; font-weight: 700; margin: 0; }
        .vs-badge { font-size: .72rem; font-weight: 600; }
        .period-btn.active { background: var(--bs-primary); color:#fff; border-color: var(--bs-primary); }
        .chart-wrap { min-height: 420px; }
    </style>
@endsection

@section('content')

@php
    $siteNames = $sites->pluck('name', 'crm_store_id')->toArray();
    $monthsOptions = [3 => '3 mois', 6 => '6 mois', 12 => '12 mois'];
@endphp

{{-- ── Header ─────────────────────────────────────────────────────── --}}
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2 flex-wrap">
            <i class="ph-duotone ph-chart-line text-primary"></i>
            Statistiques Rentabilité par Centre
            @include('backoffice.crm.partials._snapshot_badge', ['snapshotDate' => $snapshotDate ?? null])
        </h4>
    </div>
    <div class="col-auto d-flex gap-2">
        {{-- Center filter --}}
        @if ($crmCenters->isNotEmpty())
            <form method="GET" action="{{ route('backoffice.crm.statistiques') }}" class="d-flex gap-1">
                <select name="strStoreId" class="form-select form-select-sm" style="width:200px" onchange="this.form.submit()">
                    <option value="">— Tous les centres —</option>
                    @foreach ($crmCenters->whereNotNull('crm_store_id') as $center)
                        <option value="{{ $center->crm_store_id }}" {{ $storeId == $center->crm_store_id ? 'selected' : '' }}>
                            {{ $center->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
        <a href="{{ route('backoffice.crm.statistiques', array_merge(request()->query())) }}"
           class="btn btn-sm btn-outline-dark">
            <i class="ph-duotone ph-arrows-clockwise"></i>
        </a>
        @include('backoffice.crm.partials._sync_badge')
    </div>
</div>


{{-- ── Period Comparison KPIs ─────────────────────────────────────── --}}
@if (!empty($comparison))
@php
    $cur  = $comparison['this_month'];
    $prev = $comparison['last_month'];
    $py   = $comparison['prev_year'];

    $encPct  = $prev['encaissement']  > 0 ? round((($cur['encaissement']  - $prev['encaissement'])  / $prev['encaissement'])  * 100, 1) : null;
    $regPct  = $prev['inscriptions']  > 0 ? round((($cur['inscriptions']  - $prev['inscriptions'])  / $prev['inscriptions'])  * 100, 1) : null;
    $encPctY = $py['encaissement']    > 0 ? round((($cur['encaissement']  - $py['encaissement'])    / $py['encaissement'])    * 100, 1) : null;
    $regPctY = $py['inscriptions']    > 0 ? round((($cur['inscriptions']  - $py['inscriptions'])    / $py['inscriptions'])    * 100, 1) : null;
@endphp
<div class="row g-3 mb-4">
    @php
        $kpis = [
            ['label' => 'Encaissement ce mois', 'val' => $cur['encaissement'], 'prev_val' => $prev['encaissement'], 'prev_label' => $prev['label'], 'pct' => $encPct,  'color' => 'primary', 'icon' => 'ph-money'],
            ['label' => 'Inscriptions ce mois', 'val' => $cur['inscriptions'], 'prev_val' => $prev['inscriptions'], 'prev_label' => $prev['label'], 'pct' => $regPct,  'color' => 'success', 'icon' => 'ph-users', 'fmt' => 'int'],
            ['label' => 'Encaissement vs ' . $py['label'], 'val' => $cur['encaissement'], 'prev_val' => $py['encaissement'], 'prev_label' => $py['label'], 'pct' => $encPctY, 'color' => 'info',    'icon' => 'ph-trend-up'],
            ['label' => 'Inscriptions vs ' . $py['label'], 'val' => $cur['inscriptions'], 'prev_val' => $py['inscriptions'], 'prev_label' => $py['label'], 'pct' => $regPctY, 'color' => 'warning', 'icon' => 'ph-calendar', 'fmt' => 'int'],
        ];
    @endphp
    @foreach ($kpis as $kpi)
    <div class="col-lg-3 col-md-6">
        <div class="card stat-kpi h-100 border-{{ $kpi['color'] }}">
            <div class="card-body">
                <div class="kpi-label d-flex align-items-center gap-1">
                    <i class="ph-duotone {{ $kpi['icon'] }} text-{{ $kpi['color'] }}"></i>
                    {{ $kpi['label'] }}
                </div>
                <h4 class="text-{{ $kpi['color'] }} mt-1">
                    @if (($kpi['fmt'] ?? '') === 'int')
                        {{ number_format($kpi['val']) }}
                    @else
                        {{ number_format($kpi['val'], 0, ',', ' ') }} DH
                    @endif
                </h4>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <small class="text-muted">
                        vs {{ ($kpi['fmt'] ?? '') === 'int' ? number_format($kpi['prev_val']) : number_format($kpi['prev_val'], 0, ',', ' ') . ' DH' }}
                        ({{ $kpi['prev_label'] }})
                    </small>
                    @if ($kpi['pct'] !== null)
                        @php $up = $kpi['pct'] >= 0; @endphp
                        <span class="badge vs-badge {{ $up ? 'bg-success' : 'bg-danger' }}">
                            {{ $up ? '+' : '' }}{{ $kpi['pct'] }}%
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── Row: Encaissement chart + Recouvrement bar ─────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Encaissement par centre par mois --}}
    <div class="col-12">
        <div class="card chart-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="ph-duotone ph-chart-line me-2 text-primary"></i>Encaissement par centre — {{ $months }} derniers mois</h5>
                @if (!empty($encaissement['snapshot']))
                    <small class="text-muted">Snapshot : {{ $encaissement['snapshot'] }}</small>
                @endif
            </div>
            <div class="card-body">
                @if (empty($encaissement['months']))
                    <p class="text-muted text-center py-5">Aucune donnée — lancez <code>crm:snapshot-payments</code>.</p>
                @else
                    <div id="chartEncaissement" class="chart-wrap"></div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Inscriptions chart ───────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card chart-card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="ph-duotone ph-user-plus me-2 text-success"></i>Nouvelles inscriptions par centre — {{ $months }} mois</h5>
            </div>
            <div class="card-body">
                @if (empty($registrations['months']))
                    <p class="text-muted text-center py-5">Aucune donnée — lancez <code>crm:sync-registrations</code>.</p>
                @else
                    <div id="chartInscriptions" class="chart-wrap"></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Meilleur centre encaissement table --}}
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="ph-duotone ph-trophy me-2 text-warning"></i>Classement encaissement (cumul {{ $months }} mois)</h5>
            </div>
            <div class="card-body pt-2">
                @php
                    $ranking = [];
                    foreach (($encaissement['pivot'] ?? []) as $sid => $mths) {
                        $ranking[$sid] = array_sum($mths);
                    }
                    arsort($ranking);
                    $maxRank = max(array_values($ranking) ?: [1]);
                @endphp
                @if (empty($ranking))
                    <p class="text-muted text-center py-4">Aucune donnée.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>#</th><th>Centre</th><th class="text-end">Total</th><th style="width:35%"></th></tr>
                            </thead>
                            <tbody>
                                @foreach ($ranking as $sid => $total)
                                @php $pct = round($total / $maxRank * 100); $i = $loop->iteration; @endphp
                                <tr>
                                    <td>
                                        @if ($i === 1) 🥇
                                        @elseif ($i === 2) 🥈
                                        @elseif ($i === 3) 🥉
                                        @else {{ $i }}
                                        @endif
                                    </td>
                                    <td><strong>{{ $siteNames[$sid] ?? 'Store #'.$sid }}</strong></td>
                                    <td class="text-end text-primary fw-semibold">{{ number_format($total, 0, ',', ' ') }} DH</td>
                                    <td>
                                        <div class="progress" style="height:8px">
                                            <div class="progress-bar {{ $i === 1 ? 'bg-warning' : ($i === 2 ? 'bg-secondary' : 'bg-primary') }}"
                                                 style="width:{{ $pct }}%"></div>
                                        </div>
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

{{-- ── Recouvrement par période (date range) ───────────────────────── --}}
<div class="row mb-4 d-none">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="ph-duotone ph-warning-circle me-2 text-warning"></i>
                    Chiffre d'affaires par période
                    <small class="text-muted fw-normal">(échéances, statut actif)</small>
                </h5>
                <small class="text-muted" id="rec-range-snapshot"></small>
            </div>
            <div class="card-body">

                {{-- Date inputs --}}
                <form id="rec-range-form" class="row g-3 mb-4 align-items-end">
                    <div class="col-auto">
                        <label class="form-label fw-semibold mb-1">
                            <i class="ph-duotone ph-calendar-blank me-1 text-warning"></i>
                            Date de début
                        </label>
                        <input type="date" id="rec-start-date" class="form-control" style="min-width:170px">
                    </div>
                    <div class="col-auto">
                        <label class="form-label fw-semibold mb-1">
                            <i class="ph-duotone ph-calendar-blank me-1 text-warning"></i>
                            Date de fin
                        </label>
                        <input type="date" id="rec-end-date" class="form-control" style="min-width:170px">
                    </div>
                    <div class="col-auto">
                        <button id="rec-range-btn" class="btn btn-warning text-white" type="submit">
                            <i class="ph-duotone ph-magnifying-glass me-1"></i>
                            <span class="rec-range-btn-label">Afficher</span>
                        </button>
                    </div>
                    <div class="col-auto d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-dark rec-preset" data-preset="today">Aujourd'hui</button>
                        <button type="button" class="btn btn-sm btn-outline-dark rec-preset" data-preset="7d">7 jours</button>
                        <button type="button" class="btn btn-sm btn-outline-dark rec-preset" data-preset="30d">30 jours</button>
                        <button type="button" class="btn btn-sm btn-outline-dark rec-preset" data-preset="month">Ce mois</button>
                    </div>
                </form>

                {{-- Loading + error --}}
                <div id="rec-range-loading" class="text-center py-4 d-none">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="text-muted mt-2 mb-0 small">Chargement…</p>
                </div>
                <div id="rec-range-error" class="alert alert-danger d-none"></div>

                {{-- Results --}}
                <div id="rec-range-results" class="d-none">

                    {{-- KPI row --}}
                    <div class="row g-3 mb-4" id="rec-range-kpis"></div>

                    {{-- Chart --}}
                    <div class="mb-4">
                        <div id="rec-range-chart" style="min-height:320px"></div>
                    </div>

                    {{-- Table (hidden — tbody/tfoot kept in DOM so JS doesn't crash) --}}
                    <div class="table-responsive d-none">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Centre</th>
                                    <th class="text-end">Reste à payer (DH)</th>
                                    <th class="text-end">CA total (DH)</th>
                                    <th class="text-end">Nb éch.</th>
                                    <th style="width:25%">Part</th>
                                </tr>
                            </thead>
                            <tbody id="rec-range-tbody"></tbody>
                            <tfoot id="rec-range-tfoot" class="table-secondary fw-semibold"></tfoot>
                        </table>
                    </div>

                </div>

                {{-- Empty state --}}
                <div id="rec-range-empty" class="text-center py-5 text-muted d-none">
                    <i class="ph-duotone ph-check-circle text-success" style="font-size:2.5rem"></i>
                    <p class="mt-2 mb-0">Aucun impayé sur cette période.</p>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ── Classement encaissement par période ─────────────────────────── --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="ph-duotone ph-trophy me-2 text-primary"></i>
                    Classement encaissement par période
                    <small class="text-muted fw-normal">(paiements enregistrés)</small>
                </h5>
                <small class="text-muted" id="enc-rank-snapshot"></small>
            </div>
            <div class="card-body">

                {{-- Date inputs --}}
                <form id="enc-rank-form" class="row g-3 mb-4 align-items-end">
                    <div class="col-auto">
                        <label class="form-label fw-semibold mb-1">
                            <i class="ph-duotone ph-calendar-blank me-1 text-primary"></i>
                            Date de début
                        </label>
                        <input type="date" id="enc-rank-start-date" class="form-control" style="min-width:170px">
                    </div>
                    <div class="col-auto">
                        <label class="form-label fw-semibold mb-1">
                            <i class="ph-duotone ph-calendar-blank me-1 text-primary"></i>
                            Date de fin
                        </label>
                        <input type="date" id="enc-rank-end-date" class="form-control" style="min-width:170px">
                    </div>
                    <div class="col-auto">
                        <button id="enc-rank-btn" class="btn btn-primary" type="submit">
                            <i class="ph-duotone ph-magnifying-glass me-1"></i>
                            <span class="enc-rank-btn-label">Afficher</span>
                        </button>
                    </div>
                    <div class="col-auto d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-dark enc-rank-preset" data-preset="today">Aujourd'hui</button>
                        <button type="button" class="btn btn-sm btn-outline-dark enc-rank-preset" data-preset="7d">7 jours</button>
                        <button type="button" class="btn btn-sm btn-outline-dark enc-rank-preset" data-preset="30d">30 jours</button>
                        <button type="button" class="btn btn-sm btn-outline-dark enc-rank-preset" data-preset="month">Ce mois</button>
                    </div>
                </form>

                {{-- Loading + error --}}
                <div id="enc-rank-loading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0 small">Chargement…</p>
                </div>
                <div id="enc-rank-error" class="alert alert-danger d-none"></div>

                {{-- Results --}}
                <div id="enc-rank-results" class="d-none">
                    {{-- KPI row --}}
                    <div class="row g-3 mb-4" id="enc-rank-kpis"></div>

                    {{-- Chart + table side by side on large screens --}}
                    <div class="row g-3 align-items-start">
                        <div class="col-xl-7">
                            <div id="enc-rank-chart" style="min-height:300px"></div>
                        </div>
                        <div class="col-xl-5">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:42px">#</th>
                                            <th>Centre</th>
                                            <th class="text-end">Encaissé</th>
                                            <th class="text-end">Nb</th>
                                            <th style="width:28%">Part</th>
                                        </tr>
                                    </thead>
                                    <tbody id="enc-rank-tbody"></tbody>
                                    <tfoot id="enc-rank-tfoot" class="table-secondary fw-semibold"></tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Empty state --}}
                <div id="enc-rank-empty" class="text-center py-5 text-muted d-none">
                    <i class="ph-duotone ph-currency-dollar-simple text-muted" style="font-size:2.5rem"></i>
                    <p class="mt-2 mb-0">Aucun encaissement sur cette période.</p>
                </div>

            </div>
        </div>
    </div>
</div>


{{-- ── CA Drill modal ───────────────────────────────────────────────── --}}
<div class="modal fade" id="caDrillModal" tabindex="-1" aria-labelledby="caDrillModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="caDrillModalLabel">
                    <i class="ph-duotone ph-users me-2 text-primary"></i>
                    Détail CA — <span id="ca-drill-store-name"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="ca-drill-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 small">Chargement…</p>
                </div>
                <div id="ca-drill-error" class="alert alert-danger m-3 d-none"></div>
                <div id="ca-drill-body" class="d-none">
                    <div class="px-3 pt-3 pb-2 d-flex gap-3 align-items-center flex-wrap border-bottom">
                        <span class="badge bg-primary fs-6" id="ca-drill-total-ca"></span>
                        <span class="badge bg-warning text-dark fs-6" id="ca-drill-total-reste"></span>
                        <span class="badge bg-secondary fs-6" id="ca-drill-count"></span>
                        <input type="search" id="ca-drill-search" class="form-control form-control-sm ms-auto" style="max-width:240px" placeholder="Rechercher étudiant…">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Étudiant</th>
                                    <th>Centre</th>
                                    <th class="text-end">CA total (DH)</th>
                                    <th class="text-end">Reste à payer</th>
                                    <th class="text-end">Nb éch.</th>
                                    <th class="text-end">Prochaine éch.</th>
                                </tr>
                            </thead>
                            <tbody id="ca-drill-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script>
'use strict';
(function () {
    setTimeout(function () {

        if (typeof ApexCharts === 'undefined') return;

        // Brand palette — one distinct color per center (max 7)
        const COLORS = ['#4680ff','#1cc88a','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14'];

        // Compact DH (Dirham Marocain) formatter — axis labels
        function fmtDH(v) {
            if (v >= 1000000) return (v / 1000000).toFixed(1).replace('.0','') + ' M DH';
            if (v >= 1000)    return Math.round(v / 1000) + ' k DH';
            return v > 0 ? v + ' DH' : '';
        }

        // Full DH (Dirham Marocain) for tooltips
        function fullDH(v) {
            return new Intl.NumberFormat('fr-MA').format(v) + ' DH';
        }

        function monthLabel(m) {
            const [y, mo] = m.split('-');
            return new Date(y, mo - 1).toLocaleDateString('fr-FR', { month: 'short', year: '2-digit' });
        }

        // Shared axis/grid defaults
        const gridCfg  = { borderColor: '#f0f0f0', strokeDashArray: 4 };
        const axisBase = { axisBorder: { show: false }, axisTicks: { show: false } };
        const legendCfg = { position: 'bottom', fontSize: '13px', markers: { width: 10, height: 10, radius: 50 } };

        @if (!empty($encaissement['months']))
        // ── 1. Encaissement par centre — area/line chart ──────────────────
        (function () {
            const months = @json($encaissement['months']);
            const pivot  = @json($encaissement['pivot']);
            const names  = @json($encaissement['sites']);

            const series = Object.entries(pivot).map(([sid, mData], i) => ({
                name: names[sid] || 'Store #' + sid,
                data: months.map(m => Math.round(mData[m] || 0)),
            }));

            new ApexCharts(document.querySelector('#chartEncaissement'), {
                chart: {
                    type: 'area',
                    height: 380,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    fontFamily: 'inherit',
                },
                series,
                colors: COLORS,
                stroke: { width: 2, curve: 'smooth' },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.25, opacityTo: 0.02 } },
                markers: { size: 5, strokeWidth: 2, hover: { size: 7 } },
                dataLabels: { enabled: false },
                grid: gridCfg,
                xaxis: {
                    ...axisBase,
                    categories: months.map(monthLabel),
                    labels: { style: { fontSize: '13px', fontWeight: 500 } },
                },
                yaxis: {
                    labels: {
                        style: { fontSize: '12px' },
                        formatter: fmtDH,
                    },
                },
                legend: legendCfg,
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: { formatter: fullDH },
                },
            }).render();
        })();
        @endif

        @if (!empty($registrations['months']))
        // ── 3. Inscriptions — grouped bar ────────────────────────────────
        (function () {
            const months = @json($registrations['months']);
            const pivot  = @json($registrations['pivot']);
            const names  = @json($registrations['sites']);

            const series = Object.entries(pivot).map(([sid, mData]) => ({
                name: names[sid] || 'Store #' + sid,
                data: months.map(m => mData[m] || 0),
            }));

            new ApexCharts(document.querySelector('#chartInscriptions'), {
                chart: {
                    type: 'bar',
                    height: 380,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                },
                series,
                colors: COLORS,
                plotOptions: {
                    bar: { columnWidth: '65%', borderRadius: 3 },
                },
                dataLabels: { enabled: false },
                grid: gridCfg,
                xaxis: {
                    ...axisBase,
                    categories: months.map(monthLabel),
                    labels: { style: { fontSize: '13px', fontWeight: 500 } },
                },
                yaxis: {
                    labels: {
                        style: { fontSize: '12px' },
                        formatter: v => Math.round(v),
                    },
                },
                legend: legendCfg,
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: { formatter: v => v + ' inscriptions' },
                },
            }).render();
        })();
        @endif

    }, 300);
})();
</script>

<script type="application/json" id="crm-stats-dashboard-config">
{
    "encRangeEndpoint": "{{ route('backoffice.crm.statistiques.encaissement-range') }}",
    "recRangeEndpoint": "{{ route('backoffice.crm.statistiques.recouvrement-range') }}",
    "recDrillEndpoint": "{{ route('backoffice.crm.statistiques.recouvrement-range.drill') }}",
    "storeId": "{{ $storeId ?? '' }}"
}
</script>
<script src="{{ URL::asset('assets/js/backoffice/crm-stats-dashboard.js') }}?v={{ @filemtime(public_path('assets/js/backoffice/crm-stats-dashboard.js')) ?: '1' }}"></script>
@endsection
