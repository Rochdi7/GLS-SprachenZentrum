<!DOCTYPE html>
<html lang="en">

<head>
    <title>@yield('title') | GLS Sprachen Zentrum – Back Office</title>

    <!-- [Meta] -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO -->
    <meta name="description"
        content="GLS Sprachen Zentrum Back Office – Manage blog posts, teachers, certificates, groups, sites and more. Admin dashboard for the GLS centers across Morocco." />
    <meta name="author" content="GLS Team" />

    <!-- [Favicon] -->
    <!-- Modern browsers -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/favicon/favicon.svg') }}">
    <!-- PNG fallback -->
    <link rel="alternate icon" type="image/png" href="{{ asset('assets/images/favicon/favicon-96x96.png') }}">
    <!-- Apple touch icon -->
    <link rel="apple-touch-icon" href="{{ asset('assets/images/favicon/apple-touch-icon.png') }}">
    <!-- Webmanifest -->
    <link rel="manifest" href="{{ asset('assets/images/favicon/site.webmanifest') }}">

    @yield('css')
    @include('layouts.head-css')
</head>

<body data-pc-preset="preset-5" data-pc-sidebar-theme="light" data-pc-sidebar-caption="true" data-pc-direction="ltr"
    data-pc-theme="light">

    <style>
        .logo-lg {
            width: 160px !important;
            height: 60px !important;
            object-fit: contain !important;
            display: block !important;
        }

        .m-header .b-brand {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100% !important;
        }

        img[src*="assets/images/logo/gls.png"],
        img[src$="gls.png"] {
            width: 160px !important;
            height: 60px !important;
            object-fit: contain !important;
        }
    </style>
    @include('layouts.loader')
    @include('layouts.sidebar')
    @include('layouts.topbar')

    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">

            @if (View::hasSection('breadcrumb-item'))
                @include('layouts.breadcrumb')
            @endif

            @yield('content')

        </div>
    </div>
    <!-- [ Main Content ] end -->

    @include('layouts.footer')
    @include('layouts.customizer')
    @include('layouts.footerjs')

    @yield('scripts')

</body>

</html>
