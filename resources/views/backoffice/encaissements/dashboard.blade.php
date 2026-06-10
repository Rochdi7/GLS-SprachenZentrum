@extends('layouts.main')

@section('title', 'Dashboard Encaissements')
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-active', 'Dashboard')

@section('content')

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-sm-auto">
                    <label class="form-label fw-semibold">
                        <i class="ph-duotone ph-funnel me-1"></i> Filtres
                    </label>
                </div>
                <div class="col-12 col-sm">
                    <select name="site_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les centres</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ $siteId == $site->id ? 'selected' : '' }}>
                                {{ $site->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm">
                    <select name="year" class="form-select" onchange="this.form.submit()">
                        @php $currentYear = (int) now()->format('Y'); @endphp
                        @for($y = $currentYear; $y >= 2023; $y--)
                            <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>
                                Année {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-12 col-sm">
                    <select name="month_num" class="form-select" onchange="this.form.submit()">
                        <option value="">Toute l'année</option>
                        @php
                            $monthLabels = [
                                1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                                5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                                9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                            ];
                        @endphp
                        @foreach($monthLabels as $num => $label)
                            <option value="{{ $num }}" {{ (int) $monthNum === $num ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($siteId || (int) $year !== (int) now()->format('Y') || $monthNum !== '')
                    <div class="col-12 col-sm-auto">
                        <a href="{{ route('backoffice.encaissements.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="ph-duotone ph-x me-1"></i> Reset
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-light-primary">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total Recettes</h6>
                    <h3 class="text-primary mb-0">{{ number_format($data['total_revenue'], 2, ',', ' ') }} <small>DH</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light-success">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Opérations</h6>
                    <h3 class="text-success mb-0">{{ number_format($data['total_count']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light-warning">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Moy. / Opération</h6>
                    <h3 class="text-warning mb-0">
                        {{ $data['total_count'] > 0 ? number_format($data['total_revenue'] / $data['total_count'], 0, ',', ' ') : 0 }}
                        <small>DH</small>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light-info">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Opérateurs</h6>
                    <h3 class="text-info mb-0">{{ $data['by_operator']->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0"><i class="ph-duotone ph-chart-line me-1"></i> Évolution mensuelle (12 mois)</h5></div>
                <div class="card-body">
                    <div id="revenueChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0"><i class="ph-duotone ph-chart-pie me-1"></i> Répartition méthodes</h5></div>
                <div class="card-body">
                    <div id="methodPieChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="ph-duotone ph-chart-bar me-1"></i> Encaissements par méthode (6 mois)</h5></div>
                <div class="card-body">
                    <div id="methodStackedChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- By Payment Method --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Par méthode de paiement</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Méthode</th>
                                    <th class="text-end">Montant</th>
                                    <th class="text-end">Opérations</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\Encaissement::PAYMENT_METHODS as $key => $label)
                                    @php $row = $data['by_method']->get($key); @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-end fw-semibold">{{ $row ? number_format($row->total, 2, ',', ' ') : '0' }} DH</td>
                                        <td class="text-end">{{ $row ? $row->count : 0 }}</td>
                                        <td class="text-end">
                                            {{ $data['total_revenue'] > 0 && $row ? number_format(($row->total / $data['total_revenue']) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td>Total</td>
                                    <td class="text-end">{{ number_format($data['total_revenue'], 2, ',', ' ') }} DH</td>
                                    <td class="text-end">{{ $data['total_count'] }}</td>
                                    <td class="text-end">100%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- By Fee Type --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Par type de frais</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th class="text-end">Montant</th>
                                    <th class="text-end">Opérations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\Encaissement::FEE_TYPES as $key => $label)
                                    @php $row = $data['by_fee_type']->get($key); @endphp
                                    @if($row)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($row->total, 2, ',', ' ') }} DH</td>
                                        <td class="text-end">{{ $row->count }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- By Operator --}}
    @if($data['by_operator']->count() > 0)
    <div class="row">
        <div class="col-md-{{ $data['by_site'] ? '6' : '12' }} mb-4">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Performance opérateurs</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Opérateur</th>
                                    <th class="text-end">Total collecté</th>
                                    <th class="text-end">Opérations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['by_operator'] as $op)
                                <tr>
                                    <td class="fw-semibold">{{ ucfirst($op->operator_name) }}</td>
                                    <td class="text-end">{{ number_format($op->total, 2, ',', ' ') }} DH</td>
                                    <td class="text-end">{{ $op->count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- By Site (when no site filter) --}}
        @if($data['by_site'])
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Par centre</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Centre</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Opérations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['by_site'] as $s)
                                <tr>
                                    <td class="fw-semibold">{{ $s->site_name }}</td>
                                    <td class="text-end">{{ number_format($s->total, 2, ',', ' ') }} DH</td>
                                    <td class="text-end">{{ $s->count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Quick Links --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('backoffice.encaissements.index') }}" class="btn btn-outline-primary">
                            <i class="ph-duotone ph-list me-1"></i> Liste encaissements
                        </a>
                        <a href="{{ route('backoffice.encaissements.imports.create') }}" class="btn btn-primary">
                            <i class="ph-duotone ph-upload me-1"></i> Importer
                        </a>
                        <a href="{{ route('backoffice.encaissements.create') }}" class="btn btn-outline-success">
                            <i class="ph-duotone ph-plus me-1"></i> Saisie manuelle
                        </a>
                        <a href="{{ route('backoffice.encaissements.rentabilite') }}" class="btn btn-outline-warning">
                            <i class="ph-duotone ph-chart-line-up me-1"></i> Rentabilité
                        </a>
                        <a href="{{ route('backoffice.encaissements.operators') }}" class="btn btn-outline-info">
                            <i class="ph-duotone ph-users me-1"></i> Opérateurs
                        </a>
                        <a href="{{ route('backoffice.encaissements.expenses.index') }}" class="btn btn-outline-danger">
                            <i class="ph-duotone ph-money me-1"></i> Charges
                        </a>
                        <a href="{{ route('backoffice.encaissements.primes.index') }}" class="btn btn-outline-secondary">
                            <i class="ph-duotone ph-trophy me-1"></i> Primes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Revenue Evolution Chart (Area) ──
    const monthlyData = @json($monthlyEvolution);
    if (monthlyData.length > 0) {
        new ApexCharts(document.querySelector("#revenueChart"), {
            chart: { type: 'area', height: 320, toolbar: { show: false } },
            series: [
                { name: 'Recettes', data: monthlyData.map(m => m.revenue) },
                { name: 'Dépenses', data: monthlyData.map(m => m.expenses) },
            ],
            xaxis: { categories: monthlyData.map(m => m.month_label) },
            yaxis: {
                labels: { formatter: v => (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v) + ' DH' }
            },
            colors: ['#4680FF', '#dc2626'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.1 } },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            tooltip: {
                y: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) + ' DH' }
            },
        }).render();
    }

    // ── Payment Method Pie Chart ──
    const byMethod = @json($data['by_method']);
    const methodLabels = { especes: 'Espèces', tpe: 'TPE', virement: 'Virement', cheque: 'Chèque' };
    const methodColors = { especes: '#2ca87f', tpe: '#4680FF', virement: '#e58a00', cheque: '#dc2626' };
    const pieLabels = [];
    const pieSeries = [];
    const pieColors = [];
    Object.keys(methodLabels).forEach(k => {
        if (byMethod[k]) {
            pieLabels.push(methodLabels[k]);
            pieSeries.push(parseFloat(byMethod[k].total));
            pieColors.push(methodColors[k]);
        }
    });
    if (pieSeries.length > 0) {
        new ApexCharts(document.querySelector("#methodPieChart"), {
            chart: { type: 'donut', height: 320 },
            series: pieSeries,
            labels: pieLabels,
            colors: pieColors,
            legend: { position: 'bottom' },
            tooltip: {
                y: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) + ' DH' }
            },
            plotOptions: {
                pie: { donut: { size: '55%', labels: {
                    show: true,
                    total: { show: true, label: 'Total', formatter: w => {
                        const t = w.globals.seriesTotals.reduce((a,b) => a+b, 0);
                        return new Intl.NumberFormat('fr-FR').format(t) + ' DH';
                    }}
                }}}
            }
        }).render();
    }

    // ── Stacked Method Evolution Chart ──
    const methodEvo = @json($methodEvolution);
    if (methodEvo.length > 0) {
        new ApexCharts(document.querySelector("#methodStackedChart"), {
            chart: { type: 'bar', height: 300, stacked: true, toolbar: { show: false } },
            series: [
                { name: 'Espèces', data: methodEvo.map(m => m.especes) },
                { name: 'TPE', data: methodEvo.map(m => m.tpe) },
                { name: 'Virement', data: methodEvo.map(m => m.virement) },
                { name: 'Chèque', data: methodEvo.map(m => m.cheque) },
            ],
            xaxis: { categories: methodEvo.map(m => m.month) },
            yaxis: {
                labels: { formatter: v => (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v) }
            },
            colors: ['#2ca87f', '#4680FF', '#e58a00', '#dc2626'],
            dataLabels: { enabled: false },
            tooltip: {
                y: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) + ' DH' }
            },
            plotOptions: { bar: { borderRadius: 4 } },
        }).render();
    }
});
</script>
@endsection
