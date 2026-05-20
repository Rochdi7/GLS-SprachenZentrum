@extends('layouts.main')

@section('title', 'CRM — Évolution par groupe')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Évolution par groupe')

@section('css')
<style>
    .ge-page h1 { font-size: 1.5rem; font-weight: 600; }
    .ge-card-title { font-weight: 700; letter-spacing: .02em; }
    .ge-kpi-card .ge-icon {
        width: 56px; height: 56px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.7rem;
    }
    .ge-table th { font-size: .78rem; text-transform: uppercase; letter-spacing: .03em; }
    .ge-table td { font-size: .85rem; vertical-align: middle; }
    .ge-table .ge-cell-ajout      { color: #28a745; font-weight: 600; }
    .ge-table .ge-cell-quittant   { color: #dc3545; font-weight: 600; }
    .ge-table .ge-cell-changement { color: #fd7e14; font-weight: 600; }
    .ge-table .ge-cell-actif      { color: #2196f3; font-weight: 600; }
    .ge-table tfoot td { font-weight: 700; background: #fafbfc; }
    .ge-legend {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .8rem; color: #495057;
    }
    .ge-legend .ge-dot {
        width: 12px; height: 12px; border-radius: 2px; display: inline-block;
    }
    .ge-help { font-size: .8rem; color: #6c757d; }
</style>
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
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1 text-muted">Date de début</label>
                    <input type="date" name="startDate" value="{{ $startDate }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1 text-muted">Date de fin</label>
                    <input type="date" name="endDate" value="{{ $endDate }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-4">
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
                    <a href="{{ url()->current() }}?refresh=1&startDate={{ $startDate }}&endDate={{ $endDate }}{{ !empty($selectedClassIds) ? '&classIds=' . implode(',', $selectedClassIds) : '' }}" class="btn btn-sm btn-outline-secondary" title="Recalculer (ignorer le cache)">
                        <i class="ti ti-refresh me-1"></i> Recharger
                    </a>
                </div>
            </div>
        </div>
    </form>

    @php
        $groups = $report['groups'] ?? [];
        $totals = $report['totals'] ?? ['ajouts' => 0, 'quittants' => 0, 'changements' => 0, 'actifs' => 0, 'groups' => 0];
    @endphp

    @if(empty($groups))
        <div class="alert alert-info border-0 shadow-sm">
            <i class="ti ti-info-circle me-1"></i>
            Aucun groupe trouvé pour ce centre dans la plage de dates sélectionnée.
        </div>
    @else
        <div class="row g-3">
            {{-- Chart card --}}
            <div class="col-12 col-xl-7">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="text-center text-primary ge-card-title mb-3">ÉVOLUTION PAR GROUPE</h5>
                        <div class="mb-2 d-flex flex-wrap gap-3 justify-content-center">
                            <span class="ge-legend"><span class="ge-dot" style="background:#28a745;"></span> Ajouts (Les ajouts)</span>
                            <span class="ge-legend"><span class="ge-dot" style="background:#dc3545;"></span> Départs définitifs (Quittant)</span>
                            <span class="ge-legend"><span class="ge-dot" style="background:#fd7e14;"></span> Changements de groupe</span>
                            <span class="ge-legend"><span class="ge-dot" style="background:#2196f3;"></span> Actifs actuellement</span>
                        </div>
                        <div id="groupEvolutionChart" style="min-height: 420px;"></div>
                    </div>
                </div>
            </div>

            {{-- Detail table card --}}
            <div class="col-12 col-xl-5">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="text-center text-primary ge-card-title mb-3">DÉTAILS PAR GROUPE</h5>
                        <div class="table-responsive" style="max-height: 460px;">
                            <table class="table table-sm ge-table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Groupe</th>
                                        <th class="text-center text-success">Ajouts</th>
                                        <th class="text-center text-danger">Quittant</th>
                                        <th class="text-center" style="color:#fd7e14;">Changement</th>
                                        <th class="text-center text-primary">Actifs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groups as $i => $g)
                                        <tr>
                                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                                            <td>{{ $g['name'] }}</td>
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
                    <span class="ge-icon" style="background:#fff4e5; color:#fd7e14;"><i class="ti ti-arrows-exchange"></i></span>
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

        {{-- Methodology note --}}
        <div class="alert alert-light border mt-3 small">
            <i class="ti ti-info-circle me-1 text-primary"></i>
            <strong>Méthodologie :</strong>
            <ul class="mb-0 mt-1 ps-3">
                <li><strong style="color:#28a745;">Ajouts</strong> — étudiants ayant payé leur <em>inscription</em> pour ce groupe dans la plage de dates.</li>
                <li><strong style="color:#dc3545;">Quittant</strong> — étudiants qui ont payé un mois mais n'ont pas payé le mois suivant (1 mois manqué), et ne paient aucun autre groupe.</li>
                <li><strong style="color:#fd7e14;">Changement</strong> — étudiants ayant payé 2 groupes différents dans une fenêtre de {{ \App\Services\Crm\Stats\GroupEvolutionService::CHANGEMENT_WINDOW_DAYS }} jours. Le départ est crédité au groupe qu'ils ont quitté.</li>
                <li><strong style="color:#2196f3;">Actifs</strong> — compteur courant du CRM (<code>CLASS_COUNT_STUDENTS_ACTIVE</code>), pas une inférence.</li>
            </ul>
        </div>
    @endif
</div>
@endsection

@php
    // Pre-shape the chart payload so the Blade compiler doesn't have to parse
    // a multi-line arrow function inside @json() — that combo trips its tokenizer.
    $chartGroups = array_map(fn ($g) => [
        'name'        => $g['name'],
        'ajouts'      => $g['ajouts'],
        'quittants'   => $g['quittants'],
        'changements' => $g['changements'],
        'actifs'      => $g['actifs'],
    ], $groups);
@endphp

@section('scripts')
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const groups = @json($chartGroups);

    const el = document.getElementById('groupEvolutionChart');
    if (!el || typeof ApexCharts === 'undefined' || groups.length === 0) return;

    new ApexCharts(el, {
        chart: { type: 'bar', height: 420, toolbar: { show: false }, animations: { speed: 350 } },
        plotOptions: { bar: { columnWidth: '70%', borderRadius: 3, dataLabels: { position: 'top' } } },
        dataLabels: {
            enabled: true, offsetY: -18,
            style: { fontSize: '10px', colors: ['#495057'], fontWeight: 600 },
            formatter: v => v > 0 ? v : '',
        },
        stroke: { show: true, width: 1, colors: ['transparent'] },
        series: [
            { name: 'Ajouts',      data: groups.map(g => g.ajouts) },
            { name: 'Quittant',    data: groups.map(g => g.quittants) },
            { name: 'Changement',  data: groups.map(g => g.changements) },
            { name: 'Actifs',      data: groups.map(g => g.actifs) },
        ],
        colors: ['#28a745', '#dc3545', '#fd7e14', '#2196f3'],
        xaxis: {
            categories: groups.map(g => g.name),
            labels: { rotate: -35, style: { fontSize: '11px' } },
        },
        yaxis: { title: { text: "Nombre d'élèves" }, decimalsInFloat: 0 },
        legend: { show: false }, // legend rendered manually above the chart
        grid: { borderColor: '#eef0f3' },
        tooltip: { y: { formatter: v => v + ' élève(s)' } },
    }).render();
});

// ─────────────────── Group multi-select behaviour ───────────────────
document.addEventListener('DOMContentLoaded', function () {
    const hidden  = document.getElementById('geClassIdsInput');
    const allCbs  = () => Array.from(document.querySelectorAll('.ge-group-cb'));
    const list    = document.getElementById('geGroupList');
    const search  = document.getElementById('geGroupFilter');
    const btnAll  = document.getElementById('geSelectAll');
    const btnNone = document.getElementById('geSelectNone');
    if (!hidden || !list) return;

    // Sync the hidden field. Empty = "show all" (server-side default).
    // If every box is ticked we also store empty to keep URLs short.
    const sync = () => {
        const boxes = allCbs();
        const checked = boxes.filter(b => b.checked).map(b => b.value);
        hidden.value = (checked.length === 0 || checked.length === boxes.length)
            ? ''
            : checked.join(',');
    };

    list.addEventListener('change', e => {
        if (e.target.classList.contains('ge-group-cb')) sync();
    });

    btnAll?.addEventListener('click',  () => { allCbs().forEach(b => b.checked = true);  sync(); });
    btnNone?.addEventListener('click', () => { allCbs().forEach(b => b.checked = false); sync(); });

    // Live search filter inside the dropdown.
    search?.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        document.querySelectorAll('.ge-group-item').forEach(item => {
            const name = item.querySelector('.ge-group-name')?.textContent.toLowerCase() ?? '';
            item.style.display = (!q || name.includes(q)) ? '' : 'none';
        });
    });

    sync();
});
</script>
@endsection
