@extends('layouts.main')

@section('title', 'Charges par centre')
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-active', 'Charges par centre')

@section('content')

    {{-- Toast --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">GLS Backoffice</strong>
                    <small>Maintenant</small>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

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
                    <select name="site_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tous les centres</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                {{ $site->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm">
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tous les types</option>
                        @foreach(\App\Models\SiteExpense::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request()->hasAny(['site_id', 'type']))
                    <div class="col-12 col-sm-auto">
                        <a href="{{ route('backoffice.encaissements.expenses.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph-duotone ph-x me-1"></i> Reset
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card table-card">
        <div class="card-header">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h5 class="mb-3 mb-sm-0">Charges par centre</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('backoffice.encaissements.expenses.imports.create') }}" class="btn btn-primary btn-sm">
                        <i class="ph-duotone ph-upload me-1"></i> Importer PDF
                    </a>
                    <a href="{{ route('backoffice.encaissements.expenses.imports.index') }}" class="btn btn-outline-info btn-sm">
                        <i class="ph-duotone ph-clock-counter-clockwise me-1"></i> Historique
                    </a>
                    <a href="{{ route('backoffice.encaissements.expenses.create') }}" class="btn btn-outline-success btn-sm">
                        <i class="ph-duotone ph-plus me-1"></i> Saisie manuelle
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Mois</th>
                            <th>Centre</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Montant</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($expense->month)->format('m/Y') }}</td>
                                <td>{{ $expense->site->name ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-light-secondary">
                                        {{ \App\Models\SiteExpense::TYPES[$expense->type] ?? $expense->type }}
                                    </span>
                                </td>
                                <td>{{ $expense->label }}</td>
                                <td class="text-end fw-semibold">{{ number_format($expense->amount, 2, ',', ' ') }} DH</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('backoffice.encaissements.expenses.edit', $expense) }}" class="avtar avtar-xs btn-link-warning" title="Modifier">
                                        <i class="ph-duotone ph-pencil-simple"></i>
                                    </a>
                                    <form action="{{ route('backoffice.encaissements.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette charge ?')">
                                        @csrf @method('DELETE')
                                        <button class="avtar avtar-xs btn-link-danger border-0 bg-transparent" title="Supprimer">
                                            <i class="ph-duotone ph-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Aucune charge trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const toastEl = document.getElementById('liveToast');
    if (toastEl) { new bootstrap.Toast(toastEl).show(); }
});
</script>
@endsection
