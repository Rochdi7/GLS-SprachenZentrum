@php
    $dashboardActive = request()->routeIs('dashboard');
    $pilotageOpen =
        $dashboardActive ||
        request()->routeIs('backoffice.level_followups.*') ||
        request()->routeIs('backoffice.weekly_reports.*');
    $schoolOpen =
        request()->routeIs('backoffice.sites.*') ||
        request()->routeIs('backoffice.teachers.*') ||
        request()->routeIs('backoffice.groups.*') ||
        request()->routeIs('backoffice.certificates.*') ||
        request()->routeIs('backoffice.attestations.*') ||
        request()->routeIs('backoffice.attestation_requests.*') ||
        request()->routeIs('backoffice.feedbacks.*') ||
        request()->routeIs('backoffice.translations.*') ||
        request()->routeIs('backoffice.studienkollegs.*') ||
        request()->routeIs('backoffice.quizzes.*');
    $admissionsOpen =
        request()->routeIs('backoffice.applications.*') ||
        request()->routeIs('backoffice.leads.*') ||
        request()->routeIs('backoffice.newsletter_subscribers.*');
    $payrollOpen = request()->routeIs('backoffice.payroll.*') && !request()->routeIs('backoffice.payroll.presence.*');
    $presenceOpen = request()->routeIs('backoffice.payroll.presence.*');
    $contentOpen = request()->routeIs('backoffice.blog.*');
    $encaissementOpen = request()->routeIs('backoffice.encaissements.*');
    $rhOpen = request()->routeIs('backoffice.schedules.*') || request()->routeIs('backoffice.planning.*');
    $adminOpen = request()->routeIs('backoffice.users.*') || request()->routeIs('backoffice.roles.*');
    $crmOpen = request()->routeIs('backoffice.crm.*');
    $whatsappOpen = request()->routeIs('backoffice.whatsapp_campaigns.*');

    // New variables for CRM split
    $crmDataOpen =
        request()->routeIs('backoffice.crm.students') ||
        request()->routeIs('backoffice.crm.session-presence') ||
        request()->routeIs('backoffice.crm.registrations') ||
        request()->routeIs('backoffice.crm.payments') ||
        request()->routeIs('backoffice.crm.payment-checks') ||
        request()->routeIs('backoffice.crm.payment-allocations') ||
        request()->routeIs('backoffice.crm.payment-collection') ||
        request()->routeIs('backoffice.crm.groups.classes') ||
        request()->routeIs('backoffice.crm.groups.level-sessions') ||
        request()->routeIs('backoffice.crm.subscription-services') ||
        request()->routeIs('backoffice.crm.employee-salaries') ||
        request()->routeIs('backoffice.crm.lov');
    $crmStatsOpen =
        request()->routeIs('backoffice.crm.group-evolution') ||
        request()->routeIs('backoffice.crm.index') ||
        request()->routeIs('backoffice.crm.stats') ||
        request()->routeIs('backoffice.crm.duplicates') ||
        request()->routeIs('backoffice.crm.insights.*');
    $crmCollectionsOpen = request()->routeIs('backoffice.crm.collections.*');
    $crmStatsDashOpen    = request()->routeIs('backoffice.crm.statistiques');
    $crmPresenceSuiviOpen = request()->routeIs('backoffice.crm.presence-suivi');
@endphp

<li class="pc-item pc-caption">
    <label>GLS Portal</label>
    <i class="ph-duotone ph-squares-four"></i>
</li>

@can('dashboard.view')
    <li class="pc-item {{ $dashboardActive ? 'active' : '' }}">
        <a href="{{ route('dashboard') }}" class="pc-link {{ $dashboardActive ? 'active' : '' }}">
            <span class="pc-micon"><i class="ph-duotone ph-gauge"></i></span>
            <span class="pc-mtext">Dashboard</span>
        </a>
    </li>
@endcan

