@php
    $att = $attestation ?? null;
    $pr  = $prefillRequest ?? null; // optional AttestationRequest used to prefill new attestations
@endphp

<div class="row">

    {{-- ============================================================ --}}
    {{--   ÉTUDIANT                                                   --}}
    {{-- ============================================================ --}}
    <div class="col-12">
        <h5 class="mb-3 fw-bold">Étudiant</h5>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom</label>
        <input type="text" name="last_name" class="form-control" required
               value="{{ old('last_name', $att->last_name ?? $pr->last_name ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Prénom</label>
        <input type="text" name="first_name" class="form-control" required
               value="{{ old('first_name', $att->first_name ?? $pr->first_name ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Date de naissance</label>
        <input type="date" name="birth_date" class="form-control"
               value="{{ old('birth_date', isset($att->birth_date) ? $att->birth_date->format('Y-m-d') : (isset($pr->birth_date) ? $pr->birth_date->format('Y-m-d') : '')) }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Lieu de naissance</label>
        <input type="text" name="birth_place" class="form-control"
               value="{{ old('birth_place', $att->birth_place ?? $pr->birth_place ?? '') }}">
    </div>

    {{-- ============================================================ --}}
    {{--   COURS / GROUPE                                             --}}
    {{-- ============================================================ --}}
    <div class="col-12 mt-3">
        <h5 class="mb-3 fw-bold">Cours et groupe</h5>
    </div>

    <div class="col-md-12 mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_legacy" id="att-is-legacy" value="1"
                   {{ old('is_legacy', $att->is_legacy ?? false) ? 'checked' : '' }}>
            <label class="form-check-label fw-bold" for="att-is-legacy">
                Étudiant ancien (saisie manuelle, sans groupe)
            </label>
            <small class="text-muted d-block">
                À cocher si l'étudiant a suivi son cours sur l'ancien système et que son groupe n'existe plus dans la base. Vous remplissez alors manuellement le niveau, les dates, les unités et la ville.
            </small>
        </div>
    </div>

    {{-- Centre — utilisé uniquement en mode "Étudiant ancien" (sinon dérivé du groupe). --}}
    @php
        $sitesList    = $sites ?? collect();
        $selectedSite = old('site_id', $att->site_id ?? ($sitesList->count() === 1 ? $sitesList->first()->id : ''));
    @endphp
    <div class="col-md-6 mb-3" id="att-site-wrapper" style="display:none;">
        <label class="form-label fw-bold">Centre</label>
        <select name="site_id" id="att-site-select" class="form-select">
            <option value="">— Sélectionner un centre —</option>
            @foreach($sitesList as $s)
                <option value="{{ $s->id }}" {{ (string) $selectedSite === (string) $s->id ? 'selected' : '' }}>
                    {{ $s->name }}@if($s->city) — {{ $s->city }}@endif
                </option>
            @endforeach
        </select>
        <small class="text-muted">Centre auquel rattacher l'attestation (obligatoire pour un étudiant ancien).</small>
    </div>

    <div class="col-md-6 mb-3" id="att-group-wrapper">
        <label class="form-label fw-bold">Groupe</label>
        <select name="group_id" id="att-group-select" class="form-select"
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
        <label class="form-label fw-bold">Niveau sélectionné</label>
        @php
            $selectedLevel = old('level', $att->level ?? $pr->level ?? '');
            $staticLevels = ['A1', 'A2', 'B1', 'B2'];
        @endphp
        <select name="level" id="att-level-select" class="form-select" required>
            <option value="">— Sélectionner un niveau —</option>
            @foreach($staticLevels as $lvl)
                <option value="{{ $lvl }}" {{ $selectedLevel === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
            @endforeach
        </select>
        <small class="text-muted d-block">Les dates de début/fin sont récupérées automatiquement depuis le Suivi niveau du groupe sélectionné.</small>
        <small id="att-level-warning" class="text-danger d-none">
            Aucune entrée de Suivi niveau trouvée pour ce niveau dans le groupe sélectionné. Veuillez saisir les dates manuellement.
        </small>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Début du cours</label>
        <input type="date" name="course_start_date" id="att-course-start" class="form-control"
               value="{{ old('course_start_date', isset($att->course_start_date) ? $att->course_start_date->format('Y-m-d') : '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Fin du cours</label>
        <input type="date" name="course_end_date" id="att-course-end" class="form-control"
               value="{{ old('course_end_date', isset($att->course_end_date) ? $att->course_end_date->format('Y-m-d') : '') }}">
    </div>

    {{-- "Toujours en cours" est désormais piloté automatiquement par le mode "Étudiant ancien" :
         décoché en mode ancien (date de fin manuelle), coché sinon (PDF affiche « heute »). --}}
    <input type="hidden" name="is_ongoing" id="att-is-ongoing" value="1">

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
    {{--   VOLUME HORAIRE (manuel)                                    --}}
    {{-- ============================================================ --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Unités d'enseignement (45 min)</label>
        <input type="number" min="0" step="1" name="units_45min" id="att-units-input" class="form-control" required
               value="{{ old('units_45min', $att->units_45min ?? '') }}">
        <small class="text-muted">Saisir manuellement le nombre de séances de 45 minutes.</small>
    </div>

    {{-- ============================================================ --}}
    {{--   FRAIS                                                      --}}
    {{-- ============================================================ --}}
    <div class="col-12 mt-3">
        <h5 class="mb-3 fw-bold">Frais de cours</h5>
    </div>

    <div class="col-md-12 mb-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="fees_status" id="fees-full" value="full"
                   {{ old('fees_status', $att->fees_status ?? 'full') === 'full' ? 'checked' : '' }}>
            <label class="form-check-label" for="fees-full">
                Intégralement payés
            </label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="fees_status" id="fees-partial" value="partial"
                   {{ old('fees_status', $att->fees_status ?? 'full') === 'partial' ? 'checked' : '' }}>
            <label class="form-check-label" for="fees-partial">
                Partiellement payés
            </label>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{--   KURSINFO                                                   --}}
    {{-- ============================================================ --}}
    <div class="col-12 mt-3">
        <h5 class="mb-3 fw-bold">Informations sur le cours</h5>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Niveau actuel</label>
        <input type="number" min="1" max="9" name="stufe_index" class="form-control" required
               value="{{ old('stufe_index', $att->stufe_index ?? 1) }}">
        <small class="text-muted">Numéro du palier en cours.</small>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Nombre total de niveaux</label>
        <input type="number" min="1" max="9" name="stufe_total" class="form-control" required
               value="{{ old('stufe_total', $att->stufe_total ?? 3) }}">
        <small class="text-muted">Total des paliers du parcours.</small>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Appréciation</label>
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
    {{--   MÉTHODOLOGIE / NOTE LÉGALE — caché, valeur préservée       --}}
    {{-- ============================================================ --}}
    <input type="hidden" name="methodology_text" value="{{ old('methodology_text', $att->methodology_text ?? '') }}">

    {{-- ============================================================ --}}
    {{--   LANGUE DE L'ATTESTATION — fixée à la version bilingue       --}}
    {{-- ============================================================ --}}
    <input type="hidden" name="language" value="de_fr">

    {{-- ============================================================ --}}
    {{--   SIGNATURE                                                  --}}
    {{-- ============================================================ --}}
    <div class="col-12 mt-3">
        <h5 class="mb-3 fw-bold">Lieu &amp; date de délivrance</h5>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Lieu</label>
        <input type="text" name="city" id="att-city" class="form-control"
               value="{{ old('city', $att->city ?? '') }}">
        <small class="text-muted">Pré-rempli depuis la ville du centre.</small>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Date de délivrance</label>
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

    const groupSelect  = document.getElementById('att-group-select');
    const groupWrapper = document.getElementById('att-group-wrapper');
    const siteWrapper  = document.getElementById('att-site-wrapper');
    const siteSelect   = document.getElementById('att-site-select');
    const legacyCheck  = document.getElementById('att-is-legacy');
    const ongoingInput = document.getElementById('att-is-ongoing');
    const levelSelect  = document.getElementById('att-level-select');
    const courseStart  = document.getElementById('att-course-start');
    const courseEnd    = document.getElementById('att-course-end');
    const niveauStart  = document.getElementById('att-niveau-start');
    const niveauEnd    = document.getElementById('att-niveau-end');
    const cityInput    = document.getElementById('att-city');

    function applyLegacyMode() {
        const isLegacy = legacyCheck.checked;
        // Mode ancien → date de fin saisie manuellement (is_ongoing = 0).
        // Mode normal → étudiant toujours en cours par défaut (is_ongoing = 1).
        if (ongoingInput) ongoingInput.value = isLegacy ? '0' : '1';
        if (isLegacy) {
            groupWrapper.style.display = 'none';
            groupSelect.removeAttribute('required');
            groupSelect.value = '';
            if (siteWrapper) siteWrapper.style.display = '';
            if (siteSelect)  siteSelect.setAttribute('required', 'required');
            // Strip "dates disponibles / aucune date" annotations — irrelevant in legacy mode.
            Array.from(levelSelect.options).forEach(opt => {
                if (!opt.value) return;
                const baseLabel = opt.dataset.baseLabel || opt.textContent.replace(/\s*•.*$/, '');
                opt.dataset.baseLabel = baseLabel;
                opt.textContent = baseLabel;
            });
            showLevelWarning(false);
        } else {
            groupWrapper.style.display = '';
            groupSelect.setAttribute('required', 'required');
            if (siteWrapper) siteWrapper.style.display = 'none';
            if (siteSelect)  siteSelect.removeAttribute('required');
            annotateLevelOptions();
        }
    }

    const baseLevelsUrl = groupSelect.dataset.levelsUrl; // ends with /0
    const levelWarning  = document.getElementById('att-level-warning');
    let cachedLevels    = [];

    function showLevelWarning(show) {
        if (!levelWarning) return;
        levelWarning.classList.toggle('d-none', !show);
    }

    function annotateLevelOptions() {
        // Tag each <option> with whether the group has a Suivi niveau entry for it.
        const availableSet = new Set(cachedLevels.map(l => l.level));
        Array.from(levelSelect.options).forEach(opt => {
            if (!opt.value) return;
            const has = availableSet.has(opt.value);
            const baseLabel = opt.dataset.baseLabel || opt.textContent.replace(/\s*•.*$/, '');
            opt.dataset.baseLabel = baseLabel;
            opt.textContent = has
                ? baseLabel + ' • dates disponibles'
                : baseLabel + ' • aucune date enregistrée';
        });
    }

    function fetchGroupLevels(groupId, opts) {
        opts = opts || {};
        if (!groupId) {
            cachedLevels = [];
            annotateLevelOptions();
            return;
        }

        // The placeholder route was generated with group=0; substitute the real group id.
        // Match /0 when followed by /, ?, or end-of-string so it works whether the placeholder is mid-path or at the end.
        const url = baseLevelsUrl.replace(/\/0(?=\/|\?|$)/, '/' + groupId);

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                cachedLevels = data.levels || [];

                annotateLevelOptions();

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
                    onLevelChange(/* keepPreselectedDates */ !!opts.initialLoad);
                } else {
                    showLevelWarning(false);
                }
            })
            .catch(err => {
                console.error('Failed to fetch group levels', err);
            });
    }

    function onLevelChange(keepPreselectedDates) {
        const value = levelSelect.value;
        if (!value) {
            niveauStart.value = '';
            niveauEnd.value = '';
            showLevelWarning(false);
            return;
        }

        const lvl = cachedLevels.find(function (l) { return l.level === value; });
        const start = lvl ? lvl.start_date : null;
        const end   = lvl ? lvl.end_date   : null;

        if (keepPreselectedDates) {
            // On edit reload, prefer saved values, otherwise fall back to Suivi niveau dates.
            const savedStart = groupSelect.dataset.selectedNiveauStart;
            const savedEnd   = groupSelect.dataset.selectedNiveauEnd;
            niveauStart.value = savedStart || start || '';
            niveauEnd.value   = savedEnd   || end   || '';
        } else {
            // Manual change: always overwrite with Suivi niveau dates (or clear if missing).
            niveauStart.value = start || '';
            niveauEnd.value   = end   || '';
        }

        showLevelWarning(!lvl);
    }

    groupSelect.addEventListener('change', function () {
        // New group → clear level + niveau dates so they get rebuilt from the new group's Suivi niveau.
        levelSelect.value = '';
        niveauStart.value = '';
        niveauEnd.value = '';
        // Clear course dates too, will be auto-refilled from new group's date_debut/date_fin.
        courseStart.value = '';
        courseEnd.value = '';
        cityInput.value = '';
        showLevelWarning(false);
        fetchGroupLevels(this.value);
    });

    levelSelect.addEventListener('change', function () {
        // En mode "ancien étudiant", aucune entrée Suivi niveau à appliquer — on laisse les dates en saisie libre.
        if (legacyCheck.checked) return;
        onLevelChange(false);
    });

    legacyCheck.addEventListener('change', applyLegacyMode);

    // Initial load (edit / old() repopulation)
    applyLegacyMode();
    if (!legacyCheck.checked && groupSelect.value) {
        fetchGroupLevels(groupSelect.value, { initialLoad: true });
    } else if (!legacyCheck.checked) {
        annotateLevelOptions();
    }

});
</script>
