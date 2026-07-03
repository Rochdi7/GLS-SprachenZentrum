@extends('layouts.main')

@section('title', 'Paiement horaire v' . $import->version . ' — ' . $group->name)
@section('breadcrumb-item', 'Paiement Professeurs CRM')
@section('breadcrumb-item-link', route('backoffice.payroll.crm.legacy.dashboard'))
@section('breadcrumb-item-active', 'Horaire v' . $import->version)

@section('css')
<style>
    .kpi-val { font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
    .kpi-lbl { font-size: .72rem; color: #6c757d; text-transform: uppercase; letter-spacing: .04em; }
    .final-box { background: linear-gradient(135deg,#eef6ff,#e3f0ff); border: 1px solid #cfe2ff; }
    .locked-banner { background: #fff3cd; border: 1px solid #ffe69c; }
</style>
@endsection

@section('content')

@if (session('success') || session('error'))
<div class="position-fixed top-0 end-0 p-3" style="z-index:99999">
    <div id="liveToast" class="toast hide" role="alert">
        <div class="toast-header"><strong class="me-auto">{{ $group->name }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button></div>
        <div class="toast-body">{{ session('success') ?? session('error') }}</div>
    </div>
</div>
@endif

@php
    $locked   = $import->isLocked();
    $profName = $import->crm_teacher_name ?? $group->teacher?->name ?? '—';
    $rate     = (float) ($import->hourly_rate ?? 0);
    $hours    = (float) ($import->total_hours ?? 0);
    $final    = (float) ($import->final_total ?? 0);
    $statusMap = [
        'draft'     => ['Brouillon', 'secondary'],
        'validated' => ['Validé', 'info'],
        'paid'      => ['Payé', 'success'],
        'locked'    => ['Verrouillé', 'dark'],
    ];
    [$statusLabel, $statusColor] = $statusMap[$import->status] ?? ['—', 'secondary'];
@endphp

{{-- ── HEADER ─────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="mb-0 fw-bold">{{ $group->name }}</h4>
                    <span class="badge bg-primary"><i class="ph-duotone ph-clock me-1"></i>Horaire</span>
                    <span class="badge bg-secondary">v{{ $import->version }}</span>
                    @if($import->group_month_number)<span class="badge bg-light-primary text-primary">Mois {{ $import->group_month_number }}</span>@endif
                </div>
                <div class="d-flex flex-wrap gap-3" style="font-size:.83rem;color:#555">
                    <span><i class="ph-duotone ph-chalkboard-teacher me-1"></i>{{ $profName }}</span>
                    <span><i class="ph-duotone ph-graduation-cap me-1"></i>{{ $group->level }}</span>
                    @if($import->attached_month && $import->attached_year)
                        <span><i class="ph-duotone ph-calendar me-1"></i>{{ \Carbon\Carbon::create()->month($import->attached_month)->locale('fr')->isoFormat('MMMM') }} {{ $import->attached_year }}</span>
                    @endif
                    @if($import->month_label)<span><i class="ph-duotone ph-tag me-1"></i>{{ $import->month_label }}</span>@endif
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('backoffice.payroll.crm.legacy.import.pdf', ['group'=>$group->id,'import'=>$import->id]) }}"
                   class="btn btn-danger btn-sm" target="_blank"><i class="ph-duotone ph-file-pdf me-1"></i> Exporter PDF</a>
                <a href="{{ route('backoffice.payroll.crm.legacy.group.imports', $group) }}" class="btn btn-outline-primary btn-sm"><i class="ph-duotone ph-list me-1"></i> Historique</a>
                <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="ph-duotone ph-arrow-left me-1"></i> Dashboard</a>
            </div>
        </div>
    </div>
</div>


<div class="row g-3">
    {{-- ── BREAKDOWN CARD ─────────────────────────────── --}}
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0 fw-bold"><i class="ph-duotone ph-calculator me-1"></i>Détail du calcul</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><td class="text-muted">Taux horaire</td><td class="text-end fw-semibold">{{ number_format($rate, 2) }} DH</td></tr>
                    <tr><td class="text-muted">Total heures</td><td class="text-end fw-semibold">{{ number_format($hours, 2) }} h</td></tr>
                    <tr class="border-top"><td class="fw-bold">Total final ({{ number_format($rate, 2) }} × {{ number_format($hours, 2) }})</td>
                        <td class="text-end fw-bold text-primary fs-5">{{ number_format($final, 2) }} DH</td></tr>
                </table>
                @if($import->notes)
                    <div class="alert alert-light border mt-3 mb-0"><small class="text-muted">Notes :</small> {{ $import->notes }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── EDIT / STATUS CARD ─────────────────────────── --}}
    <div class="col-lg-5">
        <div class="card shadow-sm mb-3">
            <div class="card-body final-box text-center py-4">
                <div class="kpi-lbl">Total à payer</div>
                <div class="kpi-val text-primary">{{ number_format($final, 2) }} DH</div>
            </div>
        </div>
    </div>
</div>

{{-- ── LIFECYCLE PANEL (stepper, transitions, payment, audit) ── --}}
@include('backoffice.payroll.crm.imports.partials._lifecycle', ['import' => $import, 'group' => $group])

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toastEl = document.getElementById('liveToast');
    if (toastEl) new bootstrap.Toast(toastEl).show();
});
</script>
@endsection
