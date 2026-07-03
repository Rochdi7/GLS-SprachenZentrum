@extends('layouts.main')

@section('title', 'Hikvision - Alarmes')
@section('breadcrumb-item', 'Compte du partenaire tiers')
@section('breadcrumb-item-active', 'Alarmes')

@section('content')
    @include('backoffice.hikvision.partials._nav')

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Total</p><h3 class="mb-0">{{ number_format($summary['total']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Ouvertes</p><h3 class="mb-0">{{ number_format($summary['open']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Resolues</p><h3 class="mb-0">{{ number_format($summary['resolved']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Critiques / High</p><h3 class="mb-0">{{ number_format($summary['critical']) }}</h3></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Type d alarme, appareil, identifiant externe">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Severite</label>
                    <input type="text" name="severity" value="{{ request('severity') }}" class="form-control" placeholder="critical">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <input type="text" name="status" value="{{ request('status') }}" class="form-control" placeholder="open">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    <a href="{{ route('backoffice.hikvision.alarms.index') }}" class="btn btn-light-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-header">
            <h5 class="mb-0">Journal des alarmes</h5>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Appareil</th>
                            <th>Severite</th>
                            <th>Statut</th>
                            <th>Declenchement</th>
                            <th>Resolution</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alarms as $alarm)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $alarm->alarm_type }}</div>
                                    <small class="text-muted">{{ $alarm->external_id ?? 'Sans identifiant' }}</small>
                                </td>
                                <td>{{ $alarm->device?->name ?? ($alarm->device_external_id ?? 'N/A') }}</td>
                                <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $alarm->severity])</td>
                                <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $alarm->status])</td>
                                <td>{{ $alarm->triggered_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                                <td>{{ $alarm->resolved_at?->format('d/m/Y H:i') ?? 'Non resolue' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-warning-circle', 'title' => 'Aucune alarme trouvee', 'message' => 'Les alarmes Hikvision synchronisees apparaitront ici.'])</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($alarms->hasPages())
            <div class="card-footer">{{ $alarms->links() }}</div>
        @endif
    </div>
@endsection
