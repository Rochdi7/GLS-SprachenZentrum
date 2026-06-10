@extends('layouts.main')

@section('title', 'Dépenses CRM')
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'Dépenses')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

{{-- ── Header ── --}}
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="ph-duotone ph-receipt text-primary"></i>
            Dépenses CRM — Wimschool
        </h4>
    </div>
    <div class="col-auto">
        @include('backoffice.crm.partials._sync_badge')
    </div>
</div>

{{-- ── Chart ── --}}
<div class="card mb-4">
    <div class="card-header py-2">
        <h6 class="mb-0">Dépenses mensuelles par centre (6 derniers mois)</h6>
    </div>
    <div class="card-body">
        <div id="crm-expenses-chart" style="min-height:300px;"></div>
    </div>
</div>

{{-- ── Filters ── --}}
<form method="GET" action="{{ route('backoffice.crm.expenses.index') }}" class="row g-2 mb-3">
    <div class="col-auto">
        <select name="site_id" class="form-select form-select-sm" style="width:200px">
            <option value="">— Tous les centres —</option>
            @foreach ($sites as $site)
                <option value="{{ $site->id }}" {{ $selectedSite == $site->id ? 'selected' : '' }}>
                    {{ $site->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <input type="month" name="month" value="{{ $selectedMonth }}" class="form-control form-control-sm">
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary">Filtrer</button>
        <a href="{{ route('backoffice.crm.expenses.index') }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
    </div>
</form>

{{-- ── Table ── --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Centre</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-end">Montant</th>
                        <th>Référence</th>
                        <th>Opérateur</th>
                        <th>Méthode paiement</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td class="text-nowrap">
                                {{ $expense->expense_date?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td>{{ $expense->site?->name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $expense->getTypeLabel() }}
                                </span>
                            </td>
                            <td>{{ $expense->label }}</td>
                            <td class="text-end text-nowrap fw-semibold">
                                {{ number_format($expense->amount, 2, ',', ' ') }} DH
                            </td>
                            <td class="text-muted small">{{ $expense->reference ?? '—' }}</td>
                            <td class="small">{{ $expense->operator_name ?? '—' }}</td>
                            <td class="small">{{ $expense->payment_method ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Aucune dépense trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($expenses->hasPages())
        <div class="card-footer">
            {{ $expenses->links() }}
        </div>
    @endif
</div>

{{-- Data island for chart --}}
<script type="application/json" id="crm-expenses-chart-data">
{!! json_encode([
    'months'  => $chartMonths,
    'series'  => $chartSeries,
]) !!}
</script>

@endsection

@section('scripts')
    <script src="{{ URL::asset('assets/js/backoffice/crm-expenses.js') }}"></script>
@endsection
