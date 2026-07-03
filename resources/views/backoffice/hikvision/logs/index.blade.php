@extends('layouts.main')

@section('title', 'Hikvision - Logs de synchronisation')
@section('breadcrumb-item', 'Compte du partenaire tiers')
@section('breadcrumb-item-active', 'Logs de synchronisation')

@section('content')
    @include('backoffice.hikvision.partials._nav')

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Total</p><h3 class="mb-0">{{ number_format($summary['total']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Succes</p><h3 class="mb-0">{{ number_format($summary['success']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Echecs</p><h3 class="mb-0">{{ number_format($summary['failed']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">En cours</p><h3 class="mb-0">{{ number_format($summary['running']) }}</h3></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Canal</label>
                    <input type="text" name="channel" value="{{ request('channel') }}" class="form-control" placeholder="devices, persons, attendance, alarms, webhooks">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="success" @selected(request('status') === 'success')>Success</option>
                        <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                        <option value="running" @selected(request('status') === 'running')>Running</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    <a href="{{ route('backoffice.hikvision.logs.index') }}" class="btn btn-light-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-header">
            <h5 class="mb-0">Executions de synchronisation</h5>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Canal</th>
                            <th>Action</th>
                            <th>Statut</th>
                            <th>Volume</th>
                            <th>Debut</th>
                            <th>Fin</th>
                            <th>Erreur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->channel }}</td>
                                <td>{{ $log->action }}</td>
                                <td>@include('backoffice.hikvision.partials._status_badge', ['value' => $log->status])</td>
                                <td>{{ $log->records_success }}/{{ $log->records_total }} <small class="text-muted">({{ $log->records_failed }} echecs)</small></td>
                                <td>{{ $log->started_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                                <td>{{ $log->completed_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($log->error_message ?? 'Aucune', 80) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">@include('backoffice.hikvision.partials._empty_state', ['icon' => 'ph-file-text', 'title' => 'Aucun log trouve', 'message' => 'Les futures synchronisations Hikvision alimenteront ce journal.'])</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="card-footer">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
