<div class="row">

    {{-- TITLE FR --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Titre (Français)</label>
        <input type="text" name="title_fr" class="form-control" value="{{ old('title_fr', $post->title_fr ?? '') }}"
            placeholder="Titre en français" required>
    </div>

    {{-- TITLE EN --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Title (English)</label>
        <input type="text" name="title_en" class="form-control" value="{{ old('title_en', $post->title_en ?? '') }}"
            placeholder="English title">
    </div>

    {{-- CATEGORY --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Catégorie</label>
        <select name="category_id" id="blog_category_id" class="form-select" required>
            <option value="">Sélectionner une catégorie</option>

            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}"
                    {{ old('category_id', $post->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name_fr }}
                </option>
            @endforeach
        </select>
    </div>


    {{-- IMAGE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Image principale</label>
        <input type="file" name="image" class="form-control">

        @php
            $media = isset($post) ? $post->getFirstMedia('blog_images') : null;
        @endphp

        @if ($media)
            <div class="mt-2">
                <img src="{{ $media->getFullUrl() }}" class="rounded shadow"
                    style="width: 120px; height: 90px; object-fit: cover;">
            </div>
        @endif
    </div>

    {{-- READING TIME --}}
    <div class="col-md-2 mb-3">
        <label class="form-label fw-bold">Lecture (min)</label>
        <input type="number" name="reading_time" min="1" max="60" class="form-control"
            value="{{ old('reading_time', $post->reading_time ?? 3) }}">
    </div>

    {{-- STATUS --}}
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Statut</label>
        <select name="status" class="form-select" required>
            <option value="draft" {{ old('status', $post->status ?? '') === 'draft' ? 'selected' : '' }}>Brouillon
            </option>
            <option value="published" {{ old('status', $post->status ?? '') === 'published' ? 'selected' : '' }}>Publié
            </option>
        </select>
    </div>

    {{-- FEATURED --}}
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">À la une ?</label>
        <select name="featured" class="form-select">
            <option value="0" {{ old('featured', $post->featured ?? 0) == 0 ? 'selected' : '' }}>Non</option>
            <option value="1" {{ old('featured', $post->featured ?? 0) == 1 ? 'selected' : '' }}>Oui</option>
        </select>
    </div>


    {{-- CONTENT FR --}}
    <div class="col-12 mb-3">
        <label class="form-label fw-bold">Contenu (FR)</label>
        <textarea name="content_fr" id="editor-fr" class="form-control" rows="10">{{ old('content_fr', $post->content_fr ?? '') }}</textarea>
    </div>

    {{-- CONTENT EN --}}
    <div class="col-12 mb-3">
        <label class="form-label fw-bold">Content (EN)</label>
        <textarea name="content_en" id="editor-en" class="form-control" rows="10">{{ old('content_en', $post->content_en ?? '') }}</textarea>
    </div>

</div>
