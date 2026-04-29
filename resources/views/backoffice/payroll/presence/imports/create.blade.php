@extends('layouts.main')

@section('title', 'Importer fichier Présence')
@section('breadcrumb-item', 'Paiement Professeurs')
@section('breadcrumb-item-link', route('backoffice.payroll.presence.dashboard'))
@section('breadcrumb-item-active', 'Nouvel import')

@section('content')

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5>Importer un fichier de présence</h5>
                    <p class="text-muted mb-0">
                        Sélectionnez le groupe, le mois, et téléchargez le fichier Excel de présence.
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

                    <form action="{{ route('backoffice.payroll.presence.import.store') }}"
                          method="POST"
                          enctype="multipart/form-data">
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
                                                {{ old('group_id', $selectedGroupId) == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }} — {{ $group->level }}
                                            ({{ $group->teacher?->name ?? 'Sans enseignant' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Month --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Mois <span class="text-danger">*</span></label>
                                <input type="month" name="month"
                                       class="form-control"
                                       value="{{ old('month') }}"
                                       required id="month-input">
                                <small class="text-muted">Le mois de référence pour ce paiement</small>
                            </div>

                            {{-- Period --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Période <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" name="date_start"
                                           class="form-control"
                                           value="{{ old('date_start') }}"
                                           required id="date-start-input"
                                           placeholder="Début">
                                    <span class="input-group-text">→</span>
                                    <input type="date" name="date_end"
                                           class="form-control"
                                           value="{{ old('date_end') }}"
                                           required id="date-end-input"
                                           placeholder="Fin">
                                </div>
                                <small class="text-muted">Période réelle couverte par la feuille (peut chevaucher 2 mois)</small>
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

                            {{-- Excel File --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fichier Excel Présence <span class="text-danger">*</span></label>
                                <input type="file" name="file"
                                       class="form-control"
                                       accept=".xlsx,.xls,.csv"
                                       required>
                                <small class="text-muted">Formats: .xlsx, .xls, .csv (max 10 Mo)</small>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Notes (optionnel)</label>
                                <textarea name="notes" class="form-control" rows="3"
                                          placeholder="Notes sur cet import...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        {{-- Info box --}}
                        <div class="alert alert-info mb-3">
                            <strong>Format attendu :</strong> Un fichier Excel avec les noms des étudiants et leur présence quotidienne
                            (P = Présent, Q = Absent, ou &#10003;/X). Le système détectera automatiquement les colonnes de dates.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('backoffice.payroll.presence.dashboard') }}" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-duotone ph-upload-simple me-1"></i> Importer
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            {{-- Debug Tool --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Debug — Voir le contenu brut du fichier</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('backoffice.payroll.presence.debug') }}" method="POST" enctype="multipart/form-data" id="debug-form">
                        @csrf
                        <div class="input-group">
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <button type="submit" class="btn btn-outline-info">Analyser</button>
                        </div>
                    </form>
                    <pre id="debug-output" class="mt-2 p-3 bg-light" style="max-height:400px;overflow:auto;display:none;font-size:0.75rem;"></pre>
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

        // Auto-fill the period from the chosen month, if the period is empty.
        const monthInput = document.getElementById('month-input');
        const dateStartInput = document.getElementById('date-start-input');
        const dateEndInput = document.getElementById('date-end-input');
        monthInput.addEventListener('change', function () {
            const v = this.value; // YYYY-MM
            if (!v) return;
            const [y, m] = v.split('-').map(Number);
            const first = `${y}-${String(m).padStart(2,'0')}-01`;
            const lastDay = new Date(y, m, 0).getDate();
            const last = `${y}-${String(m).padStart(2,'0')}-${String(lastDay).padStart(2,'0')}`;
            if (!dateStartInput.value) dateStartInput.value = first;
            if (!dateEndInput.value) dateEndInput.value = last;
        });

        // Debug form — AJAX submit
        document.getElementById('debug-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const output = document.getElementById('debug-output');
            output.style.display = 'block';
            output.textContent = 'Analyse en cours...';

            fetch(this.action, { method: 'POST', body: new FormData(this) })
                .then(r => r.json())
                .then(data => { output.textContent = JSON.stringify(data, null, 2); })
                .catch(err => { output.textContent = 'Erreur: ' + err.message; });
        });
    </script>
@endsection
