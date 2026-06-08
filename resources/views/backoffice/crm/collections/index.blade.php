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
        ['type'=>'overdue30', 'label'=>'En retard &gt; 30 jours', 'value'=>$kpis['overdue30']??null, 'color'=>'',          'style'=>'border-left:4px solid #fd7e14; color:#fd7e14'],
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
            <div class="card kpi-card h-100" style="{{ $card['style'] ?: 'border-left:4px solid var(--bs-'.$card['color'].')' }}">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">{!! $card['label'] !!}</div>
                        <h3 @if($card['style']) style="{{ $card['style'] }}" @else class="text-{{ $card['color'] }}" @endif>
                            {{ $card['value'] !== null ? number_format($card['value'], 2, ',', ' ') . ' DH' : '—' }}
                        </h3>
                    </div>
                    <span class="drill-btn" @if($card['style']) style="{{ $card['style'] }}" @else class="text-{{ $card['color'] }}" @endif
                          data-type="{{ $card['type'] }}" data-label="{!! $card['label'] !!}" title="Voir les dossiers">
                        <i class="ph-duotone ph-eye fs-4"></i>
                    </span>
                </div>
            </div>
        </div>
        @endforeach
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
                            @php $agingTypes = ['aging_0','aging_8','aging_31','aging_61','aging_90']; @endphp
                            @foreach ($agingBuckets as $i => $bucket)
                                @php
                                    $colorClass  = $bucket['color'] === 'orange' ? '' : 'border-' . $bucket['color'];
                                    $textClass   = $bucket['color'] === 'orange' ? 'style="color:#fd7e14"' : 'class="text-' . $bucket['color'] . '"';
                                    $borderStyle = $bucket['color'] === 'orange' ? 'border-left:4px solid #fd7e14;' : '';
                                    $drillType   = $agingTypes[$i] ?? 'aging_0';
                                @endphp
                                <div class="col">
                                    <div class="card aging-card {{ $colorClass }}" style="{{ $borderStyle }}">
                                        <div class="card-body text-center py-3 position-relative">
                                            <span class="drill-btn position-absolute top-0 end-0 m-2 {!! $bucket['color'] !== 'orange' ? 'text-'.$bucket['color'] : '' !!}"
                                                  {!! $bucket['color'] === 'orange' ? 'style="color:#fd7e14"' : '' !!}
                                                  data-type="{{ $drillType }}" data-label="{{ $bucket['label'] }}" title="Voir les dossiers">
                                                <i class="ph-duotone ph-eye"></i>
                                            </span>
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
        <div class="col-xl-7 col-12">
            <div class="card table-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="ph-duotone ph-users me-2"></i>
                        Top débiteurs
                        <span class="badge bg-light-danger text-danger ms-2" id="debtorsBadge">{{ count($topDebtors) }}</span>
                    </h5>
                    <input type="text" id="debtorsSearch" class="form-control form-control-sm ms-2" style="max-width:180px" placeholder="Rechercher…">
                </div>
                <div class="card-body p-0">
                    @if (empty($topDebtors))
                        <p class="text-muted text-center py-4 mb-0">Aucune donnée disponible.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0" id="debtorsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:36px">#</th>
                                        <th>Etudiant</th>
                                        <th style="width:120px">Centre</th>
                                        <th class="text-end" style="width:120px">Montant dû</th>
                                        <th class="text-end" style="width:70px">Retard</th>
                                    </tr>
                                </thead>
                                <tbody id="debtorsTbody">
                                    @foreach ($topDebtors as $i => $debtor)
                                        <tr>
                                            <td class="text-muted">{{ $i + 1 }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $debtor['student_name'] }}</div>
                                                <small class="text-muted">#{{ $debtor['student_id'] }}</small>
                                            </td>
                                            <td><span class="badge bg-light-primary text-primary">{{ $debtor['store_name'] }}</span></td>
                                            <td class="text-end fw-semibold text-danger">
                                                {{ number_format($debtor['total_owed'], 0, ',', ' ') }} DH
                                            </td>
                                            <td class="text-end">
                                                @php
                                                    $bc = $debtor['overdue_days'] > 90 ? 'bg-dark'
                                                        : ($debtor['overdue_days'] > 60 ? 'bg-danger'
                                                        : ($debtor['overdue_days'] > 30 ? 'bg-warning text-dark'
                                                        : ($debtor['overdue_days'] > 0  ? 'bg-secondary' : 'bg-light-success text-success')));
                                                @endphp
                                                <span class="badge {{ $bc }}">
                                                    {{ $debtor['overdue_days'] > 0 ? $debtor['overdue_days'].' j' : 'À jour' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="tbl-pagination" id="debtorsPagination">
                            <span class="page-info" id="debtorsInfo"></span>
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary" id="debtorsPrev"><i class="ti ti-chevron-left"></i></button>
                                <span class="btn btn-outline-secondary disabled" id="debtorsPages" style="pointer-events:none;min-width:80px;text-align:center;font-size:.78rem"></span>
                                <button class="btn btn-outline-secondary" id="debtorsNext"><i class="ti ti-chevron-right"></i></button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Upcoming Dues --}}
        <div class="col-xl-5 col-12">
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();

            const drillUrl  = '{{ route("backoffice.crm.collections.drill") }}';
            const storeId   = '{{ $strStoreId ?? "" }}';
            let allRows     = [];

            document.querySelectorAll('.drill-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const type  = this.dataset.type;
                    const label = this.dataset.label;

                    document.getElementById('drillModalTitle').textContent = label;
                    document.getElementById('drillModalCount').textContent = '';
                    document.getElementById('drillModalTotal').textContent = '';
                    document.getElementById('drillLoading').classList.remove('d-none');
                    document.getElementById('drillContent').classList.add('d-none');
                    document.getElementById('drillEmpty').classList.add('d-none');
                    document.getElementById('drillSearch').value = '';

                    new bootstrap.Modal(document.getElementById('drillModal')).show();

                    const params = new URLSearchParams({ type });
                    if (storeId) params.set('strStoreId', storeId);

                    fetch(drillUrl + '?' + params)
                        .then(r => r.json())
                        .then(data => {
                            allRows = data.rows;
                            document.getElementById('drillLoading').classList.add('d-none');
                            document.getElementById('drillModalCount').textContent = data.count + ' dossier(s)';
                            document.getElementById('drillModalTotal').textContent = new Intl.NumberFormat('fr-MA').format(data.total) + ' DH';

                            if (data.count === 0) {
                                document.getElementById('drillEmpty').classList.remove('d-none');
                            } else {
                                document.getElementById('drillContent').classList.remove('d-none');
                                renderRows(allRows);
                            }
                        });
                });
            });

            // ── Table pagination helper ────────────────────────────────────────
            function initTablePagination(tbodyId, searchId, prevId, nextId, pagesId, infoId, pageSize) {
                const tbody  = document.getElementById(tbodyId);
                if (!tbody) return;
                const allRows = Array.from(tbody.querySelectorAll('tr'));
                let filtered  = allRows;
                let page      = 1;

                function pages() { return Math.max(1, Math.ceil(filtered.length / pageSize)); }

                function render() {
                    const start = (page - 1) * pageSize;
                    allRows.forEach(r => r.style.display = 'none');
                    filtered.slice(start, start + pageSize).forEach(r => r.style.display = '');
                    document.getElementById(pagesId).textContent = `Page ${page} / ${pages()}`;
                    document.getElementById(infoId).textContent  = `${filtered.length} résultat(s)`;
                    document.getElementById(prevId).disabled = page === 1;
                    document.getElementById(nextId).disabled = page === pages();
                }

                document.getElementById(prevId).addEventListener('click', () => { if (page > 1) { page--; render(); } });
                document.getElementById(nextId).addEventListener('click', () => { if (page < pages()) { page++; render(); } });

                document.getElementById(searchId).addEventListener('input', function () {
                    const q = this.value.toLowerCase();
                    filtered = allRows.filter(r => r.textContent.toLowerCase().includes(q));
                    page = 1;
                    render();
                });

                render();
            }

            initTablePagination('debtorsTbody', 'debtorsSearch', 'debtorsPrev', 'debtorsNext', 'debtorsPages', 'debtorsInfo', 10);
            initTablePagination('duesTbody',    'duesSearch',    'duesPrev',    'duesNext',    'duesPages',    'duesInfo',    10);
            // ──────────────────────────────────────────────────────────────────

            document.getElementById('drillSearch').addEventListener('input', function () {
                const q = this.value.toLowerCase();
                renderRows(allRows.filter(r =>
                    (r.student_name || '').toLowerCase().includes(q) ||
                    (r.store_name   || '').toLowerCase().includes(q)
                ));
            });

            function renderRows(rows) {
                const tbody = document.getElementById('drillTbody');
                tbody.innerHTML = rows.map((r, i) => {
                    const overdueClass = r.overdue_days > 90 ? 'bg-dark'
                        : r.overdue_days > 60 ? 'bg-danger'
                        : r.overdue_days > 30 ? 'bg-warning text-dark'
                        : r.overdue_days > 0  ? 'bg-secondary'
                        : 'bg-light-success text-success';
                    const overdueLabel = r.overdue_days > 0 ? r.overdue_days + ' j' : 'À jour';
                    const dueDate = r.due_date
                        ? new Date(r.due_date).toLocaleDateString('fr-MA', {day:'2-digit',month:'2-digit',year:'numeric'})
                        : '—';
                    return `<tr>
                        <td class="text-muted">${i+1}</td>
                        <td><strong>${r.student_name}</strong><br><small class="text-muted">#${r.student_id}</small></td>
                        <td><span class="badge bg-light-primary">${r.store_name}</span></td>
                        <td><small>${dueDate}</small></td>
                        <td><span class="badge ${overdueClass}">${overdueLabel}</span></td>
                        <td class="text-end fw-semibold text-danger">${new Intl.NumberFormat('fr-MA').format(r.amount)} DH</td>
                    </tr>`;
                }).join('');
            }
        });
    </script>
@endsection