@canany(['level_followups.view', 'weekly_reports.view'])
    <li class="pc-item pc-hasmenu {{ $pilotageOpen ? 'pc-trigger' : '' }}">
        <a href="#!" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-chart-line-up"></i></span>
            <span class="pc-mtext">Pilotage</span>
            <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
        </a>
        <ul class="pc-submenu">
            @can('level_followups.view')
                <li class="pc-item {{ request()->routeIs('backoffice.level_followups.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.level_followups.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.level_followups.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Suivi niveau</span>
                    </a>
                </li>
            @endcan
            @can('weekly_reports.view')
                <li class="pc-item {{ request()->routeIs('backoffice.weekly_reports.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.weekly_reports.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.weekly_reports.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Rapport semaine</span>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany

@canany(['sites.view', 'teachers.view', 'groups.view', 'certificates.view', 'attestations.view',
    'attestation_requests.view', 'feedbacks.view', 'translations.view', 'studienkollegs.view', 'quizzes.view'])
    <li class="pc-item pc-hasmenu {{ $schoolOpen ? 'pc-trigger' : '' }}">
        <a href="#!" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-buildings"></i></span>
            <span class="pc-mtext">Gestion ecole</span>
            <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
        </a>
        <ul class="pc-submenu">
            @can('sites.view')
                <li class="pc-item {{ request()->routeIs('backoffice.sites.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.sites.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.sites.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Centres GLS</span>
                    </a>
                </li>
            @endcan
            @can('teachers.view')
                <li class="pc-item {{ request()->routeIs('backoffice.teachers.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.teachers.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.teachers.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Enseignants</span>
                    </a>
                </li>
            @endcan
            @can('groups.view')
                <li class="pc-item {{ request()->routeIs('backoffice.groups.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.groups.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.groups.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Groupes</span>
                    </a>
                </li>
            @endcan
            @can('certificates.view')
                <li class="pc-item {{ request()->routeIs('backoffice.certificates.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.certificates.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.certificates.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Certificats</span>
                    </a>
                </li>
            @endcan
            @can('attestations.view')
                <li class="pc-item {{ request()->routeIs('backoffice.attestations.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.attestations.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.attestations.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Attestations</span>
                    </a>
                </li>
            @endcan
            @can('attestation_requests.view')
                <li class="pc-item {{ request()->routeIs('backoffice.attestation_requests.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.attestation_requests.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.attestation_requests.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Demandes d'attestation</span>
                    </a>
                </li>
            @endcan
            {{-- TEMP: feedbacks beta — visible only to whitelisted testers. Remove this block when opening to all. --}}
            @if (auth()->check() &&
                    in_array(auth()->user()->email, ['ichrak.fakroune@glszentrum.com', 'rochdi.karouali@glszentrum.com'], true))
                @can('feedbacks.view')
                    <li class="pc-item {{ request()->routeIs('backoffice.feedbacks.*') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.feedbacks.index') }}"
                            class="pc-link {{ request()->routeIs('backoffice.feedbacks.*') ? 'active' : '' }}">
                            <span class="pc-mtext">Avis & Feedbacks</span>
                        </a>
                    </li>
                @endcan
            @endif

            @can('translations.view')
                <li class="pc-item {{ request()->routeIs('backoffice.translations.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.translations.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.translations.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Traductions</span>
                    </a>
                </li>
            @endcan
            @hasanyrole('Super Admin|Admin')
                @can('studienkollegs.view')
                    <li class="pc-item {{ request()->routeIs('backoffice.studienkollegs.*') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.studienkollegs.index') }}"
                            class="pc-link {{ request()->routeIs('backoffice.studienkollegs.*') ? 'active' : '' }}">
                            <span class="pc-mtext">Studienkollegs</span>
                        </a>
                    </li>
                @endcan
                @can('quizzes.view')
                    <li class="pc-item {{ request()->routeIs('backoffice.quizzes.*') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.quizzes.index') }}"
                            class="pc-link {{ request()->routeIs('backoffice.quizzes.*') ? 'active' : '' }}">
                            <span class="pc-mtext">Quizzes (QCM)</span>
                        </a>
                    </li>
                @endcan
            @endhasanyrole
        </ul>
    </li>
@endcanany

