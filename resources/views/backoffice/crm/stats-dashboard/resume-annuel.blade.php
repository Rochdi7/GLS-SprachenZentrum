@extends('layouts.main')

@section('title', 'Résumé annuel — Performance des centres (primes)')
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'Résumé annuel')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .winner-card { background: linear-gradient(135deg, #fff8e1 0%, #fffdf5 100%); border: 2px solid #ffc107; }
        .winner-card .crown { font-size: 2.4rem; line-height: 1; }
        .rank-table td, .rank-table th { vertical-align: middle; }
        .score-bar { height: 8px; }
        .metric-pill { font-size: .72rem; font-weight: 600; }
    </style>
@endsection

@section('content')

{{-- ── Header ─────────────────────────────────────────────────────── --}}
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2 flex-wrap">
            <i class="ph-duotone ph-trophy text-warning"></i>
            Résumé annuel — Performance des centres
            @include('backoffice.crm.partials._snapshot_badge', ['snapshotDate' => $snapshotDate ?? null])
        </h4>
        <small class="text-muted">Scannez toute la base sur une période pour primer les meilleurs centres.</small>
    </div>
    <div class="col-auto">
        @include('backoffice.crm.partials._sync_badge')
    </div>
</div>

{{-- ── Date range form ────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body">
        <form id="ra-form" class="row g-3 align-items-end">
            <div class="col-auto">
                <label class="form-label fw-semibold mb-1"><i class="ph-duotone ph-calendar-blank me-1 text-warning"></i>Date de début</label>
                <input type="date" id="ra-start-date" class="form-control" style="min-width:170px">
            </div>
            <div class="col-auto">
                <label class="form-label fw-semibold mb-1"><i class="ph-duotone ph-calendar-blank me-1 text-warning"></i>Date de fin</label>
                <input type="date" id="ra-end-date" class="form-control" style="min-width:170px">
            </div>
            <div class="col-auto">
                <button id="ra-btn" class="btn btn-warning text-white" type="submit">
                    <i class="ph-duotone ph-magnifying-glass me-1"></i><span class="ra-btn-label">Analyser</span>
                </button>
            </div>
            <div class="col-auto d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-dark ra-preset" data-preset="ytd">Année en cours</button>
                <button type="button" class="btn btn-sm btn-outline-dark ra-preset" data-preset="lastyear">Année dernière</button>
                <button type="button" class="btn btn-sm btn-outline-dark ra-preset" data-preset="quarter">Ce trimestre</button>
                <button type="button" class="btn btn-sm btn-outline-dark ra-preset" data-preset="month">Ce mois</button>
            </div>
        </form>
    </div>
</div>

{{-- Loading + error --}}
<div id="ra-loading" class="text-center py-5 d-none">
    <div class="spinner-border text-warning" role="status"></div>
    <p class="text-muted mt-2 mb-0 small">Analyse de toute la base…</p>
</div>
<div id="ra-error" class="alert alert-danger d-none"></div>
<div id="ra-empty" class="text-center py-5 text-muted d-none">
    <i class="ph-duotone ph-chart-bar" style="font-size:2.5rem"></i>
    <p class="mt-2 mb-0">Aucune donnée sur cette période.</p>
</div>

{{-- ── Results ────────────────────────────────────────────────────── --}}
<div id="ra-results" class="d-none">

    {{-- Winner banner --}}
    <div class="card winner-card mb-4">
        <div class="card-body d-flex align-items-center gap-3 flex-wrap" id="ra-winner"></div>
    </div>

    {{-- Grand totals --}}
    <div class="row g-3 mb-4" id="ra-grand"></div>

    {{-- Composite ranking --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0"><i class="ph-duotone ph-medal me-2 text-warning"></i>Classement global (score de performance)</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm rank-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:42px">#</th>
                            <th>Centre</th>
                            <th class="text-end">Encaissé</th>
                            <th class="text-end">Reste à payer</th>
                            <th class="text-end">Recouvrement</th>
                            <th class="text-end">Inscriptions</th>
                            <th class="text-end">Score</th>
                            <th style="width:18%"></th>
                        </tr>
                    </thead>
                    <tbody id="ra-score-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Three category rankings --}}
    <div class="row g-3">
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0"><i class="ph-duotone ph-money me-2 text-primary"></i>Top encaissement</h6></div>
                <div class="card-body p-2"><div class="table-responsive"><table class="table table-sm mb-0"><tbody id="ra-enc-tbody"></tbody></table></div></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0"><i class="ph-duotone ph-hand-coins me-2 text-success"></i>Top recouvrement</h6><small class="text-muted">Plus encaissé, moins de reste à payer</small></div>
                <div class="card-body p-2"><div class="table-responsive"><table class="table table-sm mb-0"><tbody id="ra-rec-tbody"></tbody></table></div></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0"><i class="ph-duotone ph-user-plus me-2 text-info"></i>Top inscriptions</h6></div>
                <div class="card-body p-2"><div class="table-responsive"><table class="table table-sm mb-0"><tbody id="ra-insc-tbody"></tbody></table></div></div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script type="application/json" id="ra-config">
{ "endpoint": "{{ route('backoffice.crm.statistiques.resume-annuel.data') }}" }
</script>
<script src="{{ URL::asset('assets/js/backoffice/crm-resume-annuel.js') }}?v={{ @filemtime(public_path('assets/js/backoffice/crm-resume-annuel.js')) ?: '1' }}"></script>
@endsection
