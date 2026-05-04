<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>GLS Sprachenzentrum – Learning Center Morocco</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Canonical + hreflang (SEO: language alternates) --}}
    @php
        $canonicalUrl = LaravelLocalization::getLocalizedURL(app()->getLocale(), null, [], true);
    @endphp
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
        <link rel="alternate" hreflang="{{ $localeCode }}"
            href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default"
        href="{{ LaravelLocalization::getLocalizedURL('fr', null, [], true) }}">

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

    @stack('styles')
    @stack('head')
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

    <script src="{{ asset('assets/js/header.js') }}"></script>
    <script src="{{ asset('assets/js/reveal.js') }}"></script>
    <script src="{{ asset('assets/js/autoscroller.js') }}"></script>
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

    <button id="backToTop" aria-label="Back to top">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
            <path d="M6 14l6-6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
    </button>


    @include('frontoffice.templates.group-apply-modals', [
        'applyGroups' => $applyGroups ?? collect(),
    ])
    @include('frontoffice.legal.cookies')

    @stack('scripts')

    @include('frontoffice.templates.consultation-form')
    @include('frontoffice.templates.group-apply-modals')

    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17817493313"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'AW-17817493313');
    </script>

    {{-- Disable Inspect/DevTools (Production Only) - temporarily commented out --}}
    {{--
    @production
        <script>
            (function() {
                document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'F12' || e.keyCode === 123) { e.preventDefault(); return false; }
                    if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.keyCode === 73)) { e.preventDefault(); return false; }
                    if (e.ctrlKey && e.shiftKey && (e.key === 'J' || e.key === 'j' || e.keyCode === 74)) { e.preventDefault(); return false; }
                    if (e.ctrlKey && e.shiftKey && (e.key === 'C' || e.key === 'c' || e.keyCode === 67)) { e.preventDefault(); return false; }
                    if (e.ctrlKey && (e.key === 'U' || e.key === 'u' || e.keyCode === 85)) { e.preventDefault(); return false; }
                    if (e.ctrlKey && (e.key === 'S' || e.key === 's' || e.keyCode === 83)) { e.preventDefault(); return false; }
                });
                var devtools = { open: false };
                setInterval(function() {
                    var threshold = 160;
                    if (window.outerWidth - window.innerWidth > threshold || window.outerHeight - window.innerHeight > threshold) {
                        if (!devtools.open) { devtools.open = true; console.clear(); }
                    } else { devtools.open = false; }
                }, 500);
                document.addEventListener('selectstart', function(e) { if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') { e.preventDefault(); } });
                document.addEventListener('dragstart', function(e) { e.preventDefault(); });
            })();
        </script>
    @endproduction
    --}}
</body>

</html>
