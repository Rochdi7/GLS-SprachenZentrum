@extends('layouts.main')

@section('title', 'Rapports CEO')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Rapports CEO')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .rc-tabs { border-bottom: none; gap: .25rem; }
        .rc-tabs .nav-link {
            border: none; border-radius: 10px 10px 0 0; color: #6b7280; font-weight: 600;
            padding: .65rem 1.1rem; background: transparent;
        }
        .rc-tabs .nav-link.active { background: #fff; color: var(--bs-primary); box-shadow: 0 -2px 8px rgba(20,20,40,.04); }
        .rc-card-body { border-top-left-radius: 0; }
        .rc-trend-empty { min-height: 220px; display: flex; align-items: center; justify-content: center; color: #9ca3af; }
    </style>
@endsection

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Rapports CEO</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body {{ session('error') ? 'text-danger' : '' }}">
                    {{ session('success') ?? session('error') }}
                </div>
            </div>
        </div>
    @endif

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h4 class="mb-1"><i class="ph-duotone ph-chart-bar text-primary me-2"></i>Rapports CEO</h4>
            <small class="text-muted">Synthèse quotidienne et hebdomadaire envoyée par email</small>
        </div>
        @include('backoffice.crm.partials._sync_badge')
    </div>

    {{-- ── KPI Cards ────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="ph-duotone ph-sun text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Rapports quotidiens</div>
                            <div class="h3 mb-0 fw-bold">{{ $reports->count() }}</div>
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
                            <i class="ph-duotone ph-calendar-check text-info fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Dernier quotidien</div>
                            <div class="h5 mb-0 fw-bold">{{ $reports->first()?->report_date?->format('d/m/Y') ?? '—' }}</div>
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
                            <i class="ph-duotone ph-calendar-blank text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Rapports hebdo</div>
                            <div class="h3 mb-0 fw-bold">{{ $weeklyReports->count() }}</div>
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
                            <i class="ph-duotone ph-clock-counter-clockwise text-warning fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Dernier hebdo</div>
                            <div class="h6 mb-0 fw-bold">{{ $weeklyReports->first()?->week_label ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Revenue trend chart ─────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pb-0">
            <h6 class="mb-0"><i class="ph-duotone ph-trend-up text-primary me-2"></i>Tendance — encaissement quotidien</h6>
        </div>
        <div class="card-body">
            @if($reports->count() > 0)
                <div id="revenueTrendChart" style="min-height:220px"></div>
            @else
                <div class="rc-trend-empty">Aucune donnée à afficher pour la période sélectionnée.</div>
            @endif
        </div>
    </div>

    {{-- Date filter (standalone form — NO nested forms) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-end gap-2">
                {{-- Filter form --}}
                <form method="GET" class="d-flex flex-wrap gap-2 align-items-end flex-grow-1">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <label class="form-label fw-semibold mb-0 me-1">
                        <i class="ph-duotone ph-funnel me-1"></i> Période
                    </label>
                    <input type="date" name="from" class="form-control form-control-sm" style="width:145px" value="{{ $fromDate }}" placeholder="Du">
                    <input type="date" name="to"   class="form-control form-control-sm" style="width:145px" value="{{ $toDate }}"   placeholder="Au">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="ph-duotone ph-magnifying-glass me-1"></i> Filtrer
                    </button>
                    @if($fromDate || $toDate)
                        <a href="{{ route('backoffice.crm.reports.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph-duotone ph-x me-1"></i> Reset
                        </a>
                    @endif
                </form>

                {{-- Generate buttons — separate, NOT inside the filter form --}}
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalDaily">
                        <i class="ph-duotone ph-arrows-clockwise me-1"></i> Générer quotidien
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalWeekly">
                        <i class="ph-duotone ph-calendar-check me-1"></i> Générer hebdo
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs rc-tabs" id="reportTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'daily' ? 'active' : '' }}"
               href="{{ route('backoffice.crm.reports.index', array_merge(request()->only('from','to'), ['tab' => 'daily'])) }}">
                <i class="ph-duotone ph-sun me-1"></i> Quotidiens
                <span class="badge bg-light-primary text-primary ms-1">{{ $reports->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'weekly' ? 'active' : '' }}"
               href="{{ route('backoffice.crm.reports.index', array_merge(request()->only('from','to'), ['tab' => 'weekly'])) }}">
                <i class="ph-duotone ph-calendar-blank me-1"></i> Hebdomadaires
                <span class="badge bg-light-success text-success ms-1">{{ $weeklyReports->count() }}</span>
            </a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm table-card">
        <div class="card-body rc-card-body pt-3">

            {{-- ── DAILY TAB ──────────────────────────────────────────────── --}}
            @if($tab === 'daily')
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="reports-table">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Revenue J-1</th>
                                <th class="text-end">Nouvelles inscr.</th>
                                <th class="text-end">Étudiants à risque</th>
                                <th class="text-end">Créances</th>
                                <th>Meilleur centre</th>
                                <th>Email</th>
                                <th>Généré le</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td>
                                        <strong>{{ $report->report_date->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $report->report_date->translatedFormat('l') }}</small>
                                    </td>
                                    <td class="text-end">
                                        @if ($report->revenue_yesterday !== null)
                                            <span class="text-success fw-bold">
                                                {{ number_format((float) $report->revenue_yesterday, 0, ',', ' ') }} MAD
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($report->new_registrations !== null)
                                            <span class="badge bg-light-primary">{{ $report->new_registrations }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($report->students_at_risk !== null)
                                            <span class="badge {{ $report->students_at_risk > 0 ? 'bg-light-danger' : 'bg-light-success' }}">
                                                {{ $report->students_at_risk }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($report->outstanding_receivables !== null)
                                            <span class="text-warning">
                                                {{ number_format((float) $report->outstanding_receivables, 0, ',', ' ') }} MAD
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($report->best_center)
                                            <span class="badge bg-light-success">
                                                <i class="ph-duotone ph-trophy me-1"></i>{{ $report->best_center }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($report->email_sent_at)
                                            <span class="badge bg-light-success text-success" title="Envoyé le {{ $report->email_sent_at->format('d/m/Y H:i') }}">
                                                <i class="ph-duotone ph-check-circle me-1"></i>Envoyé
                                            </span>
                                        @else
                                            <span class="badge bg-light-warning text-warning">
                                                <i class="ph-duotone ph-warning-circle me-1"></i>Non envoyé
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $report->generated_at?->format('d/m/Y H:i') ?? '—' }}
                                        </small>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('backoffice.crm.reports.show', ['date' => $report->report_date->toDateString()]) }}"
                                           class="btn btn-sm btn-outline-primary" title="Voir">
                                            <i class="ph-duotone ph-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="ph-duotone ph-chart-bar" style="font-size:2rem"></i>
                                        <br>Aucun rapport quotidien.
                                        <br>
                                        <button type="button" class="btn btn-success btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalDaily">
                                            Générer maintenant
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ── WEEKLY TAB ─────────────────────────────────────────────── --}}
            @if($tab === 'weekly')
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Semaine</th>
                                <th>Période</th>
                                <th class="text-end">Revenue total</th>
                                <th class="text-end">Inscriptions</th>
                                <th>Meilleur centre</th>
                                <th>Classement centres</th>
                                <th>Généré le</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($weeklyReports as $wr)
                                @php
                                    $ranking   = $wr->centers_ranking ?? [];
                                    $breakdown = $wr->daily_breakdown ?? [];
                                @endphp
                                <tr>
                                    <td><strong>{{ $wr->week_label }}</strong></td>
                                    <td class="text-nowrap">
                                        <small class="text-muted">
                                            {{ $wr->week_start->format('d/m') }} → {{ $wr->week_end->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        {{ number_format((float) $wr->total_revenue, 0, ',', ' ') }} MAD
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-light-primary">{{ $wr->new_registrations }}</span>
                                    </td>
                                    <td>
                                        @if($wr->best_center)
                                            <span class="badge bg-light-success">
                                                <i class="ph-duotone ph-trophy me-1"></i>{{ $wr->best_center }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach(array_slice($ranking, 0, 3) as $i => $center)
                                            <div class="d-flex align-items-center gap-1 {{ $i === 0 ? 'fw-semibold' : 'text-muted' }}" style="font-size:.82rem">
                                                <span class="text-muted me-1">#{{ $i+1 }}</span>
                                                {{ $center['name'] }}
                                                <span class="ms-auto">{{ number_format($center['amount'], 0, ',', ' ') }}</span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $wr->generated_at?->format('d/m/Y H:i') ?? '—' }}</small>
                                    </td>
                                </tr>
                                @if(!empty($breakdown))
                                    <tr class="table-light" style="font-size:.82rem">
                                        <td colspan="7" class="py-2 ps-4">
                                            <div class="d-flex flex-wrap gap-3">
                                                @foreach($breakdown as $day)
                                                    <div class="text-center" style="min-width:70px">
                                                        <div class="text-muted">{{ $day['day_label'] }}</div>
                                                        <div class="fw-semibold text-success">{{ number_format($day['revenue'], 0, ',', ' ') }}</div>
                                                        <div class="text-muted">{{ $day['registrations'] }} inscr.</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="ph-duotone ph-calendar-blank" style="font-size:2rem"></i>
                                        <br>Aucun rapport hebdomadaire.
                                        <br>
                                        <button type="button" class="btn btn-success btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalWeekly">
                                            Générer la semaine passée
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>

    {{-- ══ MODAL — Générer rapport quotidien ══ --}}
    <div class="modal fade" id="modalDaily" tabindex="-1" aria-labelledby="modalDailyLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('backoffice.crm.reports.generate') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalDailyLabel">
                            <i class="ph-duotone ph-sun text-success me-2"></i>Générer rapport quotidien
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Choisissez la date du rapport. Par défaut, le rapport est généré pour <strong>hier</strong>.</p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Date du rapport</label>
                            <input type="date" name="date" class="form-control"
                                   value="{{ now('Africa/Casablanca')->subDay()->toDateString() }}"
                                   max="{{ now('Africa/Casablanca')->toDateString() }}">
                            <div class="form-text">Laissez vide pour hier automatiquement.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ph-duotone ph-arrows-clockwise me-1"></i> Générer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══ MODAL — Générer rapport hebdomadaire ══ --}}
    <div class="modal fade" id="modalWeekly" tabindex="-1" aria-labelledby="modalWeeklyLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('backoffice.crm.reports.generate-weekly') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalWeeklyLabel">
                            <i class="ph-duotone ph-calendar-check text-success me-2"></i>Générer rapport hebdomadaire
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Choisissez n'importe quelle date dans la semaine souhaitée. Le rapport couvre <strong>lundi → dimanche</strong> de cette semaine.</p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Une date dans la semaine</label>
                            <input type="date" name="date" class="form-control"
                                   value="{{ now('Africa/Casablanca')->subWeek()->toDateString() }}"
                                   max="{{ now('Africa/Casablanca')->toDateString() }}">
                            <div class="form-text">Par défaut : la semaine passée.</div>
                        </div>
                        <div class="alert alert-info py-2 mb-0" style="font-size:.85rem">
                            <i class="ph-duotone ph-info me-1"></i>
                            Le rapport hebdo est aussi généré <strong>automatiquement chaque vendredi à 06h00</strong>.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ph-duotone ph-calendar-check me-1"></i> Générer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
@if($reports->count() > 0)
<script type="application/json" id="revenue-trend-data">
{
    "labels":  @json($reports->sortBy('report_date')->map(fn($r) => $r->report_date->format('d/m'))->values()),
    "amounts": @json($reports->sortBy('report_date')->map(fn($r) => (float) ($r->revenue_yesterday ?? 0))->values())
}
</script>
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script>
    (function () {
        const data = JSON.parse(document.getElementById('revenue-trend-data').textContent);
        new ApexCharts(document.querySelector('#revenueTrendChart'), {
            chart: { type: 'area', height: 220, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Encaissement', data: data.amounts }],
            xaxis: { categories: data.labels, labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { formatter: (v) => v.toLocaleString('fr-FR') + ' MAD' } },
            colors: ['#28a745'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 } },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f1f1' },
        }).render();
    })();
</script>
@endif
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
