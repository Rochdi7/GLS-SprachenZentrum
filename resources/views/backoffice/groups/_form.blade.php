<div class="row">

    {{-- NOM DU GROUPE (DEFAULT / BACKUP) --}}
    <div class="col-md-12 mb-3">
        <label class="form-label fw-bold">Nom du groupe (Fallback)</label>
        <input type="text" name="name"
               class="form-control"
               value="{{ old('name', $group->name ?? '') }}"
               placeholder="Ex: Groupe A1 Intensif">
        <small class="text-muted">Utilisé si les versions FR/EN sont vides.</small>
    </div>

    {{-- NOM FR --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom du groupe (FR)</label>
        <input type="text" name="name_fr"
               class="form-control"
               value="{{ old('name_fr', $group->name_fr ?? '') }}"
               placeholder="Ex: Groupe A1 Intensif">
    </div>

    {{-- NOM EN --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom du groupe (EN)</label>
        <input type="text" name="name_en"
               class="form-control"
               value="{{ old('name_en', $group->name_en ?? '') }}"
               placeholder="Ex: A1 Intensive Group">
    </div>

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

    {{-- ENSEIGNANT --}}
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

    {{-- NIVEAU --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Niveau</label>
        <select name="level" class="form-select" required>
            @foreach (['A1', 'A2', 'B1', 'B2'] as $level)
                <option value="{{ $level }}"
                    {{ old('level', $group->level ?? '') == $level ? 'selected' : '' }}>
                    {{ $level }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- STATUT ACTIVE / UPCOMING --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Statut</label>
        <select name="status" class="form-select" required>
            <option value="active"   {{ old('status', $group->status ?? '') == 'active' ? 'selected' : '' }}>Actif</option>
            <option value="upcoming" {{ old('status', $group->status ?? '') == 'upcoming' ? 'selected' : '' }}>À venir</option>
        </select>
    </div>

    {{-- HORAIRE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Horaire</label>
        <input type="text" name="time_range"
               class="form-control"
               value="{{ old('time_range', $group->time_range ?? '') }}"
               placeholder="Ex: 10:00 - 12:30" required>
        <small class="text-muted">La période sera détectée automatiquement.</small>
    </div>

</div>
