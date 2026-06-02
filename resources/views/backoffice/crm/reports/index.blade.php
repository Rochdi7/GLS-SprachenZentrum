@extends('layouts.main')

@section('title', 'Rapports CEO Quotidiens')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Rapports CEO Quotidiens')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
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

    {{-- Summary cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total rapports</h6>
                    <h3 class="mb-0">{{ $reports->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Dernier rapport</h6>
                    <h3 class="mb-0">
                        {{ $reports->first()?->report_date?->format('d/m/Y') ?? '—' }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Revenue (dernier)</h6>
                    <h3 class="mb-0">
                        {{ $reports->first() ? number_format((float) $reports->first()->revenue_yesterday, 0, ',', ' ') . ' MAD' : '—' }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-end">
                    <form method="POST" action="{{ route('backoffice.crm.reports.generate') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="ph-duotone ph-arrows-clockwise me-1"></i> Générer rapport
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Reports table --}}
    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ph-duotone ph-chart-bar me-2"></i>Rapports CEO — 30 derniers jours
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover" id="reports-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Revenue J-1</th>
                                    <th class="text-end">Nouvelles inscriptions</th>
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
                                               class="btn btn-sm btn-outline-primary" title="Voir le rapport">
                                                <i class="ph-duotone ph-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-5">
                                            <i class="ph-duotone ph-chart-bar" style="font-size: 2rem"></i>
                                            <br>Aucun rapport généré pour le moment.
                                            <br>
                                            <form method="POST" action="{{ route('backoffice.crm.reports.generate') }}" class="d-inline mt-2">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm mt-2">
                                                    Générer le premier rapport
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
