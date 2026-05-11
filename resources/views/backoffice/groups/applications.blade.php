@extends('layouts.main')

@section('title', 'Inscriptions du Groupe')
@section('breadcrumb-item', 'Groupes')
@section('breadcrumb-item-link', route('backoffice.groups.index'))
@section('breadcrumb-item-active', 'Inscriptions')

@section('page-animation', 'animate__fadeIn')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/animate.min.css') }}">
@endsection

@php
    $appsCollection = $applications instanceof \Illuminate\Pagination\AbstractPaginator
        ? $applications->getCollection()
        : collect($applications);

    $approvedCount = $appsCollection->where('status', 'approved')->count();
    $rejectedCount = $appsCollection->where('status', 'rejected')->count();
    $pendingCount  = $appsCollection->where('status', 'pending')->count();

    $currentStudentsCount = $approvedCount;

    $appsAll      = $appsCollection;
    $appsApproved = $appsCollection->where('status', 'approved');
    $appsPending  = $appsCollection->where('status', 'pending');
    $appsRejected = $appsCollection->where('status', 'rejected');
@endphp

@section('content')
    <!-- [ Main Content ] start -->
    <div class="row">
        <div class="col-12">

            {{-- =========================
            ALERTS (GLOBAL / DYNAMIC)
            ========================= --}}

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="alert alert-danger animate__animated animate__shakeX">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Status action (approve / reject) --}}
            @if(session('status_action'))
                <div class="alert alert-{{ session('status_type', 'success') }}
                            animate__animated
                            {{ session('status_type') === 'danger' ? 'animate__shakeX' : 'animate__fadeInDown' }}">
                    {{ session('status_action') }}
                </div>
            @endif

            {{-- Backward compatibility (old flashes) --}}
            @if(!session('status_action'))
                @if(session('success'))
                    <div class="alert alert-success animate__animated animate__fadeInDown">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning animate__animated animate__fadeInDown">
                        {{ session('warning') }}
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info animate__animated animate__fadeInDown">
                        {{ session('info') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger animate__animated animate__shakeX">
                        {{ session('error') }}
                    </div>
                @endif
            @endif

            {{-- =========================
            HEADER
            ========================= --}}
            <div class="card animate__animated animate__fadeIn">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        Inscriptions – {{ $group->name }}
                    </h5>
                    <a href="{{ route('backoffice.groups.index') }}" class="btn btn-secondary">
                        Retour
                    </a>
                </div>

                <div class="card-body">

                    {{-- =========================
                    SUMMARY CARDS (Theme style like invoice)
                    ========================= --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="card mb-0">
                                <div class="card-body">
                                    <div class="mb-2 d-flex align-items-center justify-content-between gap-1">
                                        <h6 class="mb-0">Étudiants actuels</h6>
                                        <span class="avtar rounded-circle bg-light-success">
                                            {{ $currentStudentsCount }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="mb-0">{{ $currentStudentsCount }}</h5>
                                        <p class="mb-0 text-muted">approuvés dans ce groupe</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="card mb-0">
                                <div class="card-body">
                                    <div class="mb-2 d-flex align-items-center justify-content-between gap-1">
                                        <h6 class="mb-0">Demandes approuvées</h6>
                                        <span class="avtar rounded-circle bg-light-primary">
                                            {{ $approvedCount }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="mb-0">{{ $approvedCount }}</h5>
                                        <p class="mb-0 text-muted">applications approuvées</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-lg-4">
                            <div class="card mb-0">
                                <div class="card-body">
                                    <div class="mb-2 d-flex align-items-center justify-content-between gap-1">
                                        <h6 class="mb-0">Demandes rejetées</h6>
                                        <span class="avtar rounded-circle bg-light-danger">
                                            {{ $rejectedCount }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="mb-0">{{ $rejectedCount }}</h5>
                                        <p class="mb-0 text-muted">applications refusées</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- =========================
                    TABS (Theme invoice style)
                    ========================= --}}
                    <ul class="nav nav-tabs invoice-tab border-bottom mb-3" id="appsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-all" data-bs-toggle="tab"
                                data-bs-target="#pane-all" type="button" role="tab"
                                aria-controls="pane-all" aria-selected="true">
                                <span class="d-flex align-items-center gap-2">
                                    All
                                    <span class="avtar rounded-circle bg-light-primary">{{ $appsAll->count() }}</span>
                                </span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-approved" data-bs-toggle="tab"
                                data-bs-target="#pane-approved" type="button" role="tab"
                                aria-controls="pane-approved" aria-selected="false">
                                <span class="d-flex align-items-center gap-2">
                                    Approved
                                    <span class="avtar rounded-circle bg-light-success">{{ $appsApproved->count() }}</span>
                                </span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-pending" data-bs-toggle="tab"
                                data-bs-target="#pane-pending" type="button" role="tab"
                                aria-controls="pane-pending" aria-selected="false">
                                <span class="d-flex align-items-center gap-2">
                                    Pending
                                    <span class="avtar rounded-circle bg-light-warning">{{ $appsPending->count() }}</span>
                                </span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-rejected" data-bs-toggle="tab"
                                data-bs-target="#pane-rejected" type="button" role="tab"
                                aria-controls="pane-rejected" aria-selected="false">
                                <span class="d-flex align-items-center gap-2">
                                    Rejected
                                    <span class="avtar rounded-circle bg-light-danger">{{ $appsRejected->count() }}</span>
                                </span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="appsTabContent">

                        {{-- TAB: ALL --}}
                        <div class="tab-pane fade show active" id="pane-all" role="tabpanel" aria-labelledby="tab-all" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="pc-dt-all">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nom</th>
                                            <th>WhatsApp</th>
                                            <th>Email</th>
                                            <th>Birthday</th>
                                            <th>Date demande</th>
                                            <th>Statut</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($appsAll as $app)
                                            @php $status = $app->status ?? 'pending'; @endphp
                                            <tr>
                                                <td>{{ $app->id }}</td>
                                                <td>{{ $app->full_name ?? '-' }}</td>
                                                <td>{{ $app->whatsapp_number ?? '-' }}</td>
                                                <td>{{ $app->email ?? '-' }}</td>
                                                <td>{{ $app->birthday ? \Carbon\Carbon::parse($app->birthday)->format('d/m/Y') : '-' }}</td>
                                                <td>{{ $app->created_at ? \Carbon\Carbon::parse($app->created_at)->format('d/m/Y H:i') : '-' }}</td>
                                                <td>
                                                    @if($status === 'approved')
                                                        <span class="badge bg-light-success">Approved</span>
                                                    @elseif($status === 'rejected')
                                                        <span class="badge bg-light-danger">Rejected</span>
                                                    @else
                                                        <span class="badge bg-light-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <form action="{{ route('backoffice.groups.applications.approve', [$group->id, $app->id]) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="avtar avtar-s btn-link-success btn-pc-default border-0"
                                                            @disabled(($app->status ?? 'pending') === 'approved')
                                                            title="Approve">
                                                            <i class="ti ti-check f-20"></i>
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('backoffice.groups.applications.reject', [$group->id, $app->id]) }}"
                                                        method="POST" class="d-inline ms-1">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="avtar avtar-s btn-link-danger btn-pc-default border-0"
                                                            @disabled(($app->status ?? 'pending') === 'rejected')
                                                            title="Reject">
                                                            <i class="ti ti-x f-20"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8">
                                                    <div class="alert alert-info mb-0">
                                                        Aucun étudiant inscrit pour le moment.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if(method_exists($applications, 'links'))
                                <div class="mt-3">
                                    {{ $applications->links() }}
                                </div>
                            @endif
                        </div>

                        {{-- TAB: APPROVED --}}
                        <div class="tab-pane fade" id="pane-approved" role="tabpanel" aria-labelledby="tab-approved" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="pc-dt-approved">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nom</th>
                                            <th>WhatsApp</th>
                                            <th>Email</th>
                                            <th>Birthday</th>
                                            <th>Date demande</th>
                                            <th>Statut</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($appsApproved as $app)
                                            <tr>
                                                <td>{{ $app->id }}</td>
                                                <td>{{ $app->full_name ?? '-' }}</td>
                                                <td>{{ $app->whatsapp_number ?? '-' }}</td>
                                                <td>{{ $app->email ?? '-' }}</td>
                                                <td>{{ $app->birthday ? \Carbon\Carbon::parse($app->birthday)->format('d/m/Y') : '-' }}</td>
                                                <td>{{ $app->created_at ? \Carbon\Carbon::parse($app->created_at)->format('d/m/Y H:i') : '-' }}</td>
                                                <td><span class="badge bg-light-success">Approved</span></td>
                                                <td class="text-end">
                                                    <form action="{{ route('backoffice.groups.applications.reject', [$group->id, $app->id]) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="avtar avtar-s btn-link-danger btn-pc-default border-0" title="Reject">
                                                            <i class="ti ti-x f-20"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8">
                                                    <div class="alert alert-info mb-0">Aucun étudiant approuvé.</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB: PENDING --}}
                        <div class="tab-pane fade" id="pane-pending" role="tabpanel" aria-labelledby="tab-pending" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="pc-dt-pending">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nom</th>
                                            <th>WhatsApp</th>
                                            <th>Email</th>
                                            <th>Birthday</th>
                                            <th>Date demande</th>
                                            <th>Statut</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($appsPending as $app)
                                            <tr>
                                                <td>{{ $app->id }}</td>
                                                <td>{{ $app->full_name ?? '-' }}</td>
                                                <td>{{ $app->whatsapp_number ?? '-' }}</td>
                                                <td>{{ $app->email ?? '-' }}</td>
                                                <td>{{ $app->birthday ? \Carbon\Carbon::parse($app->birthday)->format('d/m/Y') : '-' }}</td>
                                                <td>{{ $app->created_at ? \Carbon\Carbon::parse($app->created_at)->format('d/m/Y H:i') : '-' }}</td>
                                                <td><span class="badge bg-light-warning">Pending</span></td>
                                                <td class="text-end">
                                                    <form action="{{ route('backoffice.groups.applications.approve', [$group->id, $app->id]) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="avtar avtar-s btn-link-success btn-pc-default border-0" title="Approve">
                                                            <i class="ti ti-check f-20"></i>
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('backoffice.groups.applications.reject', [$group->id, $app->id]) }}"
                                                        method="POST" class="d-inline ms-1">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="avtar avtar-s btn-link-danger btn-pc-default border-0" title="Reject">
                                                            <i class="ti ti-x f-20"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8">
                                                    <div class="alert alert-info mb-0">Aucune demande en attente.</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB: REJECTED --}}
                        <div class="tab-pane fade" id="pane-rejected" role="tabpanel" aria-labelledby="tab-rejected" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="pc-dt-rejected">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nom</th>
                                            <th>WhatsApp</th>
                                            <th>Email</th>
                                            <th>Birthday</th>
                                            <th>Date demande</th>
                                            <th>Statut</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($appsRejected as $app)
                                            <tr>
                                                <td>{{ $app->id }}</td>
                                                <td>{{ $app->full_name ?? '-' }}</td>
                                                <td>{{ $app->whatsapp_number ?? '-' }}</td>
                                                <td>{{ $app->email ?? '-' }}</td>
                                                <td>{{ $app->birthday ? \Carbon\Carbon::parse($app->birthday)->format('d/m/Y') : '-' }}</td>
                                                <td>{{ $app->created_at ? \Carbon\Carbon::parse($app->created_at)->format('d/m/Y H:i') : '-' }}</td>
                                                <td><span class="badge bg-light-danger">Rejected</span></td>
                                                <td class="text-end">
                                                    <form action="{{ route('backoffice.groups.applications.approve', [$group->id, $app->id]) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="avtar avtar-s btn-link-success btn-pc-default border-0" title="Approve">
                                                            <i class="ti ti-check f-20"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8">
                                                    <div class="alert alert-info mb-0">Aucune demande rejetée.</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div> {{-- /tab-content --}}

                </div> {{-- /card-body --}}
            </div> {{-- /card --}}

        </div>
    </div>
    <!-- [ Main Content ] end -->
@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/plugins/simple-datatables.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableIds = ['pc-dt-all', 'pc-dt-approved', 'pc-dt-pending', 'pc-dt-rejected'];

            tableIds.forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    new simpleDatatables.DataTable(el);
                }
            });
        });
    </script>
@endsection
