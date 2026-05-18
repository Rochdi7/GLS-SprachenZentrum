@extends('layouts.main')

@section('title', 'CRM — Statistiques')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Statistiques')

@section('content')

    {{-- Date-range selector --}}
    <form method="GET" class="card mb-3">
        <div class="card-body py-3">
            <div class="d-flex align-items-center mb-2">
                <h6 class="mb-0 small text-muted text-uppercase">
                    <i class="ti ti-calendar me-1"></i> Période
                </h6>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-end">
                @php $presets = [
                    'today' => "Aujourd'hui",
                    '7d'    => '7 derniers jours',
                    '30d'   => '30 derniers jours',
                    'year'  => 'Année en cours',
                    'all'   => 'Tout',
                ]; @endphp
                @foreach($presets as $key => $label)
                    <a href="?range={{ $key }}"
                       class="btn btn-sm {{ $preset === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $label }}
                    </a>
                @endforeach

                <div class="vr d-none d-md-block"></div>

                <div>
                    <label class="form-label small mb-1 text-muted">Du</label>
                    <input type="date" name="startDate" value="{{ $preset === 'custom' ? $start : '' }}" class="form-control form-control-sm">
                </div>
                <div>
                    <label class="form-label small mb-1 text-muted">Au</label>
                    <input type="date" name="endDate" value="{{ $preset === 'custom' ? $end : '' }}" class="form-control form-control-sm">
                </div>
                <input type="hidden" name="range" value="custom">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="ti ti-search me-1"></i> Personnalisé
                </button>
            </div>
            <div class="small text-muted mt-2">
                @if($start && $end)
                    Période active : <strong>{{ $start }}</strong> → <strong>{{ $end }}</strong>
                @else
                    Période active : <strong>toutes dates</strong>
                @endif
            </div>
        </div>
    </form>

    {{-- ============================ KPI CARDS ============================ --}}
    @php
        // Aggregate totals
        $totalStudents = collect($kpis)->sum('students');
        $totalRegs     = collect($kpis)->sum('registrations');
        $totalPayments = collect($kpis)->sum('payments');
        $totalClasses  = collect($kpis)->sum('classes');
    @endphp

    <div class="row g-3 mb-3">
        @php $tiles = [
            ['Étudiants',     $totalStudents, 'ti-users',          'primary'],
            ['Inscriptions',  $totalRegs,     'ti-clipboard-list', 'success'],
            ['Paiements',     $totalPayments, 'ti-cash',           'warning'],
            ['Classes',       $totalClasses,  'ti-school',         'info'],
        ]; @endphp
        @foreach($tiles as [$label, $value, $icon, $color])
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">{{ $label }} (tous centres)</p>
                                <h3 class="mb-0">{{ number_format($value, 0, ',', ' ') }}</h3>
                            </div>
                            <span class="avtar avtar-l bg-light-{{ $color }} rounded">
                                <i class="ti {{ $icon }} f-24 text-{{ $color }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ====================== PER-CENTER BREAKDOWN ====================== --}}
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="ti ti-building me-1"></i> Évolution par centre
            </h6>
        </div>
        <div class="card-body">
            @if(empty($kpis))
                <div class="alert alert-info mb-0">Aucun centre configuré avec un store CRM.</div>
            @else
                @php
                    $max = collect($kpis)->reduce(fn ($carry, $row) => max($carry, max($row['students'] ?? 0, $row['registrations'] ?? 0, $row['payments'] ?? 0, $row['classes'] ?? 0)), 1) ?: 1;
                @endphp
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Centre</th>
                                <th class="text-end">Étudiants</th>
                                <th class="text-end">Inscriptions</th>
                                <th class="text-end">Paiements</th>
                                <th class="text-end">Classes</th>
                                <th style="width: 30%;">Volume relatif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kpis as $row)
                                @php
                                    $site = $row['site'];
                                    $sum  = (int)($row['students'] ?? 0) + (int)($row['registrations'] ?? 0) + (int)($row['payments'] ?? 0) + (int)($row['classes'] ?? 0);
                                    $pct  = max(1, round($sum / max($max, 1) * 100));
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge bg-light-primary text-primary">
                                            <i class="ti ti-building me-1"></i>{{ $site->name }}
                                        </span>
                                        @if(!empty($row['errors']))
                                            <i class="ti ti-alert-triangle text-warning ms-1" title="{{ implode(', ', array_keys($row['errors'])) }} en erreur"></i>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($row['students'] ?? 0, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($row['registrations'] ?? 0, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($row['payments'] ?? 0, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($row['classes'] ?? 0, 0, ',', ' ') }}</td>
                                    <td>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pct }}%;"></div>
                                        </div>
                                        <small class="text-muted">{{ $sum }} entrées</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ====================== PAYMENTS TREND (6 months) ====================== --}}
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="ti ti-trending-up me-1"></i> Paiements — 6 derniers mois (par centre)
            </h6>
        </div>
        <div class="card-body">
            @php
                $labels = $trend['labels'] ?? [];
                $series = $trend['series'] ?? [];
                // Find the overall max so bars are scaled consistently.
                $trendMax = 0;
                foreach ($series as $s) {
                    foreach ($s['counts'] as $c) { $trendMax = max($trendMax, (int) $c); }
                }
                $trendMax = max(1, $trendMax);
            @endphp

            @if(empty($series))
                <div class="alert alert-info mb-0">Aucune donnée de tendance disponible.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 18%;">Centre</th>
                                @foreach($labels as $label)
                                    <th class="text-center small">{{ $label }}</th>
                                @endforeach
                                <th class="text-end small">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($series as $row)
                                @php
                                    $site   = $row['site'];
                                    $counts = $row['counts'];
                                    $total  = array_sum($counts);
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge bg-light-primary text-primary">
                                            {{ $site->name }}
                                        </span>
                                    </td>
                                    @foreach($counts as $c)
                                        @php $h = max(2, round(($c / $trendMax) * 40)); @endphp
                                        <td class="text-center" style="vertical-align: bottom;">
                                            <div class="d-flex flex-column align-items-center justify-content-end" style="height: 50px;">
                                                <small class="text-muted" style="font-size: 10px;">{{ $c }}</small>
                                                <div style="width: 16px; height: {{ $h }}px; background: var(--bs-primary); border-radius: 2px 2px 0 0;"
                                                     title="{{ $c }} paiement(s)"></div>
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="text-end fw-medium">{{ number_format($total, 0, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mt-2 mb-0">
                    <i class="ti ti-info-circle me-1"></i>
                    Volume = nombre de paiements enregistrés sur le mois. Données mises en cache 5 min.
                </p>
            @endif
        </div>
    </div>

@endsection
