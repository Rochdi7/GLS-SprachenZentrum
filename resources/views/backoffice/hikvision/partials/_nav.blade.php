<div class="card mb-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('backoffice.hikvision.dashboard') }}"
                class="btn btn-sm {{ request()->routeIs('backoffice.hikvision.dashboard') ? 'btn-primary' : 'btn-light-primary' }}">
                <i class="ph-duotone ph-gauge me-1"></i> Tableau de bord
            </a>
            <a href="{{ route('backoffice.hikvision.devices.index') }}"
                class="btn btn-sm {{ request()->routeIs('backoffice.hikvision.devices.*') ? 'btn-primary' : 'btn-light-primary' }}">
                <i class="ph-duotone ph-devices me-1"></i> Appareils
            </a>
            <a href="{{ route('backoffice.hikvision.persons.index') }}"
                class="btn btn-sm {{ request()->routeIs('backoffice.hikvision.persons.*') ? 'btn-primary' : 'btn-light-primary' }}">
                <i class="ph-duotone ph-users-three me-1"></i> Employes / Personnes
            </a>
            <a href="{{ route('backoffice.hikvision.attendance.index') }}"
                class="btn btn-sm {{ request()->routeIs('backoffice.hikvision.attendance.*') ? 'btn-primary' : 'btn-light-primary' }}">
                <i class="ph-duotone ph-calendar-check me-1"></i> Pointages / Presence
            </a>
            <a href="{{ route('backoffice.hikvision.alarms.index') }}"
                class="btn btn-sm {{ request()->routeIs('backoffice.hikvision.alarms.*') ? 'btn-primary' : 'btn-light-primary' }}">
                <i class="ph-duotone ph-warning-circle me-1"></i> Alarmes
            </a>
            <a href="{{ route('backoffice.hikvision.webhooks.index') }}"
                class="btn btn-sm {{ request()->routeIs('backoffice.hikvision.webhooks.*') ? 'btn-primary' : 'btn-light-primary' }}">
                <i class="ph-duotone ph-plugs-connected me-1"></i> Webhooks
            </a>
            <a href="{{ route('backoffice.hikvision.settings.index') }}"
                class="btn btn-sm {{ request()->routeIs('backoffice.hikvision.settings.*') ? 'btn-primary' : 'btn-light-primary' }}">
                <i class="ph-duotone ph-sliders-horizontal me-1"></i> Parametres API
            </a>
            <a href="{{ route('backoffice.hikvision.logs.index') }}"
                class="btn btn-sm {{ request()->routeIs('backoffice.hikvision.logs.*') ? 'btn-primary' : 'btn-light-primary' }}">
                <i class="ph-duotone ph-file-text me-1"></i> Logs de synchronisation
            </a>
        </div>
    </div>
</div>
