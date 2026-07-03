@extends('layouts.main')

@section('title', 'Hikvision - Presence')
@section('breadcrumb-item', 'Compte du partenaire tiers')
@section('breadcrumb-item-active', 'Pointages / Presence')

@section('content')
    @include('backoffice.hikvision.partials._nav')

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Total</p><h3 class="mb-0">{{ number_format($summary['total']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Aujourd'hui</p><h3 class="mb-0">{{ number_format($summary['today']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Entrees</p><h3 class="mb-0">{{ number_format($summary['entries']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Sorties</p><h3 class="mb-0">{{ number_format($summary['exits']) }}</h3></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Personne, appareil, identifiant externe">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date debut</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Statut</label>
                    <input type="text" name="status" value="{{ request('status') }}" class="form-control" placeholder="ok">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    <a href="{{ route('backoffice.hikvision.attendance.index') }}" class="btn btn-light-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-header">
            <h5 class="mb-0">Flux de pointage</h5>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Personne</th>
                            <th>Appareil</th>
                            <th>Direction</th>
                            <th>Verification</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendance as $row)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $row->person?->full_name ?: ($row->person_external_id ?? 'N/A') }}</div>
                                    <small class="text-muted">{{ $row->external_id ?? 'Sans identifiant' }}</small>
                                </td>
                                <td>{{ $row->device?->name ?? ($row->device_external_id ?? 'N/A') }}</td>
                                <td>{{ $row->direction ?? 'N/A' }}</td>
                                <td>{{ $row->verification_mode ?? 'N/A' }}</td>
                                <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $row->status])</td>
                                <td>{{ $row->occurred_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('backoffice.hikvision.attendance.show', $row) }}" class="btn btn-sm btn-light-primary">
                                        <i class="ph-duotone ph-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-calendar-check', 'title' => 'Aucun pointage trouve', 'message' => 'Aucune donnee de presence ne correspond aux filtres courants.'])</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($attendance->hasPages())
            <div class="card-footer">{{ $attendance->links() }}</div>
        @endif
    </div>
@endsection
