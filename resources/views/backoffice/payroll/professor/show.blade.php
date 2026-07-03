@extends('layouts.main')

@section('title', 'Détail paiement')
@section('breadcrumb-item', 'Espace Professeur')
@section('breadcrumb-item-link', route('backoffice.payroll.professor.index'))
@section('breadcrumb-item-active', 'Détail')

@section('css')
<style>
    .kpi-val { font-size: 1.5rem; font-weight: 700; line-height: 1.1; }
    .kpi-lbl { font-size: .72rem; color: #6c757d; text-transform: uppercase; letter-spacing: .04em; }
    .ro-badge { font-size: .72rem; }
</style>
@endsection

@section('content')

@php
    $summary  = $import->paymentSummary;
    $mode     = $import->payment_mode ?? 'weekly';
    $statusMeta = [
        'draft'     => ['Brouillon', 'secondary'],
        'validated' => ['Validé', 'info'],
        'paid'      => ['Payé', 'success'],
        'locked'    => ['Verrouillé', 'dark'],
    ];
    [$stLabel, $stColor] = $statusMeta[$import->status] ?? ['—','secondary'];
    $modeLabels = ['weekly' => 'Hebdomadaire', 'period' => 'Par période', 'hourly' => 'Par heures'];
@endphp

{{-- ── HEADER ─────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h4 class="mb-1 fw-bold">{{ $import->group?->name ?? '—' }}</h4>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-primary ro-badge">{{ $modeLabels[$mode] ?? $mode }}</span>
            <span class="badge bg-{{ $stColor }} ro-badge">{{ $stLabel }}</span>
            @if($import->group_month_number)<span class="badge bg-light-primary text-primary ro-badge">Mois {{ $import->group_month_number }}</span>@endif
            @if($import->month_label)<span class="text-muted small">{{ $import->month_label }}</span>@endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('backoffice.payroll.professor.pdf', $import->id) }}" class="btn btn-danger btn-sm" target="_blank">
            <i class="ph-duotone ph-file-pdf me-1"></i> Télécharger PDF
        </a>
        <a href="{{ route('backoffice.payroll.professor.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
        </a>
    </div>
</div>

{{-- ── READ-ONLY NOTICE ──────────────────────────────── --}}
<div class="alert alert-light border d-flex align-items-center gap-2 mb-3 py-2">
    <i class="ph-duotone ph-info text-primary"></i>
    <small class="text-muted">Cette page est en lecture seule. Pour toute question, contactez l’administration.</small>
</div>

{{-- ── PAYMENT INFO (when paid/locked) ───────────────── --}}
@if($import->payment_date)
<div class="card shadow-sm mb-3 border-success">
    <div class="card-body py-3">
        <div class="row g-3 small align-items-center">
            <div class="col-6 col-md-3"><div class="text-muted">Date de paiement</div><div class="fw-semibold">{{ $import->payment_date->format('d/m/Y') }}</div></div>
            <div class="col-6 col-md-3"><div class="text-muted">Mode</div><div class="fw-semibold">{{ $import->paymentMethodLabel() ?? '—' }}</div></div>
            <div class="col-6 col-md-3"><div class="text-muted">Référence</div><div class="fw-semibold">{{ $import->payment_reference ?: '—' }}</div></div>
            <div class="col-6 col-md-3"><div class="text-muted">Payé par</div><div class="fw-semibold">{{ $import->paidBy?->name ?? '—' }}</div></div>
        </div>
    </div>
</div>
@endif

{{-- ── META CARD ─────────────────────────────────────── --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="kpi-lbl">Période</div>
                <div class="fw-semibold">{{ $import->date_start->format('d/m/Y') }} → {{ $import->date_end->format('d/m/Y') }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-lbl">Mois rattaché</div>
                <div class="fw-semibold">
                    @if($import->attached_month && $import->attached_year)
                        {{ \Carbon\Carbon::create()->month($import->attached_month)->locale('fr')->isoFormat('MMMM') }} {{ $import->attached_year }}
                    @else — @endif
                </div>
            </div>
            @if($mode === 'period')
                <div class="col-6 col-md-3">
                    <div class="kpi-lbl">Prix de base / étudiant</div>
                    <div class="fw-semibold">{{ number_format((float)($import->base_price ?? 0), 2) }} DH</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="kpi-lbl">Total</div>
                    <div class="fw-bold text-success">{{ $summary ? number_format($summary->total_payment, 2) : '0,00' }} DH</div>
                </div>
            @elseif($mode === 'hourly')
                <div class="col-6 col-md-3">
                    <div class="kpi-lbl">Taux horaire</div>
                    <div class="fw-semibold">{{ number_format((float)($import->hourly_rate ?? 0), 2) }} DH</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="kpi-lbl">Total</div>
                    <div class="fw-bold text-primary">{{ number_format((float)($import->final_total ?? 0), 2) }} DH</div>
                </div>
            @else
                <div class="col-6 col-md-3">
                    <div class="kpi-lbl">Total</div>
                    <div class="fw-bold text-success">{{ $summary ? number_format($summary->total_payment, 2) : '0,00' }} DH</div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ── PERIOD: student table (read-only) ─────────────── --}}
@if($mode === 'period')
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0 fw-bold"><i class="ph-duotone ph-table me-1"></i>Détail par étudiant</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Étudiant</th>
                            <th class="text-center">Présences</th>
                            <th class="text-end">Montant auto</th>
                            <th class="text-end">Ajustement</th>
                            <th class="text-end">Montant final</th>
                            <th>Motif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($import->students as $student)
                            @php
                                $override  = $student->period_amount_override;
                                $effective = $student->getPeriodEffectiveAmount();
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">
                                    {{ $student->student_name }}
                                    @if($student->isCancelled())<span class="badge bg-danger ms-1" style="font-size:.6rem">Annulé</span>@endif
                                    @if($student->isTransferred())<span class="badge bg-warning ms-1" style="font-size:.6rem">Transféré</span>@endif
                                </td>
                                <td class="text-center"><span class="badge bg-light-primary text-primary">{{ (int)$student->period_presence_count }}</span></td>
                                <td class="text-end text-muted">{{ number_format((float)$student->period_auto_amount, 2) }} DH</td>
                                <td class="text-end">
                                    @if($override !== null)
                                        <span class="badge bg-light-warning text-warning">{{ number_format((float)$override, 2) }} DH</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold">{{ number_format($effective, 2) }} DH</td>
                                <td>
                                    <small class="text-muted">{{ $student->override_reason ?: '—' }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="5" class="text-end">TOTAL</td>
                            <td class="text-end">{{ $summary ? number_format($summary->total_payment, 2) : '0,00' }} DH</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

{{-- ── HOURLY: breakdown (read-only) ─────────────────── --}}
@elseif($mode === 'hourly')
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0 fw-bold"><i class="ph-duotone ph-calculator me-1"></i>Détail du calcul</h6></div>
        <div class="card-body">
            <table class="table table-borderless mb-0" style="max-width:480px">
                <tr><td class="text-muted">Taux horaire</td><td class="text-end fw-semibold">{{ number_format((float)$import->hourly_rate, 2) }} DH</td></tr>
                <tr><td class="text-muted">Total heures</td><td class="text-end fw-semibold">{{ number_format((float)$import->total_hours, 2) }} h</td></tr>
                <tr class="border-top"><td class="fw-bold">Total final</td><td class="text-end fw-bold text-primary fs-5">{{ number_format((float)$import->final_total, 2) }} DH</td></tr>
            </table>
        </div>
    </div>

{{-- ── WEEKLY (legacy) fallback: simple read-only list ─ --}}
@else
    <div class="card shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0 fw-bold"><i class="ph-duotone ph-table me-1"></i>Détail par étudiant</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark"><tr><th>#</th><th>Étudiant</th><th class="text-end">Montant</th></tr></thead>
                    <tbody>
                        @foreach($import->students as $student)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $student->student_name }}</td>
                                <td class="text-end fw-bold">{{ number_format((float)$student->weighted_amount, 2) }} DH</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@endsection
