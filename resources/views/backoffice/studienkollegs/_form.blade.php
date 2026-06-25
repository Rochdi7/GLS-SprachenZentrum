@php
    $item = $studienkolleg ?? null;

    // ===== Safe values for selects (avoid old() boolean inversion bugs) =====
    $uniAssist = old('uni_assist', (int) ($item->uni_assist ?? 1));
    $entranceExam = old('entrance_exam', (int) ($item->entrance_exam ?? 1));
    $certRequired = old('certification_required', (int) ($item->certification_required ?? 0));
    $transRequired = old('translation_required', (int) ($item->translation_required ?? 0));
    $isPublic = old('public', (int) ($item->public ?? 1));
    $featured = old('featured', (int) ($item->featured ?? 0));

    // ===== Course map (MUST be defined before the foreach) =====
    $courseMap = [
        'T' => 'T Course',
        'W' => 'W Course',
        'M' => 'M Course',
        'G' => 'G Course',
        'S' => 'S Course',
        'TI' => 'TI Course',
        'WW' => 'WW Course',
        'SW' => 'SW Course',
    ];

    // ===== Safe arrays/JSON & Deadlines Normalization =====
    // DB stores deadlines as LIST format:
    // [{semester: "Winter Semester (WS)", range: "15.03 – 15.04", note: "..."}, ...]
    // But form uses MAP format: deadlines[Winter Semester][range], etc.
    // This block normalizes both formats for edit display.
    $deadlines = old('deadlines', $item->deadlines ?? []);

    // If it's a JSON string, decode it
if (is_string($deadlines)) {
    $deadlines = json_decode($deadlines, true) ?? [];
}

// Initialize winter and summer as empty arrays with safe defaults
$winter = ['range' => '', 'note' => ''];
$summer = ['range' => '', 'note' => ''];

// If deadlines is a list format (from DB/seeder), extract winter/summer rows
if (is_array($deadlines) && !empty($deadlines)) {
    if (isset($deadlines[0]) && is_array($deadlines[0]) && isset($deadlines[0]['semester'])) {
        // LIST format detected: [{semester: "...", range: "...", ...}, ...]
        foreach ($deadlines as $row) {
            $semester = $row['semester'] ?? '';
            if (stripos($semester, 'Winter') !== false || stripos($semester, 'WS') !== false) {
                $winter = [
                    'range' => $row['range'] ?? '',
                    'note' => $row['note'] ?? '',
                ];
            } elseif (stripos($semester, 'Summer') !== false || stripos($semester, 'SS') !== false) {
                $summer = [
                    'range' => $row['range'] ?? '',
                    'note' => $row['note'] ?? '',
                ];
            }
        }
    } else {
        // MAP format detected: {"Winter Semester": {...}, "Summer Semester": {...}}
        $winter = $deadlines['Winter Semester'] ?? ['range' => '', 'note' => ''];
        $summer = $deadlines['Summer Semester'] ?? ['range' => '', 'note' => ''];
    }
}

$selectedCourses = old('courses');
if ($selectedCourses === null) {
    $raw = $item->courses ?? [];

    if (is_array($raw)) {
        $selectedCourses = $raw;
    } elseif (is_string($raw)) {
        $decoded = json_decode($raw, true);
        $selectedCourses = is_array($decoded) ? $decoded : [];
    } else {
        $selectedCourses = [];
    }
} elseif (!is_array($selectedCourses)) {
    $selectedCourses = [];
}

// Normalize courses: if DB contains labels (e.g., "TI Course"), extract the keys
$reverseMap = array_flip($courseMap); // Create "TI Course" => "TI" mapping
$selectedCourses = array_map(function ($item) use ($reverseMap, $courseMap) {
    // If it's already a key (T, W, TI, etc.), return as is
        if (isset($courseMap[$item])) {
            return $item;
        }
        // If it's a label (T Course, TI Course, etc.), map back to key
    return $reverseMap[$item] ?? $item;
}, $selectedCourses);
$selectedCourses = array_filter($selectedCourses, function ($v) use ($courseMap) {
    return isset($courseMap[$v]);
});

// Languages & documents textarea
$languagesText = old('languages');
if ($languagesText === null) {
    $languagesText = implode("\n", $item->languages ?? []);
}

$documentsText = old('documents');
if ($documentsText === null) {
    $documentsText = implode("\n", $item->documents ?? []);
}

