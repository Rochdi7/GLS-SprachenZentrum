@extends('layouts.main')

@section('title', 'CRM — Historique du paiement')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Historique paiement')

@section('content')
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('backoffice.crm.insights.payment-activity') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Retour à l'activité quotidienne
        </a>
        <div class="ms-3">
            <h5 class="mb-0">
                <i class="ti ti-history text-primary me-1"></i>
                Historique du paiement #{{ $report['payment_id'] }}
            </h5>
        </div>
        <div class="ms-auto">
            @include('backoffice.crm.partials._resync_button')
        </div>
    </div>

    @if($report['not_found'])
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-1"></i>
            Aucun snapshot trouvé pour le paiement <strong>#{{ $report['payment_id'] }}</strong>.
            Soit l'identifiant est invalide, soit ce paiement n'est jamais passé par le snapshot quotidien.
        </div>
    @else
        @php
            $first = $report['versions']->first();
            $last  = $report['versions']->last();
            $centerName = app(\App\Services\Crm\CenterContext::class)->nameForStoreId($last->crm_store_id);
        @endphp

        {{-- Status banner --}}
        @if($report['is_deleted'])
            <div class="alert alert-danger">
                <i class="ti ti-trash me-1"></i>
                <strong>Ce paiement a été supprimé.</strong>
                Dernier snapshot : <strong>{{ $report['last_seen'] }}</strong>.
                Plus présent dans l'API depuis. 🚨 À investiguer.
            </div>
        @endif

        {{-- Summary card --}}
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body">
                <div class="row g-2 small">
                    <div class="col-12 col-md-3">
                        <div class="text-muted">Référence</div>
                        <div class="fw-medium">{{ $last->reference ?? '—' }}</div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="text-muted">Étudiant</div>
                        <div class="fw-medium">{{ $last->payload['STUDENT_FULL_NAME'] ?? '—' }}</div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="text-muted">Centre</div>
                        <div>
                            @if($centerName)
                                <span class="badge bg-light-primary text-primary">{{ $centerName }}</span>
                            @else
                                <span class="text-muted small">#{{ $last->crm_store_id }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="text-muted">Montant actuel</div>
                        <div class="fw-medium">{{ number_format($last->amount, 2, ',', ' ') }} DH</div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="text-muted">Créé par</div>
                        <div>{{ $first->user_creation_full_name ?? '—' }}</div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="text-muted">Date création (API)</div>
                        <div>{{ optional($first->date_creation)->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="text-muted">Dernier modif. par</div>
                        <div>{{ $last->user_update_full_name ?? '—' }}</div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="text-muted">Dernière modif. (API)</div>
                        <div>{{ optional($last->date_update)->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="ti ti-timeline-event me-1"></i>
                    Chronologie des snapshots — {{ $report['versions']->count() }} versions
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @php
                        $labelMap = [
                            'amount' => 'Montant',
                            'effective_date' => 'Date effective',
                            'payment_method_name' => 'Méthode',
                            'payment_method_id'   => 'Méthode (id)',
                            'payment_type_name'   => 'Type',
                            'payment_type_id'     => 'Type (id)',
                            'student_id'          => 'Student ID',
                            'reference'           => 'Référence',
                            'user_creation_id'    => 'Créé par (id)',
                            'user_creation_full_name' => 'Créé par',
                            'user_update_id'      => 'Modifié par (id)',
                            'user_update_full_name' => 'Modifié par',
                            'date_creation'       => 'Date création',
                            'date_update'         => 'Date update',
                        ];
                    @endphp

                    @foreach($report['versions'] as $i => $v)
                        <div class="d-flex mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="me-3 text-center" style="min-width: 100px;">
                                <div class="small text-muted">Snapshot</div>
                                <div class="fw-medium">{{ \Carbon\Carbon::parse($v->snapshot_date)->format('Y-m-d') }}</div>
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($v->snapshot_date)->locale('fr')->dayName }}</div>
                                @if($v->is_first)
                                    <span class="badge bg-light-primary text-primary mt-1">v1 (origine)</span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap gap-3 small mb-2">
                                    <div><span class="text-muted">Montant :</span> <strong>{{ number_format($v->amount, 2, ',', ' ') }} DH</strong></div>
                                    <div><span class="text-muted">Méthode :</span> {{ $v->payment_method_name ?? '—' }}</div>
                                    <div><span class="text-muted">Type :</span> {{ $v->payment_type_name ?? '—' }}</div>
                                    <div><span class="text-muted">Modifié par :</span> {{ $v->user_update_full_name ?? '—' }}</div>
                                </div>

                                @if(!empty($v->change_set))
                                    <div class="border rounded bg-light-warning p-2">
                                        <div class="small fw-medium text-warning mb-1">
                                            <i class="ti ti-arrows-right-left me-1"></i>
                                            Changements vs snapshot précédent ({{ count($v->change_set) }})
                                        </div>
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr class="small">
                                                    <th>Champ</th>
                                                    <th class="text-end">Avant</th>
                                                    <th></th>
                                                    <th>Après</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($v->change_set as $field => $diff)
                                                    <tr class="small">
                                                        <td>{{ $labelMap[$field] ?? $field }}</td>
                                                        <td class="text-end text-muted">
                                                            {{ is_null($diff['old']) || $diff['old'] === '' ? '—' : $diff['old'] }}
                                                        </td>
                                                        <td class="text-center text-muted">→</td>
                                                        <td class="fw-medium">
                                                            {{ is_null($diff['new']) || $diff['new'] === '' ? '—' : $diff['new'] }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @elseif(!$v->is_first)
                                    <div class="small text-muted">Aucun changement vs snapshot précédent.</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection
