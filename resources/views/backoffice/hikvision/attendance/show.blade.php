@extends('layouts.main')

@section('title', 'Hikvision - Detail pointage')
@section('breadcrumb-item', 'Compte du partenaire tiers')
@section('breadcrumb-item-active', 'Detail pointage')

@section('content')
    @include('backoffice.hikvision.partials._nav')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Evenement de presence</h4>
                <p class="text-muted mb-0">{{ $attendance->external_id ?? 'Sans identifiant externe' }}</p>
            </div>
            <div class="d-flex gap-2">
                @include('backoffice.hikvision.partials._status_badge', ['value' => $attendance->status])
                <a href="{{ route('backoffice.hikvision.attendance.index') }}" class="btn btn-light-secondary btn-sm">Retour</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><small class="text-muted d-block">Personne</small><span class="fw-semibold">{{ $attendance->person?->full_name ?: ($attendance->person_external_id ?? 'N/A') }}</span></div>
                <div class="col-md-4"><small class="text-muted d-block">Appareil</small><span class="fw-semibold">{{ $attendance->device?->name ?? ($attendance->device_external_id ?? 'N/A') }}</span></div>
                <div class="col-md-4"><small class="text-muted d-block">Date</small><span class="fw-semibold">{{ $attendance->occurred_at?->format('d/m/Y H:i:s') ?? 'N/A' }}</span></div>
                <div class="col-md-4"><small class="text-muted d-block">Direction</small><span class="fw-semibold">{{ $attendance->direction ?? 'N/A' }}</span></div>
                <div class="col-md-4"><small class="text-muted d-block">Verification</small><span class="fw-semibold">{{ $attendance->verification_mode ?? 'N/A' }}</span></div>
                <div class="col-md-4"><small class="text-muted d-block">Derniere sync</small><span class="fw-semibold">{{ $attendance->last_synced_at?->format('d/m/Y H:i') ?? 'Jamais' }}</span></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Charge utile stockee</h5></div>
        <div class="card-body">
            @if($attendance->raw_data)
                <pre class="mb-0 small">{{ json_encode($attendance->raw_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                @include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-brackets-curly', 'title' => 'Aucune charge utile', 'message' => 'Aucune donnee brute n a ete stockee pour cet evenement.'])
            @endif
        </div>
    </div>
@endsection