// Requirements textarea - display as lines, not JSON
$requirementsValue = old('requirements');
    if ($requirementsValue === null) {
        $requirementsValue = implode("\n", $item->requirements ?? []);
    }
@endphp


<div class="card mb-4">
    <div class="card-header">
        <h6>Informations générales</h6>
    </div>
    <div class="card-body row">

        <div class="col-md-6 mb-3">
            <label class="form-label">Nom <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name', $item->name ?? '') }}">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Ville <span class="text-danger">*</span></label>
            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" required
                value="{{ old('city', $item->city ?? '') }}">
            @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Pays</label>
            <input type="text" name="country" class="form-control"
                value="{{ old('country', $item->country ?? 'Germany') }}">
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">État / Région</label>
            <input type="text" name="state" class="form-control" value="{{ old('state', $item->state ?? '') }}">
        </div>

    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6>Hero & Médias</h6>
    </div>

    <div class="card-body row">

        {{-- HERO IMAGE --}}
        <div class="col-md-4 mb-3">
            <label class="form-label">Hero Image</label>
            <input type="file" name="hero_image" class="form-control" accept="image/*">

            @php $hero = $item?->getFirstMediaUrl('studienkolleg_hero'); @endphp
            @if ($hero)
                <img src="{{ $hero }}" class="mt-2" style="max-height:120px;border-radius:8px;">
            @endif
        </div>

        {{-- CARD IMAGE --}}
        <div class="col-md-4 mb-3">
            <label class="form-label">Card Image</label>
            <input type="file" name="card_image" class="form-control" accept="image/*">

            @php $card = $item?->getFirstMediaUrl('studienkolleg_card'); @endphp
            @if ($card)
                <img src="{{ $card }}" class="mt-2" style="max-height:120px;border-radius:8px;">
            @endif
        </div>

        {{-- UNIVERSITY LOGO --}}
        <div class="col-md-4 mb-3">
            <label class="form-label">University Logo</label>
            <input type="file" name="university_logo" class="form-control" accept="image/*">

            @php $logo = $item?->getFirstMediaUrl('university_logo'); @endphp
            @if ($logo)
                <img src="{{ $logo }}" class="mt-2" style="max-height:80px;border-radius:6px;">
            @endif
        </div>

        {{-- VIDEO --}}
        <div class="col-md-12 mt-3">
            <label class="form-label">Vidéo YouTube (URL)</label>
            <input type="url" name="video_url" class="form-control"
                value="{{ old('video_url', $item->video_url ?? '') }}">
        </div>

        {{-- FEATURED --}}
        <div class="col-md-3 mb-3">
            <label class="form-label d-block">Mis en avant</label>

            {{-- hidden pour envoyer 0 si décoché --}}
            <input type="hidden" name="featured" value="0">

            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="featured" value="1" id="featuredSwitch"
                    @checked((string) $featured === '1')>
                <label class="form-check-label" for="featuredSwitch">
                    Featured (homepage / cards)
                </label>
            </div>
        </div>

    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6>Application Process & Selection</h6>
    </div>
    <div class="card-body row">

        <div class="col-md-4 mb-3">
            <label class="form-label">Méthode d’application</label>
            <input type="text" name="application_method" class="form-control"
                value="{{ old('application_method', $item->application_method ?? '') }}">
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Note portail application</label>
            <input type="text" name="application_portal_note" class="form-control"
                value="{{ old('application_portal_note', $item->application_portal_note ?? '') }}">
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">URL Application</label>
            <input type="url" name="application_url" class="form-control"
                value="{{ old('application_url', $item->application_url ?? '') }}">
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Langue d’enseignement</label>
            <input type="text" name="language_of_instruction" class="form-control"
                value="{{ old('language_of_instruction', $item->language_of_instruction ?? 'German') }}">
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Durée (semestres)</label>
            <input type="number" name="duration_semesters" min="1" class="form-control"
                value="{{ old('duration_semesters', $item->duration_semesters ?? 2) }}">
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Frais de scolarité</label>
            <input type="text" name="tuition" class="form-control"
                value="{{ old('tuition', $item->tuition ?? 'Free') }}">
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Uni-Assist</label>
            <select name="uni_assist" class="form-select">
                <option value="1" @selected((string) $uniAssist === '1')>Oui</option>
                <option value="0" @selected((string) $uniAssist === '0')>Non</option>
            </select>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Entrance Exam</label>
            <select name="entrance_exam" class="form-select">
                <option value="1" @selected((string) $entranceExam === '1')>Oui</option>
                <option value="0" @selected((string) $entranceExam === '0')>Non</option>
            </select>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Exam Subjects</label>
            <input type="text" name="exam_subjects" class="form-control"
                value="{{ old('exam_subjects', $item->exam_subjects ?? '') }}">
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Exam Link</label>
            <input type="url" name="exam_link" class="form-control"
                value="{{ old('exam_link', $item->exam_link ?? '') }}">
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Certification requise</label>
            <select name="certification_required" class="form-select">
                <option value="1" @selected((string) $certRequired === '1')>Oui</option>
                <option value="0" @selected((string) $certRequired === '0')>Non</option>
            </select>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Traduction requise</label>
            <select name="translation_required" class="form-select">
                <option value="1" @selected((string) $transRequired === '1')>Oui</option>
                <option value="0" @selected((string) $transRequired === '0')>Non</option>
            </select>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Statut</label>
            <select name="public" class="form-select">
                <option value="1" @selected((string) $isPublic === '1')>Oui (Public)</option>
                <option value="0" @selected((string) $isPublic === '0')>Non (Privé)</option>
            </select>
        </div>

    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6>Deadlines</h6>
    </div>
    <div class="card-body row">

        <div class="col-md-6 mb-3">
            <label class="form-label">Winter Semester – Range</label>
            <input type="text" name="deadlines[Winter Semester][range]" class="form-control"
                placeholder="15.03 – 15.04"
                value="{{ old('deadlines.Winter Semester.range', $winter['range'] ?? '') }}">
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Winter Semester – Note</label>
            <input type="text" name="deadlines[Winter Semester][note]" class="form-control"
                value="{{ old('deadlines.Winter Semester.note', $winter['note'] ?? '') }}">
        </div>

        {{-- Summer Semester --}}
        <div class="col-md-6 mb-3">
            <label class="form-label">Summer Semester – Range</label>
            <input type="text" name="deadlines[Summer Semester][range]" class="form-control"
                placeholder="15.09 – 15.10"
                value="{{ old('deadlines.Summer Semester.range', $summer['range'] ?? '') }}">
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Summer Semester – Note</label>
            <input type="text" name="deadlines[Summer Semester][note]" class="form-control"
                value="{{ old('deadlines.Summer Semester.note', $summer['note'] ?? '') }}">
        </div>

    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6>Admission Requirements</h6>
    </div>
    <div class="card-body">
        <textarea name="requirements" class="form-control" rows="6">{{ $requirementsValue }}</textarea>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6>Documents & Langues</h6>
    </div>
    <div class="card-body">

        <label class="form-label">Langues (une par ligne)</label>
        <textarea name="languages" class="form-control mb-3" rows="3">{{ $languagesText }}</textarea>

        <label class="form-label">Documents requis (une par ligne)</label>
        <textarea name="documents" class="form-control" rows="4">{{ $documentsText }}</textarea>

    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6>Contact & Map</h6>
    </div>
    <div class="card-body row">

        <div class="col-md-4 mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="contact_email" class="form-control"
                value="{{ old('contact_email', $item->contact_email ?? '') }}">
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Site officiel</label>
            <input type="url" name="official_website" class="form-control"
                value="{{ old('official_website', $item->official_website ?? '') }}">
        </div>

        <div class="col-md-12 mb-3">
            <label class="form-label">Adresse</label>
            <input type="text" name="address" class="form-control"
                value="{{ old('address', $item->address ?? '') }}">
        </div>

        <div class="col-md-12">
            <label class="form-label">Map Embed</label>
            <textarea name="map_embed" class="form-control" rows="3">{{ old('map_embed', $item->map_embed ?? '') }}</textarea>
        </div>

    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6>Sidebar – Courses</h6>
    </div>

    <div class="card-body">
        <select name="courses[]" class="form-select" multiple size="8">
            @foreach ($courseMap as $key => $label)
                <option value="{{ $key }}" @selected(in_array($key, $selectedCourses, true))>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <small class="text-muted d-block mt-2">
            Hold <strong>CTRL</strong> (Windows) or <strong>CMD</strong> (Mac) to select multiple courses.
        </small>
    </div>
</div>
