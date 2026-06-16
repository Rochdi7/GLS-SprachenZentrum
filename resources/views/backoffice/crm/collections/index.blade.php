@extends('layouts.main')

@section('title', 'Recouvrement CRM — Tableau de bord')
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'Recouvrement')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .kpi-card .card-body { padding: 1.25rem 1rem; }
        .kpi-card h3 { font-size: 1.6rem; font-weight: 700; margin: 0; }
        .kpi-card .kpi-label { font-size: .78rem; color: #6c757d; margin-bottom: .25rem; }
        .aging-card { border-left: 4px solid; }
        .aging-card.border-success { border-left-color: #198754 !important; }
        .aging-card.border-warning { border-left-color: #ffc107 !important; }
        .aging-card.border-orange  { border-left-color: #fd7e14 !important; }
        .aging-card.border-danger  { border-left-color: #dc3545 !important; }
        .aging-card.border-dark    { border-left-color: #212529 !important; }
        .drill-btn { cursor:pointer; opacity:.6; font-size:.85rem; }
        .drill-btn:hover { opacity:1; }
        #drillTable td, #drillTable th { font-size:.85rem; }
        /* Responsive table improvements */
        .col-xl-7, .col-xl-5 { min-width: 0; }
        .table-card .card-body { padding: .75rem; }
        .table td, .table th { white-space: nowrap; font-size: .82rem; }
        /* Pagination */
        .tbl-pagination { display:flex; align-items:center; justify-content:space-between; padding:.5rem .75rem; border-top:1px solid #f0f0f0; flex-wrap:wrap; gap:.5rem; }
        .tbl-pagination .page-info { font-size:.78rem; color:#6c757d; }
        .tbl-pagination .btn-group .btn { font-size:.78rem; padding:.25rem .55rem; }
    </style>
@endsection

@section('content')

    {{-- ------------------------------------------------------------------ --}}
    {{-- Toast notifications                                                  --}}
    {{-- ------------------------------------------------------------------ --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Recouvrement CRM</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body {{ session('error') ? 'text-danger' : '' }}">
                    {{ session('success') ?? session('error') }}
                </div>
            </div>
        </div>
    @endif

    {{-- ------------------------------------------------------------------ --}}
    {{-- Header row: title + center filter + refresh                          --}}
    {{-- ------------------------------------------------------------------ --}}
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="mb-0 d-flex align-items-center gap-2 flex-wrap">
                <i class="ph-duotone ph-currency-circle-dollar text-primary"></i>
                Tableau de bord Recouvrement
                @include('backoffice.crm.partials._snapshot_badge', ['snapshotDate' => $snapshotDate ?? null])
            </h4>
        </div>
        <div class="col-auto d-flex gap-2 align-items-center">
            {{-- Center filter --}}
            @if (!empty($crmCenters))
                <form method="GET" action="{{ route('backoffice.crm.collections.index') }}" class="d-flex gap-2">
                    <select name="strStoreId" class="form-select form-select-sm" style="width:220px"
                            onchange="this.form.submit()">
                        <option value="">— Tous les centres —</option>
                        @foreach ($crmCenters as $center)
                            @if ($center->crm_store_id)
                            <option value="{{ $center->crm_store_id }}"
                                {{ $strStoreId == $center->crm_store_id ? 'selected' : '' }}>
                                {{ $center->name }}
                            </option>
                            @endif
                        @endforeach
                    </select>
                </form>
            @endif

            {{-- Refresh button --}}
            <form method="POST" action="{{ route('backoffice.crm.collections.refresh') }}">
                @csrf
                @if ($strStoreId)
                    <input type="hidden" name="strStoreId" value="{{ $strStoreId }}">
                @endif
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="ph-duotone ph-arrows-clockwise me-1"></i> Actualiser
                </button>
            </form>
            @include('backoffice.crm.partials._sync_badge')
        </div>
    </div>

    {{-- ------------------------------------------------------------------ --}}
    {{-- API error alert                                                       --}}
    {{-- ------------------------------------------------------------------ --}}
    @if ($error)
        <div class="alert alert-danger mb-3">
            <i class="ph-duotone ph-warning-circle me-2"></i>
            <strong>Erreur API CRM :</strong> {{ $error }}
        </div>
    @endif

    {{-- ================================================================== --}}
    {{-- KPI CARDS — Row 1: outstanding + due buckets                         --}}
    {{-- ================================================================== --}}
    @php
    $kpiCards = [
        ['type'=>'outstanding', 'label'=>"Montant total en attente",    'value'=>$kpis['outstandingTotal']??null, 'color'=>'primary'],
        ['type'=>'dueToday',    'label'=>"Tranches dues aujourd'hui",   'value'=>$kpis['dueToday']??null,        'color'=>'info'],
        ['type'=>'dueWeek',     'label'=>"Tranches dues cette semaine", 'value'=>$kpis['dueWeek']??null,         'color'=>'success'],
        ['type'=>'dueMonth',    'label'=>"Tranches dues ce mois",       'value'=>$kpis['dueMonth']??null,        'color'=>'warning'],
    ];
    $overdueCards = [
        ['type'=>'overdue7',  'label'=>'En retard &gt; 7 jours',  'value'=>$kpis['overdue7']??null,  'color'=>'secondary', 'style'=>''],
        ['type'=>'overdue30', 'label'=>'En retard &gt; 30 jours', 'value'=>$kpis['overdue30']??null, 'color'=>'warning',   'style'=>''],
        ['type'=>'overdue60', 'label'=>'En retard &gt; 60 jours', 'value'=>$kpis['overdue60']??null, 'color'=>'danger',    'style'=>''],
        ['type'=>'overdue90', 'label'=>'En retard &gt; 90 jours', 'value'=>$kpis['overdue90']??null, 'color'=>'dark',      'style'=>''],
    ];
    @endphp

    <div class="row g-3 mb-3">
        @foreach ($kpiCards as $card)
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card h-100 border-start border-{{ $card['color'] }} border-3">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">{!! $card['label'] !!}</div>
                        <h3 class="text-{{ $card['color'] }}">
                            {{ $card['value'] !== null ? number_format($card['value'], 2, ',', ' ') . ' DH' : '—' }}
                        </h3>
                    </div>
                    <span class="drill-btn text-{{ $card['color'] }}" data-type="{{ $card['type'] }}" data-label="{!! $card['label'] !!}" title="Voir les dossiers">
                        <i class="ph-duotone ph-eye fs-4"></i>
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        @foreach ($overdueCards as $card)
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card h-100 border-start border-{{ $card['color'] }} border-3">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">{!! $card['label'] !!}</div>
                        <h3 class="text-{{ $card['color'] }}">
                            {{ $card['value'] !== null ? number_format($card['value'], 2, ',', ' ') . ' DH' : '—' }}
                        </h3>
                    </div>
                    <span class="drill-btn text-{{ $card['color'] }}"
                          data-type="{{ $card['type'] }}" data-label="{!! $card['label'] !!}" title="Voir les dossiers">
                        <i class="ph-duotone ph-eye fs-4"></i>
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ================================================================== --}}
    {{-- CA PAR MOIS — hidden for now
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0">
                        <i class="ph-duotone ph-currency-circle-dollar me-2 text-success"></i>
                        Chiffre d'affaires par période
                        <small class="text-muted fw-normal ms-1">(échéances, statut actif)</small>
                    </h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <input type="date" id="ca-start" class="form-control form-control-sm" style="width:150px">
                        <span class="text-muted small">→</span>
                        <input type="date" id="ca-end" class="form-control form-control-sm" style="width:150px">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary ca-preset active" data-preset="2m">2 mois</button>
                            <button class="btn btn-sm btn-outline-secondary ca-preset" data-preset="6m">6 mois</button>
                            <button class="btn btn-sm btn-outline-secondary ca-preset" data-preset="year">Cette année</button>
                        </div>
                        <button id="ca-fetch-btn" class="btn btn-sm btn-success">
                            <i class="ph-duotone ph-magnifying-glass me-1"></i>Afficher
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="ca-loading" class="text-center py-4 d-none">
                        <div class="spinner-border text-success" role="status"></div>
                        <p class="text-muted mt-2 mb-0 small">Calcul en cours…</p>
                    </div>
                    <div id="ca-error" class="alert alert-danger d-none mb-0"></div>
                    <div id="ca-chart-wrap" style="min-height:340px"></div>
                    <div id="ca-total-row" class="d-none mt-3 d-flex flex-wrap gap-3 justify-content-center" id="ca-kpi-row"></div>
                </div>
            </div>
        </div>
    </div>
    --}}

    {{-- ================================================================== --}}

    {{-- ================================================================== --}}
    {{-- TABLES ROW: Upcoming Dues                                             --}}
    {{-- ================================================================== --}}
    <div class="row g-3 mb-4">

        {{-- Upcoming Dues --}}
        <div class="col-12">
            <div class="card table-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="ph-duotone ph-calendar-check me-2"></i>
                        Echéances à venir <span class="text-muted small">(14 j)</span>
                        <span class="badge bg-light-info text-info ms-2" id="duesBadge">{{ count($upcomingDues) }}</span>
                    </h5>
                    <input type="text" id="duesSearch" class="form-control form-control-sm ms-2" style="max-width:160px" placeholder="Rechercher…">
                </div>
                <div class="card-body p-0">
                    @if (empty($upcomingDues))
                        <p class="text-muted text-center py-4 mb-0">Aucune échéance prochaine.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0" id="duesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Etudiant</th>
                                        <th class="text-end" style="width:110px">Montant</th>
                                        <th style="width:90px">Echéance</th>
                                        <th class="text-center" style="width:60px">Dans</th>
                                    </tr>
                                </thead>
                                <tbody id="duesTbody">
                                    @foreach ($upcomingDues as $due)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $due['student_name'] }}</div>
                                                <small class="text-muted">{{ $due['store_name'] }}</small>
                                            </td>
                                            <td class="text-end fw-semibold">
                                                {{ number_format($due['amount'], 0, ',', ' ') }} DH
                                            </td>
                                            <td><small>{{ $due['due_date'] ? \Carbon\Carbon::parse($due['due_date'])->format('d/m/Y') : '—' }}</small></td>
                                            <td class="text-center">
                                                @if ($due['days_until'] !== null)
                                                    @php
                                                        $dc = $due['days_until'];
                                                        $bc = $dc === 0 ? 'bg-danger' : ($dc <= 3 ? 'bg-warning text-dark' : 'bg-light-success text-success');
                                                    @endphp
                                                    <span class="badge {{ $bc }}">{{ $dc === 0 ? 'Auj.' : $dc.' j' }}</span>
                                                @else —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="tbl-pagination" id="duesPagination">
                            <span class="page-info" id="duesInfo"></span>
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary" id="duesPrev"><i class="ti ti-chevron-left"></i></button>
                                <span class="btn btn-outline-secondary disabled" id="duesPages" style="pointer-events:none;min-width:80px;text-align:center;font-size:.78rem"></span>
                                <button class="btn btn-outline-secondary" id="duesNext"><i class="ti ti-chevron-right"></i></button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


@endsection

{{-- Drill-down modal --}}
<div class="modal fade" id="drillModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ph-duotone ph-users me-2"></i>
                    <span id="drillModalTitle">Dossiers</span>
                    <span class="badge bg-secondary ms-2" id="drillModalCount"></span>
                    <span class="badge bg-light text-dark ms-1" id="drillModalTotal"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="drillLoading" class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Chargement...</p>
                </div>
                <div id="drillContent" class="d-none">
                    <div class="px-3 pt-2 pb-1">
                        <input type="text" id="drillSearch" class="form-control form-control-sm" placeholder="Rechercher par nom, centre...">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0" id="drillTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Nom étudiant</th>
                                    <th>Centre</th>
                                    <th>Échéance</th>
                                    <th>Retard</th>
                                    <th class="text-end">Montant dû</th>
                                </tr>
                            </thead>
                            <tbody id="drillTbody"></tbody>
                        </table>
                    </div>
                </div>
                <div id="drillEmpty" class="d-none text-center py-5 text-muted">
                    <i class="ph-duotone ph-check-circle fs-1 text-success"></i>
                    <p class="mt-2">Aucun dossier dans cette catégorie.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script type="application/json" id="crm-collections-data">
{
    "drillUrl":   "{{ route('backoffice.crm.collections.drill') }}",
    "caEndpoint": "{{ route('backoffice.crm.collections.ca-data') }}",
    "storeId":    "{{ $strStoreId ?? '' }}"
}
</script>
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/backoffice/crm-collections.js') }}"></script>
@endsection
