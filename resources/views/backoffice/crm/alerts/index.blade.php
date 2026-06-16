@extends('layouts.main')

@section('title', 'CRM — Alertes & Notifications')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Alertes')

@section('content')
    @include('backoffice.crm.partials._center')

    {{-- Header bar --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-0"><i class="ti ti-bell-ringing text-warning me-2"></i>Alertes & Notifications CRM</h5>
            @if($lastDetected)
                <p class="small text-muted mb-0">Dernière détection : {{ \Carbon\Carbon::parse($lastDetected)->setTimezone('Africa/Casablanca')->format('d/m/Y H:i') }}</p>
            @else
                <p class="small text-muted mb-0">Aucune détection effectuée — lancez <code>php artisan crm:generate-alerts</code></p>
            @endif
        </div>
        <form method="POST" action="{{ route('backoffice.crm.alerts.generate') }}">
            @csrf
            <button class="btn btn-sm btn-warning">
                <i class="ti ti-refresh me-1"></i> Détecter maintenant
            </button>
        </form>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <i class="ti ti-circle-check me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI strip --}}
    @php
        $c = $summary['counts'];
        $tiles = [
            ['Alertes actives',    $c['total']            ?? 0, 'ti-bell',           'primary',   ''],
            ['Haute priorité',     $c['high_count']       ?? 0, 'ti-alert-triangle', 'danger',    'high + critical'],
            ['Impayés >30j',       $c['unpaid_count']     ?? 0, 'ti-report-money',   'warning',   ''],
            ['Absences',           $c['absent_count']     ?? 0, 'ti-user-off',       'info',      ''],
            ['Présence faible',    $c['attendance_count'] ?? 0, 'ti-chart-bar',      'secondary', ''],
            ['Groupes fin de vie', $c['group_count']      ?? 0, 'ti-calendar-x',     'dark',      ''],
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach($tiles as [$label, $val, $icon, $color, $hint])
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3 px-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="avtar avtar-s bg-light-{{ $color }} rounded">
                                <i class="ti {{ $icon }} f-18 text-{{ $color }}"></i>
                            </span>
                            <h4 class="mb-0 text-{{ $color }}">{{ number_format($val, 0, ',', ' ') }}</h4>
                        </div>
                        <p class="small text-muted mb-0">{{ $label }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="card mb-3">
        <div class="card-body py-3 d-flex align-items-end gap-2 flex-wrap">
            <div>
                <label class="form-label small text-muted mb-1">Statut</label>
                <select name="status" class="form-select form-select-sm" style="width:150px">
                    <option value="open"       {{ $filterStatus === 'open'        ? 'selected' : '' }}>Ouvert</option>
                    <option value="in_progress"{{ $filterStatus === 'in_progress' ? 'selected' : '' }}>En cours</option>
                    <option value="resolved"   {{ $filterStatus === 'resolved'    ? 'selected' : '' }}>Résolu</option>
                    <option value="dismissed"  {{ $filterStatus === 'dismissed'   ? 'selected' : '' }}>Ignoré</option>
                    <option value="all"        {{ $filterStatus === 'all'         ? 'selected' : '' }}>Tous</option>
                </select>
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Type</label>
                <select name="type" class="form-select form-select-sm" style="width:180px">
                    <option value="">— Tous les types —</option>
                    @foreach($alertTypes as $key => $label)
                        <option value="{{ $key }}" {{ $filterType === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Sévérité</label>
                <select name="severity" class="form-select form-select-sm" style="width:140px">
                    <option value="">— Toutes —</option>
                    @foreach($severities as $sev)
                        <option value="{{ $sev }}" {{ $filterSeverity === $sev ? 'selected' : '' }}>{{ ucfirst($sev) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-sm btn-primary"><i class="ti ti-search me-1"></i> Filtrer</button>
            <a href="{{ route('backoffice.crm.alerts.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-x"></i> Reset
            </a>
        </div>
    </form>

    {{-- Alerts table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($summary['rows']->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-circle-check f-48 text-success d-block mb-2"></i>
                    Aucune alerte active pour les filtres sélectionnés.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:90px">Sévérité</th>
                                <th>Type</th>
                                <th>Titre</th>
                                <th>Centre</th>
                                <th>Détecté le</th>
                                <th style="width:110px">Statut</th>
                                <th style="width:140px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary['rows'] as $alert)
                                @php
                                    $color = $alert->severityColor();
                                    $centerName = $alert->crm_store_id
                                        ? app(\App\Services\Crm\CenterContext::class)->nameForStoreId($alert->crm_store_id)
                                        : null;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge bg-light-{{ $color }} text-{{ $color }}">
                                            {{ ucfirst($alert->severity) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-muted small">
                                            {{ $alert->typeLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">{{ $alert->title }}</div>
                                        @if($alert->message)
                                            <div class="small text-muted text-truncate" style="max-width:340px" title="{{ $alert->message }}">
                                                {{ $alert->message }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if($centerName)
                                            <span class="badge bg-light-primary text-primary">{{ $centerName }}</span>
                                        @elseif($alert->crm_store_id)
                                            <span class="text-muted">#{{ $alert->crm_store_id }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted text-nowrap">
                                        {{ optional($alert->detected_at)->setTimezone('Africa/Casablanca')->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'open'        => 'warning',
                                                'in_progress' => 'info',
                                                'resolved'    => 'success',
                                                'dismissed'   => 'secondary',
                                            ];
                                            $statusLabels = [
                                                'open'        => 'Ouvert',
                                                'in_progress' => 'En cours',
                                                'resolved'    => 'Résolu',
                                                'dismissed'   => 'Ignoré',
                                            ];
                                        @endphp
                                        <span class="badge bg-light-{{ $statusColors[$alert->status] ?? 'secondary' }} text-{{ $statusColors[$alert->status] ?? 'secondary' }}">
                                            {{ $statusLabels[$alert->status] ?? $alert->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if(in_array($alert->status, ['open']))
                                                <form method="POST" action="{{ route('backoffice.crm.alerts.acknowledge', $alert) }}">
                                                    @csrf
                                                    <button class="btn btn-xs btn-outline-info" title="En cours">
                                                        <i class="ti ti-clock"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if(in_array($alert->status, ['open', 'in_progress']))
                                                <form method="POST" action="{{ route('backoffice.crm.alerts.resolve', $alert) }}">
                                                    @csrf
                                                    <button class="btn btn-xs btn-outline-success" title="Résoudre">
                                                        <i class="ti ti-check"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('backoffice.crm.alerts.dismiss', $alert) }}">
                                                    @csrf
                                                    <button class="btn btn-xs btn-outline-secondary" title="Ignorer">
                                                        <i class="ti ti-x"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($summary['rows']->hasPages())
                    <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-2 gap-2">
                        <small class="text-muted">
                            {{ $summary['rows']->firstItem() }}–{{ $summary['rows']->lastItem() }}
                            sur {{ number_format($summary['rows']->total(), 0, ',', ' ') }} alerte(s)
                        </small>
                        {{ $summary['rows']->onEachSide(1)->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Legend --}}
    <div class="mt-3 small text-muted">
        <i class="ti ti-info-circle me-1"></i>
        Les alertes sont générées automatiquement depuis les données locales (absence, impayés, chèques, présence, groupes).
        Aucun appel API en temps réel. Données fraîches après chaque synchronisation CRM.
    </div>
@endsection
