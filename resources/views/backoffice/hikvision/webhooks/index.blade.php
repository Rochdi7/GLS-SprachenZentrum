@extends('layouts.main')

@section('title', 'Hikvision - Webhooks')
@section('breadcrumb-item', 'Compte du partenaire tiers')
@section('breadcrumb-item-active', 'Webhooks')

@section('content')
    @include('backoffice.hikvision.partials._nav')

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Total</p><h3 class="mb-0">{{ number_format($summary['total']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Recus</p><h3 class="mb-0">{{ number_format($summary['received']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Traites</p><h3 class="mb-0">{{ number_format($summary['processed']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">En echec</p><h3 class="mb-0">{{ number_format($summary['failed']) }}</h3></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Type d evenement</label>
                    <input type="text" name="event_type" value="{{ request('event_type') }}" class="form-control" placeholder="attendance, alarm, device">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="received" @selected(request('status') === 'received')>Received</option>
                        <option value="processed" @selected(request('status') === 'processed')>Processed</option>
                        <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    <a href="{{ route('backoffice.hikvision.webhooks.index') }}" class="btn btn-light-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-header">
            <h5 class="mb-0">Reception des webhooks</h5>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Signature</th>
                            <th>Statut</th>
                            <th>Recu le</th>
                            <th>Traite le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $event->event_type }}</div>
                                    <small class="text-muted">{{ $event->event_uuid ?? 'Sans UUID' }}</small>
                                </td>
                                <td>{{ $event->source ?? 'N/A' }}</td>
                                <td><code>{{ $event->signature_masked ?? 'N/A' }}</code></td>
                                <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $event->status])</td>
                                <td>{{ $event->received_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                                <td>{{ $event->processed_at?->format('d/m/Y H:i') ?? 'En attente' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-plugs-connected', 'title' => 'Aucun webhook', 'message' => 'Les notifications entrantes Hikvision apparaitront ici.'])</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($events->hasPages())
            <div class="card-footer">{{ $events->links() }}</div>
        @endif
    </div>
@endsection
