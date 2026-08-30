@extends('layouts.main')

@section('title', 'Leads Inscription (Étape 1)')
@section('breadcrumb-item', 'Admissions & leads')
@section('breadcrumb-item-active', 'Leads Inscription (Étape 1)')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    {{-- Toast Notifications --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <img src="{{ asset('assets/images/favicon/favicon.svg') }}" class="img-fluid me-2" alt="favicon"
                        style="width: 17px">
                    <strong class="me-auto">GLS Backoffice</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('success') ?? session('error') }}
                </div>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-primary">
                                <i class="ph-duotone ph-user-list f-22"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0 text-muted">Total leads</p>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-success">
                                <i class="ph-duotone ph-calendar-check f-22"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0 text-muted">Aujourd'hui</p>
                            <h4 class="mb-0">{{ $stats['today'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-info">
                                <i class="ph-duotone ph-trend-up f-22"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0 text-muted">Cette semaine</p>
                            <h4 class="mb-0">{{ $stats['this_week'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-warning">
                                <i class="ph-duotone ph-user-minus f-22"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0 text-muted">Non finalisés</p>
                            <h4 class="mb-0">{{ $stats['abandoned'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <div class="mb-3 mb-sm-0">
                            <h5 class="mb-1">Leads Inscription (Étape 1)</h5>
                            <small class="text-muted">
                                Coordonnées enregistrées dès le clic sur « Continuer », même si
                                l'inscription n'a jamais été finalisée.
                            </small>
                        </div>

                        <form method="GET" action="{{ route('backoffice.gls_step1_leads.index') }}"
                            class="d-flex gap-2 flex-wrap">
                            <input type="text" name="q" value="{{ request('q') }}"
                                class="form-control form-control-sm" placeholder="Nom, email, téléphone..."
                                style="min-width: 200px;">

                            <select name="form_source" class="form-select form-select-sm" style="min-width: 130px;">
                                <option value="">Toutes sources</option>
                                @foreach ($sources as $src)
                                    <option value="{{ $src }}" @selected(request('form_source') === $src)>
                                        {{ ucfirst($src) }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                            @if (request()->hasAny(['q', 'form_source']))
                                <a href="{{ route('backoffice.gls_step1_leads.index') }}"
                                    class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
                            @endif

                            {{-- Export Excel : garde les filtres courants, choix du statut --}}
                            @php
                                $exportFilters = array_filter([
                                    'q' => request('q'),
                                    'form_source' => request('form_source'),
                                ]);
                            @endphp
                            <div class="dropdown">
                                <button class="btn btn-sm btn-success dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-file-spreadsheet me-1"></i> Exporter Excel
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('backoffice.gls_step1_leads.export', $exportFilters + ['status' => 'all']) }}">
                                            <i class="ti ti-users me-2"></i> Tous les leads
                                            <span class="badge bg-light-secondary text-secondary ms-1">{{ $leads->count() }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('backoffice.gls_step1_leads.export', $exportFilters + ['status' => 'converted']) }}">
                                            <i class="ti ti-circle-check me-2 text-success"></i> Inscrits
                                            <span class="badge bg-light-success text-success ms-1">{{ $leads->where('is_converted', true)->count() }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('backoffice.gls_step1_leads.export', $exportFilters + ['status' => 'abandoned']) }}">
                                            <i class="ti ti-clock-exclamation me-2 text-warning"></i> Non finalisés
                                            <span class="badge bg-light-warning text-warning ms-1">{{ $leads->where('is_converted', false)->count() }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Nom complet</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Adresse</th>
                                    <th>Source</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                    <tr>
                                        <td>{{ $lead->id }}</td>
                                        <td>
                                            {{ trim($lead->prenom . ' ' . $lead->nom) ?: '—' }}
                                        </td>
                                        <td>
                                            <a href="mailto:{{ $lead->email }}" class="link-primary">
                                                {{ $lead->email }}
                                            </a>
                                        </td>
                                        <td>
                                            @if ($lead->phone)
                                                <a href="tel:{{ $lead->phone }}" class="link-secondary">
                                                    {{ $lead->phone }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $lead->adresse ?: '—' }}</td>
                                        <td>
                                            @if ($lead->form_source)
                                                <span class="badge bg-light-secondary text-secondary">
                                                    {{ ucfirst($lead->form_source) }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($lead->is_converted)
                                                <span class="badge bg-light-success text-success">Inscrit</span>
                                            @else
                                                <span class="badge bg-light-warning text-warning">Non finalisé</span>
                                            @endif
                                        </td>
                                        <td>{{ $lead->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                        <td>
                                            @can('gls_step1_leads.delete')
                                                <form action="{{ route('backoffice.gls_step1_leads.destroy', $lead) }}"
                                                    method="POST" class="d-inline-block">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                                        onclick="return confirm('Supprimer ce lead ?')"
                                                        title="Supprimer" aria-label="Supprimer">
                                                        <i class="ti ti-trash f-20"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            Aucun lead trouvé.
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
            if (toastEl) {
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>
@endsection
