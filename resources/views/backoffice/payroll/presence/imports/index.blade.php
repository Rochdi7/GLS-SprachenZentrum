@extends('layouts.main')

@section('title', 'Historique présence — ' . $group->name)
@section('breadcrumb-item', 'Paiement Professeurs')
@section('breadcrumb-item-link', route('backoffice.payroll.presence.dashboard'))
@section('breadcrumb-item-active', 'Historique')

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Paiement Professeurs</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Group Info --}}
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
                            </span>
                        </div>
                        <div>
                            <a href="{{ route('backoffice.payroll.presence.import.create', ['group_id' => $group->id]) }}"
                               class="btn btn-primary btn-sm">
                                <i class="ph-duotone ph-upload-simple me-1"></i> Nouvel import
                            </a>
                            <a href="{{ route('backoffice.payroll.presence.dashboard') }}"
                               class="btn btn-outline-secondary btn-sm">
                                Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Imports Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Historique des imports de présence</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Version</th>
                                    <th>Mois</th>
                                    <th>Période</th>
                                    <th>Etudiants</th>
                                    <th>Jours</th>
                                    <th>Semaines</th>
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
                                        <td>{{ $import->month->translatedFormat('F Y') }}</td>
                                        <td>
                                            {{ $import->date_start->format('d/m') }}
                                            — {{ $import->date_end->format('d/m/Y') }}
                                        </td>
                                        <td>{{ $import->students_count }}</td>
                                        <td>{{ $import->total_days }}</td>
                                        <td><span class="badge bg-light-info">{{ $import->total_weeks_label }}</span></td>
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
                                            <a href="{{ route('backoffice.payroll.presence.import.show', ['group' => $group->id, 'import' => $import->id]) }}"
                                               class="btn btn-sm btn-outline-primary" title="Voir">
                                                <i class="ph-duotone ph-eye"></i>
                                            </a>
                                            <a href="{{ route('backoffice.payroll.presence.import.edit', ['group' => $group->id, 'import' => $import->id]) }}"
                                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                                <i class="ph-duotone ph-pencil-simple"></i>
                                            </a>
                                            <form action="{{ route('backoffice.payroll.presence.import.destroy', $import) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Supprimer cet import ?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                    <i class="ph-duotone ph-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            Aucun import de présence pour ce groupe.
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
