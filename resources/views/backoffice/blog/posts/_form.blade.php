@php $post = $post ?? null; @endphp

<div class="row">

    {{-- TITLE FR --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Titre (Français) <span class="text-danger">*</span></label>
        <input type="text" name="title_fr" class="form-control @error('title_fr') is-invalid @enderror"
            value="{{ old('title_fr', $post->title_fr ?? '') }}" placeholder="Titre en français" required>
        @error('title_fr') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- TITLE EN --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Titre (Anglais)</label>
        <input type="text" name="title_en" class="form-control @error('title_en') is-invalid @enderror"
            value="{{ old('title_en', $post->title_en ?? '') }}" placeholder="Titre en anglais">
        @error('title_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- CATEGORY --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
        <select name="category_id" id="blog_category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">Sélectionner une catégorie</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('category_id', $post->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name_fr }}
                </option>
            @endforeach
        </select>
        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- IMAGE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Image principale</label>
        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @php $media = isset($post) ? $post->getFirstMedia('blog_images') : null; @endphp
        @if ($media)
            <div class="mt-2">
                <img src="{{ $media->getFullUrl() }}" class="rounded shadow" style="width:120px;height:90px;object-fit:cover;">
            </div>
        @endif
    </div>

    {{-- READING TIME --}}
    <div class="col-md-2 mb-3">
        <label class="form-label fw-bold">Lecture (min)</label>
        <input type="number" name="reading_time" min="1" max="60"
            class="form-control @error('reading_time') is-invalid @enderror"
            value="{{ old('reading_time', $post->reading_time ?? 3) }}">
        @error('reading_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- STATUS --}}
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Statut <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="draft" {{ old('status', $post->status ?? '') === 'draft' ? 'selected' : '' }}>Brouillon</option>
            <option value="published" {{ old('status', $post->status ?? '') === 'published' ? 'selected' : '' }}>Publié</option>
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
        <textarea name="content_fr" id="editor-fr" class="form-control @error('content_fr') is-invalid @enderror" rows="10">{{ old('content_fr', $post->content_fr ?? '') }}</textarea>
        @error('content_fr') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- CONTENT EN --}}
    <div class="col-12 mb-3">
        <label class="form-label fw-bold">Contenu (EN)</label>
        <textarea name="content_en" id="editor-en" class="form-control @error('content_en') is-invalid @enderror" rows="10">{{ old('content_en', $post->content_en ?? '') }}</textarea>
        @error('content_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

</div>
