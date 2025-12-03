<div class="row">

    <!-- NAME FR -->
    <div class="mb-3 col-md-6">
        <label class="form-label fw-bold">Nom catégorie (Français)</label>

        <input 
            type="text" 
            name="name_fr"
            class="form-control @error('name_fr') is-invalid @enderror"
            value="{{ old('name_fr', $category->name_fr ?? '') }}"
            required
        >

        @error('name_fr')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- NAME EN -->
    <div class="mb-3 col-md-6">
        <label class="form-label fw-bold">Category Name (English)</label>

        <input 
            type="text" 
            name="name_en"
            class="form-control @error('name_en') is-invalid @enderror"
            value="{{ old('name_en', $category->name_en ?? '') }}"
            required
        >

        @error('name_en')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Is Active -->
    <div class="mb-3 col-md-3">
        <label class="form-label fw-bold">Active ?</label>
        <select class="form-select" name="is_active">
            <option value="1" {{ old('is_active', $category->is_active ?? 1) == 1 ? 'selected' : '' }}>Oui</option>
            <option value="0" {{ old('is_active', $category->is_active ?? 1) == 0 ? 'selected' : '' }}>Non</option>
        </select>
    </div>

   

</div>
