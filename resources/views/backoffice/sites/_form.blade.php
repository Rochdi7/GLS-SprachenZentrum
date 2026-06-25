@php $site = $site ?? null; @endphp

<div class="row">

    {{-- NOM DU SITE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom du site <span class="text-danger">*</span></label>
        <input type="text" name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $site->name ?? '') }}"
               placeholder="Ex: GLS Rabat"
               required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- VILLE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Ville <span class="text-danger">*</span></label>
        <input type="text" name="city"
               class="form-control @error('city') is-invalid @enderror"
               value="{{ old('city', $site->city ?? '') }}"
               placeholder="Ex: Rabat"
               required>
        @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- ADRESSE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Adresse</label>
        <input type="text" name="address"
               class="form-control @error('address') is-invalid @enderror"
               value="{{ old('address', $site->address ?? '') }}"
               placeholder="Adresse complète">
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- TÉLÉPHONE --}}
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Téléphone</label>
        <input type="text" name="phone"
               class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $site->phone ?? '') }}"
               placeholder="Ex: +212 6 00 00 00 00">
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- EMAIL --}}
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Email</label>
        <input type="email" name="email"
               class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $site->email ?? '') }}"
               placeholder="Ex: info@gls.ma">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- STATUS --}}
    <hr class="my-4">

    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Statut du site</label>
        <select name="is_active" class="form-select">
            <option value="1" {{ old('is_active', $site->is_active ?? 1) == 1 ? 'selected' : '' }}>Actif</option>
            <option value="0" {{ old('is_active', $site->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactif</option>
        </select>
    </div>

</div>
