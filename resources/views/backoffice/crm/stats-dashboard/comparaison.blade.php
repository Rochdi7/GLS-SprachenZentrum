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
    <div class="row g-3 align-items-end">

        {{-- Date range --}}
        <div class="col-auto">
            <label class="form-label fw-semibold mb-1 small">Date de début</label>
            <input type="date" id="comp-start" class="form-control form-control-sm" style="min-width:150px"
                   value="{{ now()->startOfMonth()->toDateString() }}">
        </div>
        <div class="col-auto">
            <label class="form-label fw-semibold mb-1 small">Date de fin</label>
            <input type="date" id="comp-end" class="form-control form-control-sm" style="min-width:150px"
                   value="{{ now()->toDateString() }}">
        </div>

        {{-- Group by --}}
        <div class="col-auto">
            <label class="form-label fw-semibold mb-1 small">Grouper par</label>
            <select id="comp-groupby" class="form-select form-select-sm">
                <option value="day">Jour</option>
                <option value="week">Semaine</option>
                <option value="month" selected>Mois</option>
            </select>
        </div>

        {{-- Presets --}}
        <div class="col-auto">
            <label class="form-label fw-semibold mb-1 small d-block">Période rapide</label>
            <div class="d-flex gap-1 flex-wrap">
                <button class="btn btn-xs btn-outline-secondary comp-preset" data-p="month">Ce mois</button>
                <button class="btn btn-xs btn-outline-secondary comp-preset" data-p="3m">3 mois</button>
                <button class="btn btn-xs btn-outline-secondary comp-preset" data-p="6m">6 mois</button>
                <button class="btn btn-xs btn-outline-secondary comp-preset" data-p="year">Cette année</button>
            </div>
        </div>

        {{-- Centres --}}
        <div class="col">
            <label class="form-label fw-semibold mb-1 small">Centres à comparer</label>
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

        <div class="col-auto">
            <button id="comp-search" class="btn btn-primary btn-sm px-4">
                <i class="ph-duotone ph-magnifying-glass me-1"></i> Comparer
            </button>
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
                            <th style="width:35%">Part</th>
                        </tr>
                    </thead>
                    <tbody id="comp-rank-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Detail table per period --}}
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="ph-duotone ph-table me-2 text-info"></i>Détail par période</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0" id="comp-detail-table">
                    <thead class="table-light" id="comp-detail-head"></thead>
                    <tbody id="comp-detail-tbody"></tbody>
                    <tfoot id="comp-detail-tfoot" class="table-secondary fw-semibold"></tfoot>
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
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script>
(function () {
    'use strict';

    const ENDPOINT = '{{ route("backoffice.crm.statistiques.comparaison.data") }}';
    const COLORS   = @json($storeColors);

    let chartLine = null, chartBar = null, chartPie = null;
    let currentData = null;
    let chartType = 'line';

    // ── helpers ────────────────────────────────────────────────────────
    function fmtDH(v) {
        if (v >= 1_000_000) return (v/1_000_000).toFixed(2).replace(/\.?0+$/,'') + ' M DH';
        if (v >= 1_000)     return (v/1_000).toFixed(1).replace('.0','') + ' k DH';
        return v.toLocaleString('fr-MA') + ' DH';
    }
    function fullDH(v) {
        return new Intl.NumberFormat('fr-MA',{minimumFractionDigits:2}).format(v) + ' DH';
    }
    function pad(n){ return String(n).padStart(2,'0'); }
    function toIso(d){ return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`; }

    const medals = ['🥇','🥈','🥉'];

    // ── presets ────────────────────────────────────────────────────────
    document.querySelectorAll('.comp-preset').forEach(btn => {
        btn.addEventListener('click', function () {
            const today = new Date();
            let s, e = today;
            switch (this.dataset.p) {
                case 'month': s = new Date(today.getFullYear(), today.getMonth(), 1); break;
                case '3m':    s = new Date(today); s.setMonth(today.getMonth()-3); s.setDate(1); break;
                case '6m':    s = new Date(today); s.setMonth(today.getMonth()-6); s.setDate(1); break;
                case 'year':  s = new Date(today.getFullYear(), 0, 1); break;
            }
            document.getElementById('comp-start').value = toIso(s);
            document.getElementById('comp-end').value   = toIso(e);
            document.querySelectorAll('.comp-preset').forEach(b => b.classList.replace('btn-primary','btn-outline-secondary'));
            this.classList.replace('btn-outline-secondary','btn-primary');
            fetch();
        });
    });

    // ── chart type toggle ──────────────────────────────────────────────
    document.querySelectorAll('[data-ct]').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('[data-ct]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            chartType = this.dataset.ct;
            if (currentData) renderLineChart(currentData);
        });
    });

    // ── search button ──────────────────────────────────────────────────
    document.getElementById('comp-search').addEventListener('click', fetch);

    // ── fetch ──────────────────────────────────────────────────────────
    function fetch() {
        const start   = document.getElementById('comp-start').value;
        const end     = document.getElementById('comp-end').value;
        const groupBy = document.getElementById('comp-groupby').value;
        const stores  = [...document.querySelectorAll('.store-cb:checked')].map(c => c.value);

        if (!start || !end || !stores.length) return;

        const params = new URLSearchParams({ startDate: start, endDate: end, groupBy });
        stores.forEach(s => params.append('stores[]', s));

        setState('loading');

        window.fetch(`${ENDPOINT}?${params}`)
            .then(r => r.json())
            .then(data => {
                if (data.error) { setState('error', data.error); return; }
                currentData = data;
                const hasData = Object.keys(data.stores).length > 0;
                if (!hasData) { setState('empty'); return; }
                setState('results');
                renderKpis(data);
                renderLineChart(data);
                renderBarChart(data);
                renderPieChart(data);
                renderRankTable(data);
                renderDetailTable(data);
            })
            .catch(e => setState('error', e.message));
    }

    function setState(s, msg) {
        ['comp-loading','comp-error','comp-results','comp-empty'].forEach(id =>
            document.getElementById(id).classList.add('d-none'));
        if (s === 'loading')  document.getElementById('comp-loading').classList.remove('d-none');
        if (s === 'error')  { document.getElementById('comp-error').textContent = msg; document.getElementById('comp-error').classList.remove('d-none'); }
        if (s === 'results')  document.getElementById('comp-results').classList.remove('d-none');
        if (s === 'empty')    document.getElementById('comp-empty').classList.remove('d-none');
    }

    // ── KPIs ───────────────────────────────────────────────────────────
    function renderKpis(data) {
        const storeEntries = Object.entries(data.stores);
        const grandTotal = storeEntries.reduce((s,[,v]) => s + v.total, 0);
        const grandNb    = storeEntries.reduce((s,[,v]) => s + v.nb, 0);
        const top        = storeEntries[0];

        const kpis = [
            { label: 'Total encaissé', val: fullDH(grandTotal), color: '#4680ff' },
            { label: 'Nb paiements',   val: grandNb.toLocaleString('fr-MA'), color: '#1cc88a' },
            { label: 'Centres actifs', val: storeEntries.length, color: '#0dcaf0' },
            { label: 'Meilleur centre', val: top ? `${top[1].name} — ${fmtDH(top[1].total)}` : '—', color: '#ffc107' },
        ];

        document.getElementById('comp-kpis').innerHTML = kpis.map(k => `
            <div class="col-sm-6 col-xl-3">
                <div class="kpi-box" style="border-color:${k.color}">
                    <div class="kpi-label">${k.label}</div>
                    <p class="kpi-val" style="color:${k.color}">${k.val}</p>
                </div>
            </div>`).join('');
    }

    // ── Line / grouped bar chart ───────────────────────────────────────
    function renderLineChart(data) {
        if (chartLine) { chartLine.destroy(); chartLine = null; }

        const storeEntries = Object.entries(data.stores);
        const colorMap = buildColorMap(storeEntries.map(([id]) => id));

        const series = storeEntries.map(([storeId, info]) => ({
            name: info.name,
            data: data.periods.map(p => data.pivot[storeId]?.[p]?.total ?? 0),
        }));

        const opts = {
            series,
            chart: { type: chartType === 'bar' ? 'bar' : 'line', height: 380, toolbar: { show: false }, animations: { enabled: true } },
            stroke: { curve: 'smooth', width: chartType === 'bar' ? 0 : 3 },
            plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
            colors: storeEntries.map(([id]) => colorMap[id]),
            xaxis: { categories: data.periods, labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { formatter: v => fmtDH(v) } },
            tooltip: { y: { formatter: v => fullDH(v) } },
            legend: { position: 'top' },
            grid: { borderColor: '#f0f0f0' },
            dataLabels: { enabled: false },
        };

        chartLine = new ApexCharts(document.getElementById('chartLine'), opts);
        chartLine.render();
    }

    // ── Bar chart totals ───────────────────────────────────────────────
    function renderBarChart(data) {
        if (chartBar) { chartBar.destroy(); chartBar = null; }

        const storeEntries = Object.entries(data.stores);
        const colorMap = buildColorMap(storeEntries.map(([id]) => id));

        const opts = {
            series: [{ name: 'Total', data: storeEntries.map(([,v]) => v.total) }],
            chart: { type: 'bar', height: 380, toolbar: { show: false } },
            plotOptions: { bar: { horizontal: true, borderRadius: 4, dataLabels: { position: 'top' } } },
            colors: storeEntries.map(([id]) => colorMap[id]),
            xaxis: { categories: storeEntries.map(([,v]) => v.name), labels: { formatter: v => fmtDH(v) } },
            tooltip: { y: { formatter: v => fullDH(v) } },
            dataLabels: { enabled: true, formatter: v => fmtDH(v), offsetX: 8, style: { fontSize: '11px' } },
            legend: { show: false },
            grid: { borderColor: '#f0f0f0' },
        };

        chartBar = new ApexCharts(document.getElementById('chartBar'), opts);
        chartBar.render();
    }

    // ── Pie chart ──────────────────────────────────────────────────────
    function renderPieChart(data) {
        if (chartPie) { chartPie.destroy(); chartPie = null; }

        const storeEntries = Object.entries(data.stores);
        const colorMap = buildColorMap(storeEntries.map(([id]) => id));

        const opts = {
            series: storeEntries.map(([,v]) => v.total),
            labels: storeEntries.map(([,v]) => v.name),
            colors: storeEntries.map(([id]) => colorMap[id]),
            chart: { type: 'donut', height: 320 },
            tooltip: { y: { formatter: v => fullDH(v) } },
            legend: { position: 'bottom', fontSize: '12px' },
            dataLabels: { formatter: (val) => val.toFixed(1) + '%' },
            plotOptions: { pie: { donut: { size: '55%' } } },
        };

        chartPie = new ApexCharts(document.getElementById('chartPie'), opts);
        chartPie.render();
    }

    // ── Rank table ─────────────────────────────────────────────────────
    function renderRankTable(data) {
        const storeEntries = Object.entries(data.stores);
        const grandTotal   = storeEntries.reduce((s,[,v]) => s + v.total, 0);
        const colorMap     = buildColorMap(storeEntries.map(([id]) => id));

        document.getElementById('comp-rank-tbody').innerHTML = storeEntries.map(([id, info], i) => {
            const pct = grandTotal > 0 ? (info.total / grandTotal * 100).toFixed(1) : 0;
            return `<tr>
                <td class="text-center fw-bold">${medals[i] ?? (i+1)}</td>
                <td>
                    <span class="fw-semibold" style="color:${colorMap[id]}">${info.name}</span>
                </td>
                <td class="text-end fw-semibold">${fullDH(info.total)}</td>
                <td class="text-end text-muted">${info.nb.toLocaleString('fr-MA')}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="flex-grow-1 bg-light rounded" style="height:8px">
                            <div class="rank-bar" style="width:${pct}%;background:${colorMap[id]}"></div>
                        </div>
                        <span class="small text-muted">${pct}%</span>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    // ── Detail table ───────────────────────────────────────────────────
    function renderDetailTable(data) {
        const storeEntries = Object.entries(data.stores);
        const colorMap     = buildColorMap(storeEntries.map(([id]) => id));

        // Head
        document.getElementById('comp-detail-head').innerHTML = `<tr>
            <th>Période</th>
            ${storeEntries.map(([id,info]) =>
                `<th class="text-end" style="color:${colorMap[id]}">${info.name}</th>`).join('')}
            <th class="text-end">Total</th>
        </tr>`;

        // Body
        const periodTotals = {};
        const rows = data.periods.map(period => {
            let rowTotal = 0;
            const cells = storeEntries.map(([id]) => {
                const v = data.pivot[id]?.[period]?.total ?? 0;
                rowTotal += v;
                return `<td class="text-end">${v > 0 ? fmtDH(v) : '<span class="text-muted">—</span>'}</td>`;
            }).join('');
            periodTotals[period] = rowTotal;
            return `<tr><td class="fw-semibold">${period}</td>${cells}<td class="text-end fw-semibold">${fmtDH(rowTotal)}</td></tr>`;
        });
        document.getElementById('comp-detail-tbody').innerHTML = rows.join('');

        // Foot totals
        const footCells = storeEntries.map(([,info]) =>
            `<td class="text-end">${fmtDH(info.total)}</td>`).join('');
        const grandTotal = storeEntries.reduce((s,[,v]) => s + v.total, 0);
        document.getElementById('comp-detail-tfoot').innerHTML =
            `<tr><td>TOTAL</td>${footCells}<td class="text-end">${fmtDH(grandTotal)}</td></tr>`;
    }

    // ── color map: storeId → color (stable per store across renders) ───
    const STORE_COLOR_CACHE = {};
    let colorIdx = 0;
    function buildColorMap(storeIds) {
        storeIds.forEach(id => {
            if (!STORE_COLOR_CACHE[id]) {
                STORE_COLOR_CACHE[id] = COLORS[colorIdx++ % COLORS.length];
            }
        });
        return STORE_COLOR_CACHE;
    }

    // ── auto-load on page open ─────────────────────────────────────────
    fetch();
})();
</script>
@endsection
