@extends('layouts.main')

@section('title', 'Mes paiements')
@section('breadcrumb-item', 'Espace Professeur')
@section('breadcrumb-item-active', 'Mes paiements')

@section('content')

@php
    $modeMeta = [
        'weekly' => ['Hebdo', 'secondary', 'ph-calendar-blank'],
        'period' => ['Période', 'success', 'ph-calendar-check'],
        'hourly' => ['Horaire', 'primary', 'ph-clock'],
    ];
    $statusMeta = [
        'draft'     => ['Brouillon', 'secondary'],
        'validated' => ['Validé', 'info'],
        'paid'      => ['Payé', 'success'],
        'locked'    => ['Verrouillé', 'dark'],
    ];
@endphp

{{-- ── HEADER ─────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="mb-1"><i class="ph-duotone ph-chalkboard-teacher text-primary me-2"></i>Bonjour, {{ $teacher->name }}</h4>
        <small class="text-muted">Historique de vos paiements — consultation uniquement.</small>
    </div>
</div>

{{-- ── KPIs ──────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="ph-duotone ph-currency-circle-dollar text-success fs-4"></i></div>
            <div><div class="text-muted small">Total cumulé</div>
                <div class="h4 mb-0 fw-bold text-success">{{ number_format($grandTotal, 2, ',', ' ') }} <small class="fs-6">DH</small></div></div>
        </div>
    </div></div></div>
    <div class="col-6 col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary bg-opacity-10 p-3"><i class="ph-duotone ph-check-circle text-primary fs-4"></i></div>
            <div><div class="text-muted small">Déjà payé</div>
                <div class="h4 mb-0 fw-bold">{{ number_format($paidTotal, 2, ',', ' ') }} <small class="fs-6">DH</small></div></div>
        </div>
    </div></div></div>
    <div class="col-12 col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="ph-duotone ph-receipt text-info fs-4"></i></div>
            <div><div class="text-muted small">Nombre de paiements</div>
                <div class="h4 mb-0 fw-bold">{{ $imports->count() }}</div></div>
        </div>
    </div></div></div>
</div>

{{-- ── FILTERS ────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Mode</label>
                <select name="mode" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($modeMeta as $key => [$lbl])
                        <option value="{{ $key }}" {{ request('mode') === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Statut</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($statusMeta as $key => [$lbl])
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Année</label>
                <input type="number" name="attached_year" class="form-control form-control-sm" min="2000" max="2100"
                       value="{{ request('attached_year') }}" placeholder="Toutes">
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button class="btn btn-primary btn-sm"><i class="ph-duotone ph-funnel me-1"></i>Filtrer</button>
                <a href="{{ route('backoffice.payroll.professor.index') }}" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

{{-- ── HISTORY: group → Month N ───────────────────────── --}}
@forelse($byGroup as $groupName => $months)
    <div class="card mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-bold"><i class="ph-duotone ph-users-three text-primary me-1"></i>{{ $groupName }}</h6>
        </div>
        <div class="card-body">
            @foreach($months as $monthNumber => $rows)
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fw-semibold">
                            @if($monthNumber)
                                <span class="badge bg-light-primary text-primary"><i class="ph-duotone ph-calendar-dots me-1"></i>Mois {{ $monthNumber }}</span>
                            @else
                                <span class="badge bg-light-secondary text-muted">Sans numéro de mois</span>
                            @endif
                        </span>
                        <span class="text-muted small">
                            Sous-total : <strong>{{ number_format($rows->sum(fn($i) => (float)($i->paymentSummary?->total_payment ?? $i->final_total ?? 0)), 2) }} DH</strong>
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mode</th>
                                    <th>Rattaché</th>
                                    <th>Période</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                    <th class="text-end">Détail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $import)
                                    @php
                                        $mode = $import->payment_mode ?? 'weekly';
                                        [$modeLabel, $modeColor, $modeIcon] = $modeMeta[$mode] ?? $modeMeta['weekly'];
                                        [$stLabel, $stColor] = $statusMeta[$import->status] ?? ['—','secondary'];
                                        $total = $import->paymentSummary?->total_payment ?? $import->final_total;
                                    @endphp
                                    <tr>
                                        <td><span class="badge bg-light-{{ $modeColor }} text-{{ $modeColor }}"><i class="ph-duotone {{ $modeIcon }} me-1"></i>{{ $modeLabel }}</span></td>
                                        <td>
                                            @if($import->attached_month && $import->attached_year)
                                                {{ \Carbon\Carbon::create()->month($import->attached_month)->locale('fr')->isoFormat('MMM') }} {{ $import->attached_year }}
                                            @else — @endif
                                        </td>
                                        <td>{{ $import->date_start->format('d/m') }} — {{ $import->date_end->format('d/m/Y') }}</td>
                                        <td>@if($total !== null)<strong>{{ number_format((float)$total, 2) }} DH</strong>@else — @endif</td>
                                        <td><span class="badge bg-{{ $stColor }}">{{ $stLabel }}</span></td>
                                        <td class="text-end">
                                            <a href="{{ route('backoffice.payroll.professor.show', $import->id) }}"
                                               class="btn btn-sm btn-outline-primary"><i class="ph-duotone ph-eye"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="card"><div class="card-body text-center text-muted py-5">
        <i class="ph-duotone ph-folder-open fs-1 d-block mb-2"></i>
        Aucun paiement enregistré pour le moment.
    </div></div>
@endforelse

@endsection