@hasanyrole('Super Admin|Admin')
    @canany(['applications.view', 'leads.view', 'lead_stats.view', 'newsletter_subscribers.view'])
        <li class="pc-item pc-hasmenu {{ $admissionsOpen ? 'pc-trigger' : '' }}">
            <a href="#!" class="pc-link">
                <span class="pc-micon"><i class="ph-duotone ph-address-book"></i></span>
                <span class="pc-mtext">Admissions & leads</span>
                <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
            </a>
            <ul class="pc-submenu">
                @can('applications.view')
                    <li class="pc-item {{ request()->routeIs('backoffice.applications.*') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.applications.index') }}"
                            class="pc-link {{ request()->routeIs('backoffice.applications.*') ? 'active' : '' }}">
                            <span class="pc-mtext">Applications</span>
                        </a>
                    </li>
                @endcan
                @can('leads.view')
                    <li class="pc-item {{ request()->routeIs('backoffice.leads.index') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.leads.index') }}"
                            class="pc-link {{ request()->routeIs('backoffice.leads.index') ? 'active' : '' }}">
                            <span class="pc-mtext">Leads</span>
                        </a>
                    </li>
                @endcan
                @can('lead_stats.view')
                    <li class="pc-item {{ request()->routeIs('backoffice.leads.stats') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.leads.stats') }}"
                            class="pc-link {{ request()->routeIs('backoffice.leads.stats') ? 'active' : '' }}">
                            <span class="pc-mtext">Statistiques Leads</span>
                        </a>
                    </li>
                @endcan
                @can('newsletter_subscribers.view')
                    <li class="pc-item {{ request()->routeIs('backoffice.newsletter_subscribers.*') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.newsletter_subscribers.index') }}"
                            class="pc-link {{ request()->routeIs('backoffice.newsletter_subscribers.*') ? 'active' : '' }}">
                            <span class="pc-mtext">Newsletter</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany
@endhasanyrole

{{-- Hidden: not ready for production
@can('payroll.view')
<li class="pc-item pc-hasmenu {{ $payrollOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-wallet"></i></span>
        <span class="pc-mtext">Suivi Paiement</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item {{ request()->routeIs('backoffice.payroll.dashboard') ? 'active' : '' }}">
            <a href="{{ route('backoffice.payroll.dashboard') }}" class="pc-link {{ request()->routeIs('backoffice.payroll.dashboard') ? 'active' : '' }}">
                <span class="pc-mtext">Tableau de bord</span>
            </a>
        </li>
        @can('payroll.create')
        <li class="pc-item {{ request()->routeIs('backoffice.payroll.import.create') ? 'active' : '' }}">
            <a href="{{ route('backoffice.payroll.import.create') }}" class="pc-link {{ request()->routeIs('backoffice.payroll.import.create') ? 'active' : '' }}">
                <span class="pc-mtext">Importer CRM</span>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcan
--}}

{{-- @can('presence.view')
    <li class="pc-item pc-hasmenu {{ $presenceOpen ? 'pc-trigger' : '' }}">
        <a href="#!" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-chalkboard-teacher"></i></span>
            <span class="pc-mtext">Paiement Professeurs</span>
            <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
        </a>
        <ul class="pc-submenu">
            <li class="pc-item {{ request()->routeIs('backoffice.payroll.presence.dashboard') ? 'active' : '' }}">
                <a href="{{ route('backoffice.payroll.presence.dashboard') }}"
                    class="pc-link {{ request()->routeIs('backoffice.payroll.presence.dashboard') ? 'active' : '' }}">
                    <span class="pc-mtext">Tableau de bord</span>
                </a>
            </li>
        </ul>
    </li>
@endcan --}}

