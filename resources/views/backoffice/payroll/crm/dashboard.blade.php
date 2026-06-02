@extends('layouts.main')

@section('title', 'Paiement Professeurs CRM — Tableau de bord')
@section('breadcrumb-item', 'Paiement Professeurs CRM')
@section('breadcrumb-item-active', 'Tableau de bord')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

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

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Groupes liés au CRM</h6>
                    <h3 class="mb-0">{{ $groups->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total paiements CRM</h6>
                    <h3 class="mb-0">
                        {{ number_format($groups->sum(fn($g) => (float) ($g->latestPresenceImport?->paymentSummary?->total_payment ?? 0)), 2) }}
                        DH
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Dernier import CRM</h6>
                    <h3 class="mb-0">
                        @php
                            $lastImport = $groups
                                ->pluck('latestPresenceImport')
                                ->filter(fn($i) => $i?->is_crm_api)
                                ->sortByDesc('created_at')
                                ->first();
                        @endphp
                        {{ $lastImport ? $lastImport->created_at->format('d/m/Y') : '—' }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-end">
                    <a href="{{ route('backoffice.payroll.crm.legacy.import.create') }}" class="btn btn-success">
                        <i class="ph-duotone ph-cloud-arrow-down me-1"></i> Nouvel import CRM
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="ph-duotone ph-info me-2"></i>
                Ce module utilise automatiquement les données de présence depuis le CRM Homeschool.
                Aucun fichier Excel à importer !
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Groupes — Paiement Professeurs (CRM)</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <select id="site-filter" class="form-select form-select-sm" style="width:200px">
                            <option value="">— Tous les centres —</option>
                            @foreach(\App\Models\Site::orderBy('name')->get() as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
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
                                    <tr data-site-id="{{ $group->site_id }}">
                                        <td>
                                            <strong>{{ $group->name }}</strong>
                                            <br><small class="text-muted">{{ $group->time_range }}</small>
                                        </td>
                                        <td>{{ $group->teacher?->name ?? $latest?->crm_teacher_name ?? '—' }}</td>
                                        <td><span class="badge bg-light-primary">{{ $group->level }}</span></td>
                                        <td>
                                            @if ($group->crm_class_id)
                                                <span class="badge bg-light-success">{{ $group->crm_class_id }}</span>
                                            @else
                                                <span class="badge bg-light-warning">Non configuré</span>
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
                                                <a href="{{ route('backoffice.payroll.crm.legacy.group.imports', $group) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Historique CRM">
                                                    <i class="ph-duotone ph-clock-counter-clockwise"></i>
                                                </a>
                                                @if ($latest && $latest->is_crm_api)
                                                    <a href="{{ route('backoffice.payroll.crm.legacy.import.show', ['group' => $group->id, 'import' => $latest->id]) }}"
                                                        class="btn btn-sm btn-outline-success" title="Dernier import CRM">
                                                        <i class="ph-duotone ph-eye"></i>
                                                    </a>
                                                @endif
                                                @if ($group->crm_class_id)
                                                    <a href="{{ route('backoffice.payroll.crm.legacy.import.create', ['crm_class_id' => $group->crm_class_id]) }}"
                                                        class="btn btn-sm btn-outline-info" title="Nouvel import CRM">
                                                        <i class="ph-duotone ph-plus"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            Aucun groupe lié au CRM pour le moment.
                                            <br>Veuillez configurer <code>crm_class_id</code> sur les groupes.
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

            document.getElementById('site-filter').addEventListener('change', function() {
                const siteId = this.value;
                document.querySelectorAll('#pc-dt-simple tbody tr[data-site-id]').forEach(row => {
                    row.style.display = (!siteId || row.dataset.siteId === siteId) ? '' : 'none';
                });
            });
        });
    </script>
@endsection
