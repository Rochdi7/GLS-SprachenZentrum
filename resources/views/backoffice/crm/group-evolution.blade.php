@extends('layouts.main')

@section('title', 'CRM — Évolution par groupe')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Évolution par groupe')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice/crm-group-evolution.css') }}">
@endsection

@section('content')
<div class="ge-page">

    @include('backoffice.crm.partials._center')

    {{-- Filters --}}
    @php
        $selectedSet = array_flip($selectedClassIds ?? []);
        $totalAvailable = count($allGroups ?? []);
        $selectedCount  = count($selectedClassIds ?? []);
        $isAll          = $selectedCount === 0; // empty = show all
    @endphp

    <form method="GET" class="card mb-3" id="geFilterForm">
        <div class="card-body py-3">
            <div class="d-flex align-items-center mb-2">
                <h6 class="mb-0 small text-muted text-uppercase">
                    <i class="ti ti-filter me-1"></i> Filtres
                </h6>
                @if(!$isAll)
                    <span class="badge bg-light-primary text-primary ms-2">{{ $selectedCount }} / {{ $totalAvailable }} groupe(s)</span>
                @endif
            </div>
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label small mb-1 text-muted">Groupes</label>
                    {{-- Hidden field that the JS toggles below sync into. The
                         dropdown is purely visual; this is what's actually sent. --}}
                    <input type="hidden" name="classIds" id="geClassIdsInput" value="{{ implode(',', $selectedClassIds ?? []) }}">

                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <i class="ti ti-list-check me-1"></i>
                            @if($isAll)
                                Tous les groupes ({{ $totalAvailable }})
                            @else
                                {{ $selectedCount }} groupe(s) sélectionné(s)
                            @endif
                        </button>
                        <div class="dropdown-menu p-2 shadow-sm" style="min-width: 320px; max-height: 380px; overflow-y: auto;">
                            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                                <strong class="small text-muted">Choisir les groupes</strong>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-link btn-sm p-0 me-2" id="geSelectAll">Tous</button>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-danger" id="geSelectNone">Aucun</button>
                                </div>
                            </div>
                            <div class="mb-2 px-1">
                                <input type="text" class="form-control form-control-sm" id="geGroupFilter" placeholder="Rechercher…" autocomplete="off">
                            </div>
                            <div id="geGroupList">
                                @foreach($allGroups as $g)
                                    <label class="dropdown-item d-flex align-items-center gap-2 small py-1 ge-group-item" style="cursor:pointer;">
                                        <input type="checkbox"
                                               class="form-check-input ge-group-cb"
                                               value="{{ $g['class_id'] }}"
                                               {{ $isAll || isset($selectedSet[$g['class_id']]) ? 'checked' : '' }}>
                                        <span class="ge-group-name flex-grow-1">{{ $g['name'] }}</span>
                                        <span class="badge bg-light text-muted">{{ $g['actifs'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ti ti-search me-1"></i> Appliquer
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ url()->current() }}?refresh=1{{ !empty($selectedClassIds) ? '&classIds=' . implode(',', $selectedClassIds) : '' }}" class="btn btn-sm btn-outline-secondary" title="Recalculer (ignorer le cache)">
                        <i class="ti ti-refresh me-1"></i> Recharger
                    </a>
                </div>
            </div>
        </div>
    </form>

    @php
        $groups = $report['groups'] ?? [];
        $totals = $report['totals'] ?? ['debuts' => 0, 'ajouts' => 0, 'quittants' => 0, 'changements' => 0, 'actifs' => 0, 'groups' => 0];
        $rateLimited = !empty($report['diag']['rate_limited']);
    @endphp

    @if($rateLimited)
        <div class="alert alert-warning border-0 shadow-sm">
            <div class="d-flex align-items-start gap-2">
                <i class="ti ti-alert-triangle text-warning" style="font-size: 1.3rem; line-height: 1;"></i>
                <div>
                    <strong>API CRM temporairement limitée (HTTP 429)</strong>
                    <div class="small text-muted">
                        L'API Wimschool a appliqué une limite de débit sur le token actuel.
                        Les comptes Début/Ajouts/Quittant/Changement affichent <strong>0</strong>
                        parce que les allocations n'ont pas pu être récupérées —
                        ce n'est <em>pas</em> une absence de données.
                        Attendez 5–15 minutes puis cliquez <strong>Recharger</strong> pour réessayer.
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(empty($groups))
        <div class="alert alert-info border-0 shadow-sm">
            <i class="ti ti-info-circle me-1"></i>
            @if($rateLimited)
                Aucun groupe affiché — l'API CRM est limitée (HTTP 429). Voir l'avertissement ci-dessus.
            @elseif($crmCurrentStore)
                Aucun groupe trouvé pour ce centre côté API.
            @else
                Aucun groupe à afficher. Sélectionnez un centre pour charger ses groupes.
            @endif
            <span class="small text-muted d-block mt-1">
                (Les groupes ne sont pas filtrés par la plage de dates — la plage ne contrôle que les paiements analysés pour Début / Ajouts / Quittant / Changement.)
            </span>
        </div>
    @else

        <div class="row g-3">
            {{-- Detail table card --}}
            <div class="col-12">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="text-center text-primary ge-card-title mb-3">DÉTAILS PAR GROUPE</h5>
                        <div class="table-responsive" style="max-height: 460px;">
                            <table class="table table-sm ge-table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Groupe</th>
                                        <th class="text-center" style="color:#6f42c1;">Début</th>
                                        <th class="text-center text-success">Ajouts</th>
                                        <th class="text-center text-danger">Quittant</th>
                                        <th class="text-center" style="color:#fd7e14;">Changement</th>
                                        <th class="text-center text-primary">Actifs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groups as $i => $g)
                                        <tr class="ge-drill-row" style="cursor:pointer;"
                                            data-class-id="{{ $g['class_id'] }}"
                                            data-class-name="{{ $g['name'] }}"
                                            title="Cliquer pour voir les étudiants">
                                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                                            <td>
                                                {{ $g['name'] }}
                                                <i class="ph-duotone ph-eye ms-1 text-muted" style="font-size:.8rem;opacity:.5;"></i>
                                            </td>
                                            <td class="text-center ge-cell-debut">{{ $g['debuts'] ?? 0 }}</td>
                                            <td class="text-center ge-cell-ajout">{{ $g['ajouts'] }}</td>
                                            <td class="text-center ge-cell-quittant">{{ $g['quittants'] }}</td>
                                            <td class="text-center ge-cell-changement">{{ $g['changements'] }}</td>
                                            <td class="text-center ge-cell-actif">{{ $g['actifs'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="text-center">TOTAL</td>
                                        <td class="text-center ge-cell-debut">{{ $totals['debuts'] ?? 0 }}</td>
                                        <td class="text-center ge-cell-ajout">{{ $totals['ajouts'] }}</td>
                                        <td class="text-center ge-cell-quittant">{{ $totals['quittants'] }}</td>
                                        <td class="text-center ge-cell-changement">{{ $totals['changements'] }}</td>
                                        <td class="text-center ge-cell-actif">{{ $totals['actifs'] }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI cards row --}}
        <div class="row g-3 mt-1">
            <div class="col-6 col-md-4 col-xl">
                <div class="card shadow-sm ge-kpi-card h-100"><div class="card-body text-center">
                    <span class="ge-icon" style="background:#efe5fb; color:#6f42c1;"><i class="ti ti-flag-3"></i></span>
                    <div class="small fw-semibold mt-2" style="color:#6f42c1;">Total au démarrage<br>(Début)</div>
                    <div class="h2 mb-0 mt-1" style="color:#6f42c1;">{{ $totals['debuts'] ?? 0 }}</div>
                    <div class="ge-help">Élèves présents dès le 1er mois du groupe</div>
                </div></div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card shadow-sm ge-kpi-card h-100"><div class="card-body text-center">
                    <span class="ge-icon" style="background:#e3f2fd; color:#2196f3;"><i class="ti ti-users"></i></span>
                    <div class="small text-primary fw-semibold mt-2">Actifs actuellement<br>(Nombre actifs)</div>
                    <div class="h2 mb-0 mt-1 text-primary">{{ $totals['actifs'] }}</div>
                    <div class="ge-help">Élèves actifs dans la formation</div>
                </div></div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card shadow-sm ge-kpi-card h-100"><div class="card-body text-center">
                    <span class="ge-icon" style="background:#e8f5e9; color:#28a745;"><i class="ti ti-trending-up"></i></span>
                    <div class="small text-success fw-semibold mt-2">Total des ajouts<br>(Les ajouts)</div>
                    <div class="h2 mb-0 mt-1 text-success">{{ $totals['ajouts'] }}</div>
                    <div class="ge-help">Nouveaux élèves ajoutés</div>
                </div></div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card shadow-sm ge-kpi-card h-100"><div class="card-body text-center">
                    <span class="ge-icon" style="background:#fdecea; color:#dc3545;"><i class="ti ti-logout"></i></span>
                    <div class="small text-danger fw-semibold mt-2">Total des départs définitifs<br>(Quittant)</div>
                    <div class="h2 mb-0 mt-1 text-danger">{{ $totals['quittants'] }}</div>
                    <div class="ge-help">Élèves ayant quitté la formation</div>
                </div></div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card shadow-sm ge-kpi-card h-100"><div class="card-body text-center">
                    <span class="ge-icon" style="background:#fff4e5; color:#fd7e14;"><i class="ph-duotone ph-swap" style="font-size:1.7rem"></i></span>
                    <div class="small fw-semibold mt-2" style="color:#fd7e14;">Total des changements<br>de groupe (Changement)</div>
                    <div class="h2 mb-0 mt-1" style="color:#fd7e14;">{{ $totals['changements'] }}</div>
                    <div class="ge-help">Élèves ayant changé de groupe</div>
                </div></div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card shadow-sm ge-kpi-card h-100"><div class="card-body text-center">
                    <span class="ge-icon" style="background:#f3e5f5; color:#7b1fa2;"><i class="ti ti-school"></i></span>
                    <div class="small fw-semibold mt-2" style="color:#7b1fa2;">Nombre total de groupes</div>
                    <div class="h2 mb-0 mt-1" style="color:#7b1fa2;">{{ $totals['groups'] }}</div>
                    <div class="ge-help">Groupes analysés</div>
                </div></div>
            </div>
        </div>

        {{-- Diagnostics (hidden unless ?debug=1) — surface API shape issues
             so we can tell at a glance why all counts are zero. --}}
        @if(request()->boolean('debug') && !empty($report['diag']))
            @php $diag = $report['diag']; @endphp
            <div class="card mt-3 border-warning shadow-sm">
                <div class="card-body small">
                    <h6 class="mb-2 text-warning">
                        <i class="ti ti-bug me-1"></i> Diagnostic API (mode debug)
                    </h6>
                    <div class="row g-2">
                        <div class="col-6 col-md-3"><strong>Classes récupérées :</strong> {{ $diag['classes_fetched'] }}</div>
                        <div class="col-6 col-md-3"><strong>Classes avec START_DATE :</strong> {{ $diag['classes_with_start'] }}</div>
                        <div class="col-6 col-md-3"><strong>Allocations récupérées :</strong> {{ $diag['allocations_fetched'] }}</div>
                        <div class="col-6 col-md-3"><strong>Allocations avec CLASS_ID :</strong> {{ $diag['allocations_with_class'] }}</div>
                        <div class="col-6 col-md-3"><strong>Couples (étudiant, classe) :</strong> {{ $diag['student_class_pairs'] }}</div>
                        <div class="col-6 col-md-3"><strong>CLASS_IDs distincts :</strong> {{ count($diag['distinct_class_ids']) }}</div>
                        <div class="col-6 col-md-3"><strong>Classes avec END_DATE :</strong> {{ $diag['classes_with_end'] ?? 0 }}</div>
                        <div class="col-6 col-md-3"><strong>Bucket Début (étudiants) :</strong> {{ $diag['total_debuts_students'] ?? 0 }} / {{ $diag['total_debuts_keyed'] ?? 0 }} classes</div>
                        <div class="col-6 col-md-3"><strong>Bucket Ajout (étudiants) :</strong> {{ $diag['total_ajouts_students'] ?? 0 }} / {{ $diag['total_ajouts_keyed'] ?? 0 }} classes</div>
                    </div>
                    @if(!empty($diag['sample_start_months']) || !empty($diag['sample_first_months']) || !empty($diag['sample_end_dates']))
                        <div class="row g-2 mt-2">
                            <div class="col-12 col-md-4">
                                <strong>Échantillon START_DATE :</strong>
                                <pre class="small bg-light p-2 rounded mb-0" style="max-height:140px; overflow:auto;">{{ json_encode($diag['sample_start_months'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            <div class="col-12 col-md-4">
                                <strong>Échantillon END_DATE :</strong>
                                <pre class="small bg-light p-2 rounded mb-0" style="max-height:140px; overflow:auto;">{{ json_encode($diag['sample_end_dates'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            <div class="col-12 col-md-4">
                                <strong>Échantillon 1ers paiements :</strong>
                                <pre class="small bg-light p-2 rounded mb-0" style="max-height:140px; overflow:auto;">{{ json_encode($diag['sample_first_months'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif
                    @if(!empty($diag['distinct_service_types']))
                        <div class="mt-2">
                            <strong>Types de service distincts dans les allocations :</strong>
                            <div class="mt-1" style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>Service</th><th class="text-end">Occurrences</th></tr></thead>
                                    <tbody>
                                        @foreach($diag['distinct_service_types'] as $svc => $count)
                                            <tr>
                                                <td><code>{{ $svc }}</code></td>
                                                <td class="text-end">{{ $count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    <div class="mt-2 text-muted">
                        <strong>Comment lire :</strong>
                        Si <em>Allocations récupérées</em> = 0, l'API ne renvoie rien pour la plage choisie (vérifiez le token / le scope <code>payments:read</code>).
                        Si <em>Allocations avec CLASS_ID</em> = 0, les rows existent mais n'ont pas de <code>CLASS_ID</code> — impossible de les rattacher à un groupe.
                        Si <em>Classes avec START_DATE</em> ≪ <em>Classes récupérées</em>, la détection Début/Ajouts ne fonctionne que sur une minorité de groupes.
                        Les libellés affichés ci-dessus sont ceux comparés à la regex <code>inscription</code> — si le bon libellé n'apparaît jamais avec ce mot, la détection rate.
                    </div>
                </div>
            </div>
        @endif

    @endif
</div>
@endsection


{{-- Group drill modal --}}
<div class="modal fade" id="geDrillModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ph-duotone ph-users me-2 text-primary"></i>
                    <span id="geDrillTitle">Étudiants</span>
                    <span class="badge bg-secondary ms-2" id="geDrillCount"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="geDrillLoading" class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Chargement...</p>
                </div>
                <div id="geDrillContent" class="d-none">
                    <div class="px-3 pt-2 pb-1">
                        <input type="text" id="geDrillSearch" class="form-control form-control-sm" placeholder="Rechercher par nom...">
                    </div>
                    <div class="px-3 pb-2 d-flex gap-2 flex-wrap" id="geDrillBucketTabs">
                        <button type="button" class="btn btn-sm btn-primary ge-bucket-tab active" data-bucket="" data-status="">Tous</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary ge-bucket-tab" data-bucket="debut" data-status="" style="--bs-btn-color:#6f42c1;--bs-btn-border-color:#6f42c1;">Début</button>
                        <button type="button" class="btn btn-sm btn-outline-success ge-bucket-tab" data-bucket="ajout" data-status="">Ajouts</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary ge-bucket-tab" data-bucket="unpaid" data-status="">Non payé</button>
                        <button type="button" class="btn btn-sm btn-outline-danger ge-bucket-tab" data-bucket="" data-status="Annulé">Annulé</button>
                        <button type="button" class="btn btn-sm ge-bucket-tab" data-bucket="" data-status="Archive" style="color:#fd7e14;border:1px solid #fd7e14;background:transparent;">Archive</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Nom étudiant</th>
                                    <th>Statut</th>
                                    <th>Bucket</th>
                                    <th>1er paiement</th>
                                    <th>Date inscr.</th>
                                </tr>
                            </thead>
                            <tbody id="geDrillTbody"></tbody>
                        </table>
                    </div>
                </div>
                <div id="geDrillEmpty" class="d-none text-center py-5 text-muted">
                    <i class="ph-duotone ph-info fs-1"></i>
                    <p class="mt-2">Aucun étudiant trouvé pour ce groupe.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/backoffice/crm-group-evolution.js') }}" defer></script>
<script>
(function initGroupEvolutionDrill() {
    const drillUrl  = '{{ route("backoffice.crm.group-evolution.drill") }}';
    let drillModal   = null;
    let allRows      = [];
    let activeBucket = '';
    let activeStatus = '';

    document.querySelectorAll('.ge-drill-row').forEach(row => {
        row.addEventListener('click', function () {
            const classId   = this.dataset.classId;
            const className = this.dataset.className;

            document.getElementById('geDrillTitle').textContent = className;
            document.getElementById('geDrillCount').textContent = '';
            document.getElementById('geDrillSubtitle')?.remove();
            document.getElementById('geDrillLoading').classList.remove('d-none');
            document.getElementById('geDrillContent').classList.add('d-none');
            const emptyEl = document.getElementById('geDrillEmpty');
            emptyEl.innerHTML = '<i class="ph-duotone ph-info fs-1"></i><p class="mt-2">Aucun étudiant trouvé pour ce groupe.</p>';
            emptyEl.classList.add('d-none');
            document.getElementById('geDrillSearch').value = '';
            activeBucket = '';
            activeStatus = '';
            document.querySelectorAll('.ge-bucket-tab').forEach(b => b.classList.remove('active', 'btn-primary'));
            const allTab = document.querySelector('.ge-bucket-tab[data-bucket=""][data-status=""]');
            if (allTab) { allTab.classList.add('active', 'btn-primary'); }

            if (!drillModal) drillModal = new bootstrap.Modal(document.getElementById('geDrillModal'));
            drillModal.show();

            fetch(drillUrl + '?classId=' + classId, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(data => {
                    allRows = Array.isArray(data.rows) ? data.rows : [];
                    document.getElementById('geDrillLoading').classList.add('d-none');

                    // Show class start month as subtitle
                    if (data.class_start_ym) {
                        const sub = document.createElement('small');
                        sub.id = 'geDrillSubtitle';
                        sub.className = 'text-muted ms-2';
                        sub.textContent = 'Début groupe : ' + data.class_start_ym;
                        document.getElementById('geDrillTitle').after(sub);
                    }

                    if (allRows.length === 0) {
                        document.getElementById('geDrillCount').textContent = '0 étudiant(s)';
                        document.getElementById('geDrillEmpty').classList.remove('d-none');
                    } else {
                        document.getElementById('geDrillContent').classList.remove('d-none');
                        applyFilters();
                    }
                })
                .catch(err => {
                    document.getElementById('geDrillLoading').classList.add('d-none');
                    emptyEl.innerHTML = '<i class="ph-duotone ph-warning fs-1 text-danger"></i><p class="mt-2 text-danger">Erreur chargement: ' + err.message + '</p>';
                    emptyEl.classList.remove('d-none');
                });
        });
    });

    document.getElementById('geDrillSearch')?.addEventListener('input', applyFilters);

    document.querySelectorAll('.ge-bucket-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            activeBucket = this.dataset.bucket;
            activeStatus = this.dataset.status;
            document.querySelectorAll('.ge-bucket-tab').forEach(b => {
                b.classList.remove('active', 'btn-primary', 'btn-secondary', 'btn-success', 'btn-danger');
            });
            this.classList.add('active');
            applyFilters();
        });
    });

    function applyFilters() {
        const q = (document.getElementById('geDrillSearch')?.value || '').toLowerCase();
        const filtered = allRows.filter(r => {
            const matchName   = (r.student_name || '').toLowerCase().includes(q);
            const matchBucket = !activeBucket || r.payment_bucket === activeBucket;
            const matchStatus = !activeStatus || r.status === activeStatus;
            return matchName && matchBucket && matchStatus;
        });
        const countEl = document.getElementById('geDrillCount');
        const label = activeBucket ? bucketLabel(activeBucket) : (activeStatus || '');
        countEl.textContent = filtered.length + ' étudiant(s)' + (label ? ' · ' + label : '');
        renderRows(filtered);
    }

    function bucketLabel(b) {
        return { debut: 'Début', ajout: 'Ajouts', unpaid: 'Non payé' }[b] || b;
    }

    function bucketBadge(b) {
        const map = {
            debut:  '<span class="badge" style="background:#6f42c1;">Début</span>',
            ajout:  '<span class="badge bg-success">Ajouts</span>',
            unpaid: '<span class="badge bg-light text-muted border">Non payé</span>',
        };
        return map[b] || '<span class="text-muted small">—</span>';
    }

    function renderRows(rows) {
        const statusColors = {
            'Active':  'bg-success',
            'Annulé':  'bg-danger',
            'Archive': 'bg-secondary',
        };
        document.getElementById('geDrillTbody').innerHTML = rows.map((r, i) => {
            const badge = statusColors[r.status] || 'bg-light text-dark';
            return `<tr>
                <td class="text-muted">${i+1}</td>
                <td><strong>${r.student_name}</strong><br><small class="text-muted">#${r.student_id}</small></td>
                <td><span class="badge ${badge}">${r.status}</span></td>
                <td>${bucketBadge(r.payment_bucket)}</td>
                <td><small class="text-muted">${r.first_paid_month || '—'}</small></td>
                <td><small>${r.registered_at}</small></td>
            </tr>`;
        }).join('');
    }
})();

</script>
@endsection
