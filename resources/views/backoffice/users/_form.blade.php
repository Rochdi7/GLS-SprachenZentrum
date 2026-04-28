<div class="row">

    {{-- ─── Compte ─────────────────────────────────────────────── --}}
    <div class="col-12 mb-2">
        <h6 class="text-uppercase text-muted small fw-semibold mb-0">
            <i class="ph-duotone ph-user me-1"></i> Compte
        </h6>
        <hr class="mt-1 mb-3">
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
                <option value="{{ $role->name }}"
                    {{ old('role', isset($user) ? $user->roles->first()?->name : '') === $role->name ? 'selected' : '' }}>
                    {{ $role->name }}
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

    {{-- PRIMARY SITE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Centre principal</label>
        <select name="site_id" class="form-select" id="user_primary_site">
            <option value="">— Aucun —</option>
            @foreach($sites as $site)
                <option value="{{ $site->id }}"
                    {{ (string) old('site_id', $user->site_id ?? '') === (string) $site->id ? 'selected' : '' }}>
                    {{ $site->name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Centre par défaut affiché dans les filtres et exports.</small>
    </div>

    {{-- ADDITIONAL CENTRES (multi) --}}
    @php
        $assignedSiteIds = collect(old('site_ids', isset($user) ? $user->sites->pluck('id')->all() : []))
            ->map(fn ($v) => (int) $v)
            ->all();
    @endphp
    <div class="col-md-8 mb-3">
        <label class="form-label fw-bold">Centres affectés (accès multi-centres)</label>
        <select name="site_ids[]" class="form-select" id="user_sites_multi" multiple size="6">
            @foreach($sites as $site)
                <option value="{{ $site->id }}"
                    {{ in_array((int) $site->id, $assignedSiteIds, true) ? 'selected' : '' }}>
                    {{ $site->name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">
            Maintenez <kbd>Ctrl</kbd> (ou <kbd>⌘</kbd>) pour sélectionner plusieurs centres.
            L'utilisateur verra les données de chacun des centres cochés.
        </small>
    </div>

    <script>
        // Keep the primary site inside the multi list automatically.
        (function () {
            var primary = document.getElementById('user_primary_site');
            var multi   = document.getElementById('user_sites_multi');
            if (!primary || !multi) return;

            primary.addEventListener('change', function () {
                if (!primary.value) return;
                var found = false;
                for (var i = 0; i < multi.options.length; i++) {
                    if (multi.options[i].value === primary.value) {
                        multi.options[i].selected = true;
                        found = true;
                    }
                }
                // (No-op when not found — multi is filled from the same $sites list.)
            });
        })();
    </script>

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
