<div class="row">

    {{-- SITE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Centre GLS</label>
        <select name="site_id" class="form-select" required>
            <option value="">Sélectionner un centre</option>
            @foreach($sites as $site)
                <option value="{{ $site->id }}"
                    {{ old('site_id', $group->site_id ?? '') == $site->id ? 'selected' : '' }}>
                    {{ $site->name }} ({{ $site->city }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- TEACHER --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Enseignant</label>
        <select name="teacher_id" class="form-select" required>
            <option value="">Sélectionner un enseignant</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}"
                    {{ old('teacher_id', $group->teacher_id ?? '') == $teacher->id ? 'selected' : '' }}>
                    {{ $teacher->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- LEVEL --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Niveau</label>
        <select name="level" class="form-select" required>
            <option value="A1" {{ old('level', $group->level ?? '') == 'A1' ? 'selected' : '' }}>A1</option>
            <option value="A2" {{ old('level', $group->level ?? '') == 'A2' ? 'selected' : '' }}>A2</option>
            <option value="B1" {{ old('level', $group->level ?? '') == 'B1' ? 'selected' : '' }}>B1</option>
            <option value="B2" {{ old('level', $group->level ?? '') == 'B2' ? 'selected' : '' }}>B2</option>
        </select>
    </div>

    {{-- PERIOD --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Période</label>
        <input type="text" name="period_label"
               class="form-control"
               value="{{ old('period_label', $group->period_label ?? '') }}"
               placeholder="Ex: Groupe du matin" required>
    </div>

    {{-- TIME RANGE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Horaire</label>
        <input type="text" name="time_range"
               class="form-control"
               value="{{ old('time_range', $group->time_range ?? '') }}"
               placeholder="Ex: 10:00 – 12:30" required>
    </div>

    {{-- DESCRIPTION --}}
    <div class="col-12 mb-3">
        <label class="form-label fw-bold">Description</label>
        <textarea 
            name="description"
            id="group-description"
            class="form-control"
            rows="6"
        >{{ old('description', $group->description ?? '') }}</textarea>
    </div>

</div>
