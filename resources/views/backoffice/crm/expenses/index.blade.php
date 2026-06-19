@extends('layouts.main')

@section('title', 'Dépenses CRM')
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'Dépenses')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

{{-- ── Header ── --}}
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="ph-duotone ph-receipt text-primary"></i>
            Dépenses CRM — Wimschool
        </h4>
    </div>
    <div class="col-auto">
        @include('backoffice.crm.partials._sync_badge')
    </div>
</div>

{{-- ── Chart ── --}}
<div class="card mb-4">
    <div class="card-header py-2">
        <h6 class="mb-0">Dépenses mensuelles par centre (6 derniers mois)</h6>
    </div>
    <div class="card-body">
        <div id="crm-expenses-chart" style="min-height:300px;"></div>
    </div>
</div>

{{-- ── Filters ── --}}
<form method="GET" action="{{ route('backoffice.crm.expenses.index') }}" class="row g-2 mb-3 align-items-end">
    <div class="col-auto">
        <select name="site_id" class="form-select form-select-sm" style="width:180px">
            <option value="">— Tous les centres —</option>
            @foreach ($sites as $site)
                <option value="{{ $site->id }}" {{ $selectedSite == $site->id ? 'selected' : '' }}>
                    {{ $site->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="type" class="form-select form-select-sm" style="width:200px">
            <option value="">— Tous les types —</option>
            @foreach(\App\Models\SiteExpense::TYPES as $key => $label)
                <option value="{{ $key }}" {{ $selectedType === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}" style="width:140px">
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary">
            <i class="ph-duotone ph-funnel me-1"></i> Filtrer
        </button>
        <a href="{{ route('backoffice.crm.expenses.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ph-duotone ph-x me-1"></i> Reset
        </a>
    </div>
</form>

{{-- ── Centre ranking ── --}}
@php
    $rankColors = ['#4680ff','#1cc88a','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14'];
    $rankIcons  = ['ph-trophy','ph-medal','ph-star','ph-circle','ph-circle','ph-circle','ph-circle'];
@endphp
<div class="card">
    <div class="card-header py-2 d-flex align-items-center justify-content-between">
        <h6 class="mb-0">
            <i class="ph-duotone ph-ranking text-primary me-1"></i>
            Classement des centres par dépenses totales
        </h6>
        <span class="text-muted small">{{ $centerSummary->count() }} centres</span>
    </div>
    <div class="card-body py-3">
        @forelse ($centerSummary as $i => $row)
        @php
            $pct   = $maxTotal > 0 ? round(($row->total / $maxTotal) * 100) : 0;
            $color = $rankColors[$i] ?? '#adb5bd';
            $isTop = $i === 0;
        @endphp
        <div class="mb-3 {{ !$loop->last ? 'pb-3 border-bottom' : '' }}">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <div class="d-flex align-items-center gap-2">
                    {{-- Rank badge --}}
                    <span class="fw-bold text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                          style="width:28px;height:28px;font-size:.75rem;background:{{ $color }};flex-shrink:0;">
                        {{ $i + 1 }}
                    </span>
                    <span class="fw-semibold {{ $isTop ? 'fs-6' : '' }}">{{ $row->site_name }}</span>
                    @if ($isTop)
                        <span class="badge ms-1" style="background:{{ $color }};font-size:.65rem;">
                            <i class="ph ph-trophy-fill"></i> 1er
                        </span>
                    @endif
                </div>
                <div class="text-end">
                    <div class="fw-bold" style="color:{{ $color }};">
                        {{ number_format($row->total, 2, ',', ' ') }} DH
                    </div>
                    <div class="text-muted" style="font-size:.72rem;">
                        {{ number_format($row->count, 0, ',', ' ') }} dépenses
                    </div>
                </div>
            </div>
            <div class="progress" style="height:6px;border-radius:4px;">
                <div class="progress-bar" role="progressbar"
                     style="width:{{ $pct }}%;background:{{ $color }};"
                     aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>
        @empty
            <p class="text-muted text-center mb-0">Aucune donnée.</p>
        @endforelse
    </div>
</div>

{{-- ── Type breakdown ── --}}
@php
    $typeColors  = ['#4680ff','#1cc88a','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14','#20c997','#e83e8c','#6c757d','#17a2b8'];
    $typeTotalSum = array_sum(array_column($typeBreakdown, 'total')) ?: 1;
@endphp
<div class="card mt-4">
    <div class="card-header py-2 d-flex align-items-center justify-content-between">
        <h6 class="mb-0">
            <i class="ph-duotone ph-tag text-warning me-1"></i>
            Dépenses par type
        </h6>
        <span class="text-muted small">{{ count($typeBreakdown) }} types</span>
    </div>
    <div class="card-body py-3">
        @forelse ($typeBreakdown as $i => $row)
            @php
                // Use share-of-total so every bar is proportionally visible
                $sharePct = round(($row['total'] / $typeTotalSum) * 100, 1);
                // Minimum 2% width so tiny slices still show a visible bar
                $barPct   = max($sharePct, $row['total'] > 0 ? 2 : 0);
                $color    = $typeColors[$i] ?? '#adb5bd';
            @endphp
            <div class="mb-3 {{ !$loop->last ? 'pb-3 border-bottom' : '' }}">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle d-inline-flex"
                              style="width:10px;height:10px;background:{{ $color }};flex-shrink:0;"></span>
                        <span class="fw-semibold">{{ $row['label'] }}</span>
                        <a href="{{ route('backoffice.crm.expenses.index', array_merge(request()->only('site_id','month'), ['type' => $row['key']])) }}"
                           class="badge bg-light-secondary text-secondary ms-1" style="font-size:.7rem; text-decoration:none;">
                            filtrer
                        </a>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold" style="color:{{ $color }};">
                            {{ number_format($row['total'], 2, ',', ' ') }} DH
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            {{ $sharePct }}% &middot; {{ number_format($row['count'], 0, ',', ' ') }} entrées
                        </div>
                    </div>
                </div>
                <div class="progress" style="height:6px;border-radius:4px;">
                    <div class="progress-bar" role="progressbar"
                         style="width:{{ $barPct }}%;background:{{ $color }};"
                         aria-valuenow="{{ $barPct }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted text-center mb-0">Aucune donnée.</p>
        @endforelse

        {{-- Donut chart --}}
        @if(count($typeBreakdown) > 0)
            <hr>
            <div id="crm-expenses-type-chart" style="min-height:260px;" class="mt-2"></div>
            <script type="application/json" id="crm-expenses-type-data">
            {!! json_encode([
                'labels' => array_column($typeBreakdown, 'label'),
                'series' => array_column($typeBreakdown, 'total'),
                'colors' => array_slice($typeColors, 0, count($typeBreakdown)),
            ]) !!}
            </script>
        @endif
    </div>
</div>

{{-- Data island for chart --}}
<script type="application/json" id="crm-expenses-chart-data">
{!! json_encode([
    'months'  => $chartMonths,
    'series'  => $chartSeries,
]) !!}
</script>

@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/backoffice/crm-expenses.js') }}"></script>
    <script>
    (function () {
        const el = document.getElementById('crm-expenses-type-chart');
        const src = document.getElementById('crm-expenses-type-data');
        if (!el || !src) return;
        const { labels, series, colors } = JSON.parse(src.textContent);
        const total = series.reduce((a, b) => a + b, 0) || 1;
        // sqrt-normalize so small slices are still visually distinguishable
        const sqrtSeries = series.map(v => Math.sqrt(v));
        const sqrtTotal  = sqrtSeries.reduce((a, b) => a + b, 0) || 1;
        new ApexCharts(el, {
            chart: { type: 'donut', height: 300 },
            labels,
            series: sqrtSeries,
            colors,
            legend: { position: 'bottom', fontSize: '12px' },
            plotOptions: {
                pie: {
                    donut: { size: '60%' },
                    expandOnClick: false,
                    minAngleToShowLabel: 0,
                }
            },
            stroke: { width: 2, colors: ['#1e2533'] },
            dataLabels: {
                enabled: true,
                // Show real % of actual total, not the normalized arc
                formatter: (val, opts) => {
                    const real = (series[opts.seriesIndex] / total * 100).toFixed(1);
                    return real >= 1 ? real + '%' : '';
                },
                dropShadow: { enabled: false },
                style: { fontSize: '11px' },
            },
            tooltip: {
                y: {
                    formatter: (v, opts) => {
                        const real = series[opts.seriesIndex];
                        return new Intl.NumberFormat('fr-MA', { minimumFractionDigits: 2 }).format(real) + ' DH';
                    }
                }
            },
        }).render();
    })();
    </script>
@endsection
