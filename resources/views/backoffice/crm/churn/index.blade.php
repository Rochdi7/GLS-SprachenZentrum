@extends('layouts.main')

@section('title', 'Prédicteur de churn — CRM')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Risque de churn')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Churn Predictor</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Summary cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-danger mb-1"><i class="ph-duotone ph-warning me-1"></i>Critique</h6>
                    <h3 class="mb-0 text-danger">{{ $summary['critical'] ?? 0 }}</h3>
                    <small class="text-muted">Score 76–100</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="text-warning mb-1"><i class="ph-duotone ph-trend-up me-1"></i>Élevé</h6>
                    <h3 class="mb-0 text-warning">{{ $summary['high'] ?? 0 }}</h3>
                    <small class="text-muted">Score 56–75</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="text-info mb-1"><i class="ph-duotone ph-minus-circle me-1"></i>Modéré</h6>
                    <h3 class="mb-0 text-info">{{ $summary['medium'] ?? 0 }}</h3>
                    <small class="text-muted">Score 31–55</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-success mb-1"><i class="ph-duotone ph-check-circle me-1"></i>Faible</h6>
                    <h3 class="mb-0 text-success">{{ $summary['low'] ?? 0 }}</h3>
                    <small class="text-muted">Score 0–30</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="d-flex gap-3 align-items-center flex-wrap">

                {{-- Centre + submit --}}
                <form method="GET" action="{{ route('backoffice.crm.churn.index') }}" class="d-flex gap-2 align-items-center">
                    @if(request('risk_level'))
                        <input type="hidden" name="risk_level" value="{{ request('risk_level') }}">
                    @endif
                    <select name="crm_store_id" class="form-select form-select-sm" style="width:200px"
                            onchange="this.form.submit()">
                        <option value="">— Tous les centres —</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->crm_store_id }}"
                                {{ request('crm_store_id') == $site->crm_store_id ? 'selected' : '' }}>
                                {{ $site->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                {{-- Risk level tabs (carry current store) --}}
                @php $storeParam = request('crm_store_id') ? ['crm_store_id' => request('crm_store_id')] : []; @endphp
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('backoffice.crm.churn.index', $storeParam) }}"
                        class="btn btn-outline-secondary {{ !request('risk_level') ? 'active' : '' }}">Tous</a>
                    <a href="{{ route('backoffice.crm.churn.index', $storeParam + ['risk_level' => 'critical']) }}"
                        class="btn btn-outline-danger {{ request('risk_level') === 'critical' ? 'active' : '' }}">Critique</a>
                    <a href="{{ route('backoffice.crm.churn.index', $storeParam + ['risk_level' => 'high']) }}"
                        class="btn btn-outline-warning {{ request('risk_level') === 'high' ? 'active' : '' }}">Élevé</a>
                    <a href="{{ route('backoffice.crm.churn.index', $storeParam + ['risk_level' => 'medium']) }}"
                        class="btn btn-outline-info {{ request('risk_level') === 'medium' ? 'active' : '' }}">Modéré</a>
                    <a href="{{ route('backoffice.crm.churn.index', $storeParam + ['risk_level' => 'low']) }}"
                        class="btn btn-outline-success {{ request('risk_level') === 'low' ? 'active' : '' }}">Faible</a>
                </div>

                {{-- Recompute --}}
                <form method="POST" action="{{ route('backoffice.crm.churn.recompute') }}" class="ms-auto">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                        onclick="return confirm('Lancer le recalcul des scores de churn pour tous les centres ?')">
                        <i class="ph-duotone ph-arrows-clockwise me-1"></i> Recalculer
                    </button>
                </form>

            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Étudiants à risque</h5>
            <small class="text-muted">{{ $scores->total() }} résultats</small>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Centre</th>
                            <th>Score</th>
                            <th>Niveau de risque</th>
                            <th>Signaux principaux</th>
                            <th>Calculé le</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($scores as $churn)
                            @php
                                $badgeClass = match($churn->risk_level) {
                                    'critical' => 'bg-danger',
                                    'high'     => 'bg-warning text-dark',
                                    'medium'   => 'bg-info text-dark',
                                    default    => 'bg-success',
                                };
                                $levelLabel = match($churn->risk_level) {
                                    'critical' => 'Critique',
                                    'high'     => 'Élevé',
                                    'medium'   => 'Modéré',
                                    default    => 'Faible',
                                };
                                $topSignals = array_slice($churn->signals ?? [], 0, 2);
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $churn->student_name ?? "Étudiant #{$churn->crm_student_id}" }}</strong>
                                    <br><small class="text-muted">ID CRM: {{ $churn->crm_student_id }}</small>
                                </td>
                                <td>
                                    @php
                                        $site = $churn->crm_store_id
                                            ? $sites->firstWhere('crm_store_id', $churn->crm_store_id)
                                            : null;
                                    @endphp
                                    {{ $site?->name ?? ($churn->crm_store_id ? "Store #{$churn->crm_store_id}" : '—') }}
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }} fs-6">{{ $churn->score }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }}">{{ $levelLabel }}</span>
                                </td>
                                <td>
                                    @forelse($topSignals as $signal)
                                        <small class="d-block text-muted"><i class="ph-duotone ph-dot-outline me-1"></i>{{ $signal }}</small>
                                    @empty
                                        <small class="text-muted">—</small>
                                    @endforelse
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $churn->computed_at ? $churn->computed_at->format('d/m/Y H:i') : '—' }}
                                    </small>
                                </td>
                                <td>
                                    <a href="{{ route('backoffice.crm.churn.show', $churn->crm_student_id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="ph-duotone ph-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Aucun score de churn disponible.
                                    <br>Utilisez le bouton <strong>Recalculer</strong> pour lancer le calcul.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $scores->links() }}
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
