@extends('layouts.main')

@section('title', 'Statistiques des Leads')
@section('breadcrumb-item', 'Leads')
@section('breadcrumb-item-link', route('backoffice.leads.index'))
@section('breadcrumb-item-active', 'Statistiques')

@section('css')
@endsection

@php
    $totalLeads = $totalInscriptions + $totalConsultations + $totalApplications;
@endphp

@section('content')

    <div class="row">
        <div class="col-12">

            {{-- Centre Filter --}}
            <div class="card mb-4">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('backoffice.leads.stats') }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-sm-auto">
                                <label class="form-label fw-semibold mb-1"><i class="ti ti-filter me-1"></i> Filtrer par centre</label>
                            </div>
                            <div class="col-12 col-sm">
                                <select name="centre" class="form-select" onchange="this.form.submit()">
                                    <option value="all" {{ $centreFilter === 'all' ? 'selected' : '' }}>
                                        Tous les centres
                                    </option>
                                    <option value="online" {{ $centreFilter === 'online' ? 'selected' : '' }}>
                                        En ligne ({{ $centreCounts[0] ?? 0 }})
                                    </option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}" {{ $centreFilter == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }} ({{ $centreCounts[$site->id] ?? 0 }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($centreFilter !== 'all')
                                <div class="col-12 col-sm-auto">
                                    <a href="{{ route('backoffice.leads.stats') }}" class="btn btn-outline-secondary w-100">
                                        <i class="ti ti-x me-1"></i> Reset
                                    </a>
                                </div>
                            @endif
                            <div class="col-12 col-sm-auto">
                                <a href="{{ route('backoffice.leads.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="ti ti-arrow-left me-1"></i> Retour aux Leads
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Total Leads</p>
                                    <h4 class="mb-0">{{ $totalLeads }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-primary rounded-circle">
                                    <i class="ti ti-users f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Inscriptions</p>
                                    <h4 class="mb-0">{{ $totalInscriptions }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-success rounded-circle">
                                    <i class="ti ti-clipboard-list f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Consultations</p>
                                    <h4 class="mb-0">{{ $totalConsultations }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-info rounded-circle">
                                    <i class="ti ti-messages f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Applications</p>
                                    <h4 class="mb-0">{{ $totalApplications }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-warning rounded-circle">
                                    <i class="ti ti-file-description f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Monthly Leads Chart --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-chart-bar me-1"></i> Leads par Mois (12 derniers mois)
                        @if($centreFilter !== 'all')
                            <span class="badge bg-primary ms-2">
                                {{ $centreFilter === 'online' ? 'En ligne' : $sites->firstWhere('id', $centreFilter)?->name }}
                            </span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div id="leads-monthly-chart" style="min-height: 380px;"></div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                {{-- Centre Breakdown Donut --}}
                <div class="col-12 col-lg-6">
                    <div class="card mb-0 h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="ti ti-chart-donut me-1"></i> Inscriptions par Centre</h5>
                        </div>
                        <div class="card-body">
                            <div id="leads-centre-chart" style="min-height: 350px;"></div>
                        </div>
                    </div>
                </div>

                {{-- Centre Table --}}
                <div class="col-12 col-lg-6">
                    <div class="card mb-0 h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="ti ti-list me-1"></i> Total par Centre</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Centre</th>
                                            <th class="text-end">Inscriptions</th>
                                            <th class="text-end">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalCentreInsc = collect($centreStats)->sum('total'); @endphp
                                        @foreach(collect($centreStats)->sortByDesc('total') as $cs)
                                            <tr>
                                                <td class="fw-semibold">{{ $cs['name'] }}</td>
                                                <td class="text-end">{{ $cs['total'] }}</td>
                                                <td class="text-end">
                                                    @if($totalCentreInsc > 0)
                                                        {{ round($cs['total'] / $totalCentreInsc * 100, 1) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-light fw-bold">
                                            <td>Total</td>
                                            <td class="text-end">{{ $totalCentreInsc }}</td>
                                            <td class="text-end">100%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof ApexCharts === 'undefined') return;

            // Monthly bar chart
            var monthlyData = @json($monthlyStats);
            var barEl = document.getElementById('leads-monthly-chart');
            if (barEl) {
                new ApexCharts(barEl, {
                    chart: { type: 'bar', height: 380, toolbar: { show: true } },
                    series: [
                        { name: 'Inscriptions', data: monthlyData.map(m => m.inscriptions), color: '#2ca87f' },
                        { name: 'Consultations', data: monthlyData.map(m => m.consultations), color: '#3ec9d6' },
                        { name: 'Applications', data: monthlyData.map(m => m.applications), color: '#e58a00' },
                    ],
                    xaxis: { categories: monthlyData.map(m => m.label) },
                    yaxis: { title: { text: 'Nombre de Leads' }, forceNiceScale: true, min: 0 },
                    plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
                    dataLabels: { enabled: true, style: { fontSize: '11px' } },
                    legend: { position: 'top' },
                    tooltip: { shared: true, intersect: false },
                    responsive: [{
                        breakpoint: 768,
                        options: {
                            plotOptions: { bar: { columnWidth: '70%' } },
                            dataLabels: { enabled: false },
                            legend: { position: 'bottom' },
                        }
                    }],
                }).render();
            }

            // Centre donut chart
            var centreData = @json($centreStats);
            var donutEl = document.getElementById('leads-centre-chart');
            if (donutEl) {
                var labels = centreData.map(c => c.name);
                var values = centreData.map(c => c.total);
                new ApexCharts(donutEl, {
                    chart: { type: 'donut', height: 350 },
                    series: values,
                    labels: labels,
                    colors: ['#2ca87f', '#3ec9d6', '#e58a00', '#dc2626', '#7c3aed', '#0ea5e9', '#f59e0b', '#10b981'],
                    legend: { position: 'bottom' },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '55%',
                                labels: {
                                    show: true,
                                    total: { show: true, label: 'Total', fontSize: '16px' },
                                },
                            },
                        },
                    },
                    responsive: [{
                        breakpoint: 768,
                        options: { chart: { height: 300 } }
                    }],
                }).render();
            }
        });
    </script>
@endsection
