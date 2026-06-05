@extends('layouts.main')

@section('title', 'Agent — Tableau de bord')
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'Agent / Call Center')

@section('css')
    <style>
        .risk-badge-critical { background: #dc3545; color: #fff; }
        .risk-badge-high     { background: #fd7e14; color: #fff; }
        .risk-badge-medium   { background: #ffc107; color: #212529; }
        .risk-badge-low      { background: #198754; color: #fff; }
        .kpi-card h3 { font-size: 1.6rem; font-weight: 700; margin: 0; }
        .kpi-card .kpi-label { font-size: .78rem; color: #6c757d; margin-bottom: .25rem; }
        #agentTable td, #agentTable th { font-size: .85rem; vertical-align: middle; }
        .signal-pill { display:inline-block; font-size:.72rem; background:#f1f3f5; border-radius:4px; padding:1px 6px; margin:1px; }
    </style>
@endsection

@section('content')

{{-- ── Toast ──────────────────────────────────────────────────────────── --}}
@if(session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:99999">
        <div class="toast show" role="alert">
            <div class="toast-header">
                <strong class="me-auto">Agent CRM</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">{{ session('success') }}</div>
        </div>
    </div>
@endif

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="ph-duotone ph-headset text-primary"></i>
            Agent / Call Center
            @include('backoffice.crm.partials._sync_badge')
        </h4>
    </div>
    <div class="col-auto d-flex gap-2">
        <a href="{{ route('backoffice.crm.agent.call-today') }}" class="btn btn-sm btn-danger">
            <i class="ph-duotone ph-phone me-1"></i>
            À appeler aujourd'hui
            @if($kpis['call_today'] > 0)
                <span class="badge bg-white text-danger ms-1">{{ $kpis['call_today'] }}</span>
            @endif
        </a>
        <a href="{{ route('backoffice.crm.agent.follow-ups') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ph-duotone ph-clock-countdown me-1"></i> Historique suivis
        </a>
    </div>
</div>

{{-- ── Center selector ─────────────────────────────────────────────────── --}}
@include('backoffice.crm.partials._center')

{{-- ── KPI cards ───────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @php
    $kpiCards = [
        ['label' => 'À appeler auj.',    'value' => $kpis['call_today'],       'color' => 'danger',  'icon' => 'ph-phone-call',   'href' => route('backoffice.crm.agent.call-today')],
        ['label' => 'Critique',           'value' => $kpis['critical'],          'color' => 'danger',  'icon' => 'ph-warning',      'href' => route('backoffice.crm.agent.index', ['risk_level' => 'critical'])],
        ['label' => 'Haut risque',        'value' => $kpis['high'],              'color' => 'warning', 'icon' => 'ph-trend-up',     'href' => route('backoffice.crm.agent.index', ['risk_level' => 'high'])],
        ['label' => 'Risque moyen',       'value' => $kpis['medium'],            'color' => 'info',    'icon' => 'ph-minus-circle', 'href' => route('backoffice.crm.agent.index', ['risk_level' => 'medium'])],
        ['label' => 'Impayés',            'value' => $kpis['unpaid'],            'color' => 'primary', 'icon' => 'ph-currency-circle-dollar', 'href' => route('backoffice.crm.agent.unpaid')],
        ['label' => 'Suivis aujourd\'hui','value' => $kpis['follow_ups_today'],  'color' => 'success', 'icon' => 'ph-calendar-check','href' => route('backoffice.crm.agent.follow-ups')],
    ];
    @endphp
    @foreach($kpiCards as $card)
    <div class="col-lg-2 col-md-4 col-6">
        <a href="{{ $card['href'] }}" class="text-decoration-none">
            <div class="card kpi-card h-100 border-start border-{{ $card['color'] }} border-3">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <i class="ph-duotone {{ $card['icon'] }} fs-2 text-{{ $card['color'] }}"></i>
                    <div>
                        <div class="kpi-label">{{ $card['label'] }}</div>
                        <h3 class="text-{{ $card['color'] }}">{{ $card['value'] }}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- ── Filters ─────────────────────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('backoffice.crm.agent.index') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small mb-1">Niveau de risque</label>
                <select name="risk_level" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— Tous —</option>
                    @foreach(['critical' => 'Critique', 'high' => 'Haut', 'medium' => 'Moyen', 'low' => 'Faible'] as $val => $lbl)
                        <option value="{{ $val }}" {{ $riskFilter === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            @if($riskFilter)
            <div class="col-auto">
                <a href="{{ route('backoffice.crm.agent.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ph-duotone ph-x me-1"></i> Effacer
                </a>
            </div>
            @endif
            <div class="col-auto ms-auto">
                <span class="text-muted small">{{ $scores->total() }} étudiants</span>
            </div>
        </form>
    </div>
</div>

{{-- ── Risk table ──────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="ph-duotone ph-users text-primary"></i>
        <h5 class="mb-0">Étudiants à risque</h5>
        <span class="badge bg-secondary ms-auto">{{ $scores->total() }}</span>
    </div>
    <div class="card-body p-0">
        @if($scores->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="ph-duotone ph-check-circle fs-1 text-success"></i>
                <p class="mt-2">Aucun étudiant à risque pour ce filtre.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0" id="agentTable">
                <thead class="table-light">
                    <tr>
                        <th>Etudiant</th>
                        <th>Score</th>
                        <th>Risque</th>
                        <th>Signaux</th>
                        <th>Dernier suivi</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scores as $s)
                    @php
                        $sig = $s->signals ?? [];
                        $reasons = $sig['reasons'] ?? [];
                        $lastFollowUp = $latestFollowUps[$s->crm_student_id] ?? null;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $s->student_name }}</strong>
                            <br>
                            <small class="text-muted">#{{ $s->crm_student_id }}</small>
                            @if(!empty($sig['total_unpaid']))
                                <span class="badge bg-light-danger text-danger ms-1">
                                    {{ number_format($sig['total_unpaid'], 0, ',', ' ') }} DH
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <div class="progress" style="width:48px;height:6px">
                                    <div class="progress-bar bg-{{ $s->risk_level === 'critical' || $s->risk_level === 'high' ? 'danger' : ($s->risk_level === 'medium' ? 'warning' : 'success') }}"
                                         style="width:{{ $s->score }}%"></div>
                                </div>
                                <span class="fw-semibold">{{ $s->score }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge risk-badge-{{ $s->risk_level }}">
                                {{ strtoupper($s->risk_level) }}
                            </span>
                        </td>
                        <td>
                            @foreach(array_slice($reasons, 0, 2) as $r)
                                <span class="signal-pill">{{ $r }}</span>
                            @endforeach
                            @if(count($reasons) > 2)
                                {{-- Hidden extra signals, revealed on click --}}
                                <span class="signals-extra d-none">
                                    @foreach(array_slice($reasons, 2) as $r)
                                        <span class="signal-pill">{{ $r }}</span>
                                    @endforeach
                                </span>
                                <span class="signal-pill text-primary signal-toggle"
                                      role="button"
                                      title="Voir tous les signaux"
                                      style="cursor:pointer">
                                    +{{ count($reasons) - 2 }} voir
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($lastFollowUp)
                                <span class="badge
                                    {{ $lastFollowUp->status === 'solved' ? 'bg-success' :
                                       ($lastFollowUp->status === 'no_answer' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                    {{ $statuses[$lastFollowUp->status] ?? $lastFollowUp->status }}
                                </span>
                                <br>
                                <small class="text-muted">{{ $lastFollowUp->created_at->diffForHumans() }}</small>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-call"
                                    data-student-id="{{ $s->crm_student_id }}"
                                    data-student-name="{{ $s->student_name }}"
                                    data-registration-id="{{ $s->registration_id }}"
                                    data-bs-toggle="modal" data-bs-target="#callModal"
                                    title="Ajouter une note">
                                <i class="ph-duotone ph-phone me-1"></i> Note
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-3 py-2">
            {{ $scores->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ── Quick links row ─────────────────────────────────────────────────── --}}
<div class="row g-3 mt-2">
    <div class="col-md-4">
        <a href="{{ route('backoffice.crm.agent.unpaid') }}" class="card text-decoration-none h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="ph-duotone ph-currency-circle-dollar fs-2 text-danger"></i>
                <div>
                    <div class="fw-semibold">Impayés</div>
                    <small class="text-muted">{{ $kpis['unpaid'] }} étudiants avec solde dû</small>
                </div>
                <i class="ph-duotone ph-arrow-right ms-auto text-muted"></i>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('backoffice.crm.agent.new-without-payment') }}" class="card text-decoration-none h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="ph-duotone ph-user-plus fs-2 text-info"></i>
                <div>
                    <div class="fw-semibold">Nouvelles inscriptions sans paiement</div>
                    <small class="text-muted">30 derniers jours</small>
                </div>
                <i class="ph-duotone ph-arrow-right ms-auto text-muted"></i>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('backoffice.crm.agent.follow-ups') }}" class="card text-decoration-none h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="ph-duotone ph-clock-countdown fs-2 text-warning"></i>
                <div>
                    <div class="fw-semibold">Historique des appels</div>
                    <small class="text-muted">{{ $kpis['follow_ups_today'] }} suivis planifiés aujourd'hui</small>
                </div>
                <i class="ph-duotone ph-arrow-right ms-auto text-muted"></i>
            </div>
        </a>
    </div>
</div>

{{-- ── Call modal ──────────────────────────────────────────────────────── --}}
@include('backoffice.crm.agent._call_modal')

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inject student context into modal when button clicked
    document.querySelectorAll('.btn-call').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalStudentName').textContent    = this.dataset.studentName;
            document.getElementById('callStudentId').value             = this.dataset.studentId;
            document.getElementById('callRegistrationId').value        = this.dataset.registrationId ?? '';
        });
    });

    // Expand hidden signals on +N click
    document.querySelectorAll('.signal-toggle').forEach(btn => {
        btn.addEventListener('click', function () {
            const extra = this.previousElementSibling;
            const expanded = !extra.classList.contains('d-none');
            extra.classList.toggle('d-none', expanded);
            this.textContent = expanded
                ? '+' + extra.querySelectorAll('.signal-pill').length + ' voir'
                : 'masquer';
        });
    });

    // Auto-dismiss toast
    document.querySelectorAll('.toast').forEach(el => new bootstrap.Toast(el, {delay:3000}).show());
});
</script>
@endsection
