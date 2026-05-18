@extends('layouts.main')

@section('title', 'CRM — Activité quotidienne paiements')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Activité quotidienne')

@section('content')
    @include('backoffice.crm.partials._center')

    {{-- Date picker --}}
    <form method="GET" class="card mb-3">
        <div class="card-body py-3 d-flex align-items-end gap-2 flex-wrap">
            <div>
                <label class="form-label small text-muted mb-1">Date (compare cette date vs J-1)</label>
                <input type="date" name="date" value="{{ $report['date'] }}" class="form-control form-control-sm" style="width: 200px;">
            </div>
            <button class="btn btn-sm btn-primary"><i class="ti ti-search me-1"></i> Comparer</button>
            <div class="ms-auto small text-muted">
                <i class="ti ti-info-circle me-1"></i>
                <strong>{{ $report['previous_date'] }}</strong> → <strong>{{ $report['date'] }}</strong>
                · {{ $report['counts']['yesterday'] ?? 0 }} → {{ $report['counts']['today'] ?? 0 }} paiements
            </div>
        </div>
    </form>

    @if(!$report['has_baseline'])
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-1"></i>
            Pas de snapshot pour <strong>{{ $report['previous_date'] }}</strong>. Le diff sera disponible demain après l'exécution du snapshot nocturne (01:30).
            Pour forcer un snapshot manuel : <code>php artisan crm:snapshot-payments --date={{ $report['previous_date'] }}</code>
        </div>
    @else
        {{-- KPI strip --}}
        @php
            $c = $report['counts'];
            $tiles = [
                ['🗑 Supprimés',    $c['deleted']        ?? 0, 'ti-trash',         'danger',   "Disparus depuis hier — 🚨 vérifier"],
                ['💰 Montants modifiés', $c['amount_changed'] ?? 0, 'ti-currency-dollar', 'warning', 'Montant changé entre hier et aujourd\'hui'],
                ['✍ Éditions tardives', $c['late_edits']     ?? 0, 'ti-edit',          'info',    "Modifiés > 24h après création"],
                ['👤 User update changé', $c['user_changed']   ?? 0, 'ti-user-circle',   'secondary','Un autre utilisateur a touché le paiement'],
                ['＋ Créés',       $c['created']        ?? 0, 'ti-plus',          'success', "Nouveaux paiements aujourd'hui"],
            ];
        @endphp
        <div class="row g-3 mb-3">
            @foreach($tiles as [$label, $val, $icon, $color, $hint])
                <div class="col-12 col-sm-6 col-lg">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="small text-muted mb-1">{{ $label }}</p>
                                    <h4 class="mb-0">{{ number_format($val, 0, ',', ' ') }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-{{ $color }} rounded">
                                    <i class="ti {{ $icon }} f-24 text-{{ $color }}"></i>
                                </span>
                            </div>
                            <p class="small text-muted mt-2 mb-0">{{ $hint }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- DELETED payments — top priority --}}
        @if($report['deleted']->isNotEmpty())
            <div class="card mb-3 border-danger shadow-sm">
                <div class="card-header bg-light-danger">
                    <h6 class="mb-0 text-danger">
                        <i class="ti ti-alert-triangle me-1"></i>
                        Paiements supprimés depuis hier — {{ $report['deleted']->count() }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Ces paiements existaient dans le snapshot de <strong>{{ $report['previous_date'] }}</strong> mais n'apparaissent plus aujourd'hui.
                        C'est le signal le plus fort de fraude potentielle (recevoir du cash, puis supprimer la trace).
                    </p>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Réf.</th>
                                    <th>Étudiant</th>
                                    <th>Centre</th>
                                    <th class="text-end">Montant</th>
                                    <th>Méthode</th>
                                    <th>Créé par</th>
                                    <th>Date création</th>
                                    <th>Dernier update</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['deleted'] as $p)
                                    @php
                                        $centerName = app(\App\Services\Crm\CenterContext::class)->nameForStoreId($p->crm_store_id);
                                    @endphp
                                    <tr class="table-danger">
                                        <td><span class="text-muted small">#{{ $p->crm_payment_id }}</span></td>
                                        <td><span class="badge bg-light-secondary text-muted">{{ $p->reference }}</span></td>
                                        <td class="small">{{ $p->payload['STUDENT_FULL_NAME'] ?? '—' }}</td>
                                        <td>
                                            @if($centerName)
                                                <span class="badge bg-light-primary text-primary">{{ $centerName }}</span>
                                            @else
                                                <span class="text-muted small">#{{ $p->crm_store_id }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-medium text-danger">{{ number_format($p->amount, 2, ',', ' ') }} DH</td>
                                        <td class="small">{{ $p->payment_method_name ?? '—' }}</td>
                                        <td class="small">{{ $p->user_creation_full_name ?? '—' }}</td>
                                        <td class="small text-muted text-nowrap">{{ optional($p->date_creation)->format('Y-m-d H:i') }}</td>
                                        <td class="small text-muted text-nowrap">{{ optional($p->date_update)->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('backoffice.crm.insights.payment-history', $p->crm_payment_id) }}"
                                               class="btn btn-sm btn-outline-danger" title="Voir historique">
                                                <i class="ti ti-history"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- AMOUNT CHANGED --}}
        @if($report['amount_changed']->isNotEmpty())
            <div class="card mb-3 border-warning shadow-sm">
                <div class="card-header bg-light-warning">
                    <h6 class="mb-0 text-warning">
                        <i class="ti ti-currency-dollar me-1"></i>
                        Montants modifiés — {{ $report['amount_changed']->count() }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Réf.</th>
                                    <th>Étudiant</th>
                                    <th>Centre</th>
                                    <th class="text-end">Avant</th>
                                    <th class="text-end">Après</th>
                                    <th class="text-end">Δ</th>
                                    <th>Modifié par</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['amount_changed'] as $chg)
                                    @php
                                        $t = $chg['today'];
                                        $centerName = app(\App\Services\Crm\CenterContext::class)->nameForStoreId($t->crm_store_id);
                                        $delta = $chg['delta'];
                                    @endphp
                                    <tr>
                                        <td><span class="text-muted small">#{{ $t->crm_payment_id }}</span></td>
                                        <td><span class="badge bg-light-secondary text-muted">{{ $t->reference }}</span></td>
                                        <td class="small">{{ $t->payload['STUDENT_FULL_NAME'] ?? '—' }}</td>
                                        <td>
                                            @if($centerName)<span class="badge bg-light-primary text-primary">{{ $centerName }}</span>@endif
                                        </td>
                                        <td class="text-end small text-muted">{{ number_format($chg['old_amount'], 2, ',', ' ') }}</td>
                                        <td class="text-end fw-medium">{{ number_format($chg['new_amount'], 2, ',', ' ') }}</td>
                                        <td class="text-end fw-bold text-{{ $delta > 0 ? 'success' : 'danger' }}">
                                            {{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 2, ',', ' ') }}
                                        </td>
                                        <td class="small">{{ $t->user_update_full_name ?? $t->user_creation_full_name ?? '—' }}</td>
                                        <td>
                                            <a href="{{ route('backoffice.crm.insights.payment-history', $t->crm_payment_id) }}"
                                               class="btn btn-sm btn-outline-warning" title="Voir historique">
                                                <i class="ti ti-history"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- LATE EDITS --}}
        @if($report['late_edits']->isNotEmpty())
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-info">
                        <i class="ti ti-edit me-1"></i>
                        Éditions tardives (> 24h après création) — {{ $report['late_edits']->count() }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Paiements modifiés bien après leur création. Pas nécessairement frauduleux, mais à inspecter — surtout si même utilisateur crée + modifie.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Réf.</th>
                                    <th>Étudiant</th>
                                    <th class="text-end">Montant</th>
                                    <th>Créé par</th>
                                    <th>Création</th>
                                    <th>Modifié par</th>
                                    <th>Mise à jour</th>
                                    <th>Délai</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['late_edits'] as $p)
                                    @php
                                        $delayHours = optional($p->date_creation)->diffInHours($p->date_update);
                                        $sameUser = $p->user_creation_id && $p->user_creation_id === $p->user_update_id;
                                    @endphp
                                    <tr class="{{ $sameUser ? 'table-warning' : '' }}">
                                        <td><span class="text-muted small">#{{ $p->crm_payment_id }}</span></td>
                                        <td><span class="badge bg-light-secondary text-muted">{{ $p->reference }}</span></td>
                                        <td class="small">{{ $p->payload['STUDENT_FULL_NAME'] ?? '—' }}</td>
                                        <td class="text-end small">{{ number_format($p->amount, 2, ',', ' ') }}</td>
                                        <td class="small">{{ $p->user_creation_full_name ?? '—' }}</td>
                                        <td class="small text-muted text-nowrap">{{ optional($p->date_creation)->format('Y-m-d H:i') }}</td>
                                        <td class="small">
                                            {{ $p->user_update_full_name ?? '—' }}
                                            @if($sameUser) <i class="ti ti-alert-triangle text-warning ms-1" title="Même utilisateur que la création"></i> @endif
                                        </td>
                                        <td class="small text-muted text-nowrap">{{ optional($p->date_update)->format('Y-m-d H:i') }}</td>
                                        <td class="small">{{ round($delayHours) }}h</td>
                                        <td>
                                            <a href="{{ route('backoffice.crm.insights.payment-history', $p->crm_payment_id) }}"
                                               class="btn btn-sm btn-outline-info" title="Voir historique">
                                                <i class="ti ti-history"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- All-clear --}}
        @if($report['deleted']->isEmpty() && $report['amount_changed']->isEmpty() && $report['late_edits']->isEmpty())
            <div class="alert alert-success">
                <i class="ti ti-circle-check me-1"></i>
                Aucune anomalie détectée entre <strong>{{ $report['previous_date'] }}</strong> et <strong>{{ $report['date'] }}</strong>. ✅
            </div>
        @endif
    @endif
@endsection
