@extends('layouts.main')

@section('title', 'Paiement Professeurs — Tableau de bord')
@section('breadcrumb-item', 'Paiement Professeurs')
@section('breadcrumb-item-active', 'Tableau de bord')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .pc-cal-nav-btn { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; }
        .pc-cal-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 6px; }
        .pc-cal-dow { text-align: center; font-size: 0.72rem; font-weight: 700; letter-spacing: .03em; color: #8a94a6; text-transform: uppercase; padding-bottom: 4px; }
        .pc-cal-day {
            min-height: 92px; border-radius: 10px; border: 1px solid #eef0f4; background: #fff;
            padding: 6px 6px 8px; cursor: pointer; transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
            display: flex; flex-direction: column; gap: 4px;
        }
        .pc-cal-day:hover { box-shadow: 0 4px 14px rgba(20, 20, 40, .08); transform: translateY(-1px); border-color: #d8dcE4; }
        .pc-cal-day.pc-empty { background: transparent; border: none; cursor: default; box-shadow: none; }
        .pc-cal-day.pc-empty:hover { transform: none; box-shadow: none; }
        .pc-cal-day-num { font-size: 0.8rem; font-weight: 600; color: #4a5468; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
        .pc-cal-day.pc-today { border-color: #6f42c1; background: #f7f4ff; }
        .pc-cal-day.pc-today .pc-cal-day-num { background: #6f42c1; color: #fff; }
        .pc-cal-chip { font-size: 0.66rem; font-weight: 600; padding: 2px 6px; border-radius: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pc-cal-chip.st-draft { background: #eceef2; color: #5a6472; }
        .pc-cal-chip.st-validated { background: #dbeeff; color: #1367c6; }
        .pc-cal-chip.st-paid { background: #dcf6e6; color: #16824a; }
        .pc-cal-chip.st-locked { background: #2b2f38; color: #fff; }
        .pc-cal-more { font-size: 0.66rem; color: #8a94a6; font-weight: 600; }
        .pc-cal-day-total { margin-top: auto; font-size: 0.68rem; font-weight: 700; color: #16824a; }
        .pc-cal-legend span { display: inline-flex; align-items: center; gap: 5px; font-size: 0.75rem; color: #6b7280; }
        .pc-cal-legend .dot { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
        @media (max-width: 767px) {
            .pc-cal-day { min-height: 64px; }
            .pc-cal-chip, .pc-cal-day-total { display: none; }
        }
    </style>
@endsection

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Paiement Professeurs</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    @php
        $totalGroups   = $groups->count();
        $totalPayments = $groups->sum(fn($g) => (float) ($g->latestPresenceImport?->paymentSummary?->total_payment ?? 0));
        $totalStudents = $groups->sum(fn($g) => (int) ($g->latestPresenceImport?->paymentSummary?->total_students ?? 0));
        $approved      = $groups->filter(fn($g) => $g->latestPresenceImport?->paymentSummary?->isApproved())->count();
        $pending       = $groups->filter(fn($g) => $g->latestPresenceImport?->paymentSummary && !$g->latestPresenceImport?->paymentSummary?->isApproved())->count();
        $noImport      = $groups->filter(fn($g) => !$g->latestPresenceImport?->paymentSummary)->count();

        $lastImport = $groups->pluck('latestPresenceImport')->filter()->sortByDesc('created_at')->first();

        // Chart data
        $chartLabels   = $groups->filter(fn($g) => $g->latestPresenceImport?->paymentSummary)->map(fn($g) => $g->latestPresenceImport->crm_teacher_name ?? $g->teacher?->name ?? $g->name)->values();
        $chartAmounts  = $groups->filter(fn($g) => $g->latestPresenceImport?->paymentSummary)->map(fn($g) => (float) ($g->latestPresenceImport->paymentSummary->total_payment ?? 0))->values();
        $chartStudents = $groups->filter(fn($g) => $g->latestPresenceImport?->paymentSummary)->map(fn($g) => (int) ($g->latestPresenceImport->paymentSummary->total_students ?? 0))->values();
    @endphp

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="ph-duotone ph-chalkboard-teacher text-primary me-2"></i>Paiement Professeurs</h4>
            <small class="text-muted">Suivi des paiements par groupe et professeur</small>
        </div>
        <a href="{{ route('backoffice.payroll.crm.legacy.import.create-modes') }}" class="btn btn-success">
            <i class="ph-duotone ph-plus-circle me-1"></i> Ajouter un paiement
        </a>
    </div>

    {{-- ── KPI Cards ────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="ph-duotone ph-chalkboard-teacher text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Professeurs</div>
                            <div class="h3 mb-0 fw-bold">{{ $totalGroups }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="ph-duotone ph-currency-circle-dollar text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total paiements</div>
                            <div class="h3 mb-0 fw-bold text-success">{{ number_format($totalPayments, 0, ',', ' ') }} <small class="fs-6">DH</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="ph-duotone ph-student text-info fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Étudiants actifs</div>
                            <div class="h3 mb-0 fw-bold">{{ $totalStudents }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="ph-duotone ph-calendar-check text-warning fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Dernier paiement</div>
                            <div class="h5 mb-0 fw-bold">{{ $lastImport ? $lastImport->created_at->format('d/m/Y') : '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Charts Row ───────────────────────────────────────────────────── --}}
    @if($totalGroups > 0)
    <div class="row g-3 mb-4">
        {{-- Bar chart: paiement par prof --}}
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="mb-0"><i class="ph-duotone ph-chart-bar text-primary me-2"></i>Paiement par professeur (DH)</h6>
                </div>
                <div class="card-body">
                    <div id="paymentBarChart" style="min-height:280px"></div>
                </div>
            </div>
        </div>

        {{-- Donut: statut paiements --}}
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="mb-0"><i class="ph-duotone ph-chart-donut text-primary me-2"></i>Statut des paiements</h6>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div id="statusDonutChart" style="min-height:220px;width:100%"></div>
                    <div class="d-flex gap-3 mt-2 flex-wrap justify-content-center">
                        <span class="d-flex align-items-center gap-1 small"><span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#28a745"></span> Approuvé ({{ $approved }})</span>
                        <span class="d-flex align-items-center gap-1 small"><span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#ffc107"></span> En attente ({{ $pending }})</span>
                        <span class="d-flex align-items-center gap-1 small"><span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#dee2e6"></span> Sans import ({{ $noImport }})</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Calendrier des paiements ─────────────────────────────────────── --}}
    {{-- <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0"><i class="ph-duotone ph-calendar-blank text-primary me-2"></i>Historique des paiements — Calendrier</h6>
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="pc-cal-prev" class="btn btn-light btn-sm pc-cal-nav-btn"><i class="ph-duotone ph-caret-left"></i></button>
                <div id="pc-cal-label" class="fw-semibold" style="min-width:150px;text-align:center"></div>
                <button type="button" id="pc-cal-next" class="btn btn-light btn-sm pc-cal-nav-btn"><i class="ph-duotone ph-caret-right"></i></button>
                <button type="button" id="pc-cal-today" class="btn btn-outline-secondary btn-sm ms-1">Aujourd'hui</button>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="pc-cal-grid mb-2">
                <div class="pc-cal-dow">Lun</div>
                <div class="pc-cal-dow">Mar</div>
                <div class="pc-cal-dow">Mer</div>
                <div class="pc-cal-dow">Jeu</div>
                <div class="pc-cal-dow">Ven</div>
                <div class="pc-cal-dow">Sam</div>
                <div class="pc-cal-dow">Dim</div>
            </div>
            <div id="pc-cal-grid" class="pc-cal-grid"></div>
            <div class="pc-cal-legend d-flex flex-wrap gap-3 mt-3">
                <span><span class="dot" style="background:#5a6472"></span> Brouillon</span>
                <span><span class="dot" style="background:#1367c6"></span> Validé</span>
                <span><span class="dot" style="background:#16824a"></span> Payé</span>
                <span><span class="dot" style="background:#2b2f38"></span> Verrouillé</span>
            </div>
        </div>
    </div> --}}

    {{-- ── Groupes ──────────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="ph-duotone ph-list text-primary me-2"></i>Groupes — Paiement Professeurs</h6>
            <select id="site-filter" class="form-select form-select-sm" style="width:200px">
                <option value="">— Tous les centres —</option>
                @foreach(\App\Models\Site::orderBy('name')->get() as $site)
                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="card-body pt-2">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="pc-dt-simple">
                    <thead class="table-light">
                        <tr>
                            <th>Groupe</th>
                            <th>Professeur</th>
                            <th>Niveau</th>
                            <th>CRM Class ID</th>
                            <th>Taux/Étudiant</th>
                            <th>Dernier paiement</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
                            @php
                                $latest  = $group->latestPresenceImport;
                                $summary = $latest?->paymentSummary;
                                $rate    = $latest?->getEffectivePaymentPerStudent();
                            @endphp
                            <tr data-site-id="{{ $group->site_id }}">
                                <td>
                                    <strong>{{ $group->name }}</strong>
                                    @if($group->time_range)
                                        <br><small class="text-muted">{{ $group->time_range }}</small>
                                    @endif
                                </td>
                                <td>{{ $group->teacher?->name ?? $latest?->crm_teacher_name ?? '—' }}</td>
                                <td><span class="badge bg-light-primary text-primary">{{ $group->level }}</span></td>
                                <td>
                                    @if ($group->crm_class_id)
                                        <span class="badge bg-light-success text-success">{{ $group->crm_class_id }}</span>
                                    @else
                                        <span class="badge bg-light-warning text-warning">Non configuré</span>
                                    @endif
                                </td>
                                <td>{{ $rate ? number_format($rate, 2) . ' DH' : '—' }}</td>
                                <td>
                                    @if ($summary)
                                        <strong>{{ number_format($summary->total_payment, 2) }} DH</strong>
                                        <br><small class="text-muted">{{ $summary->total_students }} étudiants actifs</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($summary?->isApproved())
                                        <span class="badge bg-success">Approuvé</span>
                                    @elseif ($summary)
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    @else
                                        <span class="badge bg-light text-muted">Sans import</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('backoffice.payroll.crm.legacy.group.imports', $group) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Historique">
                                            <i class="ph-duotone ph-clock-counter-clockwise"></i>
                                        </a>
                                        @if ($latest && $latest->is_crm_api)
                                            <a href="{{ route('backoffice.payroll.crm.legacy.import.show', ['group' => $group->id, 'import' => $latest->id]) }}"
                                               class="btn btn-sm btn-outline-primary" title="Voir dernier paiement">
                                                <i class="ph-duotone ph-eye"></i>
                                            </a>
                                        @endif
                                        @role('Super Admin')
                                            @if ($latest)
                                                <form method="POST"
                                                      action="{{ route('backoffice.payroll.crm.legacy.import.destroy', ['group' => $group->id, 'import' => $latest->id]) }}"
                                                      onsubmit="return confirm('Supprimer le dernier paiement (v{{ $latest->version }}) ? Cette action est irréversible.')"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer le dernier paiement">
                                                        <i class="ph-duotone ph-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endrole
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="ph-duotone ph-folder-open fs-1 d-block mb-2"></i>
                                    Aucun groupe lié pour le moment.
                                    <br><a href="{{ route('backoffice.payroll.crm.legacy.import.create-modes') }}" class="btn btn-success btn-sm mt-2">Ajouter un paiement</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script type="application/json" id="payroll-crm-data">
{
    "labels":   @json($chartLabels),
    "amounts":  @json($chartAmounts),
    "students": @json($chartStudents),
    "approved": {{ $approved }},
    "pending":  {{ $pending }},
    "noImport": {{ $noImport }}
}
</script>
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script type="module">
    import { DataTable } from "/build/js/plugins/module.js";
    DataTable.ext.search.push(function (settings, data, dataIndex, rowData, counter) {
        if (settings.nTable.id !== 'pc-dt-simple') return true;
        const siteId = document.getElementById('site-filter')?.value ?? '';
        if (!siteId) return true;
        const row = settings.nTable.tBodies[0].rows[counter];
        return row && row.dataset.siteId === siteId;
    });
    window.dt = new DataTable('#pc-dt-simple');
</script>
<script src="{{ URL::asset('assets/js/backoffice/payroll-crm-dashboard.js') }}"></script>
<script>
(function () {
    const EVENTS_URL = '{{ route("backoffice.payroll.crm.legacy.calendar-events") }}';
    const DAY_URL     = '{{ route("backoffice.payroll.crm.legacy.day") }}';
    const MONTH_NAMES = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    const grid  = document.getElementById('pc-cal-grid');
    const label = document.getElementById('pc-cal-label');
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let cursor = new Date(today.getFullYear(), today.getMonth(), 1);

    function fmt(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function statusClass(status) {
        return ({ draft: 'st-draft', validated: 'st-validated', paid: 'st-paid', locked: 'st-locked' })[status] || 'st-draft';
    }

    async function render() {
        label.textContent = MONTH_NAMES[cursor.getMonth()] + ' ' + cursor.getFullYear();
        grid.innerHTML = '<div class="text-center text-muted py-4" style="grid-column:1/-1">Chargement…</div>';

        const monthStart = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
        const monthEnd   = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0);

        // Grid always starts Monday and ends Sunday, covering the full month.
        const gridStart = new Date(monthStart);
        gridStart.setDate(gridStart.getDate() - ((gridStart.getDay() + 6) % 7));
        const gridEnd = new Date(monthEnd);
        gridEnd.setDate(gridEnd.getDate() + (7 - ((gridEnd.getDay() + 6) % 7) - 1));

        let events = [];
        try {
            const res = await fetch(`${EVENTS_URL}?start=${fmt(gridStart)}&end=${fmt(gridEnd)}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            events = res.ok ? await res.json() : [];
        } catch (e) {
            events = [];
        }

        const byDate = {};
        events.forEach((ev) => {
            (byDate[ev.date] ||= []).push(ev);
        });

        const cells = [];
        for (let d = new Date(gridStart); d <= gridEnd; d.setDate(d.getDate() + 1)) {
            const dateStr   = fmt(d);
            const inMonth   = d.getMonth() === cursor.getMonth();
            const isToday   = d.getTime() === today.getTime();
            const dayEvents = byDate[dateStr] || [];
            const total     = dayEvents.reduce((s, e) => s + (e.amount || 0), 0);
            const visible   = dayEvents.slice(0, 2);
            const extra     = dayEvents.length - visible.length;

            const chips = visible.map((e) =>
                `<span class="pc-cal-chip ${statusClass(e.status)}" title="${e.group_name} — ${e.status_label}">${e.group_name}</span>`
            ).join('');

            cells.push(`
                <div class="pc-cal-day ${isToday ? 'pc-today' : ''}" style="${inMonth ? '' : 'opacity:.4'}" data-date="${dateStr}">
                    <div class="pc-cal-day-num">${d.getDate()}</div>
                    ${chips}
                    ${extra > 0 ? `<div class="pc-cal-more">+${extra} autre(s)</div>` : ''}
                    ${total > 0 ? `<div class="pc-cal-day-total">${total.toLocaleString('fr-FR')} DH</div>` : ''}
                </div>
            `);
        }

        grid.innerHTML = cells.join('');

        grid.querySelectorAll('.pc-cal-day').forEach((cell) => {
            cell.addEventListener('click', () => {
                window.location.href = `${DAY_URL}?date=${cell.dataset.date}`;
            });
        });
    }

    document.getElementById('pc-cal-prev').addEventListener('click', () => {
        cursor = new Date(cursor.getFullYear(), cursor.getMonth() - 1, 1);
        render();
    });
    document.getElementById('pc-cal-next').addEventListener('click', () => {
        cursor = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 1);
        render();
    });
    document.getElementById('pc-cal-today').addEventListener('click', () => {
        cursor = new Date(today.getFullYear(), today.getMonth(), 1);
        render();
    });

    render();
})();
</script>
@endsection
