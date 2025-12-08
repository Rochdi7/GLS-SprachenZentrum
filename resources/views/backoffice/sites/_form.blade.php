<div class="row">

    {{-- NOM DU SITE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom du site</label>
        <input type="text" name="name"
               class="form-control"
               value="{{ old('name', $site->name ?? '') }}"
               placeholder="Ex: GLS Rabat"
               required>
    </div>

    {{-- VILLE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Ville</label>
        <input type="text" name="city"
               class="form-control"
               value="{{ old('city', $site->city ?? '') }}"
               placeholder="Ex: Rabat"
               required>
    </div>

    {{-- ADRESSE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Adresse</label>
        <input type="text" name="address"
               class="form-control"
               value="{{ old('address', $site->address ?? '') }}"
               placeholder="Adresse complète">
    </div>

    {{-- TÉLÉPHONE --}}
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Téléphone</label>
        <input type="text" name="phone"
               class="form-control"
               value="{{ old('phone', $site->phone ?? '') }}"
               placeholder="Ex: +212 6 00 00 00 00">
    </div>

    {{-- EMAIL --}}
    <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Email</label>
        <input type="email" name="email"
               class="form-control"
               value="{{ old('email', $site->email ?? '') }}"
               placeholder="Ex: info@gls.ma">
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
