@extends('layouts.main')

@section('title', 'Tableau de bord WhatsApp')
@section('breadcrumb-item', 'Communication')
@section('breadcrumb-item-active', 'Tableau de bord WhatsApp')

@section('css')
<style>
    .wa-kpi .card-body { padding: 18px; }
    .wa-kpi h3 { font-size: 24px; font-weight: 700; margin: 0; }
    .wa-kpi small { color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; font-size: 11px; }
    .wa-kpi .ico {
        width: 44px; height: 44px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; font-size: 22px;
    }
    .wa-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;color:#fff}
    .wa-b-queued{background:#6b7280} .wa-b-running{background:#0369a1} .wa-b-paused{background:#f59e0b}
    .wa-b-completed{background:#16a34a} .wa-b-stopped{background:#111827}
</style>
@endsection

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="card wa-kpi mb-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="ico" style="background:#dcfce7;color:#16a34a"><i class="ph-duotone ph-megaphone"></i></div>
                <div>
                    <small>Campagnes</small>
                    <h3>{{ $totals['campaigns'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card wa-kpi mb-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="ico" style="background:#dbeafe;color:#0369a1"><i class="ph-duotone ph-users-three"></i></div>
                <div>
                    <small>Destinataires</small>
                    <h3>{{ number_format($totals['recipients'], 0, ',', ' ') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card wa-kpi mb-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="ico" style="background:#dcfce7;color:#16a34a"><i class="ph-duotone ph-paper-plane-tilt"></i></div>
                <div>
                    <small>Messages envoyés</small>
                    <h3>{{ number_format($totals['sent'], 0, ',', ' ') }}</h3>
                    <small class="text-success">Taux de réussite : {{ $totals['success_rate'] }}%</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card wa-kpi mb-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="ico" style="background:#fee2e2;color:#ef4444"><i class="ph-duotone ph-x-circle"></i></div>
                <div>
                    <small>Échecs</small>
                    <h3>{{ number_format($totals['failed'], 0, ',', ' ') }}</h3>
                    <small class="text-muted">En attente : {{ $totals['pending'] }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="card mb-0">
            <div class="card-body text-center">
                <span class="wa-badge wa-b-queued mb-2 d-inline-block">EN FILE</span>
                <h3 class="mt-2 mb-0">{{ $statusCounts['queued'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card mb-0">
            <div class="card-body text-center">
                <span class="wa-badge wa-b-running mb-2 d-inline-block">EN COURS</span>
                <h3 class="mt-2 mb-0">{{ $statusCounts['running'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="card mb-0">
            <div class="card-body text-center">
                <span class="wa-badge wa-b-paused mb-2 d-inline-block">PAUSE</span>
                <h3 class="mt-2 mb-0">{{ $statusCounts['paused'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="card mb-0">
            <div class="card-body text-center">
                <span class="wa-badge wa-b-completed mb-2 d-inline-block">TERMINÉES</span>
                <h3 class="mt-2 mb-0">{{ $statusCounts['completed'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="card mb-0">
            <div class="card-body text-center">
                <span class="wa-badge wa-b-stopped mb-2 d-inline-block">ARRÊTÉES</span>
                <h3 class="mt-2 mb-0">{{ $statusCounts['stopped'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Activité des 14 derniers jours</h5></div>
            <div class="card-body">
                <div id="wa-daily-chart" style="min-height:320px"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Performance par centre</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Centre</th>
                                <th class="text-end">Campagnes</th>
                                <th class="text-end">Destinataires</th>
                                <th class="text-end">Envoyés</th>
                                <th class="text-end">Échecs</th>
                                <th class="text-end">Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perSite as $s)
                                @php $rate = $s['recipients'] ? round($s['sent']*100/$s['recipients'], 1) : 0; @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $s['site_name'] }}</td>
                                    <td class="text-end">{{ $s['campaigns'] }}</td>
                                    <td class="text-end">{{ number_format($s['recipients'], 0, ',', ' ') }}</td>
                                    <td class="text-end text-success fw-semibold">{{ number_format($s['sent'], 0, ',', ' ') }}</td>
                                    <td class="text-end text-danger">{{ $s['failed'] }}</td>
                                    <td class="text-end"><span class="badge bg-light-success">{{ $rate }}%</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Aucune donnée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Statuts</h5></div>
            <div class="card-body">
                <div id="wa-status-chart" style="min-height:280px"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Top utilisateurs</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Utilisateur</th>
                                <th class="text-end">Campagnes</th>
                                <th class="text-end">Envoyés</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perUser as $u)
                                <tr>
                                    <td>{{ $u['user_name'] }}</td>
                                    <td class="text-end">{{ $u['campaigns'] }}</td>
                                    <td class="text-end text-success fw-semibold">{{ $u['sent'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Aucune donnée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Campagnes récentes</h5>
                <a href="{{ route('backoffice.whatsapp_campaigns.index') }}" class="btn btn-sm btn-light-primary">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nom</th>
                                <th>Statut</th>
                                <th>Centre</th>
                                <th>Créée par</th>
                                <th class="text-end">Progression</th>
                                <th>Créée le</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recent as $c)
                                @php
                                    $pct = $c->total ? round((($c->sent + $c->failed) * 100) / $c->total) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('backoffice.whatsapp_campaigns.show', $c) }}" class="fw-semibold">{{ $c->name }}</a>
                                    </td>
                                    <td><span class="wa-badge wa-b-{{ $c->status }}">{{ strtoupper($c->status) }}</span></td>
                                    <td>{{ $c->site?->name ?? '—' }}</td>
                                    <td>{{ $c->user?->name ?? '—' }}</td>
                                    <td class="text-end" style="min-width:160px">
                                        <div class="progress" style="height:6px"><div class="progress-bar bg-success" style="width: {{ $pct }}%"></div></div>
                                        <small class="text-muted">{{ $c->sent }}/{{ $c->total }} · {{ $pct }}%</small>
                                    </td>
                                    <td>{{ $c->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Aucune campagne.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="application/json" id="whatsapp-dashboard-data">
{
    "dailySeries":    @json($dailySeries),
    "statusCounts":   @json($statusCounts),
    "totalCampaigns": {{ $totals['campaigns'] }}
}
</script>
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/backoffice/whatsapp-dashboard.js') }}"></script>
@endsection