@if (false)
    {{-- Hidden: not ready for production --}}
    @can('encaissements.view')
        <li class="pc-item pc-hasmenu {{ $encaissementOpen ? 'pc-trigger' : '' }}">
            <a href="#!" class="pc-link">
                <span class="pc-micon"><i class="ph-duotone ph-money"></i></span>
                <span class="pc-mtext">Encaissements</span>
                <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
            </a>
            <ul class="pc-submenu">
                <li class="pc-item {{ request()->routeIs('backoffice.encaissements.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.encaissements.dashboard') }}"
                        class="pc-link {{ request()->routeIs('backoffice.encaissements.dashboard') ? 'active' : '' }}">
                        <span class="pc-mtext">Tableau de bord</span>
                    </a>
                </li>
                <li
                    class="pc-item {{ request()->routeIs('backoffice.encaissements.index') || request()->routeIs('backoffice.encaissements.show') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.encaissements.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.encaissements.index') ? 'active' : '' }}">
                        <span class="pc-mtext">Liste encaissements</span>
                    </a>
                </li>
                @can('encaissements.create')
                    <li class="pc-item {{ request()->routeIs('backoffice.encaissements.imports.create') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.encaissements.imports.create') }}"
                            class="pc-link {{ request()->routeIs('backoffice.encaissements.imports.create') ? 'active' : '' }}">
                            <span class="pc-mtext">Importer</span>
                        </a>
                    </li>
                @endcan
                <li
                    class="pc-item {{ request()->routeIs('backoffice.encaissements.imports.index') || request()->routeIs('backoffice.encaissements.imports.show') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.encaissements.imports.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.encaissements.imports.index') || request()->routeIs('backoffice.encaissements.imports.show') ? 'active' : '' }}">
                        <span class="pc-mtext">Historique imports</span>
                    </a>
                </li>
                {{-- Hidden for now — routes still work, just off the sidebar
        <li class="pc-item {{ request()->routeIs('backoffice.encaissements.rentabilite') ? 'active' : '' }}">
            <a href="{{ route('backoffice.encaissements.rentabilite') }}" class="pc-link {{ request()->routeIs('backoffice.encaissements.rentabilite') ? 'active' : '' }}">
                <span class="pc-mtext">Rentabilité</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.encaissements.operators') ? 'active' : '' }}">
            <a href="{{ route('backoffice.encaissements.operators') }}" class="pc-link {{ request()->routeIs('backoffice.encaissements.operators') ? 'active' : '' }}">
                <span class="pc-mtext">Opérateurs</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.encaissements.expenses.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.encaissements.expenses.index') }}" class="pc-link {{ request()->routeIs('backoffice.encaissements.expenses.*') ? 'active' : '' }}">
                <span class="pc-mtext">Charges</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.encaissements.recouvrement') || request()->routeIs('backoffice.encaissements.impayes.*') ? 'active' : '' }}">
            <a href="{{ route('backoffice.encaissements.recouvrement') }}" class="pc-link {{ request()->routeIs('backoffice.encaissements.recouvrement') || request()->routeIs('backoffice.encaissements.impayes.*') ? 'active' : '' }}">
                <span class="pc-mtext">Recouvrement & Impayés</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.encaissements.primes.index') || request()->routeIs('backoffice.encaissements.primes.config') ? 'active' : '' }}">
            <a href="{{ route('backoffice.encaissements.primes.index') }}" class="pc-link {{ request()->routeIs('backoffice.encaissements.primes.index') || request()->routeIs('backoffice.encaissements.primes.config') ? 'active' : '' }}">
                <span class="pc-mtext">Primes (auto)</span>
            </a>
        </li>
        --}}
            </ul>
        </li>
    @endcan
@endif

<li class="pc-item pc-hasmenu {{ $rhOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-calendar-blank"></i></span>
        <span class="pc-mtext">RH / Planning</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        {{-- Self-service weekly planning — visible to every logged-in user --}}
        <li class="pc-item {{ request()->routeIs('backoffice.schedules.week') ? 'active' : '' }}">
            <a href="{{ route('backoffice.schedules.week') }}"
                class="pc-link {{ request()->routeIs('backoffice.schedules.week') ? 'active' : '' }}">
                <span class="pc-mtext">Mon Planning</span>
            </a>
        </li>
        @can('schedules.view')
            <li class="pc-item {{ request()->routeIs('backoffice.schedules.index') ? 'active' : '' }}">
                <a href="{{ route('backoffice.schedules.index') }}"
                    class="pc-link {{ request()->routeIs('backoffice.schedules.index') ? 'active' : '' }}">
                    <span class="pc-mtext">Planning équipe</span>
                </a>
            </li>
            <li class="pc-item {{ request()->routeIs('backoffice.planning.*') ? 'active' : '' }}">
                <a href="{{ route('backoffice.planning.export-form') }}"
                    class="pc-link {{ request()->routeIs('backoffice.planning.*') ? 'active' : '' }}">
                    <span class="pc-mtext">Exportation PDF</span>
                </a>
            </li>
        @endcan
    </ul>
