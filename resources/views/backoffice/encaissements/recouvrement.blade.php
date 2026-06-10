@extends('layouts.main')

@section('title', 'État de recouvrement')
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-active', 'Recouvrement')

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header"><strong class="me-auto">Recouvrement</strong></div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto"><label class="form-label fw-semibold"><i class="ph-duotone ph-chart-bar me-1"></i> État de recouvrement</label></div>
                <div class="col">
                    <label class="form-label small text-muted mb-1">Mois de fin</label>
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}" onchange="this.form.submit()">
                </div>
                <div class="col">
                    <label class="form-label small text-muted mb-1">Période prime</label>
                    <select name="period_months" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="1" {{ $periodMonths == 1 ? 'selected' : '' }}>1 mois</option>
                        <option value="3" {{ $periodMonths == 3 ? 'selected' : '' }}>3 mois (trimestre)</option>
                        <option value="6" {{ $periodMonths == 6 ? 'selected' : '' }}>6 mois (semestre)</option>
                        <option value="12" {{ $periodMonths == 12 ? 'selected' : '' }}>12 mois (année)</option>
                    </select>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backoffice.encaissements.impayes.imports.create') }}" class="btn btn-outline-warning btn-sm">
                        <i class="ph-duotone ph-upload me-1"></i> Importer Impayés
                    </a>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backoffice.encaissements.impayes.imports.index') }}" class="btn btn-outline-info btn-sm">
                        <i class="ph-duotone ph-list me-1"></i> Historique
                    </a>
                </div>
            </form>

            {{-- Period explanation --}}
            @php
                $periodLabel = $states[0]['period_label'] ?? '';
                $snapshotDate = $states[0]['snapshot_date'] ?? null;
            @endphp
            @if($periodLabel)
                <div class="mt-2 small text-muted">
                    <i class="ph-duotone ph-info me-1"></i>
                    <strong>Logique :</strong>
                    Encaissements = somme sur
                    <strong class="text-primary">{{ $periodLabel }}</strong>
                    ({{ $periodMonths }} mois)
                    · Impayés = dernier snapshot CRM
                    @if($snapshotDate) (au <strong>{{ $snapshotDate }}</strong>) @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Global KPIs --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-light-success">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total Encaissé</h6>
                    <h3 class="text-success mb-0">{{ number_format($totalEncaissement, 0, ',', ' ') }} <small>DH</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light-danger">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Reste à Recouvrir</h6>
                    <h3 class="text-danger mb-0">{{ number_format($totalImpaye, 0, ',', ' ') }} <small>DH</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light-primary">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Chiffre d'affaires</h6>
                    <h3 class="text-primary mb-0">{{ number_format($totalCA, 0, ',', ' ') }} <small>DH</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card {{ $globalRate >= 70 ? 'bg-light-success' : ($globalRate >= 50 ? 'bg-light-warning' : 'bg-light-danger') }}">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Taux de Recouvrement</h6>
                    <h3 class="{{ $globalRate >= 70 ? 'text-success' : ($globalRate >= 50 ? 'text-warning' : 'text-danger') }} mb-0">
                        {{ $globalRate }}%
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart État de recouvrement --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="ph-duotone ph-chart-bar me-1"></i>
                État de recouvrement par centre —
                <span class="text-primary">{{ $states[0]['period_label'] ?? '' }}</span>
                <small class="text-muted">({{ $periodMonths }} mois)</small>
            </h5>
        </div>
        <div class="card-body">
            <div id="recouvrementChart" style="height: 380px;"></div>
        </div>
    </div>

    {{-- Tableau détaillé par centre + primes suggérées --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                Détail par centre & primes suggérées
                <small class="text-muted">— période de {{ $periodMonths }} mois</small>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Centre</th>
                            <th class="text-end">Encaissé ({{ $periodMonths }}m)</th>
                            <th class="text-end">Montant à Recouvrer</th>
                            <th class="text-end">CA total</th>
                            <th class="text-center">% impayés</th>
                            <th class="text-center">Taux recouvrement</th>
                            <th class="text-end">Prime suggérée</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($states as $state)
                            @php
                                $sug = $suggestions[$state['site_id']] ?? null;
                                $hasData = $state['ca'] > 0;
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $state['site_short'] }}</td>
                                <td class="text-end text-success fw-semibold">{{ number_format($state['encaissement'], 0, ',', ' ') }} DH</td>
                                <td class="text-end text-danger fw-semibold">{{ number_format($state['impaye'], 0, ',', ' ') }} DH</td>
                                <td class="text-end fw-semibold">{{ number_format($state['ca'], 0, ',', ' ') }} DH</td>
                                <td class="text-center">
                                    <span class="badge bg-light-{{ $state['unpaid_rate'] >= 50 ? 'danger' : ($state['unpaid_rate'] >= 30 ? 'warning' : 'success') }}">
                                        {{ $state['unpaid_rate'] }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($hasData)
                                        <span class="badge bg-{{ $state['collection_rate'] >= 70 ? 'success' : ($state['collection_rate'] >= 50 ? 'warning' : 'danger') }}">
                                            {{ $state['collection_rate'] }}%
                                        </span>
                                    @else — @endif
                                </td>
                                <td class="text-end">
                                    @if($sug && $sug['eligible'])
                                        <span class="fw-bold text-primary">{{ number_format($sug['total_prime'], 0, ',', ' ') }} DH</span>
                                    @elseif($hasData)
                                        <span class="text-muted"><i class="ph-duotone ph-x"></i> Non éligible</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($sug && $sug['eligible'])
                                        <form method="POST" action="{{ route('backoffice.encaissements.recouvrement.generate') }}" class="d-inline"
                                              onsubmit="return confirm('Générer prime de {{ number_format($sug['total_prime'], 0, ',', ' ') }} DH sur {{ $periodMonths }} mois pour ce centre ?');">
                                            @csrf
                                            <input type="hidden" name="site_id" value="{{ $state['site_id'] }}">
                                            <input type="hidden" name="month" value="{{ $month }}">
                                            <input type="hidden" name="period_months" value="{{ $periodMonths }}">
                                            <button class="btn btn-sm btn-primary" title="Générer primes {{ $periodMonths }} mois">
                                                <i class="ph-duotone ph-trophy me-1"></i> Générer
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td>Total</td>
                            <td class="text-end text-success">{{ number_format($totalEncaissement, 0, ',', ' ') }} DH</td>
                            <td class="text-end text-danger">{{ number_format($totalImpaye, 0, ',', ' ') }} DH</td>
                            <td class="text-end">{{ number_format($totalCA, 0, ',', ' ') }} DH</td>
                            <td class="text-center">
                                <span class="badge bg-light-info">{{ $totalCA > 0 ? round(($totalImpaye/$totalCA)*100, 1) : 0 }}%</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $globalRate }}%</span>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Info box about prime rules --}}
    <div class="alert alert-info mt-3">
        <h6 class="mb-2"><i class="ph-duotone ph-info me-1"></i> Logique de calcul</h6>
        <ol class="mb-0 small">
            <li><strong>Encaissement de la période</strong> = somme des encaissements sur {{ $periodMonths }} mois (se terminant au mois sélectionné)</li>
            <li><strong>Impayés</strong> = dernier snapshot CRM (cumulatif jusqu'à la date d'échéance)</li>
            <li><strong>CA (Chiffre d'affaires)</strong> = Encaissement + Impayés</li>
            <li><strong>Taux de recouvrement</strong> = Encaissement / CA × 100</li>
            <li>Si Taux ≥ seuil (configurable), prime = (taux - seuil) × montant_par_point</li>
            <li>La prime totale est répartie entre les employés éligibles (Réception, Commercial, Coordination)</li>
        </ol>
        <div class="mt-2">
            <a href="{{ route('backoffice.encaissements.primes.config') }}" class="btn btn-sm btn-outline-primary">
                <i class="ph-duotone ph-gear me-1"></i> Modifier la configuration des primes
            </a>
        </div>
    </div>

@endsection

@section('scripts')
<script type="application/json" id="recouvrement-data">@json($states)</script>
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/backoffice/encaissements-recouvrement.js') }}"></script>
@endsection
