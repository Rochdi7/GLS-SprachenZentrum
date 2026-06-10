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
<form method="GET" action="{{ route('backoffice.crm.expenses.index') }}" class="row g-2 mb-3">
    <div class="col-auto">
        <select name="site_id" class="form-select form-select-sm" style="width:200px">
            <option value="">— Tous les centres —</option>
            @foreach ($sites as $site)
                <option value="{{ $site->id }}" {{ $selectedSite == $site->id ? 'selected' : '' }}>
                    {{ $site->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary">Filtrer</button>
        <a href="{{ route('backoffice.crm.expenses.index') }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
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
@endsection
