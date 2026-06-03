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
        {{-- Period selector --}}
        <div class="btn-group btn-group-sm">
            @foreach ($monthsOptions as $m => $label)
                <a href="{{ route('backoffice.crm.statistiques', array_merge(request()->query(), ['months' => $m])) }}"
                   class="btn btn-outline-primary period-btn {{ $months == $m ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        {{-- Center filter --}}
        @if ($crmCenters->isNotEmpty())
            <form method="GET" action="{{ route('backoffice.crm.statistiques') }}" class="d-flex gap-1">
                <input type="hidden" name="months" value="{{ $months }}">
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
           class="btn btn-sm btn-outline-secondary">
            <i class="ph-duotone ph-arrows-clockwise"></i>
        </a>
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
    <div class="col-xl-8">
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

    {{-- Recouvrement (créances actives) --}}
    <div class="col-xl-4">
        <div class="card chart-card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="ph-duotone ph-warning me-2 text-danger"></i>Créances actives par centre</h5>
            </div>
            <div class="card-body">
                @if (empty($recouvrement))
                    <p class="text-muted text-center py-5">Aucune donnée.</p>
                @else
                    <div id="chartRecouvrement" class="chart-wrap"></div>
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

{{-- ── Recouvrement detail table ────────────────────────────────────── --}}
@if (!empty($recouvrement))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="ph-duotone ph-table me-2"></i>Détail recouvrement par centre (inscriptions actives)</h5>
            </div>
            <div class="card-body pt-2">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Centre</th>
                                <th class="text-end">Total dû</th>
                                <th class="text-end text-danger">En retard</th>
                                <th class="text-end text-warning">À venir</th>
                                <th class="text-end">Dossiers</th>
                                <th style="width:20%">Retard / Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recouvrement as $r)
                            @php $pctOverdue = $r['total_due'] > 0 ? round($r['overdue'] / $r['total_due'] * 100) : 0; @endphp
                            <tr>
                                <td><span class="badge bg-light-primary">{{ $r['store_name'] }}</span></td>
                                <td class="text-end fw-semibold">{{ number_format($r['total_due'], 0, ',', ' ') }} DH</td>
                                <td class="text-end text-danger">{{ number_format($r['overdue'], 0, ',', ' ') }} DH</td>
                                <td class="text-end text-warning">{{ number_format($r['upcoming'], 0, ',', ' ') }} DH</td>
                                <td class="text-end">{{ $r['dossiers'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:8px">
                                            <div class="progress-bar bg-danger" style="width:{{ $pctOverdue }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $pctOverdue }}%</small>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script>
'use strict';
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {

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

        @if (!empty($recouvrement))
        // ── 2. Créances — vertical stacked bar (fixes label overlap) ─────
        (function () {
            const data     = @json($recouvrement);
            // Short center names to avoid overlap
            const cats     = data.map(r => r.store_name.replace('GLS ', ''));
            const overdue  = data.map(r => Math.round(r.overdue));
            const upcoming = data.map(r => Math.round(r.upcoming));

            new ApexCharts(document.querySelector('#chartRecouvrement'), {
                chart: {
                    type: 'bar',
                    height: 380,
                    stacked: true,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                },
                series: [
                    { name: 'En retard', data: overdue },
                    { name: 'À venir',  data: upcoming },
                ],
                colors: ['#dc3545', '#ffc107'],
                plotOptions: {
                    bar: {
                        horizontal: false,   // VERTICAL — no label overlap
                        columnWidth: '50%',
                        borderRadius: 4,
                        borderRadiusApplication: 'end',
                    },
                },
                dataLabels: { enabled: false },
                grid: gridCfg,
                xaxis: {
                    ...axisBase,
                    categories: cats,
                    labels: { style: { fontSize: '12px', fontWeight: 600 } },
                },
                yaxis: {
                    labels: { formatter: fmtDH, style: { fontSize: '12px' } },
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
});
</script>
@endsection
