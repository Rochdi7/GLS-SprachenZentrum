@extends('layouts.backoffice')

@section('title', 'Rapports automatiques')

@section('content')
<div class="container-fluid py-4">

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Rapports automatiques</h4>
            <p class="text-muted small mb-0">
                Envoi automatique — hebdomadaire chaque vendredi, mensuel le 1er du mois ({{ $timezone }})
                @if($autoSendEnabled)
                    <span class="badge bg-success ms-1">Actif</span>
                @else
                    <span class="badge bg-secondary ms-1">Inactif — REPORTS_AUTO_SEND_ENABLED=false</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($testMode)
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-flask fs-5"></i>
            <div>
                <strong>Mode test activé</strong> — tous les rapports sont envoyés uniquement à
                <strong>{{ $testEmail }}</strong>.<br>
                <small>Désactivez avec <code>REPORTS_TEST_MODE=false</code> en production.</small>
            </div>
        </div>
    @endif

    <div class="row g-4">

        {{-- ─── Weekly Reports ─────────────────────────────────────────── --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-calendar-week me-2 text-primary"></i>
                        Rapports hebdomadaires
                    </h5>
                    <small class="text-muted">
                        Période : lundi 00:00 → vendredi 23:59 par défaut. Maximum {{ $maxDays }} jours.
                    </small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('backoffice.reports.scheduled.send-weekly') }}" id="weekly-form">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Type de rapport</label>
                                <select name="type" class="form-select" required>
                                    <option value="weekly-presence">Présences</option>
                                    <option value="weekly-prof-payment">Paiements professeurs</option>
                                    <option value="weekly-unpaid-students">Étudiants impayés</option>
                                    <option value="weekly-group-performance">Performance groupes</option>
                                    <option value="weekly-center-performance">Performance centres</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Date début</label>
                                <input type="date" name="from" class="form-control"
                                    value="{{ old('from', now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString()) }}"
                                    required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Date fin</label>
                                <input type="date" name="to" class="form-control"
                                    value="{{ old('to', now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(4)->toDateString()) }}"
                                    required>
                                <div class="form-text text-muted">Maximum {{ $maxDays }} jours</div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-send me-1"></i> Envoyer
                                </button>
                            </div>
                        </div>

                        <div class="row g-2">
                            @php
                                $reports = [
                                    'weekly-presence'          => ['icon' => 'bi-person-check', 'label' => 'Présences',            'color' => 'text-success'],
                                    'weekly-prof-payment'      => ['icon' => 'bi-cash-stack',   'label' => 'Paiements profs',       'color' => 'text-warning'],
                                    'weekly-unpaid-students'   => ['icon' => 'bi-exclamation-triangle', 'label' => 'Impayés',       'color' => 'text-danger'],
                                    'weekly-group-performance' => ['icon' => 'bi-graph-up',     'label' => 'Perf. groupes',         'color' => 'text-primary'],
                                    'weekly-center-performance'=> ['icon' => 'bi-building',     'label' => 'Perf. centres',         'color' => 'text-info'],
                                ];
                            @endphp
                            @foreach($reports as $key => $r)
                                <div class="col-auto">
                                    <button type="submit"
                                        class="btn btn-outline-secondary btn-sm quick-send"
                                        data-type="{{ $key }}"
                                        title="Envoyer rapport {{ $r['label'] }} pour la semaine courante">
                                        <i class="bi {{ $r['icon'] }} {{ $r['color'] }} me-1"></i>
                                        {{ $r['label'] }}
                                    </button>
                                </div>
                            @endforeach
                            <div class="col-auto">
                                <small class="text-muted d-flex align-items-center h-100">
                                    ↑ Envoi rapide avec la période sélectionnée ci-dessus
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── Monthly Reports ────────────────────────────────────────── --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-calendar-month me-2 text-warning"></i>
                        Rapports mensuels
                    </h5>
                    <small class="text-muted">Envoi automatique le 1er du mois, ou génération manuelle. Un seul mois à la fois.</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('backoffice.reports.scheduled.send-monthly') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Type de rapport</label>
                                <select name="type" class="form-select" required>
                                    <option value="monthly-revenue">Revenus</option>
                                    <option value="monthly-prof-payment">Paiements professeurs</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Mois</label>
                                <select name="month" class="form-select" required>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}"
                                            {{ $m == now()->subMonthNoOverflow()->month ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Année</label>
                                <select name="year" class="form-select" required>
                                    @foreach(range(now()->year, 2024) as $y)
                                        <option value="{{ $y }}"
                                            {{ $y == now()->subMonthNoOverflow()->year ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="bi bi-send me-1"></i> Envoyer rapport mensuel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── Scheduler info ──────────────────────────────────────────── --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-clock-history me-2 text-secondary"></i>
                        Planification automatique
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rapport</th>
                                <th>Fréquence</th>
                                <th>Heure</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Présences</td>
                                <td><span class="badge bg-primary">Hebdo</span></td>
                                <td>Vendredi 00:00</td>
                            </tr>
                            <tr>
                                <td>Paiements professeurs</td>
                                <td><span class="badge bg-primary">Hebdo</span></td>
                                <td>Vendredi 00:00</td>
                            </tr>
                            <tr>
                                <td>Étudiants impayés</td>
                                <td><span class="badge bg-primary">Hebdo</span></td>
                                <td>Vendredi 00:00</td>
                            </tr>
                            <tr>
                                <td>Performance groupes</td>
                                <td><span class="badge bg-primary">Hebdo</span></td>
                                <td>Vendredi 00:00</td>
                            </tr>
                            <tr>
                                <td>Performance centres</td>
                                <td><span class="badge bg-primary">Hebdo</span></td>
                                <td>Vendredi 00:00</td>
                            </tr>
                            <tr>
                                <td>Revenus mensuel</td>
                                <td><span class="badge bg-warning text-dark">Mensuel</span></td>
                                <td>1er du mois 00:00</td>
                            </tr>
                            <tr>
                                <td>Paiements professeurs (mensuel)</td>
                                <td><span class="badge bg-warning text-dark">Mensuel</span></td>
                                <td>1er du mois 00:00</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-muted small mt-2 mb-0">
                        Timezone : <strong>{{ $timezone }}</strong> ·
                        Pour activer : <code>REPORTS_AUTO_SEND_ENABLED=true</code>
                    </p>
                </div>
            </div>
        </div>

        {{-- ─── Send History ────────────────────────────────────────────── --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-list-check me-2 text-secondary"></i>
                        Historique des envois
                    </h5>
                    <small class="text-muted">50 derniers envois</small>
                </div>
                <div class="card-body p-0">
                    @if($logs->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">Aucun envoi enregistré.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Période</th>
                                        <th>Destinataires</th>
                                        <th>Statut</th>
                                        <th>Envoyé par</th>
                                        <th>Date</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                        <tr class="{{ $log->status === 'failed' ? 'table-danger' : '' }}">
                                            <td>
                                                <span class="fw-semibold">{{ \App\Models\ReportSendLog::typeLabel($log->type) }}</span>
                                                <span class="badge bg-light text-secondary ms-1">{{ $log->category }}</span>
                                            </td>
                                            <td class="text-nowrap">
                                                {{ $log->period_from->format('d/m/Y') }}
                                                →
                                                {{ $log->period_to->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                @foreach($log->recipients as $email)
                                                    <span class="badge bg-light text-dark border">{{ $email }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                @if($log->status === 'success')
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>Envoyé
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger" data-bs-toggle="tooltip" title="{{ $log->error }}">
                                                        <i class="bi bi-x-circle me-1"></i>Échec
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-muted small">
                                                {{ $log->sender?->name ?? 'Auto' }}
                                            </td>
                                            <td class="text-muted small text-nowrap">
                                                {{ $log->created_at->setTimezone('Africa/Casablanca')->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                <form method="POST"
                                                    action="{{ route('backoffice.reports.scheduled.resend', $log) }}"
                                                    onsubmit="return confirm('Renvoyer ce rapport ?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-arrow-repeat me-1"></i>Renvoyer
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.quick-send').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const form = document.getElementById('weekly-form');
        form.querySelector('[name="type"]').value = this.dataset.type;
        form.submit();
    });
});

// Bootstrap tooltips for error messages
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});
</script>
@endsection
