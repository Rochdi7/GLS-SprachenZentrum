<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>GLS Sprachenzentrum – Learning Center Morocco</title>
<meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/images/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/favicon/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('assets/images/favicon/site.webmanifest') }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/frontoffice/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/frontoffice/footer.css') }}">

    <!-- GLS FORM CSS (NEW) -->
    <link rel="stylesheet" href="{{ asset('assets/css/gls-form.css') }}">

    @if (app()->getLocale() == 'ar')
        <link rel="stylesheet" href="{{ asset('assets/css/rtl.css') }}">
    @endif
</head>


<body>

    {{-- Header --}}
    @include('frontoffice.partials.header')

    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('frontoffice.partials.footer')

    <!-- GLS ENROLL MODAL -->
    <div class="modal fade" id="glsEnrollModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content p-0" style="background:none;border:none;border-radius:0;">
                <div class="modal-body p-0">
                    @include('frontoffice.templates.gls-form')
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Header scroll -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const header = document.querySelector('.site-header');
            const stickyOffset = header.offsetTop;

            window.addEventListener('scroll', function() {
                if (window.pageYOffset > stickyOffset) {
                    header.classList.add('is-fixed');
                } else {
                    header.classList.remove('is-fixed');
                }
            });
        });
    </script>

    <script src="{{ asset('assets/js/header.js') }}"></script>
    <script src="{{ asset('assets/js/reveal.js') }}"></script>

    <!-- GLS FORM JS (NEW) -->
    <script src="{{ asset('assets/js/gls-form.js') }}" defer></script>

    <div id="videoPopup" class="video-popup-overlay">
        <div class="video-popup-container">
            <span id="videoPopupClose" class="video-popup-close">&times;</span>

            <iframe id="videoPopupFrame" src="" frameborder="0"
                allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen>
            </iframe>
        </div>
    </div>

</body>

</html>
