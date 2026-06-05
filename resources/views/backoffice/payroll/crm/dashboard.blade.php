@extends('layouts.main')

@section('title', 'Paiement Professeurs — Tableau de bord')
@section('breadcrumb-item', 'Paiement Professeurs')
@section('breadcrumb-item-active', 'Tableau de bord')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
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
        <a href="{{ route('backoffice.payroll.crm.legacy.import.create') }}" class="btn btn-success">
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

    {{-- ── Table ────────────────────────────────────────────────────────── --}}
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
                                        @if ($group->crm_class_id)
                                            <a href="{{ route('backoffice.payroll.crm.legacy.import.create', ['crm_class_id' => $group->crm_class_id]) }}"
                                               class="btn btn-sm btn-outline-success" title="Ajouter un paiement">
                                                <i class="ph-duotone ph-plus"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="ph-duotone ph-folder-open fs-1 d-block mb-2"></i>
                                    Aucun groupe lié pour le moment.
                                    <br><a href="{{ route('backoffice.payroll.crm.legacy.import.create') }}" class="btn btn-success btn-sm mt-2">Ajouter un paiement</a>
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
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script type="module">
    import { DataTable } from "/build/js/plugins/module.js";
    window.dt = new DataTable("#pc-dt-simple");
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toastEl = document.getElementById('liveToast');
    if (toastEl) new bootstrap.Toast(toastEl).show();

    document.getElementById('site-filter').addEventListener('change', function () {
        const siteId = this.value;
        document.querySelectorAll('#pc-dt-simple tbody tr[data-site-id]').forEach(row => {
            row.style.display = (!siteId || row.dataset.siteId === siteId) ? '' : 'none';
        });
    });

    // Bar chart — paiement par professeur
    const labels  = @json($chartLabels);
    const amounts = @json($chartAmounts);
    const students= @json($chartStudents);

    if (labels.length > 0 && document.getElementById('paymentBarChart')) {
        new ApexCharts(document.getElementById('paymentBarChart'), {
            chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [
                { name: 'Montant (DH)', data: amounts },
                { name: 'Étudiants', data: students },
            ],
            xaxis: { categories: labels, labels: { style: { fontSize: '12px' } } },
            yaxis: [
                { title: { text: 'DH' }, labels: { formatter: v => v.toLocaleString('fr-MA') + ' DH' } },
                { opposite: true, title: { text: 'Étudiants' }, labels: { formatter: v => v + ' étu.' } },
            ],
            colors: ['#4361ee', '#2ec4b6'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f1f1' },
            tooltip: {
                y: [
                    { formatter: v => v.toLocaleString('fr-MA') + ' DH' },
                    { formatter: v => v + ' étudiant(s)' },
                ]
            },
            legend: { position: 'top' },
        }).render();
    }

    // Donut chart — statut
    const approved = {{ $approved }};
    const pending  = {{ $pending }};
    const noImport = {{ $noImport }};

    if (document.getElementById('statusDonutChart') && (approved + pending + noImport) > 0) {
        new ApexCharts(document.getElementById('statusDonutChart'), {
            chart: { type: 'donut', height: 220, fontFamily: 'inherit' },
            series: [approved, pending, noImport],
            labels: ['Approuvé', 'En attente', 'Sans import'],
            colors: ['#28a745', '#ffc107', '#dee2e6'],
            legend: { show: false },
            dataLabels: { enabled: true, formatter: (val, opts) => opts.w.config.series[opts.seriesIndex] },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', formatter: () => (approved + pending + noImport) + ' groupes' } } } } },
            tooltip: { y: { formatter: v => v + ' groupe(s)' } },
        }).render();
    }
});
</script>
@endsection
