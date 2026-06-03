<!DOCTYPE html>
<html lang="fr">

<head>
    <title>@yield('title') | GLS Sprachen Zentrum – Back Office</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description"
        content="GLS Sprachen Zentrum Back Office – Gérez les articles de blog, les enseignants, les certificats, les groupes, les sites et plus encore. Tableau de bord d'administration pour les centres GLS à travers le Maroc." />
    <meta name="author" content="Équipe GLS" />

    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/favicon/favicon.svg') }}">
    <link rel="alternate icon" type="image/png" href="{{ asset('assets/images/favicon/favicon-96x96.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('assets/images/favicon/site.webmanifest') }}">

    @yield('css')
    @include('layouts.head-css')
</head>

<body data-pc-preset="preset-5" data-pc-sidebar-theme="light" data-pc-sidebar-caption="true" data-pc-direction="ltr"
    data-pc-theme="light">

    <style>
        .logo-lg {
            width: 140px !important;
            height: 50px !important;
            object-fit: contain !important;
            display: block !important;
        }

        .m-header .b-brand {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100% !important;
        }

        img[src*="assets/images/logo/gls.avif"],
        img[src$="gls.avif"] {
            width: 140px !important;
            height: 50px !important;
            object-fit: contain !important;
        }

        /* Compact layout overrides */
        .pc-sidebar {
            width: 250px;
        }
        .pc-sidebar .navbar-wrapper {
            width: 250px;
        }
        .pc-sidebar .pc-link {
            padding: 9px 18px;
            font-size: 13px;
        }
        .pc-sidebar .pc-micon {
            margin-right: 10px;
            height: 20px;
            width: 20px;
        }
        .pc-sidebar .pc-micon i {
            font-size: 18px;
        }
        .pc-sidebar .pc-caption {
            padding: 12px 20px 6px !important;
            font-size: 11px;
        }
        .pc-sidebar .pc-caption span:not(.badge) {
            font-size: 13px;
        }
        .pc-sidebar .pc-navbar > .pc-item {
            margin: 0 8px;
        }
        .pc-sidebar .pc-submenu .pc-link {
            padding: 7px 18px 7px 52px;
            font-size: 13px;
        }
        .pc-sidebar .m-header {
            height: 64px;
        }
        .pc-sidebar .card.pc-user-card .dropdown-menu {
            width: 220px;
        }

        /* Compact header */
        .pc-header {
            min-height: 64px;
            left: 250px;
        }
        .pc-header .m-header {
            width: 250px;
            height: 64px;
        }
        .pc-header .pc-h-item {
            min-height: 64px;
        }

        /* Adjust content & footer offset */
        .pc-footer {
            margin-left: 250px;
            margin-top: 64px;
        }

        /* Compact Choices.js dropdowns */
        .choices {
            font-size: 13px;
        }
        .choices__list--dropdown .choices__item,
        .choices__list[aria-expanded] .choices__item {
            padding: 6px 10px;
            font-size: 13px;
        }
        .choices__inner {
            padding: 4px 8px;
            min-height: 36px;
            font-size: 13px;
        }
        .choices__input {
            font-size: 13px;
        }

        @media (max-width: 1024px) {
            .pc-header {
                left: 0;
            }
            .pc-footer {
                margin-left: 0;
            }
        }
    </style>
    @include('layouts.loader')
    @include('layouts.backoffice-loader')
    @include('layouts.sidebar')
    @include('layouts.topbar')

    <div class="pc-container">
        <div class="pc-content">

            @if (View::hasSection('breadcrumb-item'))
                @include('layouts.breadcrumb')
            @endif

            @yield('content')

        </div>
    </div>
    @include('layouts.footer')
    @include('layouts.customizer')
    @include('layouts.footerjs')

    @yield('scripts')

</body>

</html>