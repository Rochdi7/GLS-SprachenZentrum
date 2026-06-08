<div class="row">

    {{-- ROLE NAME --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom du rôle</label>
        @if(isset($role) && $role->name === 'Super Admin')
            <input type="text" class="form-control" value="Super Admin" disabled>
            <input type="hidden" name="name" value="Super Admin">
        @else
            <input type="text" name="name"
                   class="form-control"
                   value="{{ old('name', $role->name ?? '') }}"
                   placeholder="Nom du rôle"
                   required>
        @endif
    </div>

    <div class="col-12 mb-3">
        <hr>
        <h6 class="fw-bold mb-3">Matrice des permissions</h6>

        <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Tout cocher</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">Tout décocher</button>
        </div>

        @php
        /*
        |----------------------------------------------------------------------
        | Menu-mirrored permission groups
        | Each group = one sidebar section. Modules listed in menu order.
        |----------------------------------------------------------------------
        */
        $permGroups = [
            [
                'label' => 'Dashboard',
                'icon'  => 'ph-duotone ph-gauge',
                'color' => 'primary',
                'modules' => ['dashboard'],
            ],
            [
                'label' => 'Pilotage',
                'icon'  => 'ph-duotone ph-chart-line-up',
                'color' => 'success',
                'modules' => ['level_followups', 'weekly_reports'],
            ],
            [
                'label' => 'Gestion École',
                'icon'  => 'ph-duotone ph-buildings',
                'color' => 'info',
                'modules' => ['sites', 'teachers', 'groups', 'certificates', 'attestations', 'attestation_requests', 'feedbacks', 'translations', 'studienkollegs', 'quizzes'],
            ],
            [
                'label' => 'Admissions & Leads',
                'icon'  => 'ph-duotone ph-address-book',
                'color' => 'warning',
                'modules' => ['applications', 'leads', 'lead_stats', 'newsletter_subscribers'],
            ],
            [
                'label' => 'RH / Planning',
                'icon'  => 'ph-duotone ph-calendar-blank',
                'color' => 'secondary',
                'modules' => ['schedules'],
            ],
            [
                'label' => 'Contenu',
                'icon'  => 'ph-duotone ph-newspaper',
                'color' => 'danger',
                'modules' => ['blog_categories', 'blog_posts'],
            ],
            [
                'label' => 'Administration',
                'icon'  => 'ph-duotone ph-user-gear',
                'color' => 'dark',
                'modules' => ['users', 'roles'],
            ],
            [
                'label' => 'CRM (API)',
                'icon'  => 'ph-duotone ph-database',
                'color' => 'primary',
                'modules' => ['crm', 'crm_prof_payment'],
            ],
        ];

        $moduleLabels = [
            'dashboard'              => 'Dashboard',
            'sites'                  => 'Centres GLS',
            'teachers'               => 'Enseignants',
            'groups'                 => 'Groupes',
            'certificates'           => 'Certificats',
            'attestations'           => 'Attestations',
            'attestation_requests'   => 'Demandes d\'attestation',
            'feedbacks'              => 'Avis & Feedbacks',
            'translations'           => 'Traductions',
            'studienkollegs'         => 'Studienkollegs',
            'quizzes'                => 'Quizzes (QCM)',
            'blog_categories'        => 'Catégories Blog',
            'blog_posts'             => 'Articles Blog',
            'leads'                  => 'Leads',
            'lead_stats'             => 'Statistiques Leads',
            'applications'           => 'Applications',
            'newsletter_subscribers' => 'Newsletter',
            'users'                  => 'Utilisateurs',
            'roles'                  => 'Rôles & Permissions',
            'level_followups'        => 'Suivi Niveau',
            'weekly_reports'         => 'Rapport Semaine',
            'schedules'              => 'Planning',
            'crm'                    => 'CRM — Accès général',
            'crm_prof_payment'       => 'Paiement Professeurs',
        ];

        $actions      = ['view', 'create', 'edit', 'delete'];
        $actionLabels = ['view' => 'Voir', 'create' => 'Créer', 'edit' => 'Modifier', 'delete' => 'Supprimer'];
        $actionIcons  = ['view' => 'ph-eye', 'create' => 'ph-plus-circle', 'edit' => 'ph-pencil', 'delete' => 'ph-trash'];
        $existingPermissions = $rolePermissions ?? [];

        // Build a flat lookup: module => [action => permName]
        $permLookup = [];
        foreach ($groupedPermissions as $module => $perms) {
            foreach ($perms as $p) {
                $permLookup[$module][$p['action']] = $p['name'];
            }
        }

        $colorMap = [
            'primary'   => ['bg' => '#4680ff', 'light' => '#e8edff'],
            'success'   => ['bg' => '#1cc88a', 'light' => '#e3faf1'],
            'info'      => ['bg' => '#0dcaf0', 'light' => '#e0f9ff'],
            'warning'   => ['bg' => '#ffc107', 'light' => '#fff8e1'],
            'danger'    => ['bg' => '#dc3545', 'light' => '#fdecea'],
            'secondary' => ['bg' => '#6c757d', 'light' => '#f0f0f0'],
            'dark'      => ['bg' => '#343a40', 'light' => '#e9ecef'],
        ];
        @endphp

        <div class="accordion" id="permAccordion">
            @foreach($permGroups as $gIdx => $group)
            @php
                // Check if this group has any known permissions in DB
                $groupModules = collect($group['modules'])->filter(fn($m) => isset($permLookup[$m]))->values();
                if ($groupModules->isEmpty()) continue;

                // Check if any perm in this group is currently granted (to auto-open)
                $groupChecked = $groupModules->contains(function($m) use ($permLookup, $existingPermissions) {
                    foreach ($permLookup[$m] as $action => $permName) {
                        if (in_array($permName, old('permissions', $existingPermissions))) return true;
                    }
                    return false;
                });

                $color = $colorMap[$group['color']] ?? $colorMap['primary'];
                $collapseId = 'perm-group-' . $gIdx;
            @endphp
            <div class="accordion-item border mb-2 rounded overflow-hidden" style="border-color: {{ $color['bg'] }}20 !important;">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $groupChecked ? '' : 'collapsed' }} fw-semibold py-3"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $collapseId }}"
                            aria-expanded="{{ $groupChecked ? 'true' : 'false' }}"
                            style="background: {{ $color['light'] }}; color: {{ $color['bg'] }};">
                        <i class="{{ $group['icon'] }} me-2" style="font-size:1.1rem"></i>
                        {{ $group['label'] }}
                        <span class="ms-2 badge rounded-pill"
                              style="background:{{ $color['bg'] }}; color:#fff; font-size:.7rem"
                              data-group-counter="{{ $collapseId }}">
                            {{ $groupModules->sum(fn($m) => collect($permLookup[$m] ?? [])->keys()->filter(fn($a) => in_array("{$m}.{$a}", old('permissions', $existingPermissions)))->count()) }}
                            /
                            {{ $groupModules->sum(fn($m) => count($permLookup[$m] ?? [])) }}
                        </span>
                    </button>
                </h2>
                <div id="{{ $collapseId }}"
                     class="accordion-collapse collapse {{ $groupChecked ? 'show' : '' }}"
                     data-bs-parent="">
                    <div class="accordion-body p-0">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead style="background:{{ $color['light'] }};">
                                <tr>
                                    <th style="min-width:200px; padding-left:1.25rem;">Module</th>
                                    @foreach($actions as $action)
                                    <th class="text-center" style="width:110px;">
                                        <i class="ph-duotone {{ $actionIcons[$action] }} me-1"></i>
                                        {{ $actionLabels[$action] }}
                                    </th>
                                    @endforeach
                                    <th class="text-center" style="width:90px;">
                                        <small class="text-muted">Tout</small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupModules as $module)
                                @php
                                    $modulePerms = $permLookup[$module] ?? [];
                                    $allPermNames = array_values($modulePerms);
                                    $checkedCount = collect($allPermNames)->filter(fn($p) => in_array($p, old('permissions', $existingPermissions)))->count();
                                    $allChecked = $checkedCount === count($allPermNames) && count($allPermNames) > 0;
                                @endphp
                                <tr>
                                    <td class="fw-medium ps-4" style="border-left: 3px solid {{ $color['bg'] }};">
                                        {{ $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module)) }}
                                    </td>
                                    @foreach($actions as $action)
                                    <td class="text-center">
                                        @if(isset($modulePerms[$action]))
                                            @php $permName = $modulePerms[$action]; @endphp
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input permission-checkbox perm-group-{{ $collapseId }}"
                                                       type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $permName }}"
                                                       id="perm_{{ $module }}_{{ $action }}"
                                                       data-group="{{ $collapseId }}"
                                                       {{ in_array($permName, old('permissions', $existingPermissions)) ? 'checked' : '' }}>
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    @endforeach
                                    {{-- Row "select all" toggle --}}
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input row-toggle"
                                                   type="checkbox"
                                                   title="Tout cocher / décocher"
                                                   {{ $allChecked ? 'checked' : '' }}
                                                   data-row="{{ $module }}"
                                                   data-group="{{ $collapseId }}">
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Global select / deselect all
    document.getElementById('selectAll')?.addEventListener('click', function () {
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
        document.querySelectorAll('.row-toggle').forEach(cb => cb.checked = true);
        updateAllCounters();
    });
    document.getElementById('deselectAll')?.addEventListener('click', function () {
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.row-toggle').forEach(cb => cb.checked = false);
        updateAllCounters();
    });

    // Row "Tout" toggle: check/uncheck all actions in a module row
    document.querySelectorAll('.row-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const module  = this.dataset.row;
            const groupId = this.dataset.group;
            document.querySelectorAll(`.permission-checkbox[value^="${module}."]`).forEach(cb => {
                cb.checked = toggle.checked;
            });
            updateCounter(groupId);
        });
    });

    // Per-checkbox change: sync row toggle + group counter
    document.querySelectorAll('.permission-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const groupId = this.dataset.group;
            // Sync row toggle for this module
            const module = this.value.split('.')[0];
            const rowCbs = document.querySelectorAll(`.permission-checkbox[value^="${module}."]`);
            const rowToggle = document.querySelector(`.row-toggle[data-row="${module}"]`);
            if (rowToggle) {
                rowToggle.checked = Array.from(rowCbs).every(c => c.checked);
            }
            updateCounter(groupId);
        });
    });

    function updateCounter(groupId) {
        const total   = document.querySelectorAll(`.perm-group-${groupId}`).length;
        const checked = document.querySelectorAll(`.perm-group-${groupId}:checked`).length;
        const badge   = document.querySelector(`[data-group-counter="${groupId}"]`);
        if (badge) badge.textContent = `${checked} / ${total}`;
    }

    function updateAllCounters() {
        document.querySelectorAll('[data-group-counter]').forEach(badge => {
            const groupId = badge.dataset.groupCounter;
            const total   = document.querySelectorAll(`.perm-group-${groupId}`).length;
            const checked = document.querySelectorAll(`.perm-group-${groupId}:checked`).length;
            badge.textContent = `${checked} / ${total}`;
        });
    }
});
</script>
