@php
    $item = $studienkolleg ?? null;
@endphp

<div class="card mb-4">
    <div class="card-header">
        <h6>Informations générales</h6>
    </div>
    <div class="card-body row">

        <div class="col-md-6 mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name', $item->name ?? '') }}">
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Ville</label>
            <input type="text" name="city" class="form-control" required
                value="{{ old('city', $item->city ?? '') }}">
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

            @php
                $hero = $item?->getFirstMediaUrl('studienkolleg_hero');
            @endphp

            @if ($hero)
                <img src="{{ $hero }}" class="mt-2" style="max-height:120px;border-radius:8px;">
            @endif
        </div>

        {{-- CARD IMAGE --}}
        <div class="col-md-4 mb-3">
            <label class="form-label">Card Image</label>
            <input type="file" name="card_image" class="form-control" accept="image/*">

            @php
                $card = $item?->getFirstMediaUrl('studienkolleg_card');
            @endphp

            @if ($card)
                <img src="{{ $card }}" class="mt-2" style="max-height:120px;border-radius:8px;">
            @endif
        </div>

        {{-- UNIVERSITY LOGO --}}
        <div class="col-md-4 mb-3">
            <label class="form-label">University Logo</label>
            <input type="file" name="university_logo" class="form-control" accept="image/*">

            @php
                $logo = $item?->getFirstMediaUrl('university_logo');
            @endphp

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
                    @checked(old('featured', $item->featured ?? false))>

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
                <option value="1" @selected(old('uni_assist', $item->uni_assist ?? 1))>Oui</option>
                <option value="0" @selected(!old('uni_assist', $item->uni_assist ?? 1))>Non</option>
            </select>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Entrance Exam</label>
            <select name="entrance_exam" class="form-select">
                <option value="1" @selected(old('entrance_exam', $item->entrance_exam ?? 1))>Oui</option>
                <option value="0" @selected(!old('entrance_exam', $item->entrance_exam ?? 1))>Non</option>
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
                <option value="1" @selected(old('certification_required', $item->certification_required ?? 0))>Oui</option>
                <option value="0" @selected(!old('certification_required', $item->certification_required ?? 0))>Non</option>
            </select>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Traduction requise</label>
            <select name="translation_required" class="form-select">
                <option value="1" @selected(old('translation_required', $item->translation_required ?? 0))>Oui</option>
                <option value="0" @selected(!old('translation_required', $item->translation_required ?? 0))>Non</option>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Statut</label>
            <select name="public" class="form-select">
                <option value="1" @selected(old('public', $item->public ?? 1) == 1)>
                    Oui (Public)
                </option>
                <option value="0" @selected(old('public', $item->public ?? 1) == 0)>
                    Non (Privé)
                </option>
            </select>
        </div>

    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6>Deadlines</h6>
    </div>
    <div class="card-body row">

        <div class="col-md-4 mb-3">
            <label class="form-label">Winter Semester – Start</label>
            <input type="text" name="deadlines[Winter Semester][start]" class="form-control"
                value="{{ old('deadlines.Winter Semester.start', $item->deadlines['Winter Semester']['start'] ?? '') }}">
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Winter Semester – End</label>
            <input type="text" name="deadlines[Winter Semester][end]" class="form-control"
                value="{{ old('deadlines.Winter Semester.end', $item->deadlines['Winter Semester']['end'] ?? '') }}">
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Note</label>
            <input type="text" name="deadlines[Winter Semester][note]" class="form-control"
                value="{{ old('deadlines.Winter Semester.note', $item->deadlines['Winter Semester']['note'] ?? '') }}">
        </div>

    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6>Admission Requirements</h6>
    </div>
    <div class="card-body">
        <textarea name="requirements" class="form-control" rows="6">{{ old('requirements', isset($item) ? json_encode($item->requirements, JSON_PRETTY_PRINT) : '') }}</textarea>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6>Documents & Langues</h6>
    </div>
    <div class="card-body">

        <label class="form-label">Langues (une par ligne)</label>
        <textarea name="languages" class="form-control mb-3" rows="3">{{ old('languages', isset($item) ? implode("\n", $item->languages ?? []) : '') }}</textarea>

        <label class="form-label">Documents requis (une par ligne)</label>
        <textarea name="documents" class="form-control" rows="4">{{ old('documents', isset($item) ? implode("\n", $item->documents ?? []) : '') }}</textarea>

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
        <select name="courses[]" class="form-select" multiple>
            @foreach (['T', 'W', 'M', 'G'] as $course)
                <option value="{{ $course }}" @selected(in_array($course, old('courses', $item->courses ?? [])))>
                    {{ $course }} Course
                </option>
            @endforeach
        </select>
    </div>
</div>
