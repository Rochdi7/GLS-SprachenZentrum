@extends('layouts.main')

@section('title', 'Rapports CEO')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Rapports CEO')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    <div class="d-flex justify-content-end mb-2">
        @include('backoffice.crm.partials._sync_badge')
    </div>

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

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Rapports quotidiens</h6>
                    <h3 class="mb-0">{{ $reports->count() }}</h3>
                    <small class="text-muted">affichés</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Dernier quotidien</h6>
                    <h3 class="mb-0">{{ $reports->first()?->report_date?->format('d/m/Y') ?? '—' }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Rapports hebdo</h6>
                    <h3 class="mb-0">{{ $weeklyReports->count() }}</h3>
                    <small class="text-muted">affichés</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Dernier hebdo</h6>
                    <h3 class="mb-0">{{ $weeklyReports->first()?->week_label ?? '—' }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Date filter + generate actions --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-12 col-sm-auto">
                    <label class="form-label fw-semibold mb-0">
                        <i class="ph-duotone ph-funnel me-1"></i> Période
                    </label>
                </div>
                <div class="col-12 col-sm">
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $fromDate }}" placeholder="Du">
                </div>
                <div class="col-12 col-sm">
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $toDate }}" placeholder="Au">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="ph-duotone ph-magnifying-glass me-1"></i> Filtrer
                    </button>
                </div>
                @if($fromDate || $toDate)
                    <div class="col-auto">
                        <a href="{{ route('backoffice.crm.reports.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph-duotone ph-x me-1"></i> Reset
                        </a>
                    </div>
                @endif
                <div class="col-12 col-sm-auto ms-sm-auto d-flex gap-2">
                    <form method="POST" action="{{ route('backoffice.crm.reports.generate') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="ph-duotone ph-arrows-clockwise me-1"></i> Générer quotidien
                        </button>
                    </form>
                    <form method="POST" action="{{ route('backoffice.crm.reports.generate-weekly') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-success btn-sm">
                            <i class="ph-duotone ph-calendar-check me-1"></i> Générer hebdo
                        </button>
                    </form>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-0" id="reportTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'daily' ? 'active' : '' }}"
               href="{{ route('backoffice.crm.reports.index', array_merge(request()->only('from','to'), ['tab' => 'daily'])) }}">
                <i class="ph-duotone ph-sun me-1"></i> Quotidiens
                <span class="badge bg-light-primary ms-1">{{ $reports->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'weekly' ? 'active' : '' }}"
               href="{{ route('backoffice.crm.reports.index', array_merge(request()->only('from','to'), ['tab' => 'weekly'])) }}">
                <i class="ph-duotone ph-calendar-blank me-1"></i> Hebdomadaires
                <span class="badge bg-light-success ms-1">{{ $weeklyReports->count() }}</span>
            </a>
        </li>
    </ul>

    <div class="card table-card" style="border-top-left-radius:0; border-top-right-radius:0;">
        <div class="card-body pt-3">

            {{-- ── DAILY TAB ──────────────────────────────────────────────── --}}
            @if($tab === 'daily')
                <div class="table-responsive">
                    <table class="table table-hover" id="reports-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Revenue J-1</th>
                                <th class="text-end">Nouvelles inscr.</th>
                                <th class="text-end">Étudiants à risque</th>
                                <th class="text-end">Créances</th>
                                <th>Meilleur centre</th>
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
                                        <small class="text-muted">
                                            {{ $report->generated_at?->format('d/m/Y H:i') ?? '—' }}
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('backoffice.crm.reports.show', ['date' => $report->report_date->toDateString()]) }}"
                                           class="btn btn-sm btn-outline-primary" title="Voir">
                                            <i class="ph-duotone ph-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="ph-duotone ph-chart-bar" style="font-size:2rem"></i>
                                        <br>Aucun rapport quotidien.
                                        <form method="POST" action="{{ route('backoffice.crm.reports.generate') }}" class="d-inline mt-2">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm mt-2">Générer maintenant</button>
                                        </form>
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
                    <table class="table table-hover">
                        <thead>
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
                                    $ranking = $wr->centers_ranking ?? [];
                                    $breakdown = $wr->daily_breakdown ?? [];
                                    $weekRevenue = (float) $wr->total_revenue;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $wr->week_label }}</strong>
                                    </td>
                                    <td class="text-nowrap">
                                        <small class="text-muted">
                                            {{ $wr->week_start->format('d/m') }} → {{ $wr->week_end->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        {{ number_format($weekRevenue, 0, ',', ' ') }} MAD
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
                                        <small class="text-muted">
                                            {{ $wr->generated_at?->format('d/m/Y H:i') ?? '—' }}
                                        </small>
                                    </td>
                                </tr>
                                {{-- Day-by-day breakdown (collapsed) --}}
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
                                        <form method="POST" action="{{ route('backoffice.crm.reports.generate-weekly') }}" class="d-inline mt-2">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm mt-2">Générer la semaine passée</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
