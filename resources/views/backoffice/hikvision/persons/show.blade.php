@extends('layouts.main')

@section('title', 'Hikvision - Detail personne')
@section('breadcrumb-item', 'Compte du partenaire tiers')
@section('breadcrumb-item-active', 'Detail personne')

@section('content')
    @include('backoffice.hikvision.partials._nav')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">{{ $person->full_name ?: $person->external_id }}</h4>
                <p class="text-muted mb-0">{{ $person->external_id }}</p>
            </div>
            <div class="d-flex gap-2">
                @include('backoffice.hikvision.partials._status_badge', ['value' => $person->status])
                <a href="{{ route('backoffice.hikvision.persons.index') }}" class="btn btn-light-secondary btn-sm">Retour</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><small class="text-muted d-block">Matricule</small><span class="fw-semibold">{{ $person->employee_no ?? 'N/A' }}</span></div>
                <div class="col-md-3"><small class="text-muted d-block">Email</small><span class="fw-semibold">{{ $person->email ?? 'N/A' }}</span></div>
                <div class="col-md-3"><small class="text-muted d-block">Telephone</small><span class="fw-semibold">{{ $person->phone ?? 'N/A' }}</span></div>
                <div class="col-md-3"><small class="text-muted d-block">Departement</small><span class="fw-semibold">{{ $person->department ?? 'N/A' }}</span></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Historique de presence recent</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Appareil</th>
                            <th>Direction</th>
                            <th>Verification</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendance as $row)
                            <tr>
                                <td>{{ $row->occurred_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                                <td>{{ $row->device?->name ?? ($row->device_external_id ?? 'N/A') }}</td>
                                <td>{{ $row->direction ?? 'N/A' }}</td>
                                <td>{{ $row->verification_mode ?? 'N/A' }}</td>
                                <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $row->status])</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-calendar-check', 'title' => 'Aucun historique', 'message' => 'Aucun pointage n est encore lie a cette personne.'])</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
