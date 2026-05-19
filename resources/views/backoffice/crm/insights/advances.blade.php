@extends('layouts.main')

@section('title', 'CRM — Avances')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Avances')

@section('content')
    @include('backoffice.crm.partials._center')

    {{-- Filter form --}}
    <form method="GET" class="card mb-3">
        <div class="card-body py-3">
            <div class="d-flex align-items-center mb-2">
                <h6 class="mb-0 small text-muted text-uppercase">
                    <i class="ti ti-filter me-1"></i> Filtres
                </h6>
            </div>
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small mb-1 text-muted">Du</label>
                    <input type="date" name="startDate" value="{{ $startDate }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small mb-1 text-muted">Au</label>
                    <input type="date" name="endDate" value="{{ $endDate }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-auto">
                    <div class="form-check mt-3">
                        <input type="checkbox" name="showDuplicates" value="1" id="showDuplicates"
                            class="form-check-input" {{ ($showDupes ?? false) ? 'checked' : '' }}>
                        <label for="showDuplicates" class="form-check-label small">Inclure doublons</label>
                    </div>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ti ti-search me-1"></i> Appliquer
                    </button>
                    <a href="{{ route('backoffice.crm.insights.advances', ['refresh' => 1] + array_filter(['startDate' => $startDate, 'endDate' => $endDate, 'showDuplicates' => ($showDupes ?? false) ? 1 : null])) }}"
                       class="btn btn-sm btn-outline-secondary" title="Vider le cache et recharger">
                        <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </div>
        </div>
    </form>

    @if(!empty($report['error']))
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-1"></i>
            L'analyse s'est arrêtée prématurément : {{ $report['error'] }}
        </div>
    @endif

    @if(!empty($report['duplicates']))
        <div class="alert alert-warning border-0 shadow-sm">
            <div class="d-flex align-items-start gap-3">
                <i class="ti ti-copy text-warning f-24"></i>
                <div>
                    <div class="fw-medium">{{ count($report['duplicates']) }} avance(s) en doublon détectées et masquées</div>
                    <p class="small text-muted mb-0">
                        Des paiements partagent le même étudiant + montant + date effective. Le compteur n'affiche que les avances uniques.
                        Probables doublons à fusionner dans Homeschool.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- KPI strip --}}
    @php
        $tiles = [
            ['Avances trouvées', count($report['advances']),       'ti-cash-banknote',     'warning', "Paiements avec IS_AVANCE = Y"],
            ['Montant total',    $report['total_amount'],          'ti-currency-dollar',   'primary', "Cumul des avances trouvées"],
            ['Encaisseurs',      count($report['by_handler']),     'ti-users',             'info',    "Personnes ayant enregistré une avance"],
            ['Lignes scannées',  $report['scanned'],               'ti-database',          'secondary',"Total de paiements parcourus"],
        ];
    @endphp
    <div class="row g-3 mb-3">
        @foreach($tiles as $i => [$label, $val, $icon, $color, $hint])
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="small text-muted mb-1">{{ $label }}</p>
                                <h4 class="mb-0">
                                    @if($i === 1)
                                        {{ number_format($val, 2, ',', ' ') }} <small class="text-muted">DH</small>
                                    @else
                                        {{ number_format($val, 0, ',', ' ') }}
                                    @endif
                                </h4>
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

    {{-- Per-handler breakdown --}}
    @if(!empty($report['by_handler']))
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="ti ti-user-circle text-primary me-1"></i>
                    Avances par encaisseur
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Encaisseur</th>
                                <th class="text-end">Avances</th>
                                <th class="text-end">Montant cumulé</th>
                                <th>Part</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $maxAmount = max(array_column($report['by_handler'], 'amount')) ?: 1; @endphp
                            @foreach($report['by_handler'] as $handler => $stats)
                                @php $pct = round($stats['amount'] / $maxAmount * 100); @endphp
                                <tr>
                                    <td class="fw-medium">{{ $handler }}</td>
                                    <td class="text-end">{{ number_format($stats['count'], 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($stats['amount'], 2, ',', ' ') }} DH</td>
                                    <td>
                                        <div class="progress" style="height: 6px; min-width: 80px;">
                                            <div class="progress-bar bg-warning" style="width: {{ $pct }}%;"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Main advances table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="ti ti-cash-banknote text-warning me-1"></i>
                Liste des avances
            </h6>
        </div>
        <div class="card-body">
            @if(empty($report['advances']))
                <div class="alert alert-info mb-0">
                    <i class="ti ti-info-circle me-1"></i>
                    Aucune avance trouvée sur la période sélectionnée.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Réf.</th>
                                <th>Date effective</th>
                                <th>Étudiant</th>
                                <th>Centre</th>
                                <th class="text-end">Montant</th>
                                <th class="text-end">Reste</th>
                                <th>Méthode</th>
                                <th>Type</th>
                                <th>Encaisseur</th>
                                <th>Caisse</th>
                                <th>Créé le</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $centerCtx = app(\App\Services\Crm\CenterContext::class);
                            @endphp
                            @foreach($report['advances'] as $a)
                                @php
                                    $centerName = $centerCtx->nameForStoreId($a['STR_STORE_ID'] ?? null);
                                    try { $createdF = $a['DATE_CREATION'] ? \Carbon\Carbon::parse($a['DATE_CREATION'])->format('Y-m-d H:i') : '—'; }
                                    catch (\Throwable $e) { $createdF = $a['DATE_CREATION'] ?? '—'; }
                                    try { $effF = $a['EFFECTIVE_DATE'] ? \Carbon\Carbon::parse($a['EFFECTIVE_DATE'])->format('Y-m-d') : '—'; }
                                    catch (\Throwable $e) { $effF = $a['EFFECTIVE_DATE'] ?? '—'; }
                                @endphp
                                <tr>
                                    <td><span class="text-muted small">#{{ $a['ID'] ?? '—' }}</span></td>
                                    <td><span class="badge bg-light-secondary text-muted">{{ $a['REFERENCE'] ?? '—' }}</span></td>
                                    <td class="small">{{ $effF }}</td>
                                    <td class="small">{{ $a['STUDENT_FULL_NAME'] ?? '—' }}</td>
                                    <td>
                                        @if($centerName)
                                            <span class="badge bg-light-primary text-primary">{{ $centerName }}</span>
                                        @else
                                            <span class="text-muted small">#{{ $a['STR_STORE_ID'] ?? '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-medium text-warning">
                                        {{ number_format((float) ($a['AMOUNT'] ?? 0), 2, ',', ' ') }}
                                    </td>
                                    <td class="text-end small {{ ((float)($a['OPEN_AMOUNT'] ?? 0)) > 0 ? 'text-danger' : 'text-muted' }}">
                                        {{ number_format((float) ($a['OPEN_AMOUNT'] ?? 0), 2, ',', ' ') }}
                                    </td>
                                    <td class="small">{{ $a['PAYMENT_METHOD_NAME'] ?? '—' }}</td>
                                    <td class="small">{{ $a['PAYMENT_TYPE_NAME'] ?? '—' }}</td>
                                    <td class="small">{{ $a['USER_CREATION_FULL_NAME'] ?? '—' }}</td>
                                    <td class="small text-muted">{{ $a['CASH_BOX_ACCOUNT_DESIGNATION'] ?? '—' }}</td>
                                    <td class="small text-muted">{{ $createdF }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="small text-muted mt-2 mb-0">
                    <i class="ti ti-info-circle me-1"></i>
                    Données mises en cache 5 minutes. Cliquez sur <i class="ti ti-refresh"></i> pour forcer un rafraîchissement.
                </p>
            @endif
        </div>
    </div>
@endsection
