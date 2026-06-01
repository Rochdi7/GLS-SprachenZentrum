@extends('layouts.main')

@section('title', 'Importer depuis CRM — Paiement Professeurs')
@section('breadcrumb-item', 'Paiement Professeurs CRM')
@section('breadcrumb-item-link', route('backoffice.payroll.crm.dashboard'))
@section('breadcrumb-item-active', 'Nouvel import')

@section('content')

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5>Importer des données depuis le CRM</h5>
                    <p class="text-muted mb-0">
                        Sélectionnez le groupe et la période. Les données de présence seront récupérées automatiquement depuis le CRM Homeschool.
                    </p>
                </div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('backoffice.payroll.crm.import.store') }}"
                          method="POST">
                        @csrf

                        <div class="row">
                            {{-- Group Selection --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Groupe <span class="text-danger">*</span></label>
                                <select name="group_id" class="form-select" required id="group-select">
                                    <option value="">Sélectionner un groupe</option>
                                    @foreach($groups as $group)
                                        @php
                                            $teacherRate = $group->teacher?->payment_per_student;
                                            $lastImport = $group->latestPresenceImport;
                                            $lastRate = $lastImport?->payment_per_student;
                                        @endphp
                                        <option value="{{ $group->id }}"
                                                data-teacher="{{ $group->teacher?->name ?? '—' }}"
                                                data-level="{{ $group->level }}"
                                                data-rate="{{ $teacherRate ?? '' }}"
                                                data-last-rate="{{ $lastRate ?? '' }}"
                                                data-has-import="{{ $lastImport ? '1' : '0' }}"
                                                data-crm-class-id="{{ $group->crm_class_id ?? '' }}"
                                                {{ old('group_id', $selectedGroupId) == $group->id ? 'selected' : '' }}
                                                {{ !$group->crm_class_id ? 'disabled' : '' }}>
                                            {{ $group->name }} — {{ $group->level }}
                                            ({{ $group->teacher?->name ?? 'Sans enseignant' }})
                                            @if(!$group->crm_class_id)
                                                — [CRM Class ID non configuré]
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Period --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Période <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" name="date_start"
                                           class="form-control"
                                           value="{{ old('date_start', now()->subDays(15)->toDateString()) }}"
                                           required id="date-start-input"
                                           placeholder="Début">
                                    <span class="input-group-text">→</span>
                                    <input type="date" name="date_end"
                                           class="form-control"
                                           value="{{ old('date_end', now()->toDateString()) }}"
                                           required id="date-end-input"
                                           placeholder="Fin">
                                </div>
                                <small class="text-muted">Période de présence à récupérer (max 62 jours)</small>
                            </div>

                            {{-- Auto-populated info --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Professeur</label>
                                <input type="text" class="form-control" id="teacher-display" readonly disabled>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Niveau</label>
                                <input type="text" class="form-control" id="level-display" readonly disabled>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">CRM Class ID</label>
                                <input type="text" class="form-control" id="crm-class-id-display" readonly disabled>
                            </div>

                            {{-- Payment Per Student Override --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Taux par étudiant (DH)</label>
                                <input type="number" name="payment_per_student"
                                       class="form-control"
                                       value="{{ old('payment_per_student') }}"
                                       step="0.01" min="0"
                                       id="rate-input"
                                       placeholder="Laisser vide = taux enseignant">
                                <small class="text-muted" id="rate-hint"></small>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Notes (optionnel)</label>
                                <textarea name="notes" class="form-control" rows="3"
                                          placeholder="Notes sur cet import...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        {{-- Info box --}}
                        <div class="alert alert-success mb-3">
                            <i class="ph-duotone ph-check-circle me-2"></i>
                            <strong>Mode automatique :</strong> Les données de présence seront récupérées directement depuis le CRM Homeschool via l'API.
                            Aucun fichier Excel à importer !
                        </div>

                        <div class="alert alert-info mb-3">
                            <strong>Calcul du paiement :</strong> Même logique que les imports manuels :
                            <ul class="mb-0 mt-1">
                                <li>Si étudiant présent ≥ 3 jours/semaine → paiement pour cette semaine</li>
                                <li>Taux par étudiant × 25% par semaine (configurable)</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('backoffice.payroll.crm.dashboard') }}" class="btn btn-outline-secondary">
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
        document.getElementById('group-select').addEventListener('change', function () {
            const option = this.options[this.selectedIndex];
            const rateInput = document.getElementById('rate-input');

            document.getElementById('teacher-display').value = option.dataset.teacher || '—';
            document.getElementById('level-display').value = option.dataset.level || '—';
            document.getElementById('crm-class-id-display').value = option.dataset.crmClassId || '—';

            const teacherRate = option.dataset.rate;
            const lastRate = option.dataset.lastRate;
            const hasImport = option.dataset.hasImport === '1';
            const rateHint = document.getElementById('rate-hint');

            let hints = [];
            if (hasImport && lastRate) {
                rateInput.value = lastRate;
                hints.push('Dernier import : <strong>' + lastRate + ' DH</strong>');
            } else if (teacherRate) {
                rateInput.value = teacherRate;
            } else {
                rateInput.value = '';
                rateInput.placeholder = 'Ex: 500 ou 550';
            }
            if (teacherRate) hints.push('Taux enseignant : <strong>' + teacherRate + ' DH</strong>');
            rateHint.innerHTML = hints.length ? hints.join(' | ') : '';
        });

        document.getElementById('group-select').dispatchEvent(new Event('change'));
    </script>
@endsection
