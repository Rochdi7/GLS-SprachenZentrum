@php
    $cert = $certificate ?? null;
    $configs = $scoreConfigs ?? [];
    $currentType = old('certificate_type', $cert->certificate_type ?? 'b2');
    $sitesList    = $sites ?? collect();
    $selectedSite = old('site_id', $cert->site_id ?? ($sitesList->count() === 1 ? $sitesList->first()->id : ''));
@endphp

<div class="row">

    {{-- ========================= --}}
    {{--     PERSONAL INFO        --}}
    {{-- ========================= --}}
    <div class="col-12">
        <h5 class="mb-3 fw-bold">Informations personnelles</h5>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom</label>
        <input type="text" name="last_name" class="form-control" required
            value="{{ old('last_name', $cert->last_name ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Prénom</label>
        <input type="text" name="first_name" class="form-control" required
            value="{{ old('first_name', $cert->first_name ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Date de naissance</label>
        <input type="date" name="birth_date" class="form-control" required
            value="{{ old('birth_date', isset($cert->birth_date) ? $cert->birth_date->format('Y-m-d') : '') }}">
    </div>

    <div class="col-md-8 mb-3">
        <label class="form-label fw-bold">Lieu de naissance</label>
        <input type="text" name="birth_place" class="form-control"
            value="{{ old('birth_place', $cert->birth_place ?? '') }}">
    </div>



    {{-- ========================= --}}
    {{--       EXAM META          --}}
    {{-- ========================= --}}
    <div class="col-12 mt-4">
        <h5 class="mb-3 fw-bold">Informations sur l'examen</h5>
    </div>

    {{-- CENTRE — obligatoire, filtre les listes/exports. --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Centre</label>
        <select name="site_id" class="form-select" required {{ $sitesList->count() === 1 ? 'readonly' : '' }}>
            <option value="">— Sélectionner un centre —</option>
            @foreach($sitesList as $s)
                <option value="{{ $s->id }}" {{ (string) $selectedSite === (string) $s->id ? 'selected' : '' }}>
                    {{ $s->name }}@if($s->city) — {{ $s->city }}@endif
                </option>
            @endforeach
        </select>
    </div>

    {{-- CERTIFICATE TYPE SELECTOR --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Type de certificat</label>
        <select name="certificate_type" id="certificateType" class="form-select" required>
            <option value="b2" {{ $currentType === 'b2' ? 'selected' : '' }}>Deutsch B2</option>
            <option value="a2" {{ $currentType === 'a2' ? 'selected' : '' }}>Deutsch A2</option>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Niveau</label>
        <input type="text" name="exam_level" id="examLevelInput" class="form-control" required
            value="{{ old('exam_level', $cert->exam_level ?? 'Deutsch B2') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Date examen</label>
        <input type="date" name="exam_date" class="form-control" required
            value="{{ old('exam_date', isset($cert->exam_date) ? $cert->exam_date->format('Y-m-d') : '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Date délivrance</label>
        <input type="date" name="issue_date" class="form-control" required
            value="{{ old('issue_date', isset($cert->issue_date) ? $cert->issue_date->format('Y-m-d') : '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Numéro du certificat</label>
        @if($cert)
            <input type="text" name="certificate_number" class="form-control" required
                value="{{ old('certificate_number', $cert->certificate_number) }}">
        @else
            <input type="text" class="form-control" disabled
                placeholder="Généré automatiquement">
        @endif
    </div>



    {{-- ============================================================ --}}
    {{--   B2 SCORES — Schriftliche + Mündliche Prüfung              --}}
    {{-- ============================================================ --}}
    <div id="b2-scores" class="col-12" style="{{ $currentType !== 'b2' ? 'display:none' : '' }}">
        <div class="row">

            <div class="col-12 mt-4">
                <h5 class="mb-3 fw-bold">Schriftliche Prüfung (Écrit) — B2</h5>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    Leseverstehen <small class="text-muted">(Max: {{ $configs['b2']['reading'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['reading'] }}"
                    name="reading_score" class="form-control b2-field"
                    value="{{ old('reading_score', $cert && $cert->isB2() ? $cert->reading_score : '') }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    Sprachbausteine <small class="text-muted">(Max: {{ $configs['b2']['grammar'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['grammar'] }}"
                    name="grammar_score" class="form-control b2-field"
                    value="{{ old('grammar_score', $cert->grammar_score ?? '') }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    Hörverstehen <small class="text-muted">(Max: {{ $configs['b2']['listening'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['listening'] }}"
                    name="listening_score" class="form-control b2-field"
                    value="{{ old('listening_score', $cert && $cert->isB2() ? $cert->listening_score : '') }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    Schriftlicher Ausdruck <small class="text-muted">(Max: {{ $configs['b2']['writing'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['writing'] }}"
                    name="writing_score" class="form-control b2-field"
                    value="{{ old('writing_score', $cert && $cert->isB2() ? $cert->writing_score : '') }}">
            </div>

            <div class="col-12 mt-4">
                <h5 class="mb-3 fw-bold">Mündliche Prüfung (Oral) — B2</h5>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">
                    Präsentation <small class="text-muted">(Max: {{ $configs['b2']['presentation'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['presentation'] }}"
                    name="presentation_score" class="form-control b2-field"
                    value="{{ old('presentation_score', $cert->presentation_score ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">
                    Diskussion <small class="text-muted">(Max: {{ $configs['b2']['discussion'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['discussion'] }}"
                    name="discussion_score" class="form-control b2-field"
                    value="{{ old('discussion_score', $cert->discussion_score ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">
                    Problemlösung <small class="text-muted">(Max: {{ $configs['b2']['problemsolving'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['problemsolving'] }}"
                    name="problemsolving_score" class="form-control b2-field"
                    value="{{ old('problemsolving_score', $cert->problemsolving_score ?? '') }}">
            </div>

        </div>
    </div>



    {{-- ============================================================ --}}
    {{--   A2 SCORES — Lesen / Hören / Schreiben / Sprechen          --}}
    {{-- ============================================================ --}}
    <div id="a2-scores" class="col-12" style="{{ $currentType !== 'a2' ? 'display:none' : '' }}">
        <div class="row">

            <div class="col-12 mt-4">
                <h5 class="mb-3 fw-bold">Prüfung — A2 <small class="text-muted">(Total: 100)</small></h5>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    Lesen <small class="text-muted">(Max: {{ $configs['a2']['reading'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['a2']['reading'] }}"
                    name="reading_score" class="form-control a2-field"
                    value="{{ old('reading_score', $cert && $cert->isA2() ? $cert->reading_score : '') }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    Hören <small class="text-muted">(Max: {{ $configs['a2']['listening'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['a2']['listening'] }}"
                    name="listening_score" class="form-control a2-field"
                    value="{{ old('listening_score', $cert && $cert->isA2() ? $cert->listening_score : '') }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    Schreiben <small class="text-muted">(Max: {{ $configs['a2']['writing'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['a2']['writing'] }}"
                    name="writing_score" class="form-control a2-field"
                    value="{{ old('writing_score', $cert && $cert->isA2() ? $cert->writing_score : '') }}">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    Sprechen <small class="text-muted">(Max: {{ $configs['a2']['speaking'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['a2']['speaking'] }}"
                    name="speaking_score" class="form-control a2-field"
                    value="{{ old('speaking_score', $cert->speaking_score ?? '') }}">
            </div>

        </div>
    </div>



    {{-- ========================= --}}
    {{--       FINAL RESULT        --}}
    {{-- ========================= --}}
    <div class="col-12 mt-4">
        <h5 class="mb-3 fw-bold">Résultat final</h5>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Note <small class="text-muted">(auto-calculée)</small></label>
        <input type="text" name="final_result" id="finalResultInput" class="form-control" required
            value="{{ old('final_result', $cert->final_result ?? '') }}"
            placeholder="Ex: 240/300">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Ergebnis</label>
        <input type="text" name="ergebnis_note" id="ergebnisNoteInput" class="form-control"
            value="{{ old('ergebnis_note', $cert->ergebnis_note ?? '') }}"
            placeholder="Ex: Befriedigend, Gut, Sehr gut...">
    </div>

</div>

{{-- ========================= --}}
{{--   TYPE TOGGLE SCRIPT     --}}
{{-- ========================= --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect       = document.getElementById('certificateType');
    const examLevel        = document.getElementById('examLevelInput');
    const b2Block          = document.getElementById('b2-scores');
    const a2Block          = document.getElementById('a2-scores');
    const finalResultInput = document.getElementById('finalResultInput');

    // Score configs matching PHP SCORE_CONFIGS
    const configs = {
        b2: { reading: 75, grammar: 30, listening: 75, writing: 45, presentation: 25, discussion: 25, problemsolving: 25, total: 300 },
        a2: { reading: 25, listening: 25, writing: 25, speaking: 25, total: 100 }
    };

    function getVal(name) {
        const el = document.querySelector('input[name="' + name + '"]:not([disabled])');
        return el ? (parseInt(el.value) || 0) : 0;
    }

    function calculateNote() {
        const type = typeSelect.value;
        let fields = [], max = 0;

        if (type === 'b2') {
            fields = ['reading_score','grammar_score','listening_score','writing_score','presentation_score','discussion_score','problemsolving_score'];
            max = configs.b2.total;
        } else {
            fields = ['reading_score','listening_score','writing_score','speaking_score'];
            max = configs.a2.total;
        }

        let total = 0, hasAny = false;
        fields.forEach(function(name) {
            const el = document.querySelector('input[name="' + name + '"]:not([disabled])');
            if (el && el.value !== '') {
                hasAny = true;
                total += parseInt(el.value) || 0;
            }
        });

        if (hasAny) {
            finalResultInput.value = total + '/' + max;
        }
    }

    function toggleType() {
        const type = typeSelect.value;

        if (type === 'a2') {
            b2Block.style.display = 'none';
            a2Block.style.display = '';
            b2Block.querySelectorAll('.b2-field').forEach(f => { f.disabled = true; f.removeAttribute('required'); });
            a2Block.querySelectorAll('.a2-field').forEach(f => { f.disabled = false; f.required = true; });
            if (examLevel.value === 'Deutsch B2' || examLevel.value === '') examLevel.value = 'Deutsch A2';
        } else {
            b2Block.style.display = '';
            a2Block.style.display = 'none';
            b2Block.querySelectorAll('.b2-field').forEach(f => { f.disabled = false; f.required = true; });
            a2Block.querySelectorAll('.a2-field').forEach(f => { f.disabled = true; f.removeAttribute('required'); });
            if (examLevel.value === 'Deutsch A2' || examLevel.value === '') examLevel.value = 'Deutsch B2';
        }

        calculateNote();
    }

    // Listen to score input changes for auto-calculation
    document.querySelectorAll('.b2-field, .a2-field').forEach(function(input) {
        input.addEventListener('input', calculateNote);
    });

    typeSelect.addEventListener('change', toggleType);
    toggleType(); // set initial state
});
</script>
