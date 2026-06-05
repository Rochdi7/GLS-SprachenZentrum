@extends('layouts.main')

@section('title', 'Ajouter un paiement prof')
@section('breadcrumb-item', 'Paiement Professeurs')
@section('breadcrumb-item-link', route('backoffice.payroll.crm.legacy.dashboard'))
@section('breadcrumb-item-active', 'Ajouter un paiement')

@section('content')

    @if ($error)
        <div class="alert alert-danger">
            <i class="ph-duotone ph-warning-circle me-2"></i>{{ $error }}
        </div>
    @endif

    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ph-duotone ph-arrow-left me-1"></i> Tableau de bord
        </a>
    </div>

    <div class="row">
        <div class="col-lg-7 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ph-duotone ph-plus-circle me-2 text-success"></i>
                        Ajouter un paiement professeur
                    </h5>
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
                        <input type="hidden" name="crm_class_name"   id="hidden-class-name">
                        <input type="hidden" name="crm_teacher_name" id="hidden-teacher-name">
                        <input type="hidden" name="crm_level"        id="hidden-level">

                        <div class="row g-3">

                            {{-- Classe --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Classe <span class="text-danger">*</span></label>
                                <select name="crm_class_id" class="form-select" required id="class-select">
                                    <option value="">— Sélectionner une classe —</option>
                                    @forelse ($crmClasses as $cls)
                                        <option value="{{ $cls['crm_id'] }}"
                                            data-name="{{ $cls['name'] }}"
                                            data-teacher="{{ $cls['teacher'] }}"
                                            data-level="{{ $cls['level'] }}"
                                            data-last-rate="{{ $cls['last_rate'] ?? '' }}"
                                            {{ old('crm_class_id', $selectedCrmId) == $cls['crm_id'] ? 'selected' : '' }}>
                                            {{ $cls['name'] }}
                                            @if($cls['teacher'] !== '—') — {{ $cls['teacher'] }} @endif
                                        </option>
                                    @empty
                                        <option value="" disabled>Aucune classe disponible</option>
                                    @endforelse
                                </select>
                                {{-- Info row --}}
                                <div class="d-flex gap-3 mt-1 small text-muted" id="class-info" style="display:none!important">
                                    <span>Professeur : <strong id="info-teacher">—</strong></span>
                                    <span>Niveau : <strong id="info-level">—</strong></span>
                                </div>
                            </div>

                            {{-- Période --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date début <span class="text-danger">*</span></label>
                                <input type="date" name="date_start" class="form-control"
                                    value="{{ old('date_start', now()->startOfMonth()->toDateString()) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date fin <span class="text-danger">*</span></label>
                                <input type="date" name="date_end" class="form-control"
                                    value="{{ old('date_end', now()->endOfMonth()->toDateString()) }}" required>
                                <div class="form-text">Maximum 62 jours.</div>
                            </div>

                            {{-- Libellé période --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Libellé de la période</label>
                                <input type="text" name="month_label" class="form-control"
                                    value="{{ old('month_label') }}"
                                    placeholder="Ex : Juin 2026, Mi-mai → Mi-juin…">
                                <div class="form-text">Affiché sur le rapport PDF.</div>
                            </div>

                            {{-- Taux --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Taux par étudiant (DH)</label>
                                <div class="input-group">
                                    <input type="number" name="payment_per_student" id="rate-input"
                                        class="form-control"
                                        value="{{ old('payment_per_student') }}"
                                        step="0.01" min="0" placeholder="500">
                                    <span class="input-group-text">DH</span>
                                </div>
                                <div class="form-text text-info" id="rate-hint"></div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optionnel)</span></label>
                                <textarea name="notes" class="form-control" rows="2"
                                    placeholder="Notes internes…">{{ old('notes') }}</textarea>
                            </div>

                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}"
                               class="btn btn-outline-secondary">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="ph-duotone ph-floppy-disk me-1"></i> Enregistrer
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
document.addEventListener('DOMContentLoaded', function () {
    const select   = document.getElementById('class-select');
    const rateInput = document.getElementById('rate-input');
    const rateHint  = document.getElementById('rate-hint');
    const infoRow   = document.getElementById('class-info');

    function syncSelect() {
        const opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) {
            infoRow.style.display = 'none';
            return;
        }
        document.getElementById('hidden-class-name').value   = opt.dataset.name    || opt.text;
        document.getElementById('hidden-teacher-name').value = opt.dataset.teacher || '';
        document.getElementById('hidden-level').value        = opt.dataset.level   || '';
        document.getElementById('info-teacher').textContent  = opt.dataset.teacher || '—';
        document.getElementById('info-level').textContent    = opt.dataset.level   || '—';
        infoRow.style.removeProperty('display');

        const lastRate = opt.dataset.lastRate;
        if (lastRate) {
            rateInput.placeholder = lastRate;
            rateHint.innerHTML = 'Dernier taux utilisé : <strong>' + lastRate + ' DH</strong>';
            if (!rateInput.value) rateInput.value = lastRate;
        } else {
            rateHint.innerHTML = '';
        }
    }

    select.addEventListener('change', syncSelect);
    syncSelect();
});
</script>
@endsection
