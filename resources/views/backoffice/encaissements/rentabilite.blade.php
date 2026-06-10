@extends('layouts.main')

@section('title', 'Rentabilité')
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-active', 'Rentabilité')

@section('content')

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-sm-auto">
                    <label class="form-label fw-semibold"><i class="ph-duotone ph-chart-line-up me-1"></i> Rentabilité</label>
                </div>
                <div class="col-12 col-sm">
                    <select name="site_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Sélectionner un centre --</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ $siteId == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm">
                    <input type="month" name="month" class="form-control" value="{{ $month }}" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    @if($rentabilite)
        {{-- KPI Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-light-success">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">Recettes</h6>
                        <h3 class="text-success mb-0">{{ number_format($rentabilite['revenue'], 0, ',', ' ') }} <small>DH</small></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light-danger">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">Charges totales</h6>
                        <h3 class="text-danger mb-0">{{ number_format($rentabilite['charges']['total'], 0, ',', ' ') }} <small>DH</small></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card {{ $rentabilite['margin'] >= 0 ? 'bg-light-primary' : 'bg-light-danger' }}">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">Marge</h6>
                        <h3 class="{{ $rentabilite['margin'] >= 0 ? 'text-primary' : 'text-danger' }} mb-0">
                            {{ number_format($rentabilite['margin'], 0, ',', ' ') }} <small>DH</small>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card {{ $rentabilite['margin_rate'] >= 0 ? 'bg-light-info' : 'bg-light-danger' }}">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">Taux rentabilité</h6>
                        <h3 class="{{ $rentabilite['margin_rate'] >= 0 ? 'text-info' : 'text-danger' }} mb-0">
                            {{ $rentabilite['margin_rate'] }}%
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart + Charges breakdown --}}
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0"><i class="ph-duotone ph-chart-line me-1"></i> Recettes vs Dépenses (12 mois)</h5></div>
                    <div class="card-body">
                        <div id="rentabiliteChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0">Détail des charges</h5></div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td><i class="ph-duotone ph-buildings me-1 text-muted"></i> Charges fixes</td>
                                    <td class="text-end fw-semibold">{{ number_format($rentabilite['charges']['expenses'], 0, ',', ' ') }} DH</td>
                                </tr>
                                <tr>
                                    <td><i class="ph-duotone ph-chalkboard-teacher me-1 text-muted"></i> Paiements profs</td>
                                    <td class="text-end fw-semibold">{{ number_format($rentabilite['charges']['teacher_payments'], 0, ',', ' ') }} DH</td>
                                </tr>
                                <tr>
                                    <td><i class="ph-duotone ph-trophy me-1 text-muted"></i> Primes</td>
                                    <td class="text-end fw-semibold">{{ number_format($rentabilite['charges']['primes'], 0, ',', ' ') }} DH</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td>Total charges</td>
                                    <td class="text-end text-danger">{{ number_format($rentabilite['charges']['total'], 0, ',', ' ') }} DH</td>
                                </tr>
                            </tfoot>
                        </table>
                        <div id="chargesDonut" class="mt-3" style="height: 200px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- History table --}}
        @if(!empty($history))
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Évolution (6 mois)</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Mois</th>
                                <th class="text-end">Recettes</th>
                                <th class="text-end">Charges</th>
                                <th class="text-end">Marge</th>
                                <th class="text-end">Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $h)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($h['month'] . '-01')->translatedFormat('M Y') }}</td>
                                <td class="text-end">{{ number_format($h['revenue'], 0, ',', ' ') }} DH</td>
                                <td class="text-end">{{ number_format($h['charges']['total'], 0, ',', ' ') }} DH</td>
                                <td class="text-end fw-semibold {{ $h['margin'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($h['margin'], 0, ',', ' ') }} DH
                                </td>
                                <td class="text-end">
                                    <span class="badge {{ $h['margin_rate'] >= 0 ? 'bg-light-success' : 'bg-light-danger' }}">
                                        {{ $h['margin_rate'] }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    @elseif(!$siteId)
        <div class="alert alert-info">
            <i class="ph-duotone ph-info me-2"></i> Sélectionnez un centre pour voir sa rentabilité détaillée.
        </div>
    @endif

    {{-- Multi-site comparison --}}
    @if(!empty($comparison))
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Comparaison des centres — {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</h5></div>
        <div class="card-body">
            <div id="sitesCompareChart" style="height: 300px;" class="mb-3"></div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Centre</th>
                            <th class="text-end">Recettes</th>
                            <th class="text-end">Opérations</th>
                            <th class="text-end">Jours actifs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comparison as $i => $row)
                        <tr>
                            <td>
                                @if($i === 0) <span class="badge bg-warning">1</span>
                                @elseif($i === 1) <span class="badge bg-secondary">2</span>
                                @elseif($i === 2) <span class="badge bg-info">3</span>
                                @else {{ $i + 1 }}
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $row['site_name'] }}</td>
                            <td class="text-end fw-bold">{{ number_format($row['total_revenue'], 0, ',', ' ') }} DH</td>
                            <td class="text-end">{{ $row['total_operations'] }}</td>
                            <td class="text-end">{{ $row['active_days'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

@endsection

@section('scripts')
<script type="application/json" id="rentabilite-data">
{
    "hasEvo": {{ ($rentabilite && !empty($monthlyEvolution)) ? 'true' : 'false' }},
    "evo": @json($monthlyEvolution ?? []),
    "charges": @json($rentabilite['charges'] ?? null),
    "hasComparison": {{ !empty($comparison) ? 'true' : 'false' }},
    "comparison": @json($comparison ?? [])
}
</script>
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/backoffice/encaissements-rentabilite.js') }}"></script>
@endsection
