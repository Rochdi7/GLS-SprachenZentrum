@extends('layouts.main')

@section('title', 'Nouvelles inscriptions sans paiement')
@section('breadcrumb-item', 'Agent CRM')
@section('breadcrumb-item-active', 'Nouvelles inscriptions sans paiement')

@section('css')
<style>td,th { font-size:.85rem; vertical-align:middle; }</style>
@endsection

@section('content')

<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="ph-duotone ph-user-plus text-info"></i>
            Nouvelles inscriptions sans paiement
        </h4>
        <small class="text-muted">Inscriptions depuis le {{ \Carbon\Carbon::parse($since)->format('d/m/Y') }} avec solde impayé</small>
    </div>
    <div class="col-auto">
        <a href="{{ route('backoffice.crm.agent.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
        </a>
    </div>
</div>

@include('backoffice.crm.partials._center')

<div class="card">
    <div class="card-body p-0">
        @if($rows->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="ph-duotone ph-check-circle fs-1 text-success"></i>
                <p class="mt-2">Aucune nouvelle inscription sans paiement.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Etudiant</th>
                        <th>Centre</th>
                        <th class="text-end">Solde dû</th>
                        <th>Echéance</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                    <tr>
                        <td>
                            <strong>{{ $row->student_name }}</strong>
                            <br><small class="text-muted">#{{ $row->student_id }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light-info text-info">{{ $row->store_name }}</span>
                        </td>
                        <td class="text-end fw-semibold text-danger">
                            {{ number_format($row->rest_amount, 2, ',', ' ') }} DH
                        </td>
                        <td>
                            <small>{{ $row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d/m/Y') : '—' }}</small>
                        </td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-sm btn-outline-info btn-call"
                                    data-student-id="{{ $row->student_id }}"
                                    data-student-name="{{ $row->student_name }}"
                                    data-registration-id=""
                                    data-bs-toggle="modal" data-bs-target="#callModal">
                                <i class="ph-duotone ph-phone me-1"></i> Contacter
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-3 py-2">{{ $rows->links() }}</div>
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
