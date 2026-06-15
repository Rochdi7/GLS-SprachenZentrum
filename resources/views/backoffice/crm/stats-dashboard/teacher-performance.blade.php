@extends('layouts.main')

@section('title', 'Performance professeurs')
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'Performance professeurs')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .tp-table td, .tp-table th { vertical-align: middle; }
        .ret-bar { height: 7px; }
        .metric-pill { font-size: .72rem; font-weight: 600; }
        .top-card { border: 2px solid #1cc88a; background: linear-gradient(135deg,#effaf4 0%,#fbfffd 100%); }
    </style>
@endsection

@section('content')

{{-- ── Header ─────────────────────────────────────────────────────── --}}
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2 flex-wrap">
            <i class="ph-duotone ph-chalkboard-teacher text-success"></i>
            Performance professeurs
            @include('backoffice.crm.partials._snapshot_badge', ['snapshotDate' => $snapshotDate ?? null])
        </h4>
        <small class="text-muted">Étudiants au départ (débuts), arrivées tardives, et étudiants perdus par professeur.</small>
    </div>
    <div class="col-auto d-flex gap-2">
        @if ($crmCenters->isNotEmpty())
            <select id="tp-store" class="form-select form-select-sm" style="width:200px">
                <option value="">— Tous les centres —</option>
                @foreach ($crmCenters->whereNotNull('crm_store_id') as $center)
                    <option value="{{ $center->crm_store_id }}" {{ $storeId == $center->crm_store_id ? 'selected' : '' }}>{{ $center->name }}</option>
                @endforeach
            </select>
        @endif
        @include('backoffice.crm.partials._sync_badge')
    </div>
</div>

{{-- ── Date range form ────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body">
        <form id="tp-form" class="row g-3 align-items-end">
            <div class="col-auto">
                <label class="form-label fw-semibold mb-1"><i class="ph-duotone ph-calendar-blank me-1 text-success"></i>Date de début</label>
                <input type="date" id="tp-start-date" class="form-control" style="min-width:170px">
            </div>
            <div class="col-auto">
                <label class="form-label fw-semibold mb-1"><i class="ph-duotone ph-calendar-blank me-1 text-success"></i>Date de fin</label>
                <input type="date" id="tp-end-date" class="form-control" style="min-width:170px">
            </div>
            <div class="col-auto">
                <button id="tp-btn" class="btn btn-success" type="submit">
                    <i class="ph-duotone ph-magnifying-glass me-1"></i><span class="tp-btn-label">Analyser</span>
                </button>
            </div>
            <div class="col-auto d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-dark tp-preset" data-preset="ytd">Année en cours</button>
                <button type="button" class="btn btn-sm btn-outline-dark tp-preset" data-preset="lastyear">Année dernière</button>
                <button type="button" class="btn btn-sm btn-outline-dark tp-preset" data-preset="quarter">Ce trimestre</button>
            </div>
        </form>
    </div>
</div>

{{-- Loading + error --}}
<div id="tp-loading" class="text-center py-5 d-none">
    <div class="spinner-border text-success" role="status"></div>
    <p class="text-muted mt-2 mb-0 small">Calcul des évolutions par professeur…</p>
</div>
<div id="tp-error" class="alert alert-danger d-none"></div>
<div id="tp-empty" class="text-center py-5 text-muted d-none">
    <i class="ph-duotone ph-chalkboard-teacher" style="font-size:2.5rem"></i>
    <p class="mt-2 mb-0">Aucune classe / professeur sur cette période.</p>
</div>

{{-- ── Results ────────────────────────────────────────────────────── --}}
<div id="tp-results" class="d-none">

    {{-- Top teacher banner --}}
    <div class="card top-card mb-4">
        <div class="card-body d-flex align-items-center gap-3 flex-wrap" id="tp-top"></div>
    </div>

    {{-- Totals --}}
    <div class="row g-3 mb-4" id="tp-totals"></div>

    <div class="row g-3 mb-4">
        {{-- Chart débuts vs quittants --}}
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0"><i class="ph-duotone ph-chart-bar me-2 text-success"></i>Débuts vs Étudiants perdus par professeur</h6></div>
                <div class="card-body"><div id="tp-chart" style="min-height:360px"></div></div>
            </div>
        </div>
        {{-- Top retention --}}
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0"><i class="ph-duotone ph-shield-check me-2 text-info"></i>Meilleure rétention</h6><small class="text-muted">Moins de pertes relatives aux arrivées</small></div>
                <div class="card-body p-2"><div class="table-responsive"><table class="table table-sm mb-0"><tbody id="tp-ret-tbody"></tbody></table></div></div>
            </div>
        </div>
    </div>

    {{-- Full table --}}
    <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="ph-duotone ph-list-numbers me-2 text-success"></i>Tableau complet</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm tp-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:42px">#</th>
                            <th>Professeur</th>
                            <th class="text-end">Classes</th>
                            <th class="text-end" title="Étudiants au départ">Débuts</th>
                            <th class="text-end" title="Arrivées tardives">Ajouts</th>
                            <th class="text-end text-danger" title="Étudiants perdus (annulés)">Perdus</th>
                            <th class="text-end">Actifs</th>
                            <th class="text-end">Rétention</th>
                            <th style="width:14%"></th>
                        </tr>
                    </thead>
                    <tbody id="tp-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script type="application/json" id="tp-config">
{ "endpoint": "{{ route('backoffice.crm.statistiques.professeurs.data') }}" }
</script>
<script src="{{ URL::asset('assets/js/backoffice/crm-teacher-performance.js') }}?v={{ @filemtime(public_path('assets/js/backoffice/crm-teacher-performance.js')) ?: '1' }}"></script>
@endsection
