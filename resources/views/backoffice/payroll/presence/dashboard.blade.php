@extends('layouts.main')

@section('title', 'Paiement Professeurs — Tableau de bord')
@section('breadcrumb-item', 'Paiement Professeurs')
@section('breadcrumb-item-active', 'Tableau de bord')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    {{-- Toast --}}
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

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Groupes avec imports</h6>
                    <h3 class="mb-0">{{ $groups->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total paiements</h6>
                    <h3 class="mb-0">
                        {{ number_format($groups->sum(fn($g) => (float) ($g->latestPresenceImport?->paymentSummary?->total_payment ?? 0)), 2) }} DH
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Dernier import</h6>
                    <h3 class="mb-0">
                        @php
                            $lastImport = $groups->pluck('latestPresenceImport')->filter()->sortByDesc('created_at')->first();
                        @endphp
                        {{ $lastImport ? $lastImport->created_at->format('d/m/Y') : '—' }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-end">
                    <a href="{{ route('backoffice.payroll.presence.import.create') }}" class="btn btn-primary">
                        <i class="ph-duotone ph-upload-simple me-1"></i> Nouvel import
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Groups Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Groupes — Paiement Professeurs</h5>
                    <a href="{{ route('backoffice.payroll.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ph-duotone ph-arrow-left me-1"></i> Suivi Paiement
                    </a>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>Groupe</th>
                                    <th>Professeur</th>
                                    <th>Niveau</th>
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
                                        <td>{{ $rate ? number_format($rate, 2) . ' DH' : '—' }}</td>
                                        <td>
                                            @if($summary)
                                                <strong>{{ number_format($summary->total_payment, 2) }} DH</strong>
                                                <br><small class="text-muted">{{ $summary->total_students }} étudiants actifs</small>
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
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                                                <a href="{{ route('backoffice.payroll.presence.group.imports', $group) }}"
                                                   class="btn btn-sm btn-outline-primary" title="Historique">
                                                    <i class="ph-duotone ph-clock-counter-clockwise"></i>
                                                </a>
                                                @if($latest)
                                                    <a href="{{ route('backoffice.payroll.presence.import.show', ['group' => $group->id, 'import' => $latest->id]) }}"
                                                       class="btn btn-sm btn-outline-success" title="Dernier import">
                                                        <i class="ph-duotone ph-eye"></i>
                                                    </a>
                                                    <form action="{{ route('backoffice.payroll.presence.import.destroy', $latest) }}"
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('Supprimer le dernier import de présence de ce groupe ?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer le dernier import">
                                                            <i class="ph-duotone ph-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Aucun import de présence pour le moment.
                                            <a href="{{ route('backoffice.payroll.presence.import.create') }}">Importer un fichier</a>
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
        import { DataTable } from "/build/js/plugins/module.js";
        window.dt = new DataTable("#pc-dt-simple");
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
