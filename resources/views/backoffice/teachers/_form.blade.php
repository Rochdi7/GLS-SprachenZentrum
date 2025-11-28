<div class="row">

    {{-- TEACHER NAME --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom complet</label>
        <input type="text" name="name"
               class="form-control"
               value="{{ old('name', $teacher->name ?? '') }}"
               placeholder="Nom de l’enseignant"
               required>
    </div>

    {{-- GLS SITE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Centre GLS</label>
        <select name="site_id" class="form-select" required>
            <option value="">Sélectionner un centre</option>
            @foreach($sites as $site)
                <option value="{{ $site->id }}"
                    {{ old('site_id', $teacher->site_id ?? '') == $site->id ? 'selected' : '' }}>
                    {{ $site->name }} ({{ $site->city }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- IMAGE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Photo</label>
        <input type="file" name="image" class="form-control">

        @php
            $media = isset($teacher) ? $teacher->getFirstMedia('teacher_image') : null;
        @endphp

        @if($media)
            <div class="mt-2">
                <img src="{{ $media->getUrl() }}"
                     class="rounded-circle"
                     style="width: 80px; height: 80px; object-fit: cover;">
            </div>
        @endif
    </div>

    {{-- EMAIL --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Email</label>
        <input type="email" name="email"
               class="form-control"
               value="{{ old('email', $teacher->email ?? '') }}"
               placeholder="email@exemple.com">
    </div>

    {{-- TELEPHONE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Téléphone</label>
        <input type="text" name="phone"
               class="form-control"
               value="{{ old('phone', $teacher->phone ?? '') }}"
               placeholder="+212 6 00 00 00 00">
    </div>

    {{-- SPECIALITY --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Spécialité</label>
        <input type="text" name="speciality"
               class="form-control"
               value="{{ old('speciality', $teacher->speciality ?? '') }}"
               placeholder="Ex: A1, A2, B1, Grammaire">
    </div>

    {{-- BIO --}}
    <div class="col-md-12 mb-3">
        <label class="form-label fw-bold">Biographie</label>
        <textarea 
            name="bio"
            id="bio-editor"
            class="form-control"
            rows="8"
        >{{ old('bio', $teacher->bio ?? '') }}</textarea>
    </div>

</div>
