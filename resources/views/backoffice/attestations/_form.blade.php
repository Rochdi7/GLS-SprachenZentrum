@php
    $att = $attestation ?? null;
@endphp

<div class="row">

    {{-- ============================================================ --}}
    {{--   ÉTUDIANT                                                   --}}
    {{-- ============================================================ --}}
    <div class="col-12">
        <h5 class="mb-3 fw-bold">Étudiant</h5>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom (Name)</label>
        <input type="text" name="last_name" class="form-control" required
               value="{{ old('last_name', $att->last_name ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Prénom (Vorname)</label>
        <input type="text" name="first_name" class="form-control" required
               value="{{ old('first_name', $att->first_name ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Date de naissance (geboren am)</label>
        <input type="date" name="birth_date" class="form-control"
               value="{{ old('birth_date', isset($att->birth_date) ? $att->birth_date->format('Y-m-d') : '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Lieu de naissance (geboren in)</label>
        <input type="text" name="birth_place" class="form-control"
               value="{{ old('birth_place', $att->birth_place ?? '') }}">
    </div>

    {{-- ============================================================ --}}
    {{--   COURS / GROUPE                                             --}}
    {{-- ============================================================ --}}
    <div class="col-12 mt-3">
        <h5 class="mb-3 fw-bold">Cours / Groupe</h5>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Groupe</label>
        <select name="group_id" id="att-group-select" class="form-select" required
                data-levels-url="{{ route('backoffice.attestations.group_levels', ['group' => 0]) }}"
                data-selected-level="{{ old('level', $att->level ?? '') }}"
                data-selected-niveau-start="{{ old('niveau_start_date', isset($att->niveau_start_date) ? $att->niveau_start_date->format('Y-m-d') : '') }}"
                data-selected-niveau-end="{{ old('niveau_end_date', isset($att->niveau_end_date) ? $att->niveau_end_date->format('Y-m-d') : '') }}">
            <option value="">— Sélectionner un groupe —</option>
            @foreach($groups as $g)
                <option value="{{ $g->id }}"
                        data-site="{{ $g->site?->name }}"
                        data-city="{{ $g->site?->city }}"
                        {{ (old('group_id', $att->group_id ?? '') == $g->id) ? 'selected' : '' }}>
                    {{ $g->name }} — {{ $g->site?->name ?? '—' }} ({{ $g->level }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Niveau (sélectionné)</label>
        @php
            $selectedLevel = old('level', $att->level ?? '');
            $staticLevels = ['A1', 'A2', 'B1', 'B2'];
        @endphp
        <select name="level" id="att-level-select" class="form-select" required>
            <option value="">— Sélectionner un niveau —</option>
            @foreach($staticLevels as $lvl)
                <option value="{{ $lvl }}" {{ $selectedLevel === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
            @endforeach
        </select>
        <small class="text-muted">Les dates de début/fin sont récupérées depuis le Suivi niveau du groupe sélectionné.</small>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Début du cours (vom)</label>
        <input type="date" name="course_start_date" id="att-course-start" class="form-control"
               value="{{ old('course_start_date', isset($att->course_start_date) ? $att->course_start_date->format('Y-m-d') : '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Fin du cours (bis)</label>
        <input type="date" name="course_end_date" id="att-course-end" class="form-control"
               value="{{ old('course_end_date', isset($att->course_end_date) ? $att->course_end_date->format('Y-m-d') : '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Début du niveau</label>
        <input type="date" name="niveau_start_date" id="att-niveau-start" class="form-control"
               value="{{ old('niveau_start_date', isset($att->niveau_start_date) ? $att->niveau_start_date->format('Y-m-d') : '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Fin du niveau</label>
        <input type="date" name="niveau_end_date" id="att-niveau-end" class="form-control"
               value="{{ old('niveau_end_date', isset($att->niveau_end_date) ? $att->niveau_end_date->format('Y-m-d') : '') }}">
    </div>

    {{-- ============================================================ --}}
    {{--   AUTO-CALCUL                                                --}}
    {{-- ============================================================ --}}
    <div class="col-12 mt-3">
        <h5 class="mb-3 fw-bold">Volume horaire (auto)</h5>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Heures par séance (centre)</label>
        <input type="text" id="att-hours-display" class="form-control" readonly
               value="{{ old('hours_per_session', isset($att->hours_per_session) ? $att->hours_per_session : '') }}">
        <small class="text-muted">2h pour Rabat / Salé / Casablanca / Online — 2h30 ailleurs.</small>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Jours ouvrés (Lun–Ven)</label>
        <input type="text" id="att-weekdays-display" class="form-control" readonly>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Unterrichtseinheiten (45 min)</label>
        <input type="text" id="att-units-display" class="form-control" readonly
               value="{{ old('units_45min', isset($att->units_45min) ? $att->units_45min : '') }}">
        <small class="text-muted">Calcul final côté serveur.</small>
    </div>

    {{-- ============================================================ --}}
    {{--   FRAIS                                                      --}}
    {{-- ============================================================ --}}
    <div class="col-12 mt-3">
        <h5 class="mb-3 fw-bold">Frais (Kursgebühren)</h5>
    </div>

    <div class="col-md-12 mb-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="fees_status" id="fees-full" value="full"
                   {{ old('fees_status', $att->fees_status ?? 'full') === 'full' ? 'checked' : '' }}>
            <label class="form-check-label" for="fees-full">
                Vollständig entrichtet (intégralement payés)
            </label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="fees_status" id="fees-partial" value="partial"
                   {{ old('fees_status', $att->fees_status ?? 'full') === 'partial' ? 'checked' : '' }}>
            <label class="form-check-label" for="fees-partial">
                Teilweise entrichtet (partiellement payés)
            </label>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{--   KURSINFO                                                   --}}
    {{-- ============================================================ --}}
    <div class="col-12 mt-3">
        <h5 class="mb-3 fw-bold">Kursinfo</h5>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Stufe (X)</label>
        <input type="number" min="1" max="9" name="stufe_index" class="form-control" required
               value="{{ old('stufe_index', $att->stufe_index ?? 1) }}">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">von (Y)</label>
        <input type="number" min="1" max="9" name="stufe_total" class="form-control" required
               value="{{ old('stufe_total', $att->stufe_total ?? 3) }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Erfolg / Appréciation</label>
        <select name="erfolg" class="form-select" required>
            @foreach($erfolgOptions as $value => $label)
                <option value="{{ $value }}"
                        {{ old('erfolg', $att->erfolg ?? 'Erfolg') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- ============================================================ --}}
    {{--   LANGUE DE L'ATTESTATION                                    --}}
    {{-- ============================================================ --}}
    <div class="col-12 mt-3">
        <h5 class="mb-3 fw-bold">Langue du document</h5>
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label fw-bold">Modèle linguistique</label>
        <select name="language" class="form-select" required>
            @foreach($languageOptions as $value => $label)
                <option value="{{ $value }}"
                        {{ old('language', $att->language ?? 'de_fr') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Bilingue = format officiel GLS (DE+FR). Les autres modèles affichent une seule langue.</small>
    </div>

    {{-- ============================================================ --}}
    {{--   SIGNATURE                                                  --}}
    {{-- ============================================================ --}}
    <div class="col-12 mt-3">
        <h5 class="mb-3 fw-bold">Lieu &amp; date de délivrance</h5>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Ort / Lieu</label>
        <input type="text" name="city" id="att-city" class="form-control"
               value="{{ old('city', $att->city ?? '') }}">
        <small class="text-muted">Pré-rempli depuis la ville du centre.</small>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Datum / Date</label>
        <input type="date" name="issue_date" class="form-control" required
               value="{{ old('issue_date', isset($att->issue_date) ? $att->issue_date->format('Y-m-d') : now()->format('Y-m-d')) }}">
    </div>

</div>

{{-- ============================================================ --}}
{{--   SCRIPT — chargement niveaux + calcul aperçu unités        --}}
{{-- ============================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const groupSelect    = document.getElementById('att-group-select');
    const levelSelect    = document.getElementById('att-level-select');
    const courseStart    = document.getElementById('att-course-start');
    const courseEnd      = document.getElementById('att-course-end');
    const niveauStart    = document.getElementById('att-niveau-start');
    const niveauEnd      = document.getElementById('att-niveau-end');
    const hoursDisplay   = document.getElementById('att-hours-display');
    const weekdaysDisplay = document.getElementById('att-weekdays-display');
    const unitsDisplay   = document.getElementById('att-units-display');
    const cityInput      = document.getElementById('att-city');

    const baseLevelsUrl  = groupSelect.dataset.levelsUrl; // ends with /0
    let cachedLevels     = [];
    let hoursPerSession  = parseFloat(hoursDisplay.value) || null;

    function fetchGroupLevels(groupId) {
        if (!groupId) {
            cachedLevels = [];
            return;
        }

        const url = baseLevelsUrl.replace(/\/0(\?|$)/, '/' + groupId + '$1');

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                cachedLevels = data.levels || [];
                hoursPerSession = parseFloat(data.group?.hours_per_session) || 2.5;
                hoursDisplay.value = hoursPerSession.toFixed(2);

                // Pre-fill course (vom .. bis) from group dates if empty
                if (!courseStart.value && data.group?.date_debut) {
                    courseStart.value = data.group.date_debut;
                }
                if (!courseEnd.value && data.group?.date_fin) {
                    courseEnd.value = data.group.date_fin;
                }

                // Pre-fill city from site
                if (!cityInput.value && data.group?.site_city) {
                    cityInput.value = data.group.site_city;
                } else if (!cityInput.value && data.group?.site_name) {
                    cityInput.value = data.group.site_name;
                }

                // If a level is already selected (edit / old()), auto-fill its date range from Suivi niveau.
                if (levelSelect.value) {
                    onLevelChange(/* keepPreselectedDates */ true);
                }

                computeUnitsPreview();
            })
            .catch(err => {
                console.error('Failed to fetch group levels', err);
            });
    }

    function onLevelChange(keepPreselectedDates) {
        const value = levelSelect.value;
        if (!value) return;

        const lvl = cachedLevels.find(function (l) { return l.level === value; });

        // Falls back gracefully if the group has no Suivi niveau entry for the chosen level.
        const start = lvl ? lvl.start_date : null;
        const end   = lvl ? lvl.end_date   : null;

        if (keepPreselectedDates) {
            // On edit reload, use the saved values if present.
            const savedStart = groupSelect.dataset.selectedNiveauStart;
            const savedEnd   = groupSelect.dataset.selectedNiveauEnd;
            if (savedStart) niveauStart.value = savedStart;
            else if (start) niveauStart.value = start;

            if (savedEnd)   niveauEnd.value = savedEnd;
            else if (end)   niveauEnd.value = end;
        } else {
            if (start) niveauStart.value = start;
            if (end)   niveauEnd.value   = end;
        }

        computeUnitsPreview();
    }

    function countWeekdays(startStr, endStr) {
        if (!startStr || !endStr) return 0;
        const start = new Date(startStr + 'T00:00:00');
        const end   = new Date(endStr + 'T00:00:00');
        if (isNaN(start) || isNaN(end) || end < start) return 0;

        let count = 0;
        const cur = new Date(start);
        while (cur <= end) {
            const d = cur.getDay(); // 0 = Sun, 6 = Sat
            if (d !== 0 && d !== 6) count++;
            cur.setDate(cur.getDate() + 1);
        }
        return count;
    }

    function computeUnitsPreview() {
        const days = countWeekdays(niveauStart.value, niveauEnd.value);
        weekdaysDisplay.value = days;

        const hours = parseFloat(hoursDisplay.value) || hoursPerSession || 0;
        if (!hours || !days) {
            unitsDisplay.value = '';
            return;
        }

        const minutes = days * hours * 60;
        const units = Math.round(minutes / 45);
        unitsDisplay.value = units;
    }

    groupSelect.addEventListener('change', function () {
        fetchGroupLevels(this.value);
    });

    levelSelect.addEventListener('change', function () { onLevelChange(false); });
    niveauStart.addEventListener('change', computeUnitsPreview);
    niveauEnd.addEventListener('change', computeUnitsPreview);

    // Initial load (edit)
    if (groupSelect.value) {
        fetchGroupLevels(groupSelect.value);
    } else {
        computeUnitsPreview();
    }
});
</script>
