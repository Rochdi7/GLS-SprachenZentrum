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
        <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" required
               value="{{ old('last_name', $att->last_name ?? $pr->last_name ?? '') }}">
        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" required
               value="{{ old('first_name', $att->first_name ?? $pr->first_name ?? '') }}">
        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Date de naissance</label>
        <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror"
               value="{{ old('birth_date', isset($att->birth_date) ? $att->birth_date->format('Y-m-d') : (isset($pr->birth_date) ? $pr->birth_date->format('Y-m-d') : '')) }}">
        @error('birth_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Lieu de naissance</label>
        <input type="text" name="birth_place" class="form-control @error('birth_place') is-invalid @enderror"
               value="{{ old('birth_place', $att->birth_place ?? $pr->birth_place ?? '') }}">
        @error('birth_place') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

    {{-- Niveau(x) — un seul menu multi-sélection.
         L'ordre CEFR est A1 < A2 < B1 < B2. Le plus bas coché alimente `level_from`
         (vide si un seul niveau), le plus haut coché alimente `level`. Les dates
         « Début / Fin du niveau » sont auto-remplies du début du premier niveau
         à la fin du dernier depuis le Suivi niveau. --}}
    @php
        $staticLevels      = ['A1', 'A2', 'B1', 'B2'];
        $selectedLevel     = old('level', $att->level ?? $pr->level ?? '');
        $selectedLevelFrom = old('level_from', $att->level_from ?? '');

        // Reconstruit la liste des niveaux cochés à partir de level_from → level.
        $order        = ['A1', 'A2', 'B1', 'B2', 'C1'];
        $fromIdx      = $selectedLevelFrom !== '' ? array_search($selectedLevelFrom, $order, true) : false;
        $toIdx        = $selectedLevel !== '' ? array_search($selectedLevel, $order, true) : false;
        if ($fromIdx !== false && $toIdx !== false && $fromIdx <= $toIdx) {
            $checkedLevels = array_slice($order, $fromIdx, $toIdx - $fromIdx + 1);
        } elseif ($selectedLevel !== '') {
            $checkedLevels = [$selectedLevel];
        } else {
            $checkedLevels = [];
        }
        $checkedLabel = count($checkedLevels) ? implode(', ', $checkedLevels) : 'Sélectionner un ou plusieurs niveaux';
    @endphp

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Niveau(x) de l'attestation <span class="text-danger">*</span></label>

        {{-- Champs réellement soumis : dérivés des cases cochées (level = plus haut, level_from = plus bas). --}}
        <input type="hidden" name="level" id="att-level-input" value="{{ $selectedLevel }}">
        <input type="hidden" name="level_from" id="att-level-from-input"
               value="{{ (count($checkedLevels) > 1 && $selectedLevelFrom !== '') ? $selectedLevelFrom : '' }}">

        <div class="dropdown" id="att-levels-dropdown">
            <button class="form-select text-start @error('level') is-invalid @enderror" type="button"
                    id="att-levels-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <span id="att-levels-label">{{ $checkedLabel }}</span>
            </button>
            <ul class="dropdown-menu w-100 p-2" aria-labelledby="att-levels-toggle" style="min-width: 100%;">
                @foreach($staticLevels as $lvl)
                    <li>
                        <label class="dropdown-item d-flex align-items-center gap-2 rounded" style="cursor:pointer;">
                            <input class="form-check-input m-0 att-level-check" type="checkbox"
                                   value="{{ $lvl }}" {{ in_array($lvl, $checkedLevels, true) ? 'checked' : '' }}>
                            <span>{{ $lvl }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>

        <small class="text-muted d-block mt-1">
            Cochez un seul niveau pour une attestation de ce niveau, ou plusieurs pour couvrir la période du premier au dernier niveau.
        </small>
        <small id="att-level-warning" class="text-danger d-none">
            Aucune entrée de Suivi niveau trouvée pour un des niveaux dans le groupe sélectionné. Veuillez saisir les dates manuellement.
        </small>
        @error('level') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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
        <label class="form-label fw-bold">Unités d'enseignement (minutes) <span class="text-danger">*</span></label>
        <input type="number" min="0" step="1" name="units_45min" id="att-units-input"
               class="form-control @error('units_45min') is-invalid @enderror" required
               value="{{ old('units_45min', $att->units_45min ?? '') }}">
        <small class="text-muted">Saisir manuellement le nombre de minutes.</small>
        @error('units_45min') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
        <label class="form-label fw-bold">Niveau actuel <span class="text-danger">*</span></label>
        <input type="number" min="1" max="9" name="stufe_index"
               class="form-control @error('stufe_index') is-invalid @enderror" required
               value="{{ old('stufe_index', $att->stufe_index ?? 1) }}">
        <small class="text-muted">Numéro du palier en cours.</small>
        @error('stufe_index') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Nombre total de niveaux <span class="text-danger">*</span></label>
        <input type="number" min="1" max="9" name="stufe_total"
               class="form-control @error('stufe_total') is-invalid @enderror" required
               value="{{ old('stufe_total', $att->stufe_total ?? 3) }}">
        <small class="text-muted">Total des paliers du parcours.</small>
        @error('stufe_total') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
        <label class="form-label fw-bold">Date de délivrance <span class="text-danger">*</span></label>
        <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror" required
               value="{{ old('issue_date', isset($att->issue_date) ? $att->issue_date->format('Y-m-d') : now()->format('Y-m-d')) }}">
        @error('issue_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

</div>

{{-- ============================================================ --}}
{{--   SCRIPT — chargement niveaux + calcul aperçu unités        --}}
{{-- ============================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const groupSelect     = document.getElementById('att-group-select');
    const groupWrapper    = document.getElementById('att-group-wrapper');
    const siteWrapper     = document.getElementById('att-site-wrapper');
    const siteSelect      = document.getElementById('att-site-select');
    const legacyCheck     = document.getElementById('att-is-legacy');
    const ongoingInput    = document.getElementById('att-is-ongoing');
    const levelInput      = document.getElementById('att-level-input');       // hidden -> level (plus haut)
    const levelFromInput  = document.getElementById('att-level-from-input');  // hidden -> level_from (plus bas)
    const levelChecks     = Array.prototype.slice.call(document.querySelectorAll('.att-level-check'));
    const levelsLabel     = document.getElementById('att-levels-label');
    const courseStart     = document.getElementById('att-course-start');
    const courseEnd       = document.getElementById('att-course-end');
    const niveauStart     = document.getElementById('att-niveau-start');
    const niveauEnd       = document.getElementById('att-niveau-end');
    const cityInput       = document.getElementById('att-city');

    // Ordre CEFR : A1 < A2 < B1 < B2 (< C1). Sert à ordonner les niveaux cochés.
    const LEVEL_ORDER = ['A1', 'A2', 'B1', 'B2', 'C1'];

    // Retourne les niveaux cochés, triés du plus bas au plus haut.
    function getCheckedLevels() {
        return levelChecks
            .filter(function (c) { return c.checked; })
            .map(function (c) { return c.value; })
            .sort(function (a, b) { return LEVEL_ORDER.indexOf(a) - LEVEL_ORDER.indexOf(b); });
    }

    // Synchronise le libellé du menu + les champs cachés level / level_from.
    function syncLevelState() {
        const checked = getCheckedLevels();
        levelsLabel.textContent = checked.length ? checked.join(', ') : 'Sélectionner un ou plusieurs niveaux';

        const highest = checked.length ? checked[checked.length - 1] : '';
        const lowest  = checked.length ? checked[0] : '';
        levelInput.value = highest;
        // level_from n'a de sens qu'avec plusieurs niveaux (sinon niveau unique).
        levelFromInput.value = checked.length > 1 ? lowest : '';
    }

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
            showLevelWarning(false);
        } else {
            groupWrapper.style.display = '';
            groupSelect.setAttribute('required', 'required');
            if (siteWrapper) siteWrapper.style.display = 'none';
            if (siteSelect)  siteSelect.removeAttribute('required');
        }
    }

    const baseLevelsUrl = groupSelect.dataset.levelsUrl; // ends with /0
    const levelWarning  = document.getElementById('att-level-warning');
    let cachedLevels    = [];

    function showLevelWarning(show) {
        if (!levelWarning) return;
        levelWarning.classList.toggle('d-none', !show);
    }

    function fetchGroupLevels(groupId, opts) {
        opts = opts || {};
        if (!groupId) {
            cachedLevels = [];
            return;
        }

        // The placeholder route was generated with group=0; substitute the real group id.
        // Match /0 when followed by /, ?, or end-of-string so it works whether the placeholder is mid-path or at the end.
        const url = baseLevelsUrl.replace(/\/0(?=\/|\?|$)/, '/' + groupId);

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                cachedLevels = data.levels || [];

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

                // If levels are already checked (edit / old()), auto-fill their date range from Suivi niveau.
                if (getCheckedLevels().length) {
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
        // Toujours resynchroniser libellé + champs cachés à partir des cases cochées.
        syncLevelState();

        const checked = getCheckedLevels();

        if (!checked.length) {
            niveauStart.value = '';
            niveauEnd.value = '';
            showLevelWarning(false);
            return;
        }

        // Début = premier niveau (le plus bas), Fin = dernier niveau (le plus haut).
        const lowest  = checked[0];
        const highest = checked[checked.length - 1];

        const startLvl = cachedLevels.find(function (l) { return l.level === lowest; });
        const endLvl   = cachedLevels.find(function (l) { return l.level === highest; });

        const start = startLvl ? startLvl.start_date : null;
        const end   = endLvl   ? endLvl.end_date     : null;

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

        showLevelWarning(!startLvl || !endLvl);
    }

    groupSelect.addEventListener('change', function () {
        // New group → clear levels + niveau dates so they get rebuilt from the new group's Suivi niveau.
        levelChecks.forEach(function (c) { c.checked = false; });
        syncLevelState();
        niveauStart.value = '';
        niveauEnd.value = '';
        // Clear course dates too, will be auto-refilled from new group's date_debut/date_fin.
        courseStart.value = '';
        courseEnd.value = '';
        cityInput.value = '';
        showLevelWarning(false);
        fetchGroupLevels(this.value);
    });

    // Chaque case (dé)cochée recalcule level / level_from + les dates du niveau.
    levelChecks.forEach(function (chk) {
        chk.addEventListener('change', function () {
            // En mode "ancien étudiant", aucune entrée Suivi niveau à appliquer — on laisse les dates en saisie libre.
            if (legacyCheck.checked) {
                syncLevelState();
                return;
            }
            onLevelChange(false);
        });
    });

    legacyCheck.addEventListener('change', applyLegacyMode);

    // Initial load (edit / old() repopulation)
    applyLegacyMode();
    syncLevelState(); // libellé + champs cachés cohérents dès le chargement (y compris mode ancien)
    if (!legacyCheck.checked && groupSelect.value) {
        fetchGroupLevels(groupSelect.value, { initialLoad: true });
    }

});
</script>
