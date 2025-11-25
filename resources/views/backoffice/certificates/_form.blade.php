@php
    $cert = $certificate ?? null;
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

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Niveau</label>
        <input type="text" name="exam_level" class="form-control" required
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

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Numéro du certificat</label>
        <input type="text" name="certificate_number" class="form-control" required
               value="{{ old('certificate_number', $cert->certificate_number ?? '') }}">
    </div>


    {{-- ========================= --}}
    {{--     SCHRIFTLICHE PRÜFUNG --}}
    {{-- ========================= --}}
    <div class="col-12 mt-4">
        <h5 class="mb-3 fw-bold">Schriftliche Prüfung (Écrit)</h5>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Leseverstehen</label>
        <input type="number" min="0" name="reading_score" class="form-control" required
               value="{{ old('reading_score', $cert->reading_score ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Sprachbausteine</label>
        <input type="number" min="0" name="grammar_score" class="form-control" required
               value="{{ old('grammar_score', $cert->grammar_score ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Hörverstehen</label>
        <input type="number" min="0" name="listening_score" class="form-control" required
               value="{{ old('listening_score', $cert->listening_score ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Schriftlicher Ausdruck</label>
        <input type="number" min="0" name="writing_score" class="form-control" required
               value="{{ old('writing_score', $cert->writing_score ?? '') }}">
    </div>


    {{-- ========================= --}}
    {{--     MÜNDLICHE PRÜFUNG    --}}
    {{-- ========================= --}}
    <div class="col-12 mt-4">
        <h5 class="mb-3 fw-bold">Mündliche Prüfung (Oral)</h5>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Präsentation</label>
        <input type="number" min="0" name="presentation_score" class="form-control" required
               value="{{ old('presentation_score', $cert->presentation_score ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Diskussion</label>
        <input type="number" min="0" name="discussion_score" class="form-control" required
               value="{{ old('discussion_score', $cert->discussion_score ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Problemlösung</label>
        <input type="number" min="0" name="problemsolving_score" class="form-control" required
               value="{{ old('problemsolving_score', $cert->problemsolving_score ?? '') }}">
    </div>


    {{-- ========================= --}}
    {{--       FINAL RESULT        --}}
    {{-- ========================= --}}
    <div class="col-12 mt-4">
        <h5 class="mb-3 fw-bold">Résultat final</h5>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Résultat</label>
        <input type="text" name="final_result" class="form-control" required
               value="{{ old('final_result', $cert->final_result ?? '') }}">
    </div>

</div>
