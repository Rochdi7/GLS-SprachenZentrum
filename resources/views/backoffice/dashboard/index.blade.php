@extends('layouts.main')

@section('title', 'Tableau de bord GLS')
@section('breadcrumb-item', 'Accueil')
@section('breadcrumb-item-active', 'Dashboard')

@section('css')
<link rel="stylesheet" href="{{ URL::asset('build/css/plugins/apexcharts.css') }}">
@endsection

@section('content')

{{-- =========================
TOP STATISTICS (DESIGN IDENTIQUE)
========================= --}}
<div class="row">

    {{-- Centres GLS --}}
    <div class="col-md-4 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="map-pin" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>

                <h5 class="mb-4">Centres GLS</h5>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $stats['totalSites'] }}</h3>

                    {{-- Peity sparkline --}}
                    <span class="line text-end"
                          data-peity='{ "height": 24 }'>
                        {{ implode(',', $analytics['sitesTrend']) }}
                    </span>
                </div>

                <span class="badge bg-light-success mt-2">
                    {{ $stats['activeGroups'] }} groupes actifs
                </span>

                <p class="text-muted text-sm mt-3">Centres de formation GLS</p>

                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:85%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Enseignants --}}
    <div class="col-md-4 col-sm-6">
        <div class="card statistics-card-1 overflow-hidden">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="users" class="text-brand-color-3" style="font-size:2rem"></i>
                </div>

                <h5 class="mb-4">Enseignants</h5>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="f-w-300 m-b-0">{{ $stats['totalTeachers'] }}</h3>

                    {{-- Peity bar --}}
                    <span class="bar"
                          data-peity='{ "height": 24 }'>
                        {{ implode(',', $analytics['teachersTrend']) }}
                    </span>
                </div>

                <span class="badge bg-light-primary mt-2">
                    {{ $stats['totalGroups'] }} groupes
                </span>

                <p class="text-muted text-sm mt-3">Équipe pédagogique GLS</p>

                <div class="progress" style="height:7px">
                    <div class="progress-bar bg-brand-color-3" style="width:50%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Articles --}}
    <div class="col-md-4 col-sm-12">
        <div class="card statistics-card-1 overflow-hidden bg-brand-color-3">
            <div class="card-body">
                <div class="float-end">
                    <i data-feather="file-text" class="text-white" style="font-size:2rem"></i>
                </div>

                <h5 class="mb-4 text-white">Articles publiés</h5>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="text-white f-w-300">{{ $stats['publishedPosts'] }}</h3>

                    {{-- Peity donut --}}
                    <span class="donut"
                          data-peity='{ "height": 26 }'>
                        {{ $stats['publishedPosts'] }}/{{ $stats['totalPosts'] }}
                    </span>
                </div>

                <p class="text-white text-opacity-75 text-sm mt-3">
                    Blog GLS
                </p>

                <div class="progress bg-white bg-opacity-10" style="height:7px">
                    <div class="progress-bar bg-white" style="width:65%"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<hr>

{{-- =========================
APEX CHARTS (ONLY WHERE USEFUL)
========================= --}}
<div class="row">

    {{-- Articles par mois --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Articles par mois</h5>
            </div>
            <div class="card-body">
                <div id="bar-chart-1"
                     data-series='@json(array_values($postsByMonth->toArray()))'
                     data-labels='@json(array_keys($postsByMonth->toArray()))'>
                </div>
            </div>
        </div>
    </div>

    {{-- Certificats par mois --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Certificats par mois</h5>
            </div>
            <div class="card-body">
                <div id="bar-chart-2"
                     data-series='@json(array_values($certificatesByMonth->toArray()))'
                     data-labels='@json(array_keys($certificatesByMonth->toArray()))'>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
{{-- ApexCharts --}}
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('build/js/chart_maps/chart-apex.js') }}"></script>

{{-- Peity --}}
<script src="{{ URL::asset('build/js/plugins/peity-vanilla.min.js') }}"></script>

<script>
/* =========================
PEITY DEFAULTS (CLEAN)
========================= */
peity.defaults.line = {
    stroke: "#04a9f5",
    fill: "#e0f5fe",
    height: 24,
    width: 80
};

peity.defaults.bar = {
    fill: ["#04a9f5"],
    height: 24,
    width: 80
};

peity.defaults.donut = {
    fill: ["#ffffff", "rgba(255,255,255,.3)"],
    innerRadius: 8,
    radius: 12
};

/* Init Peity */
document.querySelectorAll(".line").forEach(el => peity(el, "line"));
document.querySelectorAll(".bar").forEach(el => peity(el, "bar"));
document.querySelectorAll(".donut").forEach(el => peity(el, "donut"));

/* Feather icons */
if (typeof feather !== 'undefined') {
    feather.replace();
}
</script>
@endsection
