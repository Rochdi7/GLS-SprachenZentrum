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
            <h4 class="mb-0">
                <i class="ph-duotone ph-currency-circle-dollar me-2 text-primary"></i>
                Tableau de bord Recouvrement
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
    <div class="row g-3 mb-3">
        {{-- Outstanding Total --}}
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card h-100 border-start border-primary border-3">
                <div class="card-body">
                    <div class="kpi-label">Montant total en attente</div>
                    <h3 class="text-primary">
                        {{ isset($kpis['outstandingTotal']) ? number_format($kpis['outstandingTotal'], 2, ',', ' ') . ' DH' : '—' }}
                    </h3>
                </div>
            </div>
        </div>
        {{-- Due Today --}}
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card h-100 border-start border-info border-3">
                <div class="card-body">
                    <div class="kpi-label">Tranches dues aujourd'hui</div>
                    <h3 class="text-info">
                        {{ isset($kpis['dueToday']) ? number_format($kpis['dueToday'], 2, ',', ' ') . ' DH' : '—' }}
                    </h3>
                </div>
            </div>
        </div>
        {{-- Due This Week --}}
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card h-100 border-start border-success border-3">
                <div class="card-body">
                    <div class="kpi-label">Tranches dues cette semaine</div>
                    <h3 class="text-success">
                        {{ isset($kpis['dueWeek']) ? number_format($kpis['dueWeek'], 2, ',', ' ') . ' DH' : '—' }}
                    </h3>
                </div>
            </div>
        </div>
        {{-- Due This Month --}}
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card h-100 border-start border-warning border-3">
                <div class="card-body">
                    <div class="kpi-label">Tranches dues ce mois</div>
                    <h3 class="text-warning">
                        {{ isset($kpis['dueMonth']) ? number_format($kpis['dueMonth'], 2, ',', ' ') . ' DH' : '—' }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI CARDS — Row 2: overdue buckets --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card h-100 border-start border-secondary border-3">
                <div class="card-body">
                    <div class="kpi-label">En retard &gt; 7 jours</div>
                    <h3 class="text-secondary">
                        {{ isset($kpis['overdue7']) ? number_format($kpis['overdue7'], 2, ',', ' ') . ' DH' : '—' }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card h-100" style="border-left: 4px solid #fd7e14;">
                <div class="card-body">
                    <div class="kpi-label">En retard &gt; 30 jours</div>
                    <h3 style="color:#fd7e14">
                        {{ isset($kpis['overdue30']) ? number_format($kpis['overdue30'], 2, ',', ' ') . ' DH' : '—' }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card h-100 border-start border-danger border-3">
                <div class="card-body">
                    <div class="kpi-label">En retard &gt; 60 jours</div>
                    <h3 class="text-danger">
                        {{ isset($kpis['overdue60']) ? number_format($kpis['overdue60'], 2, ',', ' ') . ' DH' : '—' }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card h-100 border-start border-dark border-3">
                <div class="card-body">
                    <div class="kpi-label">En retard &gt; 90 jours</div>
                    <h3 class="text-dark">
                        {{ isset($kpis['overdue90']) ? number_format($kpis['overdue90'], 2, ',', ' ') . ' DH' : '—' }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- AGING BUCKETS                                                         --}}
    {{-- ================================================================== --}}
    @if (!empty($agingBuckets))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ph-duotone ph-clock me-2"></i>Répartition par ancienneté</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($agingBuckets as $bucket)
                                @php
                                    $colorClass = match($bucket['color']) {
                                        'orange' => '',
                                        default  => 'border-' . $bucket['color'],
                                    };
                                    $textClass = match($bucket['color']) {
                                        'orange' => 'style="color:#fd7e14"',
                                        default  => 'class="text-' . $bucket['color'] . '"',
                                    };
                                    $borderStyle = $bucket['color'] === 'orange'
                                        ? 'border-left: 4px solid #fd7e14;'
                                        : '';
                                @endphp
                                <div class="col">
                                    <div class="card aging-card {{ $colorClass }}" style="{{ $borderStyle }}">
                                        <div class="card-body text-center py-3">
                                            <div class="fw-bold fs-5" {!! $textClass !!}>
                                                {{ number_format($bucket['amount'], 2, ',', ' ') }} DH
                                            </div>
                                            <div class="text-muted small">{{ $bucket['label'] }}</div>
                                            <div class="badge bg-light text-dark mt-1">{{ $bucket['count'] }} dossier(s)</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================== --}}
    {{-- TABLES ROW: Top Debtors + Upcoming Dues                              --}}
    {{-- ================================================================== --}}
    <div class="row g-3 mb-4">

        {{-- Top Debtors --}}
        <div class="col-xl-7">
            <div class="card table-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ph-duotone ph-users me-2"></i>
                        Top débiteurs
                        <span class="badge bg-light-danger text-danger ms-2">{{ count($topDebtors) }}</span>
                    </h5>
                </div>
                <div class="card-body pt-2">
                    @if (empty($topDebtors))
                        <p class="text-muted text-center py-4 mb-0">Aucune donnée disponible.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Etudiant</th>
                                        <th>Centre</th>
                                        <th class="text-end">Montant dû</th>
                                        <th class="text-end">Retard</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topDebtors as $i => $debtor)
                                        <tr>
                                            <td class="text-muted small">{{ $i + 1 }}</td>
                                            <td>
                                                <strong>{{ $debtor['student_name'] }}</strong>
                                                <br><small class="text-muted">#{{ $debtor['student_id'] }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light-primary">{{ $debtor['store_name'] }}</span>
                                            </td>
                                            <td class="text-end fw-semibold text-danger">
                                                {{ number_format($debtor['total_owed'], 2, ',', ' ') }} DH
                                            </td>
                                            <td class="text-end">
                                                @if ($debtor['overdue_days'] > 0)
                                                    @php
                                                        $badgeClass = $debtor['overdue_days'] > 90 ? 'bg-dark'
                                                            : ($debtor['overdue_days'] > 60 ? 'bg-danger'
                                                            : ($debtor['overdue_days'] > 30 ? 'bg-warning text-dark'
                                                            : 'bg-secondary'));
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">
                                                        {{ $debtor['overdue_days'] }} j
                                                    </span>
                                                @else
                                                    <span class="badge bg-light-success text-success">À jour</span>
                                                @endif
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

        {{-- Upcoming Dues --}}
        <div class="col-xl-5">
            <div class="card table-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ph-duotone ph-calendar-check me-2"></i>
                        Echéances à venir (14 j)
                        <span class="badge bg-light-info text-info ms-2">{{ count($upcomingDues) }}</span>
                    </h5>
                </div>
                <div class="card-body pt-2">
                    @if (empty($upcomingDues))
                        <p class="text-muted text-center py-4 mb-0">Aucune échéance prochaine.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Etudiant</th>
                                        <th class="text-end">Montant</th>
                                        <th>Echéance</th>
                                        <th class="text-center">Dans</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($upcomingDues as $due)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">{{ $due['student_name'] }}</span>
                                                <br><small class="text-muted">{{ $due['store_name'] }}</small>
                                            </td>
                                            <td class="text-end fw-semibold">
                                                {{ number_format($due['amount'], 2, ',', ' ') }} DH
                                            </td>
                                            <td>
                                                <small>{{ $due['due_date'] ? \Carbon\Carbon::parse($due['due_date'])->format('d/m/Y') : '—' }}</small>
                                            </td>
                                            <td class="text-center">
                                                @if ($due['days_until'] !== null)
                                                    @php
                                                        $dc = $due['days_until'];
                                                        $badgeClass = $dc === 0 ? 'bg-danger'
                                                            : ($dc <= 3  ? 'bg-warning text-dark'
                                                            : 'bg-light-success text-success');
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">
                                                        {{ $dc === 0 ? 'Auj.' : ($dc . ' j') }}
                                                    </span>
                                                @else
                                                    —
                                                @endif
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

    {{-- ================================================================== --}}
    {{-- PERFORMANCE BY CENTER (local DB)                                      --}}
    {{-- ================================================================== --}}
    @if (!empty($perfByCenter))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ph-duotone ph-chart-bar me-2"></i>
                            Encaissements par centre — 3 derniers mois
                        </h5>
                    </div>
                    <div class="card-body pt-2">
                        @php
                            // Collect all months from the pivot
                            $allMonths = collect($perfByCenter)
                                ->flatMap(fn($m) => array_keys($m))
                                ->unique()->sort()->values()->all();
                        @endphp
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0">
                                @php
                                    $storeNames = $crmCenters->whereNotNull('crm_store_id')
                                        ->pluck('name', 'crm_store_id')->toArray();
                                @endphp
                                <thead class="table-light">
                                    <tr>
                                        <th>Centre</th>
                                        @foreach ($allMonths as $m)
                                            <th class="text-end">{{ \Carbon\Carbon::createFromFormat('Y-m', $m)->translatedFormat('M Y') }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($perfByCenter as $storeId => $months)
                                        <tr>
                                            <td><span class="badge bg-light-primary">{{ $storeNames[$storeId] ?? 'Store #'.$storeId }}</span></td>
                                            @foreach ($allMonths as $m)
                                                <td class="text-end">
                                                    @if (isset($months[$m]))
                                                        {{ number_format($months[$m], 2, ',', ' ') }} DH
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
