@extends('layouts.main')

@section('title', 'Présence CRM v' . $import->version . ' — ' . $group->name)
@section('breadcrumb-item', 'Paiement Professeurs CRM')
@section('breadcrumb-item-link', route('backoffice.payroll.crm.dashboard'))
@section('breadcrumb-item-active', 'Détails v' . $import->version)

@section('css')
    <style>
        .presence-cell {
            min-width: 36px;
            text-align: center;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 4px !important;
        }
        .presence-cell.present { background-color: #d4edda; color: #155724; }
        .presence-cell.absent { background-color: #f8d7da; color: #721c24; }
        .presence-cell.no_data { background-color: #f8f9fa; color: #6c757d; }
        .summary-card .display-6 { font-size: 2rem; }
        .week-cell { min-width: 130px; }
        .week-cell .week-presence {
            font-size: 0.7rem;
            color: #6c757d;
            display: block;
            text-align: center;
            margin-bottom: 2px;
        }
        .week-cell .week-presence.qualified { color: #198754; font-weight: 600; }
        .week-cell .week-presence.unqualified { color: #dc3545; }
        .week-amount-input {
            text-align: right;
            font-weight: 600;
        }
        .week-amount-input.is-overridden { border-color: #ffc107; background-color: #fff8e1; }
        .attendance-scroll {
            max-height: 70vh;
            overflow: auto;
            position: relative;
        }
        .attendance-scroll table { margin-bottom: 0; }
        .attendance-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background-color: #f8f9fa;
        }
        .attendance-scroll th.col-sticky-num,
        .attendance-scroll td.col-sticky-num {
            position: sticky;
            left: 0;
            z-index: 2;
            background-color: #fff;
            min-width: 40px;
        }
        .attendance-scroll th.col-sticky-name,
        .attendance-scroll td.col-sticky-name {
            position: sticky;
            left: 40px;
            z-index: 2;
            background-color: #fff;
            min-width: 200px;
            box-shadow: 2px 0 4px -2px rgba(0, 0, 0, 0.15);
        }
        .attendance-scroll thead th.col-sticky-num,
        .attendance-scroll thead th.col-sticky-name {
            z-index: 4;
            background-color: #f8f9fa;
        }
        .attendance-scroll th.col-sticky-total,
        .attendance-scroll td.col-sticky-total {
            position: sticky;
            right: 0;
            z-index: 2;
            background-color: #fff;
            min-width: 110px;
            box-shadow: -2px 0 4px -2px rgba(0, 0, 0, 0.15);
        }
        .attendance-scroll thead th.col-sticky-total {
            z-index: 4;
            background-color: #f8f9fa;
        }
        .attendance-scroll tfoot td {
            position: sticky;
            bottom: 0;
            z-index: 3;
            background-color: #fff3cd;
        }
        .attendance-scroll tfoot td.col-sticky-num,
        .attendance-scroll tfoot td.col-sticky-name,
        .attendance-scroll tfoot td.col-sticky-total {
            z-index: 5;
        }
    </style>
@endsection

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Paiement Professeurs CRM</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    @php
        $summary = $import->paymentSummary;
        $allDates = $import->students->flatMap(fn($s) => $s->records->pluck('date'))->unique()->sort()->values();
        $weekThreshold = $import->getThreshold();
        $weeklyUnitAmount = $import->getWeeklyUnitAmount();
    @endphp

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <h5 class="mb-0">{{ $group->name }}</h5>
                                <span class="badge bg-success">
                                    <i class="ph-duotone ph-cloud me-1"></i> Import CRM API
                                </span>
                                <span class="badge bg-primary">v{{ $import->version }}</span>
                            </div>
                            <span class="text-muted">
                                Professeur : <strong>{{ $group->teacher?->name ?? '—' }}</strong>
                                | Niveau : <span class="badge bg-light-primary">{{ $group->level }}</span>
                                | Période : {{ $import->date_start->format('d/m/Y') }} — {{ $import->date_end->format('d/m/Y') }}
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('backoffice.payroll.crm.group.imports', $group) }}"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="ph-duotone ph-clock-counter-clockwise me-1"></i> Historique
                            </a>
                            <a href="{{ route('backoffice.payroll.crm.dashboard') }}"
                               class="btn btn-outline-secondary btn-sm">
                                Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card summary-card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total paiement</h6>
                    <div class="display-6">
                        @if($summary)
                            {{ number_format($summary->total_payment, 2) }} DH
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Étudiants actifs</h6>
                    <div class="display-6">
                        @if($summary)
                            {{ $summary->total_students }}
                        @else
                            {{ $import->students->count() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Taux/étudiant</h6>
                    <div class="display-6">
                        {{ number_format($import->getEffectivePaymentPerStudent() ?? 0, 2) }} DH
                    </div>
                    <small class="text-muted">soit {{ number_format($weeklyUnitAmount, 2) }} DH/semaine</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Seuil semaine</h6>
                    <div class="display-6">{{ $weekThreshold }} jours</div>
                    <small class="text-muted">minimum pour être payé</small>
                </div>
            </div>
        </div>
    </div>

    @if($summary)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Résumé par catégorie</h6>
                        <div>
                            @if(!$summary->isApproved())
                                <form action="{{ route('backoffice.payroll.presence.import.approve', $import) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="ph-duotone ph-check-circle me-1"></i> Approuver
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-success">
                                    <i class="ph-duotone ph-check-circle me-1"></i>
                                    Approuvé par {{ $summary->approvedBy?->name ?? '—' }}
                                    le {{ $summary->approved_at?->format('d/m/Y H:i') ?? '—' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="display-4">{{ $summary->count_full }}</div>
                                    <div class="text-muted small">Complet (4/4)</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="display-4">{{ $summary->count_three_quarter }}</div>
                                    <div class="text-muted small">3/4</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="display-4">{{ $summary->count_half }}</div>
                                    <div class="text-muted small">1/2</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="display-4">{{ $summary->count_quarter }}</div>
                                    <div class="text-muted small">1/4</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="display-4">{{ $summary->count_zero }}</div>
                                    <div class="text-muted small">Zéro</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="display-4">{{ $summary->count_qualified_weeks }}</div>
                                    <div class="text-muted small">Semaines qualifiées</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Présence quotidienne</h6>
                </div>
                <div class="card-body p-0">
                    <div class="attendance-scroll">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="col-sticky-num">#</th>
                                    <th class="col-sticky-name">Étudiant</th>
                                    @foreach($allDates as $date)
                                        <th class="text-nowrap small">
                                            {{ \Carbon\Carbon::parse($date)->translatedFormat('D d') }}
                                        </th>
                                    @endforeach
                                    <th class="col-sticky-total text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($import->students as $student)
                                    @php
                                        $recordsByDate = $student->records->keyBy('date');
                                    @endphp
                                    <tr class="{{ $student->isCancelled() || $student->isTransferred() ? 'table-danger' : '' }}">
                                        <td class="col-sticky-num text-muted small">{{ $loop->iteration }}</td>
                                        <td class="col-sticky-name">
                                            <strong>{{ $student->student_name }}</strong>
                                            @if($student->isCancelled())
                                                <span class="badge bg-danger ms-1">Annulé</span>
                                            @endif
                                            @if($student->isTransferred())
                                                <span class="badge bg-warning ms-1">Transféré</span>
                                            @endif
                                        </td>
                                        @foreach($allDates as $date)
                                            @php
                                                $record = $recordsByDate->get($date);
                                                $status = $record?->status;
                                            @endphp
                                            <td class="presence-cell {{ $status === 'present' ? 'present' : ($status === 'absent' ? 'absent' : 'no_data') }}">
                                                {{ $status === 'present' ? 'P' : ($status === 'absent' ? 'Q' : '—') }}
                                            </td>
                                        @endforeach
                                        <td class="col-sticky-total text-center">
                                            <strong>{{ $student->total_present }}</strong>
                                            <small class="text-muted">/{{ $student->total_absent + $student->total_present }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Calcul par semaine</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Étudiant</th>
                                    @foreach([1, 2, 3, 4] as $w)
                                        <th class="week-cell text-center">
                                            Semaine {{ $w }}
                                        </th>
                                    @endforeach
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($import->students as $student)
                                    <tr class="{{ $student->isCancelled() || $student->isTransferred() ? 'table-danger' : '' }}">
                                        <td class="text-muted small">{{ $loop->iteration }}</td>
                                        <td><strong>{{ $student->student_name }}</strong></td>
                                        @foreach([1, 2, 3, 4] as $w)
                                            @php
                                                $presence = $student->{"week_{$w}_presence"} ?? 0;
                                                $autoAmount = $student->{"week_{$w}_amount"} ?? 0;
                                                $override = $student->{"week_{$w}_amount_override"};
                                                $effective = $override !== null ? $override : $autoAmount;
                                                $isQualified = $presence >= $weekThreshold;
                                            @endphp
                                            <td class="week-cell">
                                                <span class="week-presence {{ $isQualified ? 'qualified' : 'unqualified' }}">
                                                    {{ $presence }}/{{ $weekThreshold }} jours
                                                </span>
                                                <div class="fw-bold">
                                                    {{ number_format($effective, 2) }} DH
                                                </div>
                                            </td>
                                        @endforeach
                                        <td class="text-end">
                                            <strong>{{ number_format($student->weighted_amount, 2) }} DH</strong>
                                        </td>
                                    </tr>
                                @endforeach
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
        document.addEventListener("DOMContentLoaded", function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
