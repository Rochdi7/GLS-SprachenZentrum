@extends('layouts.main')

@section('title', 'CRM — Performance des Centres')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Performance des Centres')

@section('content')

    @include('backoffice.crm.partials._center')

    {{-- Filters Card --}}
    <form method="GET" class="card mb-3">
        <div class="card-body py-3">
            <div class="d-flex align-items-center mb-2">
                <h6 class="mb-0 small text-muted text-uppercase">
                    <i class="ti ti-filter me-1"></i> Filtres
                </h6>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-end">
                <div>
                    <label class="form-label small mb-1 text-muted">Du</label>
                    <input type="date" name="startDate" value="{{ $startDate }}" class="form-control form-control-sm">
                </div>
                <div>
                    <label class="form-label small mb-1 text-muted">Au</label>
                    <input type="date" name="endDate" value="{{ $endDate }}" class="form-control form-control-sm">
                </div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="ti ti-search me-1"></i> Appliquer
                </button>
                <a href="{{ route('backoffice.crm.center-performance', ['refresh' => 1]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-refresh me-1"></i> Actualiser
                </a>
            </div>
        </div>
    </form>

    {{-- Alerts --}}
    @if(!empty($dashboard['alerts']))
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="ti ti-alert-triangle me-1"></i> Alertes
                </h6>
                <div class="row g-2">
                    @foreach($dashboard['alerts'] as $alert)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="alert alert-{{ $alert['level'] }} mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-{{ $alert['level'] === 'danger' ? 'alert-octagon' : 'alert-circle' }} me-2"></i>
                                    <div>
                                        <strong>{{ $alert['center'] }}</strong>
                                        <div class="small">{{ $alert['message'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-3">
        @php
            $summary = $dashboard['summary'] ?? [];
        @endphp
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Revenu Total</p>
                            <h3 class="mb-0">{{ number_format($summary['totalRevenue'] ?? 0, 2, ',', ' ') }} MAD</h3>
                        </div>
                        <span class="avtar avtar-l bg-light-success rounded">
                            <i class="ti ti-cash f-24 text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Étudiants Actifs</p>
                            <h3 class="mb-0">{{ number_format($summary['totalActiveStudents'] ?? 0, 0, ',', ' ') }}</h3>
                        </div>
                        <span class="avtar avtar-l bg-light-primary rounded">
                            <i class="ti ti-users f-24 text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Inscriptions Actives</p>
                            <h3 class="mb-0">{{ number_format($summary['totalActiveRegistrations'] ?? 0, 0, ',', ' ') }}</h3>
                        </div>
                        <span class="avtar avtar-l bg-light-info rounded">
                            <i class="ti ti-clipboard-list f-24 text-info"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Taux de Collecte Moyen</p>
                            <h3 class="mb-0">{{ number_format($summary['avgCollectionRate'] ?? 0, 2, ',', ' ') }}%</h3>
                        </div>
                        <span class="avtar avtar-l bg-light-warning rounded">
                            <i class="ti ti-percentage f-24 text-warning"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @if(!empty($summary['bestCenter']))
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Meilleur Centre</p>
                        <h4 class="mb-0">{{ $summary['bestCenter']['site']->name }}</h4>
                        <p class="text-success small mb-0 mt-1">Score: {{ $summary['bestCenter']['score'] }}/100</p>
                    </div>
                </div>
            </div>
        @endif
        @if(!empty($summary['lowestCenter']))
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Centre à Améliorer</p>
                        <h4 class="mb-0">{{ $summary['lowestCenter']['site']->name }}</h4>
                        <p class="text-danger small mb-0 mt-1">Score: {{ $summary['lowestCenter']['score'] }}/100</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row g-3 mb-3">
        {{-- Center Ranking Table --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="ti ti-trophy me-1"></i> Classement des Centres
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" id="centerRankingTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Centre</th>
                                    <th class="text-end">Étudiants Actifs</th>
                                    <th class="text-end">Revenu</th>
                                    <th class="text-end">Croissance Revenu</th>
                                    <th class="text-end">Taux Collecte</th>
                                    <th class="text-end">Encours</th>
                                    <th class="text-end">Classes Actives</th>
                                    <th class="text-end">Revenu/Classe</th>
                                    <th class="text-end">Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dashboard['centers'] ?? [] as $center)
                                    @php
                                        $site = $center['site'];
                                        $revenue = $center['revenue']['total'] ?? 0;
                                        $revenueGrowth = $center['revenue']['growth'] ?? 0;
                                        $collectionRate = $center['collections']['collectionRate'] ?? 0;
                                        $outstanding = $center['collections']['outstanding'] ?? 0;
                                        $activeClasses = $center['classes']['active'] ?? 0;
                                        $revenuePerClass = $center['classes']['revenuePerClass'] ?? 0;
                                        $score = $center['score'] ?? 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge bg-light-primary text-primary">
                                                <i class="ti ti-building me-1"></i>{{ $site->name }}
                                            </span>
                                        </td>
                                        <td class="text-end">{{ number_format($center['students']['active'] ?? 0, 0, ',', ' ') }}</td>
                                        <td class="text-end">{{ number_format($revenue, 2, ',', ' ') }} MAD</td>
                                        <td class="text-end">
                                            <span class="{{ $revenueGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($revenueGrowth, 2, ',', ' ') }}%
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="{{ $collectionRate >= 80 ? 'text-success' : 'text-warning' }}">
                                                {{ number_format($collectionRate, 2, ',', ' ') }}%
                                            </span>
                                        </td>
                                        <td class="text-end">{{ number_format($outstanding, 2, ',', ' ') }} MAD</td>
                                        <td class="text-end">{{ number_format($activeClasses, 0, ',', ' ') }}</td>
                                        <td class="text-end">{{ number_format($revenuePerClass, 2, ',', ' ') }} MAD</td>
                                        <td class="text-end">
                                            <span class="badge {{ $score >= 80 ? 'bg-success' : ($score >= 60 ? 'bg-warning' : 'bg-danger') }} text-white">
                                                {{ number_format($score, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="ti ti-chart-bar me-1"></i> Revenu par Centre
                    </h6>
                </div>
                <div class="card-body">
                    <div id="revenueByCenterChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="ti ti-chart-bar me-1"></i> Taux de Collecte par Centre
                    </h6>
                </div>
                <div class="card-body">
                    <div id="collectionRateByCenterChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="ti ti-chart-line me-1"></i> Tendances Mensuelles - Revenu
                    </h6>
                </div>
                <div class="card-body">
                    <div id="monthlyRevenueTrendChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="ti ti-chart-line me-1"></i> Tendances Mensuelles - Étudiants Actifs
                    </h6>
                </div>
                <div class="card-body">
                    <div id="activeStudentsTrendChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Centers Sections --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="ti ti-crown me-1"></i> Top 5 Centres - Revenu
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($dashboard['topCenters']['revenue'] ?? [] as $idx => $center)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary me-2">{{ $idx + 1 }}</span>
                                    <span>{{ $center['site']->name }}</span>
                                </div>
                                <strong>{{ number_format($center['revenue']['total'] ?? 0, 2, ',', ' ') }} MAD</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="ti ti-check-circle me-1"></i> Top 5 Centres - Taux de Collecte
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($dashboard['topCenters']['collection'] ?? [] as $idx => $center)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-success me-2">{{ $idx + 1 }}</span>
                                    <span>{{ $center['site']->name }}</span>
                                </div>
                                <strong>{{ number_format($center['collections']['collectionRate'] ?? 0, 2, ',', ' ') }}%</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script type="application/json" id="center-performance-data">{!! json_encode($dashboard['charts'] ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartsData = JSON.parse(document.getElementById('center-performance-data').textContent);

    // Revenue by Center Chart
    if (chartsData.revenueByCenter) {
        const revenueLabels = Object.keys(chartsData.revenueByCenter);
        const revenueValues = Object.values(chartsData.revenueByCenter);
        new ApexCharts(document.querySelector("#revenueByCenterChart"), {
            series: [{ name: 'Revenu', data: revenueValues }],
            chart: { type: 'bar', height: 300 },
            plotOptions: { bar: { borderRadius: 4, horizontal: false, } },
            dataLabels: { enabled: false },
            xaxis: { categories: revenueLabels, },
            colors: ['#673ab7'],
            title: { text: '', align: 'left' },
        }).render();
    }

    // Collection Rate by Center Chart
    if (chartsData.collectionRateByCenter) {
        const collectionLabels = Object.keys(chartsData.collectionRateByCenter);
        const collectionValues = Object.values(chartsData.collectionRateByCenter);
        new ApexCharts(document.querySelector("#collectionRateByCenterChart"), {
            series: [{ name: 'Taux Collecte', data: collectionValues }],
            chart: { type: 'bar', height: 300 },
            plotOptions: { bar: { borderRadius: 4, horizontal: false, } },
            dataLabels: { enabled: false },
            xaxis: { categories: collectionLabels, },
            yaxis: { max: 100, title: { text: '%' } },
            colors: ['#20c997'],
            title: { text: '', align: 'left' },
        }).render();
    }

    // Monthly Revenue Trend Chart
    if (chartsData.monthlyRevenueTrend) {
        const revenueTrendLabels = Object.keys(chartsData.monthlyRevenueTrend);
        const revenueTrendValues = Object.values(chartsData.monthlyRevenueTrend);
        new ApexCharts(document.querySelector("#monthlyRevenueTrendChart"), {
            series: [{ name: 'Revenu', data: revenueTrendValues }],
            chart: { type: 'line', height: 300 },
            stroke: { curve: 'smooth', width: 2 },
            markers: { size: 4 },
            dataLabels: { enabled: false },
            xaxis: { categories: revenueTrendLabels, },
            colors: ['#673ab7'],
        }).render();
    }

    // Active Students Trend Chart
    if (chartsData.activeStudentsTrend) {
        const studentsTrendLabels = Object.keys(chartsData.activeStudentsTrend);
        const studentsTrendValues = Object.values(chartsData.activeStudentsTrend);
        new ApexCharts(document.querySelector("#activeStudentsTrendChart"), {
            series: [{ name: 'Étudiants Actifs', data: studentsTrendValues }],
            chart: { type: 'line', height: 300 },
            stroke: { curve: 'smooth', width: 2 },
            markers: { size: 4 },
            dataLabels: { enabled: false },
            xaxis: { categories: studentsTrendLabels, },
            colors: ['#3f51b5'],
        }).render();
    }
});
</script>
@endsection
