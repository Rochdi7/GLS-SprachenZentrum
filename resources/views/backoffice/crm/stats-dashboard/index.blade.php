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

{{-- ── Encaissement par période (date range checker) ───────────────── --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="ph-duotone ph-calendar-check me-2 text-primary"></i>
                    Encaissement par période — par centre
                </h5>
                <small class="text-muted" id="enc-range-snapshot"></small>
            </div>
            <div class="card-body">

                {{-- Date inputs --}}
                <div class="row g-3 mb-4 align-items-end">
                    <div class="col-auto">
                        <label class="form-label fw-semibold mb-1">
                            <i class="ph-duotone ph-calendar-blank me-1 text-primary"></i>
                            Date de début
                        </label>
                        <input type="date" id="enc-start-date" class="form-control" style="min-width:170px">
                    </div>
                    <div class="col-auto">
                        <label class="form-label fw-semibold mb-1">
                            <i class="ph-duotone ph-calendar-blank me-1 text-primary"></i>
                            Date de fin
                        </label>
                        <input type="date" id="enc-end-date" class="form-control" style="min-width:170px">
                    </div>
                    <div class="col-auto">
                        <button id="enc-range-btn" class="btn btn-primary" type="button">
                            <i class="ph-duotone ph-magnifying-glass me-1"></i>
                            Vérifier
                        </button>
                    </div>
                    <div class="col-auto d-flex gap-2 flex-wrap">
                        <button class="btn btn-sm btn-outline-secondary enc-preset" data-preset="today">Aujourd'hui</button>
                        <button class="btn btn-sm btn-outline-secondary enc-preset" data-preset="7d">7 jours</button>
                        <button class="btn btn-sm btn-outline-secondary enc-preset" data-preset="30d">30 jours</button>
                        <button class="btn btn-sm btn-outline-secondary enc-preset" data-preset="month">Ce mois</button>
                    </div>
                </div>

                {{-- Loading + error state --}}
                <div id="enc-range-loading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0 small">Chargement…</p>
                </div>
                <div id="enc-range-error" class="alert alert-danger d-none"></div>

                {{-- Results --}}
                <div id="enc-range-results" class="d-none">

                    {{-- KPI row --}}
                    <div class="row g-3 mb-4" id="enc-range-kpis"></div>

                    {{-- Chart --}}
                    <div class="mb-4">
                        <div id="enc-range-chart" style="min-height:320px"></div>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Centre</th>
                                    <th class="text-end">Encaissé (DH)</th>
                                    <th class="text-end">Nb paiements</th>
                                    <th style="width:30%">Part</th>
                                </tr>
                            </thead>
                            <tbody id="enc-range-tbody"></tbody>
                            <tfoot id="enc-range-tfoot" class="table-secondary fw-semibold"></tfoot>
                        </table>
                    </div>

                </div>

                {{-- Empty state --}}
                <div id="enc-range-empty" class="text-center py-5 text-muted d-none">
                    <i class="ph-duotone ph-chart-bar" style="font-size:2.5rem"></i>
                    <p class="mt-2 mb-0">Aucun encaissement sur cette période.</p>
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

// ── Encaissement range checker ────────────────────────────────────────
(function () {
    'use strict';

    const ENDPOINT  = '{{ route("backoffice.crm.statistiques.encaissement-range") }}';
    const COLORS    = ['#4680ff','#1cc88a','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14'];

    let rangeChart  = null;

    function fmtDH(v) {
        if (v >= 1_000_000) return (v / 1_000_000).toFixed(2).replace(/\.?0+$/, '') + ' M DH';
        if (v >= 1_000)     return (v / 1_000).toFixed(1).replace('.0', '') + ' k DH';
        return v.toLocaleString('fr-MA') + ' DH';
    }

    function fullDH(v) {
        return new Intl.NumberFormat('fr-MA', { minimumFractionDigits: 2 }).format(v) + ' DH';
    }

    function pad(n) { return String(n).padStart(2, '0'); }

    function toIso(d) {
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    }

    // Pre-set shortcuts
    document.querySelectorAll('.enc-preset').forEach(btn => {
        btn.addEventListener('click', function () {
            const today = new Date();
            let s, e;
            switch (this.dataset.preset) {
                case 'today':
                    s = e = today; break;
                case '7d':
                    s = new Date(today); s.setDate(today.getDate() - 6); e = today; break;
                case '30d':
                    s = new Date(today); s.setDate(today.getDate() - 29); e = today; break;
                case 'month':
                    s = new Date(today.getFullYear(), today.getMonth(), 1); e = today; break;
            }
            document.getElementById('enc-start-date').value = toIso(s);
            document.getElementById('enc-end-date').value   = toIso(e);
            document.querySelectorAll('.enc-preset').forEach(b => b.classList.remove('active','btn-primary'));
            document.querySelectorAll('.enc-preset').forEach(b => b.classList.add('btn-outline-secondary'));
            this.classList.remove('btn-outline-secondary');
            this.classList.add('active', 'btn-primary');
            fetchRange();
        });
    });

    document.getElementById('enc-range-btn').addEventListener('click', fetchRange);

    function fetchRange() {
        const start = document.getElementById('enc-start-date').value;
        const end   = document.getElementById('enc-end-date').value;
        if (!start || !end) {
            showError('Veuillez choisir une date de début et de fin.');
            return;
        }
        if (start > end) {
            showError('La date de début doit être ≤ la date de fin.');
            return;
        }

        setLoading(true);
        clearResults();

        const params = new URLSearchParams({ startDate: start, endDate: end });
        @if ($storeId)
        params.set('strStoreId', '{{ $storeId }}');
        @endif

        fetch(`${ENDPOINT}?${params}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(json => {
                setLoading(false);
                if (json.error) { showError(json.error); return; }
                if (!json.data || json.data.length === 0) {
                    document.getElementById('enc-range-empty').classList.remove('d-none');
                    return;
                }
                renderResults(json);
            })
            .catch(err => {
                setLoading(false);
                showError('Erreur réseau : ' + err.message);
            });
    }

    function renderResults(json) {
        const { data, grand_total, grand_nb, start_date, end_date, snapshot } = json;

        // Snapshot badge
        if (snapshot) {
            document.getElementById('enc-range-snapshot').textContent = 'Snapshot : ' + snapshot;
        }

        // KPI cards
        const kpiEl = document.getElementById('enc-range-kpis');
        kpiEl.innerHTML = `
            <div class="col-6 col-lg-3">
                <div class="card border-primary border-2 h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small mb-1"><i class="ph-duotone ph-money me-1 text-primary"></i>Total encaissé</div>
                        <div class="fw-bold fs-5 text-primary">${fullDH(grand_total)}</div>
                        <div class="text-muted" style="font-size:.75rem">${start_date} → ${end_date}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-success border-2 h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small mb-1"><i class="ph-duotone ph-receipt me-1 text-success"></i>Nb paiements</div>
                        <div class="fw-bold fs-5 text-success">${grand_nb.toLocaleString('fr-MA')}</div>
                        <div class="text-muted" style="font-size:.75rem">sur tous les centres</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-info border-2 h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small mb-1"><i class="ph-duotone ph-buildings me-1 text-info"></i>Centres actifs</div>
                        <div class="fw-bold fs-5 text-info">${data.length}</div>
                        <div class="text-muted" style="font-size:.75rem">avec encaissement</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-warning border-2 h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small mb-1"><i class="ph-duotone ph-chart-pie me-1 text-warning"></i>Moy. / paiement</div>
                        <div class="fw-bold fs-5 text-warning">${grand_nb > 0 ? fullDH(grand_total / grand_nb) : '—'}</div>
                        <div class="text-muted" style="font-size:.75rem">montant moyen</div>
                    </div>
                </div>
            </div>`;

        // Chart
        if (rangeChart) { rangeChart.destroy(); rangeChart = null; }
        const names  = data.map(r => r.store_name.replace(/^GLS\s*/i, ''));
        const totals = data.map(r => Math.round(r.total));

        rangeChart = new ApexCharts(document.getElementById('enc-range-chart'), {
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            series: [{ name: 'Encaissé (DH)', data: totals }],
            colors: COLORS,
            plotOptions: {
                bar: {
                    distributed: true,
                    borderRadius: 6,
                    columnWidth: '55%',
                    dataLabels: { position: 'top' },
                },
            },
            dataLabels: {
                enabled: true,
                formatter: fmtDH,
                offsetY: -22,
                style: { fontSize: '11px', colors: ['#374151'] },
            },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
            xaxis: {
                categories: names,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { fontSize: '12px', fontWeight: 600 } },
            },
            yaxis: {
                labels: { formatter: fmtDH, style: { fontSize: '11px' } },
            },
            legend: { show: false },
            tooltip: {
                y: {
                    formatter: v => new Intl.NumberFormat('fr-MA', { minimumFractionDigits: 2 }).format(v) + ' DH',
                },
            },
        });
        rangeChart.render();

        // Table
        const tbody = document.getElementById('enc-range-tbody');
        const tfoot = document.getElementById('enc-range-tfoot');
        tbody.innerHTML = '';

        data.forEach((r, i) => {
            const pct = grand_total > 0 ? (r.total / grand_total * 100).toFixed(1) : 0;
            const color = COLORS[i % COLORS.length];
            tbody.innerHTML += `
                <tr>
                    <td class="text-muted">${i + 1}</td>
                    <td><span class="badge" style="background:${color}20;color:${color};font-size:.8rem">${r.store_name}</span></td>
                    <td class="text-end fw-semibold text-primary">${new Intl.NumberFormat('fr-MA', {minimumFractionDigits:2}).format(r.total)} DH</td>
                    <td class="text-end">${r.nb.toLocaleString('fr-MA')}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px">
                                <div class="progress-bar" style="width:${pct}%;background:${color}"></div>
                            </div>
                            <small class="text-muted">${pct}%</small>
                        </div>
                    </td>
                </tr>`;
        });

        tfoot.innerHTML = `
            <tr>
                <td colspan="2">Total</td>
                <td class="text-end">${new Intl.NumberFormat('fr-MA', {minimumFractionDigits:2}).format(grand_total)} DH</td>
                <td class="text-end">${grand_nb.toLocaleString('fr-MA')}</td>
                <td></td>
            </tr>`;

        document.getElementById('enc-range-results').classList.remove('d-none');
    }

    function setLoading(on) {
        document.getElementById('enc-range-loading').classList.toggle('d-none', !on);
    }

    function clearResults() {
        document.getElementById('enc-range-results').classList.add('d-none');
        document.getElementById('enc-range-empty').classList.add('d-none');
        document.getElementById('enc-range-error').classList.add('d-none');
        document.getElementById('enc-range-kpis').innerHTML = '';
        document.getElementById('enc-range-tbody').innerHTML = '';
        document.getElementById('enc-range-tfoot').innerHTML = '';
        if (rangeChart) { rangeChart.destroy(); rangeChart = null; }
    }

    function showError(msg) {
        const el = document.getElementById('enc-range-error');
        el.textContent = msg;
        el.classList.remove('d-none');
    }

})();
</script>
@endsection
