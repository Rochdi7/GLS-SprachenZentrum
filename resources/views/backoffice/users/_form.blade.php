<div class="row">

    {{-- ─── Compte ─────────────────────────────────────────────── --}}
    <div class="col-12 mb-2">
        <h6 class="text-uppercase text-muted small fw-semibold mb-0">
            <i class="ph-duotone ph-user me-1"></i> Compte
        </h6>
        <hr class="mt-1 mb-3">
    </div>

    {{-- AVATAR --}}
    <div class="col-12 mb-4">
        <label class="form-label fw-bold">Photo de profil</label>
        <div class="d-flex align-items-center gap-3">
            <img id="avatar-preview"
                 src="{{ isset($user) ? $user->avatarUrl() : asset('assets/images/user/avatar-2.avif') }}"
                 alt="Avatar"
                 class="rounded-circle border"
                 style="width:72px;height:72px;object-fit:cover;">
            <div>
                <input type="file" name="avatar" id="avatar-input"
                       class="form-control form-control-sm"
                       accept="image/jpeg,image/png,image/webp,image/gif"
                       style="max-width:280px;">
                <small class="text-muted d-block mt-1">JPG, PNG, WEBP — max 2 Mo</small>
                @if(isset($user) && $user->getFirstMedia('profile_photo'))
                    <div class="form-check mt-1">
                        <input type="checkbox" name="remove_avatar" value="1"
                               class="form-check-input" id="remove_avatar">
                        <label class="form-check-label text-danger small" for="remove_avatar">
                            Supprimer la photo actuelle
                        </label>
                    </div>
                @endif
            </div>
        </div>
        <script>
            document.getElementById('avatar-input').addEventListener('change', function () {
                var file = this.files[0];
                if (!file) return;
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        </script>
    </div>

    {{-- NAME --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
        <input type="text" name="name"
               class="form-control"
               value="{{ old('name', $user->name ?? '') }}"
               placeholder="Nom de l'utilisateur"
               required>
    </div>

    {{-- EMAIL --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
        {{-- type="text" + pattern: tolerate non-ASCII local parts (e.g. réception@gls.ma) --}}
        <input type="text" name="email"
               class="form-control"
               value="{{ old('email', $user->email ?? '') }}"
               placeholder="email@exemple.com"
               pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$"
               title="Adresse email valide (les caractères accentués sont autorisés)"
               inputmode="email"
               autocomplete="email"
               required>
    </div>

    {{-- PASSWORD --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">
            Mot de passe
            @if(isset($user))
                <small class="text-muted">(laisser vide pour ne pas changer)</small>
            @endif
        </label>
        <input type="password" name="password"
               class="form-control"
               placeholder="Mot de passe"
               {{ isset($user) ? '' : 'required' }}>
    </div>

    {{-- PASSWORD CONFIRMATION --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation"
               class="form-control"
               placeholder="Confirmer le mot de passe"
               {{ isset($user) ? '' : 'required' }}>
    </div>

    {{-- ROLE (application role) --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Rôle application <span class="text-danger">*</span></label>
        <select name="role" class="form-select" required>
            <option value="">-- Sélectionner un rôle --</option>
            @foreach($roles as $role)
                @php
                    $roleLabel = in_array($role->name, ['Reception', 'Réception', 'RÃ©ception'], true)
                        ? 'Conseiller Administration'
                        : $role->name;
                @endphp
                <option value="{{ $role->name }}"
                    {{ old('role', isset($user) ? $user->roles->first()?->name : '') === $role->name ? 'selected' : '' }}>
                    {{ $roleLabel }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Détermine les permissions dans le backoffice.</small>
    </div>

    {{-- ACTIVE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold d-block">Statut</label>
        <div class="form-check form-switch mt-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active_switch"
                {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active_switch">Compte actif</label>
        </div>
    </div>

    {{-- ─── Fiche employé ──────────────────────────────────────── --}}
    <div class="col-12 mb-2 mt-3">
        <h6 class="text-uppercase text-muted small fw-semibold mb-0">
            <i class="ph-duotone ph-identification-badge me-1"></i> Fiche employé
        </h6>
        <hr class="mt-1 mb-3">
        <p class="text-muted small mb-0">
            Renseignez ces informations si l'utilisateur travaille dans un centre GLS.
            Elles alimentent le planning, les primes et les exports RH.
        </p>
    </div>

    {{-- CENTRES AFFECTÉS --}}
    @php
        $assignedSiteIds = collect(old('site_ids', isset($user) ? $user->sites->pluck('id')->all() : []))
            ->map(fn ($v) => (int) $v)
            ->all();
    @endphp
    <div class="col-12 mb-3">
        <label class="form-label fw-bold">Centres affectés (accès multi-centres)</label>
        <select name="site_ids[]" class="form-select" multiple size="6">
            @foreach($sites as $site)
                <option value="{{ $site->id }}"
                    {{ in_array((int) $site->id, $assignedSiteIds, true) ? 'selected' : '' }}>
                    {{ $site->name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">
            Maintenez <kbd>Ctrl</kbd> (ou <kbd>⌘</kbd>) pour sélectionner plusieurs centres.
            Le premier centre sélectionné devient le centre principal.
        </small>
    </div>

    {{-- STAFF ROLE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Poste</label>
        <select name="staff_role" class="form-select">
            <option value="">— Non défini —</option>
            @foreach($staffRoles as $r)
                <option value="{{ $r }}"
                    {{ old('staff_role', $user->staff_role ?? '') === $r ? 'selected' : '' }}>
                    {{ $r }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- HIRED AT --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Date d'embauche</label>
        <input type="date" name="hired_at" class="form-control"
               value="{{ old('hired_at', isset($user) && $user->hired_at ? $user->hired_at->format('Y-m-d') : '') }}">
    </div>

    {{-- PHONE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Téléphone</label>
        <input type="text" name="phone" class="form-control"
               value="{{ old('phone', $user->phone ?? '') }}"
               placeholder="+212 6…">
    </div>

    {{-- NOTES --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Notes internes</label>
        <input type="text" name="staff_notes" class="form-control"
               value="{{ old('staff_notes', $user->staff_notes ?? '') }}"
               maxlength="2000"
               placeholder="Remarques RH (optionnel)">
    </div>

</div>
