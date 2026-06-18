@extends('layouts.main')

@section('title', 'CRM Wimschool — Paiement Professeurs')
@section('breadcrumb-item', 'Paiement Professeurs')
@section('breadcrumb-item-active', 'CRM Wimschool')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    {{-- Toast --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">CRM Wimschool</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Groupes disponibles</h6>
                    <h3 class="mb-0">{{ $groups->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Dernière synchro</h6>
                    <h3 class="mb-0">
                        @php
                            $lastSync = $syncLogs->first();
                        @endphp
                        {{ $lastSync ? $lastSync->created_at->format('d/m/Y H:i') : '—' }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Synchro réussie</h6>
                    <h3 class="mb-0 text-success">
                        {{ $syncLogs->where('status', 'success')->count() }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-end">
                    <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="ph-duotone ph-database me-1"></i> Legacy
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Sync Form --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Synchroniser la présence</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backoffice.payroll.crm.sync') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="group_id" class="form-label">Groupe</label>
                            <select class="form-select" id="group_id" name="group_id" required>
                                <option value="">Choisissez un groupe</option>
                                @foreach ($allGroups as $group)
                                    <option value="{{ $group->id }}"
                                        {{ !$group->crm_class_id ? 'disabled' : '' }}>
                                        {{ $group->name }}
                                        @if ($group->crm_class_id)
                                            — CRM #{{ $group->crm_class_id }}
                                        @else
                                            — ⚠ CRM non configuré
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_start" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="date_start" name="date_start"
                                    value="{{ now()->startOfMonth()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date_end" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="date_end" name="date_end"
                                    value="{{ now()->endOfMonth()->toDateString() }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="payment_per_student" class="form-label">Taux par étudiant (DH)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="payment_per_student"
                                    name="payment_per_student" step="0.01" placeholder="Ex: 500" required>
                                <span class="input-group-text">DH</span>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-duotone ph-sync me-1"></i> Synchroniser et calculer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sync Logs --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Historique des synchronisations</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Groupe</th>
                                    <th>Période</th>
                                    <th>Statut</th>
                                    <th>Records</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($syncLogs as $log)
                                    <tr>
                                        <td>{{ $log->group?->name ?? '—' }}</td>
                                        <td>
                                            @if ($log->date_start && $log->date_end)
                                                {{ $log->date_start->format('d/m') }} →
                                                {{ $log->date_end->format('d/m') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if ($log->status === 'success')
                                                <span class="badge bg-success">Réussie</span>
                                            @elseif($log->status === 'failed')
                                                <span class="badge bg-danger">Échouée</span>
                                            @else
                                                <span class="badge bg-warning">En attente</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->records_synced }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            Aucune synchronisation pour le moment
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Groups Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Groupes avec statut CRM</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>Groupe</th>
                                    <th>Professeur</th>
                                    <th>Niveau</th>
                                    <th>CRM Class ID</th>
                                    <th>Taux/Etudiant</th>
                                    <th>Dernier paiement</th>
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groups as $group)
                                    @php
                                        $latest = $group->latestPresenceImport;
                                        $summary = $latest?->paymentSummary;
                                        $rate = $latest?->getEffectivePaymentPerStudent();
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $group->name }}</strong>
                                            <br><small class="text-muted">{{ $group->time_range }}</small>
                                        </td>
                                        <td>{{ $group->teacher?->name ?? '—' }}</td>
                                        <td><span class="badge bg-light-primary">{{ $group->level }}</span></td>
                                        <td>
                                            @if ($group->crm_class_id)
                                                <span class="badge bg-light-info">{{ $group->crm_class_id }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $rate ? number_format($rate, 2) . ' DH' : '—' }}</td>
                                        <td>
                                            @if ($summary)
                                                <strong>{{ number_format($summary->total_payment, 2) }} DH</strong>
                                                <br><small class="text-muted">{{ $summary->total_students }} étudiants
                                                    actifs</small>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if ($summary?->isApproved())
                                                <span class="badge bg-success">Approuvé</span>
                                            @elseif($summary)
                                                <span class="badge bg-warning">En attente</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                                                <a href="{{ route('backoffice.payroll.presence.group.imports', $group) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Historique">
                                                    <i class="ph-duotone ph-clock-counter-clockwise"></i>
                                                </a>
                                                @if ($latest)
                                                    <a href="{{ route('backoffice.payroll.presence.import.show', ['group' => $group->id, 'import' => $latest->id]) }}"
                                                        class="btn btn-sm btn-outline-success" title="Dernier import">
                                                        <i class="ph-duotone ph-eye"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            Aucun groupe pour le moment.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script type="module">
        import {
            DataTable
        } from "/build/js/plugins/module.js";
        window.dt = new DataTable("#pc-dt-simple");
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
