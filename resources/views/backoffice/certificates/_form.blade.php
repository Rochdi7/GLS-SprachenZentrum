@php
    $cert        = $certificate ?? null;
    $configs     = $scoreConfigs ?? [];
    $currentType = old('certificate_type', $cert->certificate_type ?? 'b2');
    $sitesList   = $sites ?? collect();
    $selectedSite = old('site_id', $cert->site_id ?? ($sitesList->count() === 1 ? $sitesList->first()->id : ''));
    $simpleTypes = ['a1', 'a2', 'b1'];
@endphp

<div class="row">

    {{-- ========================= --}}
    {{--     PERSONAL INFO        --}}
    {{-- ========================= --}}
    <div class="col-12">
        <h5 class="mb-3 fw-bold">Informations personnelles</h5>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" required
            value="{{ old('last_name', $cert->last_name ?? '') }}">
        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" required
            value="{{ old('first_name', $cert->first_name ?? '') }}">
        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Date de naissance <span class="text-danger">*</span></label>
        <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" required
            value="{{ old('birth_date', isset($cert->birth_date) ? $cert->birth_date->format('Y-m-d') : '') }}">
        @error('birth_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-8 mb-3">
        <label class="form-label fw-bold">Lieu de naissance</label>
        <input type="text" name="birth_place" class="form-control @error('birth_place') is-invalid @enderror"
            value="{{ old('birth_place', $cert->birth_place ?? '') }}">
        @error('birth_place') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>



    {{-- ========================= --}}
    {{--       EXAM META          --}}
    {{-- ========================= --}}
    <div class="col-12 mt-4">
        <h5 class="mb-3 fw-bold">Informations sur l'examen</h5>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Centre <span class="text-danger">*</span></label>
        <select name="site_id" class="form-select @error('site_id') is-invalid @enderror" required {{ $sitesList->count() === 1 ? 'readonly' : '' }}>
            <option value="">— Sélectionner un centre —</option>
            @foreach($sitesList as $s)
                <option value="{{ $s->id }}" {{ (string) $selectedSite === (string) $s->id ? 'selected' : '' }}>
                    {{ $s->name }}@if($s->city) — {{ $s->city }}@endif
                </option>
            @endforeach
        </select>
        @error('site_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Type de certificat <span class="text-danger">*</span></label>
        <select name="certificate_type" id="certificateType" class="form-select @error('certificate_type') is-invalid @enderror" required>
            <option value="a1" {{ $currentType === 'a1' ? 'selected' : '' }}>Deutsch A1</option>
            <option value="a2" {{ $currentType === 'a2' ? 'selected' : '' }}>Deutsch A2</option>
            <option value="b1" {{ $currentType === 'b1' ? 'selected' : '' }}>Deutsch B1</option>
            <option value="b2" {{ $currentType === 'b2' ? 'selected' : '' }}>Deutsch B2</option>
        </select>
        @error('certificate_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Niveau <span class="text-danger">*</span></label>
        <input type="text" name="exam_level" id="examLevelInput" class="form-control @error('exam_level') is-invalid @enderror" required
            value="{{ old('exam_level', $cert->exam_level ?? 'Deutsch B2') }}">
        @error('exam_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Date examen <span class="text-danger">*</span></label>
        <input type="date" name="exam_date" class="form-control @error('exam_date') is-invalid @enderror" required
            value="{{ old('exam_date', isset($cert->exam_date) ? $cert->exam_date->format('Y-m-d') : '') }}">
        @error('exam_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Date délivrance <span class="text-danger">*</span></label>
        <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror" required
            value="{{ old('issue_date', isset($cert->issue_date) ? $cert->issue_date->format('Y-m-d') : '') }}">
        @error('issue_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">
            Numéro du certificat
            @if($cert) <span class="text-danger">*</span> @endif
        </label>
        @if($cert)
            <input type="text" name="certificate_number" class="form-control @error('certificate_number') is-invalid @enderror" required
                value="{{ old('certificate_number', $cert->certificate_number) }}">
            @error('certificate_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @else
            <input type="text" class="form-control" disabled placeholder="Généré automatiquement">
            <small class="text-muted">Attribué automatiquement à la création.</small>
        @endif
    </div>



    {{-- ============================================================ --}}
    {{--   B2 SCORES — Schriftliche + Mündliche Prüfung              --}}
    {{-- ============================================================ --}}
    <div id="b2-scores" class="col-12" style="{{ $currentType !== 'b2' ? 'display:none' : '' }}">
        <div class="row">

            <div class="col-12 mt-4">
                <h5 class="mb-3 fw-bold">Schriftliche Prüfung (Écrit) — B2 <small class="text-muted">(Total: 225)</small></h5>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    Leseverstehen <span class="text-danger">*</span> <small class="text-muted">(Max: {{ $configs['b2']['reading'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['reading'] }}"
                    name="reading_score" class="form-control b2-field @error('reading_score') is-invalid @enderror"
                    {{ $currentType === 'b2' ? 'required' : 'disabled' }}
                    value="{{ old('reading_score', $cert && $cert->isB2() ? $cert->reading_score : '') }}">
                @error('reading_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    Sprachbausteine <span class="text-danger">*</span> <small class="text-muted">(Max: {{ $configs['b2']['grammar'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['grammar'] }}"
                    name="grammar_score" class="form-control b2-field @error('grammar_score') is-invalid @enderror"
                    {{ $currentType === 'b2' ? 'required' : 'disabled' }}
                    value="{{ old('grammar_score', $cert && $cert->isB2() ? $cert->grammar_score : '') }}">
                @error('grammar_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    Hörverstehen <span class="text-danger">*</span> <small class="text-muted">(Max: {{ $configs['b2']['listening'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['listening'] }}"
                    name="listening_score" class="form-control b2-field @error('listening_score') is-invalid @enderror"
                    {{ $currentType === 'b2' ? 'required' : 'disabled' }}
                    value="{{ old('listening_score', $cert && $cert->isB2() ? $cert->listening_score : '') }}">
                @error('listening_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    Schriftlicher Ausdruck <span class="text-danger">*</span> <small class="text-muted">(Max: {{ $configs['b2']['writing'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['writing'] }}"
                    name="writing_score" class="form-control b2-field @error('writing_score') is-invalid @enderror"
                    {{ $currentType === 'b2' ? 'required' : 'disabled' }}
                    value="{{ old('writing_score', $cert && $cert->isB2() ? $cert->writing_score : '') }}">
                @error('writing_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 mt-3">
                <h5 class="mb-3 fw-bold">Mündliche Prüfung (Oral) — B2 <small class="text-muted">(Total: 75)</small></h5>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">
                    Präsentation <span class="text-danger">*</span> <small class="text-muted">(Max: {{ $configs['b2']['presentation'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['presentation'] }}"
                    name="presentation_score" class="form-control b2-field @error('presentation_score') is-invalid @enderror"
                    {{ $currentType === 'b2' ? 'required' : 'disabled' }}
                    value="{{ old('presentation_score', $cert && $cert->isB2() ? $cert->presentation_score : '') }}">
                @error('presentation_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">
                    Diskussion <span class="text-danger">*</span> <small class="text-muted">(Max: {{ $configs['b2']['discussion'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['discussion'] }}"
                    name="discussion_score" class="form-control b2-field @error('discussion_score') is-invalid @enderror"
                    {{ $currentType === 'b2' ? 'required' : 'disabled' }}
                    value="{{ old('discussion_score', $cert && $cert->isB2() ? $cert->discussion_score : '') }}">
                @error('discussion_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">
                    Problemlösung <span class="text-danger">*</span> <small class="text-muted">(Max: {{ $configs['b2']['problemsolving'] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b2']['problemsolving'] }}"
                    name="problemsolving_score" class="form-control b2-field @error('problemsolving_score') is-invalid @enderror"
                    {{ $currentType === 'b2' ? 'required' : 'disabled' }}
                    value="{{ old('problemsolving_score', $cert && $cert->isB2() ? $cert->problemsolving_score : '') }}">
                @error('problemsolving_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

        </div>
    </div>



    {{-- ============================================================ --}}
    {{--   A2 SCORES — Lesen / Hören / Schreiben / Sprechen (Max 25) --}}
    {{-- ============================================================ --}}
    <div id="a2-scores" class="col-12" style="{{ $currentType !== 'a2' ? 'display:none' : '' }}">
        <div class="row">
            <div class="col-12 mt-4">
                <h5 class="mb-3 fw-bold">Prüfung — A2 <small class="text-muted">(Total: {{ $configs['a2']['reading'] * 4 }})</small></h5>
            </div>
            @foreach(['reading' => 'Lesen', 'listening' => 'Hören', 'writing' => 'Schreiben', 'speaking' => 'Sprechen'] as $field => $label)
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    {{ $label }} <span class="text-danger">*</span> <small class="text-muted">(Max: {{ $configs['a2'][$field] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['a2'][$field] }}"
                    name="{{ $field }}_score" class="form-control a2-field @error($field.'_score') is-invalid @enderror"
                    {{ $currentType === 'a2' ? 'required' : 'disabled' }}
                    value="{{ old($field.'_score', $cert && $cert->isA2() ? $cert->{$field.'_score'} : '') }}">
                @error($field.'_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            @endforeach
        </div>
    </div>

    {{-- ============================================================ --}}
    {{--   A1 SCORES — Lesen / Hören / Schreiben / Sprechen (Max 15) --}}
    {{-- ============================================================ --}}
    <div id="a1-scores" class="col-12" style="{{ $currentType !== 'a1' ? 'display:none' : '' }}">
        <div class="row">
            <div class="col-12 mt-4">
                <h5 class="mb-3 fw-bold">Prüfung — A1 <small class="text-muted">(Total: {{ $configs['a1']['reading'] * 4 }})</small></h5>
            </div>
            @foreach(['reading' => 'Lesen', 'listening' => 'Hören', 'writing' => 'Schreiben', 'speaking' => 'Sprechen'] as $field => $label)
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    {{ $label }} <span class="text-danger">*</span> <small class="text-muted">(Max: {{ $configs['a1'][$field] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['a1'][$field] }}"
                    name="{{ $field }}_score" class="form-control a1-field @error($field.'_score') is-invalid @enderror"
                    {{ $currentType === 'a1' ? 'required' : 'disabled' }}
                    value="{{ old($field.'_score', $cert && $cert->isA1() ? $cert->{$field.'_score'} : '') }}">
                @error($field.'_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            @endforeach
        </div>
    </div>

    {{-- ============================================================ --}}
    {{--   B1 SCORES — Lesen / Hören / Schreiben / Sprechen (Max 60) --}}
    {{-- ============================================================ --}}
    <div id="b1-scores" class="col-12" style="{{ $currentType !== 'b1' ? 'display:none' : '' }}">
        <div class="row">
            <div class="col-12 mt-4">
                <h5 class="mb-3 fw-bold">Prüfung — B1 <small class="text-muted">(Total: {{ $configs['b1']['reading'] * 4 }})</small></h5>
            </div>
            @foreach(['reading' => 'Lesen', 'listening' => 'Hören', 'writing' => 'Schreiben', 'speaking' => 'Sprechen'] as $field => $label)
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">
                    {{ $label }} <span class="text-danger">*</span> <small class="text-muted">(Max: {{ $configs['b1'][$field] }})</small>
                </label>
                <input type="number" min="0" max="{{ $configs['b1'][$field] }}"
                    name="{{ $field }}_score" class="form-control b1-field @error($field.'_score') is-invalid @enderror"
                    {{ $currentType === 'b1' ? 'required' : 'disabled' }}
                    value="{{ old($field.'_score', $cert && $cert->isB1() ? $cert->{$field.'_score'} : '') }}">
                @error($field.'_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            @endforeach
        </div>
    </div>



    {{-- ========================= --}}
    {{--       FINAL RESULT        --}}
    {{-- ========================= --}}
    <div class="col-12 mt-4">
        <h5 class="mb-3 fw-bold">Résultat final</h5>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Note <span class="text-danger">*</span> <small class="text-muted">(auto-calculée)</small></label>
        <input type="text" name="final_result" id="finalResultInput" class="form-control @error('final_result') is-invalid @enderror" required
            value="{{ old('final_result', $cert->final_result ?? '') }}"
            placeholder="Ex: 240/300">
        @error('final_result') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Ergebnis</label>
        <input type="text" name="ergebnis_note" id="ergebnisNoteInput" class="form-control @error('ergebnis_note') is-invalid @enderror"
            value="{{ old('ergebnis_note', $cert->ergebnis_note ?? '') }}"
            placeholder="Ex: Befriedigend, Gut, Sehr gut...">
        @error('ergebnis_note') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

</div>

{{-- ========================= --}}
{{--   TYPE TOGGLE SCRIPT     --}}
{{-- ========================= --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect       = document.getElementById('certificateType');
    const examLevel        = document.getElementById('examLevelInput');
    const finalResultInput = document.getElementById('finalResultInput');

    const blocks = {
        b2: document.getElementById('b2-scores'),
        a2: document.getElementById('a2-scores'),
        a1: document.getElementById('a1-scores'),
        b1: document.getElementById('b1-scores'),
    };

    const configs = {
        b2: { fields: ['reading_score','grammar_score','listening_score','writing_score','presentation_score','discussion_score','problemsolving_score'], total: 300 },
        a2: { fields: ['reading_score','listening_score','writing_score','speaking_score'], total: 100 },
        a1: { fields: ['reading_score','listening_score','writing_score','speaking_score'], total: 60  },
        b1: { fields: ['reading_score','listening_score','writing_score','speaking_score'], total: 240 },
    };

    const levelLabels = { b2: 'Deutsch B2', b1: 'Deutsch B1', a2: 'Deutsch A2', a1: 'Deutsch A1' };

    function calculateNote() {
        const type = typeSelect.value;
        const cfg  = configs[type];
        const cls  = type + '-field';

        let total = 0, hasAny = false;
        document.querySelectorAll('.' + cls + ':not([disabled])').forEach(function(el) {
            if (el.value !== '') { hasAny = true; total += parseInt(el.value) || 0; }
        });

        if (hasAny) finalResultInput.value = total + '/' + cfg.total;
    }

    // Single shared alert element
    let alertEl = null;
    function getAlert() {
        if (!alertEl) {
            alertEl = document.createElement('div');
            alertEl.className = 'alert alert-danger alert-dismissible fade show mt-2 mb-0 py-2 px-3';
            alertEl.style.fontSize = '0.85rem';
            alertEl.innerHTML = '<span id="score-alert-msg"></span>'
                + '<button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>';
        }
        return alertEl;
    }

    function showMaxAlert(input, max) {
        const label = input.closest('.col-md-3, .col-md-4')
                           ?.querySelector('label')
                           ?.childNodes[0]?.textContent?.trim() || 'Ce champ';
        const al = getAlert();
        document.getElementById('score-alert-msg').textContent =
            label + ' : la valeur maximale est ' + max + '. La note a été ramenée à ' + max + '.';
        input.closest('.col-md-3, .col-md-4').appendChild(al);
        clearTimeout(alertEl._timer);
        alertEl._timer = setTimeout(function() {
            if (alertEl.parentNode) alertEl.parentNode.removeChild(alertEl);
        }, 3000);
    }

    function enforceMax(input) {
        const max = parseInt(input.getAttribute('max'));
        if (!isNaN(max) && input.value !== '') {
            const v = parseInt(input.value);
            if (!isNaN(v) && v > max) { input.value = max; showMaxAlert(input, max); }
            if (!isNaN(v) && v < 0)   { input.value = 0; }
        }
    }

    function toggleType() {
        const type = typeSelect.value;

        Object.keys(blocks).forEach(function(t) {
            const block = blocks[t];
            if (!block) return;
            const isActive = (t === type);
            block.style.display = isActive ? '' : 'none';
            block.querySelectorAll('.' + t + '-field').forEach(function(f) {
                f.disabled = !isActive;
                if (isActive) { f.required = true; } else { f.removeAttribute('required'); }
            });
        });

        if (!examLevel.value || Object.values(levelLabels).includes(examLevel.value)) {
            examLevel.value = levelLabels[type] || '';
        }

        calculateNote();
    }

    document.querySelectorAll('.b2-field, .a2-field, .a1-field, .b1-field').forEach(function(input) {
        input.addEventListener('input', function() {
            enforceMax(this);
            calculateNote();
        });
    });

    typeSelect.addEventListener('change', toggleType);
    toggleType();
});
</script>
