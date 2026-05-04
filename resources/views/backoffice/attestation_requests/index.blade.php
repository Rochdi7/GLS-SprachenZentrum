@extends('layouts.main')

@section('title', 'Demandes d\'attestation')
@section('breadcrumb-item', 'Ecole')
@section('breadcrumb-item-active', 'Demandes d\'attestation')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')
    @if (session('success') || session('error'))
        <div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
            {{ session('success') ?? session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h5 class="mb-3 mb-sm-0">Demandes d'attestation</h5>

                        <div class="btn-group" role="group" aria-label="Filtre statut">
                            <a href="{{ route('backoffice.attestation_requests.index') }}"
                               class="btn btn-sm {{ !$currentStatus ? 'btn-primary' : 'btn-outline-secondary' }}">
                                Toutes <span class="badge bg-light text-dark ms-1">{{ $counts['all'] }}</span>
                            </a>
                            <a href="{{ route('backoffice.attestation_requests.index', ['status' => 'pending']) }}"
                               class="btn btn-sm {{ $currentStatus === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                                En attente <span class="badge bg-light text-dark ms-1">{{ $counts['pending'] }}</span>
                            </a>
                            <a href="{{ route('backoffice.attestation_requests.index', ['status' => 'accepted']) }}"
                               class="btn btn-sm {{ $currentStatus === 'accepted' ? 'btn-success' : 'btn-outline-success' }}">
                                Acceptées <span class="badge bg-light text-dark ms-1">{{ $counts['accepted'] }}</span>
                            </a>
                            <a href="{{ route('backoffice.attestation_requests.index', ['status' => 'refused']) }}"
                               class="btn btn-sm {{ $currentStatus === 'refused' ? 'btn-danger' : 'btn-outline-danger' }}">
                                Refusées <span class="badge bg-light text-dark ms-1">{{ $counts['refused'] }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Étudiant</th>
                                    <th>Email</th>
                                    <th>Groupe (saisi)</th>
                                    <th>Niveau</th>
                                    <th>Notes</th>
                                    <th>Statut</th>
                                    <th>Reçue le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $r)
                                    <tr>
                                        <td>{{ $r->id }}</td>
                                        <td>{{ $r->last_name }} {{ $r->first_name }}</td>
                                        <td><a href="mailto:{{ $r->email }}">{{ $r->email }}</a></td>
                                        <td><em>{{ $r->group_name }}</em></td>
                                        <td><span class="badge bg-light-primary text-primary">{{ $r->level }}</span></td>
                                        <td>
                                            @if(filled($r->notes))
                                                <span title="{{ $r->notes }}" style="display:inline-block;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle;">
                                                    {{ \Illuminate\Support\Str::limit($r->notes, 60) }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($r->status === 'pending')
                                                <span class="badge bg-light-warning text-warning">En attente</span>
                                            @elseif ($r->status === 'accepted')
                                                <span class="badge bg-light-success text-success">Acceptée</span>
                                            @else
                                                <span class="badge bg-light-danger text-danger">Refusée</span>
                                            @endif
                                        </td>
                                        <td>{{ $r->created_at?->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('backoffice.attestation_requests.show', $r->id) }}"
                                               class="avtar avtar-xs btn-link-secondary me-2" title="Voir">
                                                <i class="ti ti-eye f-20"></i>
                                            </a>
                                            @can('attestations.delete')
                                                <form action="{{ route('backoffice.attestation_requests.destroy', $r->id) }}" method="POST" class="d-inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                                            onclick="return confirm('Supprimer cette demande ?')"
                                                            title="Supprimer">
                                                        <i class="ti ti-trash f-20"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">Aucune demande pour ce filtre.</td>
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
