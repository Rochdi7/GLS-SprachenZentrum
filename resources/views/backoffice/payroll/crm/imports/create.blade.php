@extends('layouts.main')

@section('title', 'Importer depuis CRM — Paiement Professeurs')
@section('breadcrumb-item', 'Paiement Professeurs CRM')
@section('breadcrumb-item-link', route('backoffice.payroll.crm.legacy.dashboard'))
@section('breadcrumb-item-active', 'Nouvel import')

@section('content')
    @include('backoffice.crm.partials._center')

    @if ($error)
        <div class="alert alert-danger">
            <i class="ph-duotone ph-warning-circle me-2"></i>{{ $error }}
        </div>
    @endif

    {{-- Nav --}}
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ph-duotone ph-arrow-left me-1"></i> Tableau de bord CRM
        </a>
    </div>

    <div class="row">
        <div class="col-lg-7 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Nouvel import depuis le CRM</h5>
                </div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('backoffice.payroll.crm.legacy.import.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="crm_class_name" id="crm-class-name-hidden">
                        <input type="hidden" name="crm_teacher_name" id="crm-teacher-name-hidden">
                        <input type="hidden" name="crm_level" id="crm-level-hidden">

                        <div class="row g-3">

                            {{-- Classe CRM --}}
                            <div class="col-12">
                                <label class="form-label fw-bold">Classe CRM <span class="text-danger">*</span></label>
                                <select name="crm_class_id" class="form-select" required id="crm-class-select">
                                    <option value="">— Sélectionner une classe —</option>
                                    @forelse ($crmClasses as $cls)
                                        <option value="{{ $cls['crm_id'] }}"
                                            data-name="{{ $cls['name'] }}"
                                            data-teacher="{{ $cls['teacher'] }}"
                                            data-level="{{ $cls['level'] }}"
                                            data-status="{{ $cls['status'] }}"
                                            data-last-rate="{{ $cls['last_rate'] ?? '' }}"
                                            {{ old('crm_class_id', $selectedCrmId) == $cls['crm_id'] ? 'selected' : '' }}>
                                            {{ $cls['name'] }} — {{ $cls['level'] }} ({{ $cls['teacher'] }})
                                        </option>
                                    @empty
                                        <option value="" disabled>Aucune classe disponible depuis le CRM</option>
                                    @endforelse
                                </select>
                                <div class="d-flex gap-3 mt-1" style="font-size:.82rem">
                                    <span class="text-muted">Prof : <strong id="teacher-display">—</strong></span>
                                    <span class="text-muted">Niveau : <strong id="level-display">—</strong></span>
                                    <span class="text-muted">Statut : <strong id="status-display">—</strong></span>
                                </div>
                            </div>

                            {{-- Période --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date début <span class="text-danger">*</span></label>
                                <input type="date" name="date_start" class="form-control"
                                    value="{{ old('date_start', now()->startOfMonth()->toDateString()) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date fin <span class="text-danger">*</span></label>
                                <input type="date" name="date_end" class="form-control"
                                    value="{{ old('date_end', now()->endOfMonth()->toDateString()) }}" required>
                                <small class="text-muted">Max 62 jours</small>
                            </div>

                            {{-- Libellé du mois --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Libellé de la période</label>
                                <input type="text" name="month_label" class="form-control"
                                    value="{{ old('month_label') }}"
                                    placeholder="Ex: Mars 2026, Mi-mars → Mi-avril...">
                                <small class="text-muted">Affiché sur le rapport. Utile si la période ne coïncide pas avec un mois calendaire.</small>
                            </div>

                            {{-- Taux --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Taux par étudiant (DH)</label>
                                <div class="input-group">
                                    <input type="number" name="payment_per_student" class="form-control"
                                        value="{{ old('payment_per_student') }}" step="0.01" min="0" id="rate-input"
                                        placeholder="500">
                                    <span class="input-group-text">DH</span>
                                </div>
                                <small class="text-info" id="rate-hint"></small>
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label class="form-label fw-bold">Notes (optionnel)</label>
                                <textarea name="notes" class="form-control" rows="2"
                                    placeholder="Notes internes...">{{ old('notes') }}</textarea>
                            </div>

                        </div>{{-- /row --}}

                        <hr class="my-3">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="ph-duotone ph-cloud-arrow-down me-1"></i> Récupérer depuis CRM
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    const select   = document.getElementById('crm-class-select');
    const rateInput = document.getElementById('rate-input');
    const rateHint  = document.getElementById('rate-hint');

    select.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        document.getElementById('teacher-display').textContent  = opt.dataset.teacher || '—';
        document.getElementById('level-display').textContent    = opt.dataset.level   || '—';
        document.getElementById('status-display').textContent   = opt.dataset.status  || '—';
        document.getElementById('crm-class-name-hidden').value  = opt.dataset.name    || opt.text;
        document.getElementById('crm-teacher-name-hidden').value = opt.dataset.teacher || '';
        document.getElementById('crm-level-hidden').value        = opt.dataset.level   || '';

        const lastRate = opt.dataset.lastRate;
        if (lastRate) {
            rateInput.value = lastRate;
            rateHint.innerHTML = 'Dernier import : <strong>' + lastRate + ' DH</strong>';
        } else {
            rateInput.value = '';
            rateHint.innerHTML = '';
        }
    });

    select.dispatchEvent(new Event('change'));
</script>
@endsection
