@php $teacher = $teacher ?? null; @endphp

<div class="row">

    {{-- TEACHER NAME --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
        <input type="text" name="name"
               class="form-control @error(‘name’) is-invalid @enderror"
               value="{{ old(‘name’, $teacher->name ?? ‘’) }}"
               placeholder="Nom de l’enseignant"
               required>
        @error(‘name’) <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- CENTRES AFFECTÉS (multi) --}}
    @php
        $assignedSiteIds = collect(old('site_ids', isset($teacher) ? $teacher->accessibleSiteIds() : []))
            ->map(fn ($v) => (int) $v)
            ->all();
    @endphp
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Centres affectés (accès multi-centres) <span class="text-danger">*</span></label>
        <select name="site_ids[]" class="form-select" id="teacher_sites_multi" multiple size="6" required>
            @foreach($sites as $site)
                <option value="{{ $site->id }}"
                    {{ in_array((int) $site->id, $assignedSiteIds, true) ? 'selected' : '' }}>
                    {{ $site->name }} ({{ $site->city }})
                </option>
            @endforeach
        </select>
        <small class="text-muted">
            Maintenez <kbd>Ctrl</kbd> (ou <kbd>⌘</kbd>) pour sélectionner plusieurs centres.
            Au moins un centre est requis.
        </small>
    </div>

    {{-- IMAGE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Photo</label>
        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @php $media = isset($teacher) ? $teacher->getFirstMedia('teacher_image') : null; @endphp
        @if($media)
            <div class="mt-2">
                <img src="{{ $media->getUrl() }}" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;">
            </div>
        @endif
    </div>

    {{-- EMAIL --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Email</label>
        <input type="email" name="email"
               class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $teacher->email ?? '') }}"
               placeholder="email@exemple.com">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- TELEPHONE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Téléphone</label>
        <input type="text" name="phone"
               class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $teacher->phone ?? '') }}"
               placeholder="+212 6 00 00 00 00">
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- SPECIALITY --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Spécialité</label>
        <input type="text" name="speciality"
               class="form-control @error('speciality') is-invalid @enderror"
               value="{{ old('speciality', $teacher->speciality ?? '') }}"
               placeholder="Ex: A1, A2, B1, Grammaire">
        @error('speciality') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- BIO --}}
    <div class="col-md-12 mb-3">
        <label class="form-label fw-bold">Biographie</label>
        <textarea name="bio" id="bio-editor" class="form-control @error('bio') is-invalid @enderror" rows="8">{{ old('bio', $teacher->bio ?? '') }}</textarea>
        @error('bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

</div>