</li>

@canany(['blog_categories.view', 'blog_posts.view'])
    <li class="pc-item pc-hasmenu {{ $contentOpen ? 'pc-trigger' : '' }}">
        <a href="#!" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-newspaper"></i></span>
            <span class="pc-mtext">Contenu</span>
            <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
        </a>
        <ul class="pc-submenu">
            @can('blog_categories.view')
                <li class="pc-item {{ request()->routeIs('backoffice.blog.categories.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.blog.categories.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.blog.categories.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Categories blog</span>
                    </a>
                </li>
            @endcan
            @can('blog_posts.view')
                <li class="pc-item {{ request()->routeIs('backoffice.blog.posts.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.blog.posts.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.blog.posts.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Articles blog</span>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany

{{-- Hidden: not ready for production
@can('whatsapp_campaigns.view')
<li class="pc-item pc-hasmenu {{ $whatsappOpen ? 'pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-whatsapp-logo"></i></span>
        <span class="pc-mtext">Campagnes WhatsApp</span>
        <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item {{ request()->routeIs('backoffice.whatsapp_campaigns.dashboard') ? 'active' : '' }}">
            <a href="{{ route('backoffice.whatsapp_campaigns.dashboard') }}" class="pc-link {{ request()->routeIs('backoffice.whatsapp_campaigns.dashboard') ? 'active' : '' }}">
                <span class="pc-mtext">Tableau de bord</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('backoffice.whatsapp_campaigns.index') ? 'active' : '' }}">
            <a href="{{ route('backoffice.whatsapp_campaigns.index') }}" class="pc-link {{ request()->routeIs('backoffice.whatsapp_campaigns.index') ? 'active' : '' }}">
                <span class="pc-mtext">Historique</span>
            </a>
        </li>
        @can('whatsapp_campaigns.create')
        <li class="pc-item {{ request()->routeIs('backoffice.whatsapp_campaigns.create') ? 'active' : '' }}">
            <a href="{{ route('backoffice.whatsapp_campaigns.create') }}" class="pc-link {{ request()->routeIs('backoffice.whatsapp_campaigns.create') ? 'active' : '' }}">
                <span class="pc-mtext">Nouvelle campagne</span>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcan
--}}

@canany(['users.view', 'roles.view'])
    <li class="pc-item pc-hasmenu {{ $adminOpen ? 'pc-trigger' : '' }}">
        <a href="#!" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-user-gear"></i></span>
            <span class="pc-mtext">Administration</span>
            <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
        </a>
        <ul class="pc-submenu">
            @can('users.view')
                <li class="pc-item {{ request()->routeIs('backoffice.users.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.users.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.users.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Utilisateurs</span>
                    </a>
                </li>
            @endcan
            @can('roles.view')
                <li class="pc-item {{ request()->routeIs('backoffice.roles.*') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.roles.index') }}"
                        class="pc-link {{ request()->routeIs('backoffice.roles.*') ? 'active' : '' }}">
                        <span class="pc-mtext">Rôles & Permissions</span>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany

