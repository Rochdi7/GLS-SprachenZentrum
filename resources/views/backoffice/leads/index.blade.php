@extends('layouts.main')

@section('title', 'Gestion des Leads')
@section('breadcrumb-item', 'Leads')
@section('breadcrumb-item-active', 'Liste')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@php
    $totalLeads = $consultations->count() + $inscriptions->count() + $applications->count();
@endphp

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

    <div class="row">
        <div class="col-12">

            {{-- Centre Filter --}}
            <div class="card mb-4">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('backoffice.leads.index') }}">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-sm-auto">
                                <label class="form-label fw-semibold mb-1"><i class="ti ti-filter me-1"></i> Filtrer par centre</label>
                            </div>
                            <div class="col-12 col-sm">
                                <select name="centre" class="form-select" onchange="this.form.submit()">
                                    <option value="all" {{ $centreFilter === 'all' ? 'selected' : '' }}>
                                        Tous les centres
                                    </option>
                                    <option value="online" {{ $centreFilter === 'online' ? 'selected' : '' }}>
                                        En ligne ({{ $centreCounts[0] ?? 0 }})
                                    </option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}" {{ $centreFilter == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }} ({{ $centreCounts[$site->id] ?? 0 }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($centreFilter !== 'all')
                                <div class="col-12 col-sm-auto">
                                    <a href="{{ route('backoffice.leads.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary w-100">
                                        <i class="ti ti-x me-1"></i> Reset
                                    </a>
                                </div>
                            @endif
                            <div class="col-12 col-sm-auto">
                                <a href="{{ route('backoffice.leads.stats', ['centre' => $centreFilter]) }}" class="btn btn-primary w-100">
                                    <i class="ti ti-chart-bar me-1"></i> Statistiques
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Total Leads</p>
                                    <h4 class="mb-0">{{ $totalLeads }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-primary rounded-circle">
                                    <i class="ti ti-users f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Consultations</p>
                                    <h4 class="mb-0">{{ $consultations->count() }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-info rounded-circle">
                                    <i class="ti ti-messages f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Inscriptions</p>
                                    <h4 class="mb-0">{{ $inscriptions->count() }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-success rounded-circle">
                                    <i class="ti ti-clipboard-list f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Applications</p>
                                    <h4 class="mb-0">{{ $applications->count() }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-warning rounded-circle">
                                    <i class="ti ti-file-description f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="leadsTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'consultations' ? 'active' : '' }}"
                               href="{{ route('backoffice.leads.index', ['tab' => 'consultations', 'centre' => $centreFilter]) }}">
                                <i class="ti ti-messages me-1"></i> Consultations
                                <span class="badge bg-info ms-1">{{ $consultations->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'inscriptions' ? 'active' : '' }}"
                               href="{{ route('backoffice.leads.index', ['tab' => 'inscriptions', 'centre' => $centreFilter]) }}">
                                <i class="ti ti-clipboard-list me-1"></i> Inscriptions
                                <span class="badge bg-success ms-1">{{ $inscriptions->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'applications' ? 'active' : '' }}"
                               href="{{ route('backoffice.leads.index', ['tab' => 'applications', 'centre' => $centreFilter]) }}">
                                <i class="ti ti-file-description me-1"></i> Applications
                                <span class="badge bg-warning ms-1">{{ $applications->count() }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body pt-3">

                    {{-- CONSULTATIONS TAB --}}
                    @if($tab === 'consultations')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Ville</th>
                                        <th>Sync</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($consultations as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td class="fw-semibold">{{ $item->name }}</td>
                                            <td>{{ $item->email }}</td>
                                            <td>{{ $item->phone }}</td>
                                            <td>{{ $item->city ?? '-' }}</td>
                                            <td>
                                                @if($item->isSyncedToSheet())
                                                    <span class="badge bg-light-success" title="Sync le {{ $item->google_sheet_synced_at->format('d/m/Y H:i') }}">
                                                        <i class="ti ti-check"></i>
                                                    </span>
                                                @else
                                                    <span class="badge bg-light-secondary" title="Non synchronisé">
                                                        <i class="ti ti-cloud-off"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                            <td class="text-end">
                                                <form action="{{ route('backoffice.leads.consultation.destroy', $item) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Supprimer cette consultation ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="avtar avtar-s btn-link-danger btn-pc-default border-0" title="Supprimer">
                                                        <i class="ti ti-trash f-20"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8">
                                                <div class="alert alert-info mb-0">Aucune consultation trouvée.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- INSCRIPTIONS TAB --}}
                    @if($tab === 'inscriptions')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Niveau</th>
                                        <th>Type</th>
                                        <th>Centre</th>
                                        <th>Sync</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inscriptions as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td class="fw-semibold">{{ $item->nom }}</td>
                                            <td>{{ $item->prenom }}</td>
                                            <td>{{ $item->email }}</td>
                                            <td>{{ $item->phone }}</td>
                                            <td>
                                                <span class="badge bg-light-primary">{{ $item->niveau ?? '-' }}</span>
                                            </td>
                                            <td>{{ $item->type_cours ?? '-' }}</td>
                                            <td>{{ $item->site?->name ?? '-' }}</td>
                                            <td>
                                                @if($item->isSyncedToSheet())
                                                    <span class="badge bg-light-success" title="Sync le {{ $item->google_sheet_synced_at->format('d/m/Y H:i') }}">
                                                        <i class="ti ti-check"></i>
                                                    </span>
                                                @else
                                                    <span class="badge bg-light-secondary" title="Non synchronisé">
                                                        <i class="ti ti-cloud-off"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                            <td class="text-end">
                                                <form action="{{ route('backoffice.leads.inscription.destroy', $item) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Supprimer cette inscription ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="avtar avtar-s btn-link-danger btn-pc-default border-0" title="Supprimer">
                                                        <i class="ti ti-trash f-20"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11">
                                                <div class="alert alert-info mb-0">Aucune inscription trouvée.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- APPLICATIONS TAB --}}
                    @if($tab === 'applications')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nom</th>
                                        <th>WhatsApp</th>
                                        <th>Email</th>
                                        <th>Centre</th>
                                        <th>Groupe</th>
                                        <th>Statut</th>
                                        <th>Sync</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td class="fw-semibold">{{ $item->full_name }}</td>
                                            <td>{{ $item->whatsapp_number }}</td>
                                            <td>{{ $item->email }}</td>
                                            <td>{{ $item->group?->site?->name ?? '-' }}</td>
                                            <td>{{ $item->group?->name ?? '-' }}</td>
                                            <td>
                                                @if($item->status === 'approved')
                                                    <span class="badge bg-light-success">Approuvé</span>
                                                @elseif($item->status === 'rejected')
                                                    <span class="badge bg-light-danger">Rejeté</span>
                                                @else
                                                    <span class="badge bg-light-warning">En attente</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->isSyncedToSheet())
                                                    <span class="badge bg-light-success" title="Sync le {{ $item->google_sheet_synced_at->format('d/m/Y H:i') }}">
                                                        <i class="ti ti-check"></i>
                                                    </span>
                                                @else
                                                    <span class="badge bg-light-secondary" title="Non synchronisé">
                                                        <i class="ti ti-cloud-off"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                            <td class="text-end">
                                                <form action="{{ route('backoffice.leads.application.destroy', $item) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Supprimer cette application ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="avtar avtar-s btn-link-danger btn-pc-default border-0" title="Supprimer">
                                                        <i class="ti ti-trash f-20"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10">
                                                <div class="alert alert-info mb-0">Aucune application trouvée.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/plugins/simple-datatables.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('pc-dt-simple');
            if (el) new simpleDatatables.DataTable(el);

            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
