@extends('layouts.main')

@section('title', 'Rapport CEO — ' . $report->report_date->format('d/m/Y'))
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Rapport CEO ' . $report->report_date->format('d/m/Y'))

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Rapport CEO</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    @php
        $payload         = $report->payload ?? [];
        $attentionItems  = $payload['attention_items'] ?? [];
        $topCenter       = $payload['top_center_today'] ?? null;
        $centersRanking  = $payload['centers_ranking'] ?? [];
    @endphp

    {{-- Header --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="mb-0 d-flex align-items-center gap-2 flex-wrap">
                <i class="ph-duotone ph-chart-bar text-primary"></i>
                Rapport CEO Quotidien &mdash; {{ $report->report_date->format('d/m/Y') }}
                @include('backoffice.crm.partials._snapshot_badge', ['snapshotDate' => $snapshotDate ?? null])
            </h4>
            <small class="text-muted">
                Généré le {{ $report->generated_at?->format('d/m/Y à H:i') ?? '—' }}
            </small>
        </div>
        <div class="col-auto d-flex gap-2 align-items-center">
            @include('backoffice.crm.partials._resync_button')
<a href="{{ route('backoffice.crm.reports.index') }}" class="btn btn-outline-secondary">
                <i class="ph-duotone ph-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">

        {{-- Revenue --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 rounded p-2">
                                <i class="ph-duotone ph-currency-circle-dollar text-success" style="font-size:1.75rem"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Encaissement hier</h6>
                        </div>
                    </div>
                    <h2 class="text-success mb-0">
                        {{ number_format((float) ($report->revenue_yesterday ?? 0), 0, ',', ' ') }}
                        <small class="fs-6">MAD</small>
                    </h2>
                </div>
            </div>
        </div>

        {{-- New registrations --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 rounded p-2">
                                <i class="ph-duotone ph-user-plus text-primary" style="font-size:1.75rem"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Nouvelles inscriptions</h6>
                        </div>
                    </div>
                    <h2 class="text-primary mb-0">
                        {{ $report->new_registrations ?? '—' }}
                    </h2>
                </div>
            </div>
        </div>

        {{-- Top centre encaissement --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 rounded p-2">
                                <i class="ph-duotone ph-trophy text-warning" style="font-size:1.75rem"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Top centre hier</h6>
                        </div>
                    </div>
                    @if ($topCenter)
                        <h2 class="text-warning mb-0" style="font-size:1.4rem">{{ $topCenter['name'] }}</h2>
                        <div class="text-muted small mt-1">
                            {{ number_format($topCenter['amount'], 0, ',', ' ') }} MAD encaissés
                        </div>
                    @else
                        <h2 class="text-muted mb-0">—</h2>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <div class="row g-3 mb-4">

        {{-- Best center --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-3">
                        <i class="ph-duotone ph-trophy me-2 text-warning"></i>Meilleur centre hier
                    </h6>
                    @if ($report->best_center)
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                <i class="ph-duotone ph-trophy me-1"></i>{{ $report->best_center }}
                            </span>
                            <span class="text-muted">Centre le plus performant en recettes</span>
                        </div>
                    @else
                        <p class="text-muted mb-0">Aucun paiement enregistré hier.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-3">
                        <i class="ph-duotone ph-info me-2"></i>Métadonnées du rapport
                    </h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Date du rapport</dt>
                        <dd class="col-sm-7">{{ $report->report_date->format('d/m/Y') }}</dd>

                        <dt class="col-sm-5">Généré le</dt>
                        <dd class="col-sm-7">{{ $report->generated_at?->format('d/m/Y H:i:s') ?? '—' }}</dd>

                        <dt class="col-sm-5">Envoi email</dt>
                        <dd class="col-sm-7">
                            @if ($report->email_sent_at)
                                <span class="badge bg-success">
                                    <i class="ph-duotone ph-check-circle me-1"></i>
                                    Envoyé le {{ $report->email_sent_at->format('d/m/Y à H:i') }}
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="ph-duotone ph-warning-circle me-1"></i>
                                    Email non envoyé
                                </span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

    </div>

    {{-- Centers ranking --}}
    @if (count($centersRanking) > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">
                            <i class="ph-duotone ph-ranking me-2 text-warning"></i>Classement des centres — encaissements hier
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:3rem">#</th>
                                        <th>Centre</th>
                                        <th class="text-end">Encaissé (MAD)</th>
                                        <th style="width:40%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $maxAmount = $centersRanking[0]['amount'] ?? 1; @endphp
                                    @foreach ($centersRanking as $i => $center)
                                        @php
                                            $rank = $i + 1;
                                            $pct  = $maxAmount > 0 ? round($center['amount'] / $maxAmount * 100) : 0;
                                            $medal = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => $rank };
                                            $barClass = $center['amount'] > 0 ? 'bg-success' : 'bg-secondary';
                                        @endphp
                                        <tr class="{{ $center['amount'] == 0 ? 'text-muted' : '' }}">
                                            <td class="fw-bold fs-5">{{ $medal }}</td>
                                            <td>{{ $center['name'] }}</td>
                                            <td class="text-end fw-semibold">
                                                {{ $center['amount'] > 0 ? number_format($center['amount'], 0, ',', ' ') : '—' }}
                                            </td>
                                            <td>
                                                <div class="progress" style="height:8px">
                                                    <div class="progress-bar {{ $barClass }}" style="width:{{ $pct }}%"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Attention items --}}
    @if (count($attentionItems) > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning border-warning">
                    <h6 class="alert-heading mb-3">
                        <i class="ph-duotone ph-warning-circle me-2"></i>Points d'attention
                    </h6>
                    <ul class="mb-0">
                        @foreach($attentionItems as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @else
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-success">
                    <i class="ph-duotone ph-check-circle me-2"></i>
                    Aucun point d'attention — tous les indicateurs sont normaux.
                </div>
            </div>
        </div>
    @endif

    {{-- Actions --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">

                    {{-- Email status badge --}}
                    <div>
                        @if ($report->email_sent_at)
                            <span class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded bg-success bg-opacity-10 border border-success border-opacity-25 text-success" style="font-size:.88rem">
                                <i class="ph-duotone ph-check-circle fs-5"></i>
                                <span>Rapport envoyé par email le <strong>{{ $report->email_sent_at->format('d/m/Y à H:i') }}</strong></span>
                            </span>
                        @else
                            <span class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded bg-warning bg-opacity-10 border border-warning border-opacity-25 text-warning" style="font-size:.88rem">
                                <i class="ph-duotone ph-warning-circle fs-5"></i>
                                <span>Email non envoyé</span>
                            </span>
                        @endif
                    </div>

                    {{-- Action buttons --}}
                    <div class="d-flex gap-2 flex-wrap">
                        {{-- Send / Resend email --}}
                        <form method="POST"
                              action="{{ route('backoffice.crm.reports.resend', ['date' => $report->report_date->toDateString()]) }}"
                              onsubmit="return confirm('{{ $report->email_sent_at ? 'Déjà envoyé le ' . $report->email_sent_at->format('d/m/Y à H:i') . '. Renvoyer quand même ?' : 'Envoyer ce rapport par email ?' }}')">
                            @csrf
                            <button type="submit" class="btn {{ $report->email_sent_at ? 'btn-outline-success' : 'btn-success' }}">
                                <i class="ph-duotone ph-paper-plane-tilt me-1"></i>
                                {{ $report->email_sent_at ? 'Renvoyer l\'email' : 'Envoyer par email' }}
                            </button>
                        </form>

                        {{-- Regenerate --}}
                        <form method="POST" action="{{ route('backoffice.crm.reports.generate') }}">
                            @csrf
                            <input type="hidden" name="date" value="{{ $report->report_date->toDateString() }}">
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="ph-duotone ph-arrows-clockwise me-1"></i> Regénérer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();

        });
    </script>
@endsection
