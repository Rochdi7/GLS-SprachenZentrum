@extends('layouts.main')

@section('title', 'CRM — Rentabilité par groupe')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Rentabilité par groupe')

@section('content')
    @include('backoffice.crm.partials._center')

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h5 class="mb-0"><i class="ti ti-chart-pie text-primary me-2"></i>Rentabilité par groupe</h5>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('backoffice.crm.profitability.export', ['month' => $periodMonth] + request()->query()) }}"
               class="btn btn-sm btn-outline-success">
                <i class="ti ti-download me-1"></i> CSV
            </a>
            <form method="POST" action="{{ route('backoffice.crm.profitability.rebuild') }}" class="d-inline">
                @csrf
                <input type="hidden" name="month" value="{{ $periodMonth }}">
                <button class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-refresh me-1"></i> Recalculer
                </button>
            </form>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <i class="ti ti-circle-check me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Period filter --}}
    <form method="GET" class="card mb-3">
        <div class="card-body py-3 d-flex align-items-end gap-2 flex-wrap">
            <div>
                <label class="form-label small text-muted mb-1">Période</label>
                <select name="month" class="form-select form-select-sm" style="width:160px" onchange="this.form.submit()">
                    @php
                        // Generate last 12 months if no computed data yet
                        $months = !empty($report['availableMonths'])
                            ? $report['availableMonths']
                            : collect(range(0, 11))->map(fn ($i) => now()->subMonths($i)->format('Y-m'))->toArray();
                    @endphp
                    @foreach($months as $m)
                        <option value="{{ $m }}" {{ $m === $periodMonth ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $m)->locale('fr')->isoFormat('MMMM YYYY') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Trier par</label>
                <select name="sort" class="form-select form-select-sm" style="width:160px" onchange="this.form.submit()">
                    @php
                        $sorts = [
                            'revenue'        => 'CA (DH)',
                            'profit'         => 'Bénéfice',
                            'margin_pct'     => 'Marge %',
                            'teacher_salary' => 'Salaire prof',
                            'attendance_rate'=> 'Présence %',
                            'active_students'=> 'Étudiants',
                        ];
                    @endphp
                    @foreach($sorts as $key => $label)
                        <option value="{{ $key }}" {{ $sortBy === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="dir" value="{{ $sortDir === 'asc' ? 'desc' : 'asc' }}">
        </div>
    </form>

    {{-- KPI Strip --}}
    @php
        $t = $report['totals'];
        $tiles = [
            ['CA Total',          $t['revenue'],        'ti-coin',           'primary',   'DH'],
            ['Salaires profs',    $t['teacher_salary'], 'ti-user-dollar',    'warning',   'DH'],
            ['Autres charges',    $t['other_expenses'], 'ti-receipt',        'secondary', 'DH'],
            ['Bénéfice total',    $t['profit'],         'ti-trending-up',    $t['profit'] >= 0 ? 'success' : 'danger', 'DH'],
            ['Marge moy.',        $t['avg_margin'],     'ti-percentage',     'info',      '%'],
            ['Groupes actifs',    $t['groups'],         'ti-school',         'dark',      ''],
        ];
    @endphp
    <div class="row g-3 mb-4">
        @foreach($tiles as [$label, $val, $icon, $color, $unit])
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3 px-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="avtar avtar-s bg-light-{{ $color }} rounded">
                                <i class="ti {{ $icon }} f-18 text-{{ $color }}"></i>
                            </span>
                        </div>
                        <h5 class="mb-0 text-{{ $color }}">
                            {{ $unit === '%' ? number_format($val, 1, ',', ' ') . '%' : number_format($val, 0, ',', ' ') . ($unit ? ' ' . $unit : '') }}
                        </h5>
                        <p class="small text-muted mb-0">{{ $label }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex align-items-center justify-content-between">
            <h6 class="mb-0">
                <i class="ti ti-table me-1"></i>
                Détail par groupe — {{ \Carbon\Carbon::createFromFormat('Y-m', $periodMonth)->locale('fr')->isoFormat('MMMM YYYY') }}
            </h6>
            <span class="badge bg-light-secondary text-muted">{{ $report['rows']->count() }} groupe(s)</span>
        </div>

        @if($report['rows']->isEmpty())
            <div class="card-body text-center text-muted py-5">
                <i class="ti ti-database-off f-48 d-block mb-2"></i>
                Aucune donnée pour ce mois. Lancez <code>php artisan crm:build-group-profitability --month={{ $periodMonth }}</code>
                ou cliquez <strong>Recalculer</strong>.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Groupe</th>
                            <th>Centre</th>
                            <th>Prof</th>
                            <th>Niveau</th>
                            <th class="text-center">Étudiants</th>
                            <th class="text-end">CA (DH)</th>
                            <th class="text-end">Salaire prof (DH)
                                <i class="ti ti-info-circle text-muted small" title="Source : dépenses type paiement_prof. Méthode : direct (étiquette) ou proportionnel (répartition CA)"></i>
                            </th>
                            <th class="text-end">Autres charges</th>
                            <th class="text-end">Bénéfice (DH)</th>
                            <th class="text-center">Marge %</th>
                            <th class="text-center">Présence %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['rows'] as $row)
                            @php
                                $marginColor = $row->marginColor();
                                $attWarn     = $row->attendanceWarning();
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-semibold small">{{ $row->class_name ?: '—' }}</span>
                                </td>
                                <td class="small">
                                    @if($row->site_name)
                                        <span class="badge bg-light-primary text-primary">{{ $row->site_name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $row->teacher_name ?: '—' }}</td>
                                <td class="small text-muted">{{ $row->level_name ?: '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light-dark text-dark">{{ $row->active_students }}</span>
                                </td>
                                <td class="text-end fw-medium">
                                    {{ number_format($row->revenue, 2, ',', ' ') }}
                                </td>
                                <td class="text-end small">
                                    {{ number_format($row->teacher_salary, 2, ',', ' ') }}
                                    @if($row->salary_match_method === 'proportional')
                                        <i class="ti ti-alert-circle text-warning ms-1"
                                           title="Répartition proportionnelle (label non trouvé)"></i>
                                    @elseif($row->salary_match_method === 'equal')
                                        <i class="ti ti-alert-circle text-secondary ms-1"
                                           title="Répartition égale (aucune donnée CA disponible)"></i>
                                    @endif
                                </td>
                                <td class="text-end small text-muted">
                                    {{ number_format($row->other_expenses, 2, ',', ' ') }}
                                </td>
                                <td class="text-end fw-bold text-{{ $row->profit >= 0 ? 'success' : 'danger' }}">
                                    {{ ($row->profit >= 0 ? '+' : '') . number_format($row->profit, 2, ',', ' ') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light-{{ $marginColor }} text-{{ $marginColor }}">
                                        {{ number_format($row->margin_pct, 1, ',', '') }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($row->attendance_rate !== null)
                                        <span class="badge bg-light-{{ $attWarn ? 'warning' : 'success' }} text-{{ $attWarn ? 'warning' : 'success' }}">
                                            {{ number_format($row->attendance_rate, 1, ',', '') }}%
                                            @if($attWarn) <i class="ti ti-alert-triangle"></i> @endif
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="5" class="small text-muted">Total</td>
                            <td class="text-end">{{ number_format($report['totals']['revenue'], 2, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($report['totals']['teacher_salary'], 2, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($report['totals']['other_expenses'], 2, ',', ' ') }}</td>
                            <td class="text-end text-{{ $report['totals']['profit'] >= 0 ? 'success' : 'danger' }}">
                                {{ number_format($report['totals']['profit'], 2, ',', ' ') }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light-info text-info">
                                    {{ number_format($report['totals']['avg_margin'], 1, ',', '') }}% moy.
                                </span>
                            </td>
                            <td class="text-center small text-muted">
                                {{ number_format($report['totals']['avg_attendance'], 1, ',', '') }}% moy.
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    {{-- Notes --}}
    <div class="mt-3 small text-muted">
        <i class="ti ti-info-circle me-1"></i>
        <strong>CA</strong> : source <code>crm_payment_allocations</code>.
        <strong>Salaire prof</strong> : source <code>site_expenses</code> (type = paiement_prof).
        Attribution : <em>direct</em> si le libellé contient le nom du groupe,
        <em>proportionnel</em> si réparti selon la part de CA,
        <em>égal</em> si aucune donnée CA disponible.
        <i class="ti ti-alert-circle text-warning"></i> = attribution incertaine.
    </div>
@endsection
