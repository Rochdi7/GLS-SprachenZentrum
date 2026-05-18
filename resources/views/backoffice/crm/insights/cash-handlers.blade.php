@extends('layouts.main')

@section('title', 'CRM — Cash handlers')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Cash handlers')

@section('content')
    @include('backoffice.crm.partials._center')

    {{-- Week picker --}}
    <form method="GET" class="card mb-3">
        <div class="card-body py-3 d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label small text-muted mb-1">Semaine (date dans la semaine)</label>
                <input type="date" name="week" value="{{ $report['week_start'] }}" class="form-control form-control-sm">
            </div>
            <button class="btn btn-sm btn-primary"><i class="ti ti-search me-1"></i> Afficher</button>
            <div class="ms-auto small text-muted">
                Période : <strong>{{ $report['week_start'] }}</strong> → <strong>{{ $report['week_end'] }}</strong>
                · {{ $report['totals']['handlers'] }} encaisseur(s)
                · {{ number_format($report['totals']['payments'], 0, ',', ' ') }} paiements
                · {{ number_format($report['totals']['amount'], 2, ',', ' ') }} DH
            </div>
        </div>
    </form>

    {{-- Per-handler table --}}
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="ti ti-users text-primary me-1"></i> Activité par encaisseur</h6>
        </div>
        <div class="card-body">
            @if($report['rows']->isEmpty())
                <div class="alert alert-info mb-0">Aucun paiement enregistré sur la période.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Encaisseur</th>
                                <th class="text-end">Paiements</th>
                                <th class="text-end">Total (DH)</th>
                                <th class="text-end">Moyenne</th>
                                <th>Espèces</th>
                                <th>Chèques</th>
                                <th>Virements</th>
                                <th class="text-nowrap">Première / dernière</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report['rows'] as $r)
                                <tr class="{{ $r->is_outlier ? 'table-warning' : '' }}">
                                    <td>
                                        <div class="fw-medium">
                                            {{ trim($r->handler) }}
                                            @if($r->is_outlier)
                                                <span class="badge bg-warning text-dark ms-1" title="Volume anormal vs moyenne">
                                                    <i class="ti ti-alert-triangle"></i> Outlier
                                                </span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">ID #{{ $r->handler_id ?? '—' }}</div>
                                    </td>
                                    <td class="text-end">{{ number_format($r->total_payments, 0, ',', ' ') }}</td>
                                    <td class="text-end fw-medium">{{ number_format($r->total_amount, 2, ',', ' ') }}</td>
                                    <td class="text-end small text-muted">{{ number_format($r->avg_amount, 2, ',', ' ') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; min-width: 80px;">
                                                <div class="progress-bar bg-success" style="width: {{ $r->cash_pct }}%;"></div>
                                            </div>
                                            <small class="text-nowrap">{{ $r->cash_pct }}% ({{ $r->cash_count }})</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; min-width: 80px;">
                                                <div class="progress-bar bg-info" style="width: {{ $r->cheque_pct }}%;"></div>
                                            </div>
                                            <small class="text-nowrap">{{ $r->cheque_pct }}% ({{ $r->cheque_count }})</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; min-width: 80px;">
                                                <div class="progress-bar bg-primary" style="width: {{ $r->transfer_pct }}%;"></div>
                                            </div>
                                            <small class="text-nowrap">{{ $r->transfer_pct }}% ({{ $r->transfer_count }})</small>
                                        </div>
                                    </td>
                                    <td class="small text-muted text-nowrap">
                                        {{ $r->first_recorded ? \Carbon\Carbon::parse($r->first_recorded)->format('Y-m-d H:i') : '—' }}<br>
                                        {{ $r->last_recorded  ? \Carbon\Carbon::parse($r->last_recorded)->format('Y-m-d H:i')  : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($report['threshold'] > 0)
                    <p class="small text-muted mt-3 mb-0">
                        <i class="ti ti-info-circle me-1"></i>
                        Moyenne : {{ number_format($report['mean'], 2, ',', ' ') }} DH ·
                        Seuil outlier (μ + 2σ) : {{ number_format($report['threshold'], 2, ',', ' ') }} DH
                    </p>
                @endif
            @endif
        </div>
    </div>

    {{-- Hour-of-day distribution --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="ti ti-clock text-primary me-1"></i> Distribution par heure (semaine)</h6>
        </div>
        <div class="card-body">
            @php $maxH = max($report['hourly']) ?: 1; @endphp
            <div class="d-flex align-items-end justify-content-between gap-1" style="height: 120px;">
                @foreach($report['hourly'] as $h => $n)
                    @php $height = round(($n / $maxH) * 100); @endphp
                    <div class="d-flex flex-column align-items-center" style="flex: 1; min-width: 0;">
                        <small class="text-muted" style="font-size: 10px;">{{ $n ?: '' }}</small>
                        <div title="{{ $n }} paiement(s) à {{ $h }}h"
                             style="width: 100%; max-width: 24px; height: {{ max(2, $height) }}%;
                                    background: var(--bs-primary); border-radius: 3px 3px 0 0;"></div>
                        <small class="text-muted" style="font-size: 10px;">{{ $h }}h</small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
