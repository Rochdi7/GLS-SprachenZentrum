@php $group = $group ?? null; @endphp

<div class="row">

    {{-- NOM DU GROUPE (DEFAULT / BACKUP) --}}
    <div class="col-md-12 mb-3">
        <label class="form-label fw-bold">Nom du groupe (Fallback) <span class="text-danger">*</span></label>
        <input type="text" name="name"
               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
               value="{{ old('name', $group->name ?? '') }}"
               placeholder="Ex: Groupe A1 Intensif">
        <small class="text-muted">Utilisé si les versions FR/EN sont vides.</small>
        @if($errors->has('name')) <div class="invalid-feedback">{{ $errors->first('name') }}</div> @endif
    </div>

    {{-- NOM FR --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom du groupe (FR)</label>
        <input type="text" name="name_fr"
               class="form-control {{ $errors->has('name_fr') ? 'is-invalid' : '' }}"
               value="{{ old('name_fr', $group->name_fr ?? '') }}"
               placeholder="Ex: Groupe A1 Intensif">
        @if($errors->has('name_fr')) <div class="invalid-feedback">{{ $errors->first('name_fr') }}</div> @endif
    </div>

    {{-- NOM EN --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom du groupe (EN)</label>
        <input type="text" name="name_en"
               class="form-control {{ $errors->has('name_en') ? 'is-invalid' : '' }}"
               value="{{ old('name_en', $group->name_en ?? '') }}"
               placeholder="Ex: A1 Intensive Group">
        @if($errors->has('name_en')) <div class="invalid-feedback">{{ $errors->first('name_en') }}</div> @endif
    </div>

    {{-- SITE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Centre GLS <span class="text-danger">*</span></label>
        <select name="site_id" class="form-select {{ $errors->has('site_id') ? 'is-invalid' : '' }}" required>
            <option value="">Sélectionner un centre</option>
            @foreach($sites as $gSite)
                <option value="{{ $gSite->id }}"
                    {{ old('site_id', $group->site_id ?? '') == $gSite->id ? 'selected' : '' }}>
                    {{ $gSite->name }} ({{ $gSite->city }})
                </option>
            @endforeach
        </select>
        @if($errors->has('site_id')) <div class="invalid-feedback">{{ $errors->first('site_id') }}</div> @endif
    </div>

    {{-- ENSEIGNANT (OPTIONNEL) --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Enseignant</label>
        <select name="teacher_id" class="form-select {{ $errors->has('teacher_id') ? 'is-invalid' : '' }}">
            <option value="">(Non défini pour le moment)</option>
            @foreach($teachers as $gTeacher)
                <option value="{{ $gTeacher->id }}"
                    {{ old('teacher_id', $group->teacher_id ?? '') == $gTeacher->id ? 'selected' : '' }}>
                    {{ $gTeacher->name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Tu peux l'ajouter plus tard après validation.</small>
        @if($errors->has('teacher_id')) <div class="invalid-feedback">{{ $errors->first('teacher_id') }}</div> @endif
    </div>

    {{-- NIVEAU --}}
    @php $groupLevel = old('level', $group->level ?? ''); @endphp
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Niveau <span class="text-danger">*</span></label>
        <select name="level" class="form-select {{ $errors->has('level') ? 'is-invalid' : '' }}" required>
            @foreach (['A1', 'A2', 'B1', 'B2'] as $lvlOpt)
                <option value="{{ $lvlOpt }}" {{ $groupLevel == $lvlOpt ? 'selected' : '' }}>{{ $lvlOpt }}</option>
            @endforeach
        </select>
        @if($errors->has('level')) <div class="invalid-feedback">{{ $errors->first('level') }}</div> @endif
    </div>

    {{-- STATUT --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Statut <span class="text-danger">*</span></label>
        <select name="status" class="form-select {{ $errors->has('status') ? 'is-invalid' : '' }}" required id="group_status">
            <option value="active"   {{ old('status', $group->status ?? '') == 'active' ? 'selected' : '' }}>Actif</option>
            <option value="upcoming" {{ old('status', $group->status ?? '') == 'upcoming' ? 'selected' : '' }}>À venir</option>
        </select>
        <small class="text-muted">Si "À venir", le suivi (dates) sera désactivé.</small>
        @if($errors->has('status')) <div class="invalid-feedback">{{ $errors->first('status') }}</div> @endif
    </div>

    {{-- HORAIRE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Horaire <span class="text-danger">*</span></label>
        <input type="text" name="time_range"
               class="form-control {{ $errors->has('time_range') ? 'is-invalid' : '' }}"
               value="{{ old('time_range', $group->time_range ?? '') }}"
               placeholder="Ex: 10:00 - 12:30" required>
        <small class="text-muted">La période sera détectée automatiquement.</small>
        @if($errors->has('time_range')) <div class="invalid-feedback">{{ $errors->first('time_range') }}</div> @endif
    </div>

</div>


{{-- ========================================================== --}}
{{-- ===============  SUIVI DU GROUPE (DATE) ================== --}}
{{-- ========================================================== --}}

@php
  $dateModeOld = old('date_mode', 'auto');
@endphp

<div class="row mt-4 pt-3 border-top" id="suivi_block">
    <h5 class="fw-bold mb-3">📅 Suivi du groupe</h5>

    {{-- MODE SELECTOR --}}
    <div class="col-md-12 mb-3">
        <div class="d-flex gap-4">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="date_mode" id="date_mode_auto" value="auto"
                    {{ $dateModeOld !== 'manual' ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="date_mode_auto">
                    Automatique <span class="text-muted fw-normal">(date fin = début + 10 mois)</span>
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="date_mode" id="date_mode_manual" value="manual"
                    {{ $dateModeOld === 'manual' ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="date_mode_manual">
                    Manuel <span class="text-muted fw-normal">(saisir les deux dates librement)</span>
                </label>
            </div>
        </div>
    </div>

    {{-- DATE DE DÉBUT --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Date de début</label>
        <input
            type="text"
            id="date_range_picker"
            class="form-control"
            placeholder="Sélectionner la date de début"
            value="{{ old('date_debut', $group->date_debut ?? '') }}"
        >
        <small class="text-muted">
            Les week-ends sont automatiquement désactivés.
        </small>
    </div>

    {{-- DATE DE FIN --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Date de fin</label>
        <input type="date" id="picker_end_display" name="date_fin" class="form-control"
               value="{{ old('date_fin', $group->date_fin ?? '') }}">
        <small id="date_fin_hint" class="text-muted">
            Calculée automatiquement à 10 mois.
        </small>
    </div>

    {{-- INFO DURÉE --}}
    <div class="col-md-12 mb-2">
        <div class="text-muted">
            Durée détectée : <strong id="duration_label">—</strong>
        </div>
    </div>

    {{-- HIDDEN FIELD for date_debut (Sent to Backend) --}}
    <input type="hidden" name="date_debut" id="date_debut_value"
           value="{{ old('date_debut', $group->date_debut ?? '') }}">

</div>

<script>
(function () {
  const $status       = document.getElementById('group_status');
  const $suiviBlock   = document.getElementById('suivi_block');
  const $rangeInput   = document.getElementById('date_range_picker');
  const $endInput     = document.getElementById('picker_end_display');
  const $dateDebut    = document.getElementById('date_debut_value');
  const $durationLabel= document.getElementById('duration_label');
  const $dateFinHint  = document.getElementById('date_fin_hint');
  const $modeAuto     = document.getElementById('date_mode_auto');
  const $modeManual   = document.getElementById('date_mode_manual');

  const FORMATION_MONTHS = 10;

  if (!$status || !$suiviBlock || !$dateDebut || !$durationLabel) return;

  const pad2 = (n) => String(n).padStart(2, '0');
  const toYMD = (d) => `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;

  const parseYMD = (s) => {
    if (!s) return null;
    const clean = String(s).trim();
    const [y, m, d] = clean.split('-').map(Number);
    if (!y || !m || !d) return null;
    return new Date(y, m - 1, d);
  };

  const addMonths = (date, months) => {
    const result = new Date(date);
    result.setMonth(result.getMonth() + months);
    return result;
  };

  const diffMonths = (start, end) => {
    if (!start || !end) return null;
    let months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
    if (end.getDate() < start.getDate()) months -= 1;
    return Math.max(0, months);
  };

  const isAutoMode = () => $modeAuto && $modeAuto.checked;

  const updateDurationLabel = () => {
    const s = parseYMD($dateDebut.value);
    const e = parseYMD($endInput ? $endInput.value : '');
    $durationLabel.textContent = (s && e) ? diffMonths(s, e) + ' mois' : '—';
  };

  const applyModeUI = () => {
    if (isAutoMode()) {
      if ($endInput) $endInput.setAttribute('readonly', 'readonly');
      if ($dateFinHint) $dateFinHint.textContent = 'Calculée automatiquement à 10 mois.';
      // Recalculate end if start already set
      const start = parseYMD($dateDebut.value);
      if (start && $endInput) {
        $endInput.value = toYMD(addMonths(start, FORMATION_MONTHS));
      }
    } else {
      if ($endInput) $endInput.removeAttribute('readonly');
      if ($dateFinHint) $dateFinHint.textContent = 'Saisir la date de fin manuellement.';
    }
    updateDurationLabel();
  };

  const clearDates = () => {
    $dateDebut.value = '';
    if ($rangeInput) $rangeInput.value = '';
    if ($endInput) $endInput.value = '';
    $durationLabel.textContent = '—';
  };

  const toggleSuiviByStatus = () => {
    if ($status.value === 'upcoming') {
      $suiviBlock.style.display = 'none';
      clearDates();
    } else {
      $suiviBlock.style.display = '';
      applyModeUI();
    }
  };

  $status.addEventListener('change', toggleSuiviByStatus);

  if ($modeAuto)   $modeAuto.addEventListener('change', applyModeUI);
  if ($modeManual) $modeManual.addEventListener('change', applyModeUI);

  // Called from the date picker when user picks a start date
  window.__syncGroupDatesFromPicker = function (startYMD) {
    if ($status.value === 'upcoming') return;
    $dateDebut.value = startYMD;
    if (isAutoMode() && $endInput) {
      const start = parseYMD(startYMD);
      if (start) $endInput.value = toYMD(addMonths(start, FORMATION_MONTHS));
    }
    updateDurationLabel();
  };

  if ($endInput) {
    $endInput.addEventListener('change', updateDurationLabel);
  }

  // init
  toggleSuiviByStatus();
})();
</script>
