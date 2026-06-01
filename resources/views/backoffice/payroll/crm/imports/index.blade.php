@extends('layouts.main')

@section('title', 'Historique CRM — ' . $group->name)
@section('breadcrumb-item', 'Paiement Professeurs CRM')
@section('breadcrumb-item-link', route('backoffice.payroll.crm.dashboard'))
@section('breadcrumb-item-active', 'Historique')

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Paiement Professeurs CRM</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">{{ $group->name }}</h5>
                            <span class="text-muted">
                                Professeur : <strong>{{ $group->teacher?->name ?? '—' }}</strong>
                                | Niveau : <span class="badge bg-light-primary">{{ $group->level }}</span>
                                | Horaire : {{ $group->time_range }}
                                | CRM Class ID : <span class="badge bg-light-success">{{ $group->crm_class_id ?? 'Non configuré' }}</span>
                            </span>
                        </div>
                        <div>
                            @if($group->crm_class_id)
                                <a href="{{ route('backoffice.payroll.crm.import.create', ['group_id' => $group->id]) }}"
                                   class="btn btn-success btn-sm">
                                    <i class="ph-duotone ph-cloud-arrow-down me-1"></i> Nouvel import CRM
                                </a>
                            @endif
                            <a href="{{ route('backoffice.payroll.crm.dashboard') }}"
                               class="btn btn-outline-secondary btn-sm">
                                Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Historique des imports CRM</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Version</th>
                                    <th>Source</th>
                                    <th>Période</th>
                                    <th>Etudiants</th>
                                    <th>Jours</th>
                                    <th>Paiement total</th>
                                    <th>Statut</th>
                                    <th>Importé le</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($imports as $import)
                                    @php $summary = $import->paymentSummary; @endphp
                                    <tr>
                                        <td><span class="badge bg-primary">v{{ $import->version }}</span></td>
                                        <td>
                                            @if($import->is_crm_api)
                                                <span class="badge bg-light-success">
                                                    <i class="ph-duotone ph-cloud me-1"></i> CRM API
                                                </span>
                                            @else
                                                <span class="badge bg-light-secondary">
                                                    <i class="ph-duotone ph-file me-1"></i> Excel
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $import->date_start->format('d/m') }}
                                            — {{ $import->date_end->format('d/m/Y') }}
                                        </td>
                                        <td>{{ $import->students_count }}</td>
                                        <td>{{ $import->total_days }}</td>
                                        <td>
                                            @if($summary)
                                                <strong>{{ number_format($summary->total_payment, 2) }} DH</strong>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($summary?->isApproved())
                                                <span class="badge bg-success">Approuvé</span>
                                            @elseif($summary)
                                                <span class="badge bg-warning">En attente</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $import->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('backoffice.payroll.crm.import.show', ['group' => $group->id, 'import' => $import->id]) }}"
                                               class="btn btn-sm btn-outline-primary" title="Voir">
                                                <i class="ph-duotone ph-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            Aucun import CRM pour ce groupe.
                                            @if($group->crm_class_id)
                                                <br><a href="{{ route('backoffice.payroll.crm.import.create', ['group_id' => $group->id]) }}">Créer le premier import CRM</a>
                                            @endif
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
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
