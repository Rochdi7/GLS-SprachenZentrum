@extends('layouts.main')

@section('title', 'Historique des suivis')
@section('breadcrumb-item', 'Agent CRM')
@section('breadcrumb-item-active', 'Historique suivis')

@section('css')
<style>td,th { font-size:.85rem; vertical-align:middle; }</style>
@endsection

@section('content')

<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="ph-duotone ph-clock-countdown text-primary"></i>
            Historique des suivis
        </h4>
    </div>
    <div class="col-auto">
        <a href="{{ route('backoffice.crm.agent.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('backoffice.crm.agent.follow-ups') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small mb-1">Statut</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— Tous —</option>
                    @foreach($statuses as $val => $lbl)
                        <option value="{{ $val }}" {{ $statusFilter === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small mb-1">Agent</label>
                <select name="agent_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— Tous —</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ $agentFilter === $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($statusFilter || $agentFilter)
            <div class="col-auto">
                <a href="{{ route('backoffice.crm.agent.follow-ups') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ph-duotone ph-x me-1"></i> Effacer
                </a>
            </div>
            @endif
            <div class="col-auto ms-auto">
                <span class="text-muted small">{{ $history->total() }} entrées</span>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($history->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="ph-duotone ph-list-dashes fs-1"></i>
                <p class="mt-2">Aucun suivi enregistré.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Etudiant</th>
                        <th>Statut</th>
                        <th>Agent</th>
                        <th>Note</th>
                        <th>Relance</th>
                        <th>Date appel</th>
                        <th class="text-center">Nouvelle note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $fu)
                    <tr>
                        <td class="text-muted">{{ $fu->id }}</td>
                        <td>
                            <strong>#{{ $fu->crm_student_id }}</strong>
                        </td>
                        <td>
                            @php
                            $badge = match($fu->status) {
                                'solved'         => 'bg-success',
                                'interested'     => 'bg-info',
                                'not_interested' => 'bg-dark',
                                'no_answer'      => 'bg-warning text-dark',
                                'contacted'      => 'bg-primary',
                                default          => 'bg-secondary',
                            };
                            @endphp
                            <span class="badge {{ $badge }}">{{ $statuses[$fu->status] ?? $fu->status }}</span>
                        </td>
                        <td>{{ $fu->agent?->name ?? '—' }}</td>
                        <td><small class="text-muted">{{ Str::limit($fu->note, 80) }}</small></td>
                        <td>
                            @if($fu->follow_up_date)
                                <span class="badge {{ $fu->follow_up_date->isPast() && $fu->status !== 'solved' ? 'bg-danger' : 'bg-light text-dark' }}">
                                    {{ $fu->follow_up_date->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><small>{{ $fu->called_at?->setTimezone('Africa/Casablanca')->format('d/m H:i') ?? '—' }}</small></td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-call"
                                    data-student-id="{{ $fu->crm_student_id }}"
                                    data-student-name="#{{ $fu->crm_student_id }}"
                                    data-registration-id="{{ $fu->registration_id }}"
                                    data-bs-toggle="modal" data-bs-target="#callModal">
                                <i class="ph-duotone ph-plus"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-3 py-2">{{ $history->links() }}</div>
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
