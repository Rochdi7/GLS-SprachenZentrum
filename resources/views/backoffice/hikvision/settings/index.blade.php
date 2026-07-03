@extends('layouts.main')

@section('title', 'Hikvision - Parametres API')
@section('breadcrumb-item', 'Compte du partenaire tiers')
@section('breadcrumb-item-active', 'Parametres API')

@section('content')
    @include('backoffice.hikvision.partials._nav')

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Configuration</p><h3 class="mb-0">{{ $config['configured'] ? 'OK' : 'Incomplete' }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Logs sync</p><h3 class="mb-0">{{ number_format($logSummary['total_logs']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Logs en echec</p><h3 class="mb-0">{{ number_format($logSummary['failed_logs']) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Webhooks en attente</p><h3 class="mb-0">{{ number_format($logSummary['pending_webhooks']) }}</h3></div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Configuration chargee via config()</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">HIKVISION_BASE_URL</label>
                            <input type="text" class="form-control" value="{{ $config['base_url'] }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Timeout</label>
                            <input type="text" class="form-control" value="{{ $config['timeout'] }} secondes" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">HIKVISION_API_KEY</label>
                            <input type="text" class="form-control" value="{{ $config['api_key'] }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">HIKVISION_API_SECRET</label>
                            <input type="text" class="form-control" value="{{ $config['api_secret'] }}" readonly>
                        </div>
                    </div>
                    <div class="alert alert-light-primary border mt-4 mb-0">
                        Les secrets ne sont jamais affiches en clair. Les vues consomment uniquement les valeurs masquees envoyees par le controleur.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Regles de securite</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        <li>Les credentials Hikvision restent dans le fichier `.env`.</li>
                        <li>Aucune vue ne lit directement `config('hikvision')`.</li>
                        <li>Les secrets sont masques avant affichage.</li>
                        <li>Les logs de synchro stockent uniquement un contexte non sensible.</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Dernier succes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 fw-semibold">{{ $logSummary['latest_success']?->format('d/m/Y H:i') ?? 'Aucun succes enregistre' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
