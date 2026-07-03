@extends('layouts.main')

@section('title', 'Hikvision - Tableau de bord')
@section('breadcrumb-item', 'Compte du partenaire tiers')
@section('breadcrumb-item-active', 'Tableau de bord')

@section('content')
    @include('backoffice.hikvision.partials._nav')

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-xl-2">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Appareils</p>
                    <h3 class="mb-1">{{ number_format($stats['devices_total']) }}</h3>
                    <span class="badge bg-light-primary text-primary">{{ number_format($stats['devices_online']) }} en ligne</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Personnes</p>
                    <h3 class="mb-1">{{ number_format($stats['persons_total']) }}</h3>
                    <span class="badge bg-light-secondary text-secondary">Base synchronisee</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Presence du jour</p>
                    <h3 class="mb-1">{{ number_format($stats['attendance_today']) }}</h3>
                    <span class="badge bg-light-success text-success">Derniers pointages</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Alarmes ouvertes</p>
                    <h3 class="mb-1">{{ number_format($stats['alarms_open']) }}</h3>
                    <span class="badge bg-light-danger text-danger">Suivi requis</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Webhooks en attente</p>
                    <h3 class="mb-1">{{ number_format($stats['webhooks_pending']) }}</h3>
                    <span class="badge bg-light-warning text-warning">File d'entree</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Etat API</p>
                    <h3 class="mb-1">{{ $apiStatus['configured'] ? 'Pret' : 'A verifier' }}</h3>
                    @include('backoffice.hikvision.partials._status_badge', ['value' => $apiStatus['last_sync_status'], 'label' => ucfirst($apiStatus['last_sync_status'])])
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Configuration API</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Base URL</small>
                        <span class="fw-semibold">{{ $apiStatus['base_url'] }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">API Key</small>
                        <code>{{ $apiStatus['api_key'] }}</code>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">API Secret</small>
                        <code>{{ $apiStatus['api_secret'] }}</code>
                    </div>
                    <div>
                        <small class="text-muted d-block">Derniere synchronisation</small>
                        <span class="fw-semibold">{{ $apiStatus['last_sync_at']?->format('d/m/Y H:i') ?? 'Aucune execution' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Derniers logs</h5>
                    <a href="{{ route('backoffice.hikvision.logs.index') }}" class="btn btn-sm btn-light-primary">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Canal</th>
                                    <th>Action</th>
                                    <th>Statut</th>
                                    <th>Debut</th>
                                    <th>Succes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                    <tr>
                                        <td>{{ $log->channel }}</td>
                                        <td>{{ $log->action }}</td>
                                        <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $log->status])</td>
                                        <td>{{ $log->started_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                                        <td>{{ $log->records_success }}/{{ $log->records_total }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-file-text', 'title' => 'Aucun log', 'message' => 'Les executions de synchronisation apparaitront ici.'])</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Appareils recents</h5>
                    <a href="{{ route('backoffice.hikvision.devices.index') }}" class="btn btn-sm btn-light-primary">Appareils</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Appareil</th>
                                    <th>Etat</th>
                                    <th>Vue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDevices as $device)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $device->name }}</div>
                                            <small class="text-muted">{{ $device->serial_number ?? $device->external_id }}</small>
                                        </td>
                                        <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $device->status])</td>
                                        <td>
                                            <a href="{{ route('backoffice.hikvision.devices.show', $device) }}" class="btn btn-sm btn-light-secondary">
                                                <i class="ph-duotone ph-arrow-up-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-devices', 'title' => 'Aucun appareil', 'message' => 'Les appareils synchronises apparaitront ici.'])</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Derniers pointages</h5>
                    <a href="{{ route('backoffice.hikvision.attendance.index') }}" class="btn btn-sm btn-light-primary">Presence</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Personne</th>
                                    <th>Appareil</th>
                                    <th>Heure</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendance as $row)
                                    <tr>
                                        <td>{{ $row->person?->full_name ?: ($row->person_external_id ?? 'N/A') }}</td>
                                        <td>{{ $row->device?->name ?? ($row->device_external_id ?? 'N/A') }}</td>
                                        <td>{{ $row->occurred_at?->format('d/m H:i') ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-calendar-check', 'title' => 'Aucun pointage', 'message' => 'Les evenements de presence apparaitront ici.'])</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Alarmes recentes</h5>
                    <a href="{{ route('backoffice.hikvision.alarms.index') }}" class="btn btn-sm btn-light-primary">Alarmes</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Severite</th>
                                    <th>Declenchement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAlarms as $alarm)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $alarm->alarm_type }}</div>
                                            <small class="text-muted">{{ $alarm->device?->name ?? ($alarm->device_external_id ?? 'N/A') }}</small>
                                        </td>
                                        <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $alarm->severity])</td>
                                        <td>{{ $alarm->triggered_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-warning-circle', 'title' => 'Aucune alarme', 'message' => 'Les remontees de surete apparaitront ici.'])</td>
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
