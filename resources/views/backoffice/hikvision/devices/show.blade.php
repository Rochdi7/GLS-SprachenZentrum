@extends('layouts.main')

@section('title', 'Hikvision - Detail appareil')
@section('breadcrumb-item', 'Compte du partenaire tiers')
@section('breadcrumb-item-active', 'Detail appareil')

@section('content')
    @include('backoffice.hikvision.partials._nav')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">{{ $device->name }}</h4>
                <p class="text-muted mb-0">{{ $device->external_id }}</p>
            </div>
            <div class="d-flex gap-2">
                @include('backoffice.hikvision.partials._status_badge', ['value' => $device->status])
                <a href="{{ route('backoffice.hikvision.devices.index') }}" class="btn btn-light-secondary btn-sm">Retour</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <small class="text-muted d-block">Serial</small>
                    <span class="fw-semibold">{{ $device->serial_number ?? 'N/A' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Adresse IP</small>
                    <span class="fw-semibold">{{ $device->ip_address ?? 'N/A' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Firmware</small>
                    <span class="fw-semibold">{{ $device->firmware_version ?? 'N/A' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Derniere activite</small>
                    <span class="fw-semibold">{{ $device->last_seen_at?->format('d/m/Y H:i') ?? 'Jamais' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Derniers pointages lies</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Personne</th>
                                    <th>Direction</th>
                                    <th>Heure</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendance as $row)
                                    <tr>
                                        <td>{{ $row->person?->full_name ?: ($row->person_external_id ?? 'N/A') }}</td>
                                        <td>{{ $row->direction ?? 'N/A' }}</td>
                                        <td>{{ $row->occurred_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                                        <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $row->status])</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-calendar-check', 'title' => 'Aucun pointage', 'message' => 'Aucun evenement de presence n est lie a cet appareil.'])</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Alarmes recentes</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Severite</th>
                                    <th>Declenchee le</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAlarms as $alarm)
                                    <tr>
                                        <td>{{ $alarm->alarm_type }}</td>
                                        <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $alarm->severity])</td>
                                        <td>{{ $alarm->triggered_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-warning-circle', 'title' => 'Aucune alarme', 'message' => 'Aucune remontee associee a cet appareil.'])</td>
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
