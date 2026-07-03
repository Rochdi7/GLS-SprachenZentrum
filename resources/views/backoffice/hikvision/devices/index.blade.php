@extends('layouts.main')

@section('title', 'Hikvision - Appareils')
@section('breadcrumb-item', 'Compte du partenaire tiers')
@section('breadcrumb-item-active', 'Appareils')

@section('content')
    @include('backoffice.hikvision.partials._nav')

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card"><div class="card-body"><p class="text-muted mb-1">Total</p><h3 class="mb-0">{{ number_format($summary['total']) }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body"><p class="text-muted mb-1">En ligne</p><h3 class="mb-0">{{ number_format($summary['online']) }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body"><p class="text-muted mb-1">Hors ligne</p><h3 class="mb-0">{{ number_format($summary['offline']) }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body"><p class="text-muted mb-1">Attention</p><h3 class="mb-0">{{ number_format($summary['attention']) }}</h3></div></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom, serial, IP, identifiant externe">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="online" @selected(request('status') === 'online')>Online</option>
                        <option value="offline" @selected(request('status') === 'offline')>Offline</option>
                        <option value="connected" @selected(request('status') === 'connected')>Connected</option>
                        <option value="disconnected" @selected(request('status') === 'disconnected')>Disconnected</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    <a href="{{ route('backoffice.hikvision.devices.index') }}" class="btn btn-light-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-header">
            <h5 class="mb-0">Inventaire des appareils</h5>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Appareil</th>
                            <th>Serial</th>
                            <th>IP</th>
                            <th>Statut</th>
                            <th>Firmware</th>
                            <th>Derniere activite</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $device)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $device->name }}</div>
                                    <small class="text-muted">{{ $device->external_id }}</small>
                                </td>
                                <td>{{ $device->serial_number ?? 'N/A' }}</td>
                                <td>{{ $device->ip_address ?? 'N/A' }}</td>
                                <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $device->status])</td>
                                <td>{{ $device->firmware_version ?? 'N/A' }}</td>
                                <td>{{ $device->last_seen_at?->format('d/m/Y H:i') ?? 'Jamais' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('backoffice.hikvision.devices.show', $device) }}" class="btn btn-sm btn-light-primary">
                                        <i class="ph-duotone ph-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-devices', 'title' => 'Aucun appareil trouve', 'message' => 'Affinez les filtres ou lancez une synchronisation Hikvision.'])</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($devices->hasPages())
            <div class="card-footer">{{ $devices->links() }}</div>
        @endif
    </div>
@endsection
