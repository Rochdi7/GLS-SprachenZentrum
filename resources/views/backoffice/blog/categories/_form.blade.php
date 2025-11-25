<div class="row">

    <!-- NAME -->
    <div class="mb-3 col-md-6">
        <label for="name" class="form-label">Nom de la catégorie</label>

        <input 
            type="text" 
            name="name" 
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $category->name ?? '') }}"
            required
        >

        <div class="invalid-feedback">
            @error('name')
                {{ $message }}
            @else
                Veuillez entrer le nom de la catégorie.
            @enderror
        </div>
    </div>

</div>
