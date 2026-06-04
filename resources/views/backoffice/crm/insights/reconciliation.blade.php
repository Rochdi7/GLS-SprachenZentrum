@extends('layouts.main')

@section('title', 'CRM — Réconciliation quotidienne')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Réconciliation')

@section('content')
    {{-- Range picker --}}
    <form method="GET" class="card mb-3">
        <div class="card-body py-3 d-flex align-items-end gap-2 flex-wrap">
            <div>
                <label class="form-label small text-muted mb-1">Jours en arrière</label>
                <input type="number" name="days" value="{{ $report['days'] }}" min="1" max="60" class="form-control form-control-sm" style="width: 100px;">
            </div>
            <button class="btn btn-sm btn-primary"><i class="ti ti-search me-1"></i> Afficher</button>
            <div class="ms-auto small text-muted">
                Période : <strong>{{ $report['start_date'] }}</strong> → <strong>{{ $report['end_date'] }}</strong>
                — z-score &gt; 2 signale une journée hors norme
            </div>
            @include('backoffice.crm.partials._sync_badge')
        </div>
    </form>

    @if(empty($report['series']))
        <div class="alert alert-info">
            Aucun snapshot disponible. Exécutez <code>php artisan crm:snapshot-payments</code> pour générer les données initiales.
        </div>
    @endif

    @foreach($report['series'] as $center)
        @php $site = $center['site']; @endphp
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-header bg-light d-flex align-items-center justify-content-between">
                <h6 class="mb-0">
                    <i class="ti ti-building text-primary me-1"></i>
                    {{ $site?->name ?? 'Centre inconnu' }}
                    @if($site)
                        <span class="text-muted small ms-2">#{{ $site->crm_store_id }}</span>
                    @endif
                </h6>
                <small class="text-muted">
                    Moyenne / jour : {{ number_format($center['mean'], 2, ',', ' ') }} DH
                    · σ {{ number_format($center['std'], 2, ',', ' ') }}
                </small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Paiements</th>
                                <th class="text-end">Espèces</th>
                                <th class="text-end">Chèques</th>
                                <th class="text-end">Virements</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">z-score</th>
                                <th>État</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($center['rows'] as $d)
                                <tr class="{{ $d->is_anomaly ? 'table-warning' : '' }}">
                                    <td>{{ \Carbon\Carbon::parse($d->snapshot_date)->format('Y-m-d (D)') }}</td>
                                    <td class="text-end">{{ $d->n }}</td>
                                    <td class="text-end">{{ number_format($d->cash, 2, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($d->cheque, 2, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($d->transfer, 2, ',', ' ') }}</td>
                                    <td class="text-end fw-medium">{{ number_format($d->total, 2, ',', ' ') }}</td>
                                    <td class="text-end small">{{ $d->z >= 0 ? '+' : '' }}{{ $d->z }}</td>
                                    <td>
                                        @if($d->is_anomaly)
                                            <span class="badge bg-warning text-dark">
                                                <i class="ti ti-alert-triangle me-1"></i> Anormal
                                            </span>
                                        @else
                                            <span class="badge bg-light-success text-success">
                                                <i class="ti ti-check me-1"></i> Normal
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endsection
