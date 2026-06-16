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
    $contentOpen = request()->routeIs('backoffice.blog.*');
    $rhOpen = request()->routeIs('backoffice.schedules.*') || request()->routeIs('backoffice.planning.*');
    $adminOpen = request()->routeIs('backoffice.users.*') || request()->routeIs('backoffice.roles.*');
    $crmOpen = request()->routeIs('backoffice.crm.*');

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
    $crmExpensesOpen = request()->routeIs('backoffice.crm.expenses.*');
    $crmCollectionsOpen = request()->routeIs('backoffice.crm.collections.*');
    $crmStatsDashOpen =
        request()->routeIs('backoffice.crm.statistiques') &&
        !request()->routeIs('backoffice.crm.statistiques.comparaison*') &&
        !request()->routeIs('backoffice.crm.statistiques.ca-annuel');
    $crmStatsCompOpen = request()->routeIs('backoffice.crm.statistiques.comparaison*');
    $crmCaAnnuelOpen = request()->routeIs('backoffice.crm.statistiques.ca-annuel');
    $crmPresenceSuiviOpen = request()->routeIs('backoffice.crm.presence-suivi');

    $financesOpen = request()->routeIs('backoffice.encaissements.*');
@endphp

<li class="pc-item pc-caption">
    <label>GLS Portal</label>
    <i class="ph-duotone ph-squares-four"></i>
</li>

@can('dashboard.view')
    <li class="pc-item {{ $dashboardActive ? 'active' : '' }}">
        <a href="{{ route('dashboard') }}" id="reception-tour-menu-dashboard"
            class="pc-link {{ $dashboardActive ? 'active' : '' }}">
            <span class="pc-micon"><i class="ph-duotone ph-gauge"></i></span>
            <span class="pc-mtext">Dashboard</span>
        </a>
    </li>
@endcan

@canany(['level_followups.view', 'weekly_reports.view'])
    <li class="pc-item pc-hasmenu {{ $pilotageOpen ? 'pc-trigger' : '' }}">
        <a href="#!" id="reception-tour-menu-pilotage" class="pc-link">
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
        <a href="#!" id="reception-tour-menu-school" class="pc-link">
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


<li class="pc-item pc-hasmenu {{ $rhOpen ? 'pc-trigger' : '' }}">
    <a href="#!" id="reception-tour-menu-rh" class="pc-link">
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

            {{-- Paiement Professeurs --}}
            <li class="pc-item {{ request()->routeIs('backoffice.payroll.crm.*') ? 'active' : '' }}">
                <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}" class="pc-link">
                    <span class="pc-mtext">Paiement Profs</span>
                </a>
            </li>

            {{-- Dépenses CRM --}}
            <li class="pc-item {{ $crmExpensesOpen ? 'active' : '' }}">
                <a href="{{ route('backoffice.crm.expenses.index') }}"
                    class="pc-link {{ $crmExpensesOpen ? 'active' : '' }}">
                    <span class="pc-mtext">Dépenses</span>
                </a>
            </li>

            {{-- Recouvrement --}}
            <li class="pc-item {{ $crmCollectionsOpen ? 'active' : '' }}">
                <a href="{{ route('backoffice.crm.collections.index') }}"
                    class="pc-link {{ $crmCollectionsOpen ? 'active' : '' }}">
                    <span class="pc-mtext">Recouvrement</span>
                </a>
            </li>

            {{-- Statistiques (submenu grouping all analytics) --}}
            @php
                $crmAllStatsOpen =
                    $crmStatsDashOpen ||
                    $crmStatsCompOpen ||
                    $crmStatsOpen ||
                    request()->routeIs('backoffice.crm.group-evolution') ||
                    request()->routeIs('backoffice.crm.presence-stats') ||
                    request()->routeIs('backoffice.crm.reports.*');

            @endphp
            <li class="pc-item pc-hasmenu {{ $crmAllStatsOpen ? 'pc-trigger' : '' }}">
                <a href="#!" class="pc-link">
                    <span class="pc-mtext">Statistiques</span>
                    <span class="pc-arrow"><i class="ph-duotone ph-caret-right"></i></span>
                </a>
                <ul class="pc-submenu">
                    <li class="pc-item {{ $crmStatsDashOpen ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.statistiques') }}"
                            class="pc-link {{ $crmStatsDashOpen ? 'active' : '' }}">
                            <span class="pc-mtext">Rentabilité par centre</span>
                        </a>
                    </li>
                    {{-- <li class="pc-item {{ $crmStatsCompOpen ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.statistiques.comparaison') }}"
                            class="pc-link {{ $crmStatsCompOpen ? 'active' : '' }}">
                            <span class="pc-mtext">Comparaison centres</span>
                        </a>
                    </li> --}}
                    {{-- CA Annuel --}}
                    {{-- <li class="pc-item {{ $crmCaAnnuelOpen ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.statistiques.ca-annuel') }}"
                            class="pc-link {{ $crmCaAnnuelOpen ? 'active' : '' }}">
                            <span class="pc-mtext">CA Annuel</span>
                        </a>
                    </li> --}}
                    {{-- <li class="pc-item {{ request()->routeIs('backoffice.crm.statistiques.resume-annuel') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.statistiques.resume-annuel') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.statistiques.resume-annuel') ? 'active' : '' }}">
                            <span class="pc-mtext">Résumé annuel (primes)</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.statistiques.professeurs') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.statistiques.professeurs') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.statistiques.professeurs') ? 'active' : '' }}">
                            <span class="pc-mtext">Performance professeurs</span>
                        </a>
                    </li> --}}
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.group-evolution') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.group-evolution') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.group-evolution') ? 'active' : '' }}">
                            <span class="pc-mtext">Évolution par groupe</span>
                        </a>
                    </li>
                    <li class="pc-item {{ request()->routeIs('backoffice.crm.presence-stats') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.presence-stats') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.presence-stats') ? 'active' : '' }}">
                            <span class="pc-mtext">Statistiques présence</span>
                        </a>
                    </li>
                    {{-- <li class="pc-item {{ request()->routeIs('backoffice.crm.reports.*') ? 'active' : '' }}">
                        <a href="{{ route('backoffice.crm.reports.index') }}"
                            class="pc-link {{ request()->routeIs('backoffice.crm.reports.*') ? 'active' : '' }}">
                            <span class="pc-mtext">Rapports CEO</span>
                        </a>
                    </li> --}}
                </ul>
            </li>
        </ul>
    </li>
@endcan
