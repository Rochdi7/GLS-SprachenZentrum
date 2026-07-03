@extends('layouts.main')

@section('title', 'Hikvision - Personnes')
@section('breadcrumb-item', 'Compte du partenaire tiers')
@section('breadcrumb-item-active', 'Employes / Personnes')

@section('content')
    @include('backoffice.hikvision.partials._nav')

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Total</p><h3 class="mb-0">{{ number_format($summary['total']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Actifs</p><h3 class="mb-0">{{ number_format($summary['active']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Inactifs</p><h3 class="mb-0">{{ number_format($summary['inactive']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Sans email</p><h3 class="mb-0">{{ number_format($summary['without_email']) }}</h3></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom, email, telephone, matricule, identifiant externe">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="enabled" @selected(request('status') === 'enabled')>Enabled</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        <option value="disabled" @selected(request('status') === 'disabled')>Disabled</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    <a href="{{ route('backoffice.hikvision.persons.index') }}" class="btn btn-light-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-header">
            <h5 class="mb-0">Repertoire des personnes</h5>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Personne</th>
                            <th>Matricule</th>
                            <th>Contact</th>
                            <th>Departement</th>
                            <th>Statut</th>
                            <th>Sync</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($persons as $person)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $person->full_name ?: $person->external_id }}</div>
                                    <small class="text-muted">{{ $person->external_id }}</small>
                                </td>
                                <td>{{ $person->employee_no ?? 'N/A' }}</td>
                                <td>
                                    <div>{{ $person->email ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $person->phone ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $person->department ?? 'N/A' }}</td>
                                <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $person->status])</td>
                                <td>{{ $person->last_synced_at?->format('d/m/Y H:i') ?? 'Jamais' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('backoffice.hikvision.persons.show', $person) }}" class="btn btn-sm btn-light-primary">
                                        <i class="ph-duotone ph-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-users-three', 'title' => 'Aucune personne trouvee', 'message' => 'La synchronisation du personnel alimentera cet ecran.'])</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($persons->hasPages())
            <div class="card-footer">{{ $persons->links() }}</div>
        @endif
    </div>
@endsection
