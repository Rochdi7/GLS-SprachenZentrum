@extends('layouts.main')

@section('title', 'À appeler aujourd\'hui')
@section('breadcrumb-item', 'Agent CRM')
@section('breadcrumb-item-active', 'À appeler aujourd\'hui')

@section('css')
<style>
    .risk-badge-critical { background:#dc3545;color:#fff; }
    .risk-badge-high     { background:#fd7e14;color:#fff; }
    td,th { font-size:.85rem; vertical-align:middle; }
    .signal-pill { display:inline-block;font-size:.72rem;background:#f1f3f5;border-radius:4px;padding:1px 6px;margin:1px; }
</style>
@endsection

@section('content')

<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="ph-duotone ph-phone-call text-danger"></i>
            À appeler aujourd'hui
        </h4>
        <small class="text-muted">Étudiants haut/critique risque sans suivi depuis 7 jours</small>
    </div>
    <div class="col-auto">
        <a href="{{ route('backoffice.crm.agent.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
        </a>
    </div>
</div>

@include('backoffice.crm.partials._center')

{{-- Follow-ups due today --}}
@if($followUpsToday->isNotEmpty())
<div class="card mb-3 border-warning">
    <div class="card-header bg-warning bg-opacity-10">
        <h5 class="mb-0 text-warning">
            <i class="ph-duotone ph-calendar-check me-2"></i>
            Relances planifiées aujourd'hui ({{ $followUpsToday->count() }})
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Etudiant</th><th>Agent</th><th>Statut</th><th>Note</th><th class="text-center">Action</th></tr>
                </thead>
                <tbody>
                    @foreach($followUpsToday as $fu)
                    <tr>
                        <td><strong>#{{ $fu->crm_student_id }}</strong></td>
                        <td>{{ $fu->agent?->name ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ \App\Models\AgentFollowUp::STATUSES[$fu->status] ?? $fu->status }}</span></td>
                        <td><small class="text-muted">{{ Str::limit($fu->note, 60) }}</small></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary btn-call"
                                    data-student-id="{{ $fu->crm_student_id }}"
                                    data-student-name="#{{ $fu->crm_student_id }}"
                                    data-registration-id="{{ $fu->registration_id }}"
                                    data-bs-toggle="modal" data-bs-target="#callModal">
                                <i class="ph-duotone ph-phone me-1"></i> Note
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- High/Critical risk without recent contact --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="ph-duotone ph-warning text-danger me-2"></i>
            Haut risque sans contact récent ({{ $scores->total() }})
        </h5>
    </div>
    <div class="card-body p-0">
        @if($scores->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="ph-duotone ph-check-circle fs-1 text-success"></i>
                <p class="mt-2">Tous les étudiants à risque ont été contactés récemment.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Etudiant</th>
                        <th>Score</th>
                        <th>Risque</th>
                        <th>Signaux clés</th>
                        <th class="text-center">Appeler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scores as $s)
                    @php $sig = $s->signals ?? []; $reasons = $sig['reasons'] ?? []; @endphp
                    <tr>
                        <td>
                            <strong>{{ $s->student_name }}</strong>
                            <br><small class="text-muted">#{{ $s->crm_student_id }}</small>
                            @if(!empty($sig['total_unpaid']))
                                <span class="badge bg-danger ms-1">{{ number_format($sig['total_unpaid'],0,',',' ') }} DH</span>
                            @endif
                        </td>
                        <td><strong>{{ $s->score }}/100</strong></td>
                        <td>
                            <span class="badge risk-badge-{{ $s->risk_level }}">{{ strtoupper($s->risk_level) }}</span>
                        </td>
                        <td>
                            @foreach(array_slice($reasons, 0, 2) as $r)
                                <span class="signal-pill">{{ $r }}</span>
                            @endforeach
                        </td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-sm btn-danger btn-call"
                                    data-student-id="{{ $s->crm_student_id }}"
                                    data-student-name="{{ $s->student_name }}"
                                    data-registration-id="{{ $s->registration_id }}"
                                    data-bs-toggle="modal" data-bs-target="#callModal">
                                <i class="ph-duotone ph-phone me-1"></i> Appeler
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-3 py-2">{{ $scores->links() }}</div>
        @endif
    </div>
</div>

@include('backoffice.crm.agent._call_modal')

@endsection

@section('scripts')
<script>
document.querySelectorAll('.btn-call').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('modalStudentName').textContent = this.dataset.studentName;
        document.getElementById('callStudentId').value          = this.dataset.studentId;
        document.getElementById('callRegistrationId').value     = this.dataset.registrationId ?? '';
    });
});
</script>
@endsection