{{-- CRM (API) — separate menu section --}}
@can('crm.view')
    <li class="pc-item pc-hasmenu {{ $crmOpen ? 'pc-trigger' : '' }}">
        <a href="#!" class="pc-link">
            <span class="pc-micon"><i class="ph-duotone ph-database"></i></span>
            <span class="pc-mtext">CRM (API)</span>
            <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
        </a>
        <ul class="pc-submenu">
            {{-- Données (Data) submenu --}}
            <li class="pc-item pc-hasmenu {{ $crmDataOpen ? 'pc-trigger' : '' }}">
                <a href="#!" class="pc-link">
                    <span class="pc-mtext">Données</span>
                    <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
                </a>
                <ul class="pc-submenu">
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.students') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.students') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.students') ? 'active' : '' }}">
                            <span class="pc-mtext">Étudiants</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.session-presence') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.session-presence') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.session-presence') ? 'active' : '' }}">
                            <span class="pc-mtext">Présences sessions</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.registrations') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.registrations') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.registrations') ? 'active' : '' }}">
                            <span class="pc-mtext">Inscriptions</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.payments') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.payments') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.payments') ? 'active' : '' }}">
                            <span class="pc-mtext">Paiements</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.payment-checks') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.payment-checks') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.payment-checks') ? 'active' : '' }}">
                            <span class="pc-mtext">Chèques</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.payment-allocations') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.payment-allocations') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.payment-allocations') ? 'active' : '' }}">
                            <span class="pc-mtext">Allocations paiement</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.payment-collection') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.payment-collection') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.payment-collection') ? 'active' : '' }}">
                            <span class="pc-mtext">Recouvrement</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.groups.classes') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.groups.classes') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.groups.classes') ? 'active' : '' }}">
                            <span class="pc-mtext">Classes</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.groups.level-sessions') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.groups.level-sessions') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.groups.level-sessions') ? 'active' : '' }}">
                            <span class="pc-mtext">Level sessions</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.subscription-services') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.subscription-services') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.subscription-services') ? 'active' : '' }}">
                            <span class="pc-mtext">Services d'abonnement</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.employee-salaries') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.employee-salaries') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.employee-salaries') ? 'active' : '' }}">
                            <span class="pc-mtext">Salaires employés</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.lov') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.lov', ['kind' => 'banks']) }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.lov') ? 'active' : '' }}">
                            <span class="pc-mtext">Listes de valeurs (LOV)</span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Paiement Professeurs CRM --}}
            <li class="pc-item pc-hasmenu {{ request()->routeIs('backoffice.payroll.crm.*') ? 'pc-trigger' : '' }}">
                <a href="#!" class="pc-link">
                    <span class="pc-mtext">Paiement Profs</span>
                    <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
                </a>
                <ul class="pc-submenu">
                    <li class="pc-item {{ request()->routeIs('backoffice.payroll.crm.legacy.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}"
                            class="pc-link {{ request()->routeIs('backoffice.payroll.crm.legacy.dashboard') ? 'active' : '' }}">
                            <span class="pc-mtext">Tableau de bord</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.payroll.crm.legacy.import.create') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.payroll.crm.legacy.import.create') }}"
                            class="pc-link {{ request()->routeIs('backoffice.payroll.crm.legacy.import.create') ? 'active' : '' }}">
                            <span class="pc-mtext">Importer depuis CRM</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.groups.classes') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.groups.classes') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.groups.classes') ? 'active' : '' }}">
                            <span class="pc-mtext">Classes CRM</span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Collections / Recouvrement --}}
            <li class="pc-item {{ $crmCollectionsOpen ? 'active' : '' }}">
                <a href="{{ route('backoffice.crm.collections.index') }}"
                    class="pc-link {{ $crmCollectionsOpen ? 'active' : '' }}">
                    <span class="pc-mtext">Recouvrement</span>
                </a>
            </li>

            {{-- Rapports CEO Quotidiens --}}
            <li class="pc-item {{ request()->routeIs('backoffice.crm.reports.*') ? 'active' : '' }}">
                <a href="{{ route('backoffice.crm.reports.index') }}"
                    class="pc-link {{ request()->routeIs('backoffice.crm.reports.*') ? 'active' : '' }}">
                    <span class="pc-mtext">Rapports CEO</span>
                </a>
            </li>

            {{-- Statistiques Rentabilité --}}
            <li class="pc-item {{ $crmStatsDashOpen ? 'active' : '' }}">
                <a href="{{ route('backoffice.crm.statistiques') }}"
                    class="pc-link {{ $crmStatsDashOpen ? 'active' : '' }}">
                    <span class="pc-mtext">📊 Statistiques</span>
                </a>
            </li>

            {{-- Suivi Présences --}}
            <li class="pc-item {{ $crmPresenceSuiviOpen ? 'active' : '' }}">
                <a href="{{ route('backoffice.crm.presence-suivi') }}"
                    class="pc-link {{ $crmPresenceSuiviOpen ? 'active' : '' }}">
                    <span class="pc-mtext">🗓 Suivi Présences</span>
                </a>
            </li>

            {{-- Statistiques submenu --}}
            <li class="pc-item pc-hasmenu {{ $crmStatsOpen ? 'pc-trigger' : '' }}">
                <a href="#!" class="pc-link">
                    <span class="pc-mtext">Statistiques</span>
                    <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
                </a>
                <ul class="pc-submenu">
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.group-evolution') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.group-evolution') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.group-evolution') ? 'active' : '' }}">
                            <span class="pc-mtext">Évolution par groupe</span>
                        </a>
                    </li>
                    {{-- Hidden from sidebar for now, but uncomment if needed:
                <li class="pc-item {{ request()->routeIs('backoffice.crm.index') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.crm.index') }}" class="pc-link {{ request()->routeIs('backoffice.crm.index') ? 'active' : '' }}">
                        <span class="pc-mtext">Vue d'ensemble</span>
                    </a>
                </li>
                <li class="pc-item {{ request()->routeIs('backoffice.crm.stats') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.crm.stats') }}" class="pc-link {{ request()->routeIs('backoffice.crm.stats') ? 'active' : '' }}">
                        <span class="pc-mtext">Statistiques</span>
                    </a>
                </li>
                <li class="pc-item {{ request()->routeIs('backoffice.crm.duplicates') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.crm.duplicates') }}" class="pc-link {{ request()->routeIs('backoffice.crm.duplicates') ? 'active' : '' }}">
                        <span class="pc-mtext">Doublons</span>
                    </a>
                </li>
                --}}
                    {{-- Insights hidden for now, uncomment if needed:
                <li class="pc-item {{ request()->routeIs('backoffice.crm.insights.cash-handlers') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.crm.insights.cash-handlers') }}" class="pc-link {{ request()->routeIs('backoffice.crm.insights.cash-handlers') ? 'active' : '' }}">
                        <span class="pc-mtext">Cash handlers</span>
                    </a>
                </li>
                <li class="pc-item {{ request()->routeIs('backoffice.crm.insights.reconciliation') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.crm.insights.reconciliation') }}" class="pc-link {{ request()->routeIs('backoffice.crm.insights.reconciliation') ? 'active' : '' }}">
                        <span class="pc-mtext">Réconciliation</span>
                    </a>
                </li>
                <li class="pc-item {{ request()->routeIs('backoffice.crm.insights.retention') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.crm.insights.retention') }}" class="pc-link {{ request()->routeIs('backoffice.crm.insights.retention') ? 'active' : '' }}">
                        <span class="pc-mtext">Rétention</span>
                    </a>
                </li>
                <li class="pc-item {{ request()->routeIs('backoffice.crm.insights.forecast') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.crm.insights.forecast') }}" class="pc-link {{ request()->routeIs('backoffice.crm.insights.forecast') ? 'active' : '' }}">
                        <span class="pc-mtext">Prévisions revenus</span>
                    </a>
                </li>
                <li class="pc-item {{ request()->routeIs('backoffice.crm.insights.payment-activity') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.crm.insights.payment-activity') }}" class="pc-link {{ request()->routeIs('backoffice.crm.insights.payment-activity') ? 'active' : '' }}">
                        <span class="pc-mtext">Activité paiements</span>
                    </a>
                </li>
                <li class="pc-item {{ request()->routeIs('backoffice.crm.insights.advances') ? 'active' : '' }}">
                    <a href="{{ route('backoffice.crm.insights.advances') }}" class="pc-link {{ request()->routeIs('backoffice.crm.insights.advances') ? 'active' : '' }}">
                        <span class="pc-mtext">Avances</span>
                    </a>
                </li>
                --}}
                </ul>
            </li>
        </ul>
    </li>
@endcan
