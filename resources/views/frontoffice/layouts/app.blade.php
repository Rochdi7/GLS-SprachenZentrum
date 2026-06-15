<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="facebook-domain-verification" content="21382f16lc5kb1hxg8a19ch6iwvzoc" />

    @include('frontoffice.partials.seo-head')

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/images/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/favicon/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('assets/images/favicon/site.webmanifest') }}">

    <!-- Preconnect to required origins (faster fetch of deferred CSS / fonts) -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdn.jotfor.ms" crossorigin>

    {{-- Critical above-the-fold CSS (inlined) so header + hero LCP paint immediately --}}
    @include('frontoffice.partials.critical-css')

    {{-- Preload the primary "Now" weight used by the hero LCP title to avoid late FOUT swap --}}
    <link rel="preload" as="font" type="font/otf" href="{{ asset('assets/fonts/Now-Medium.otf') }}" crossorigin>

    @php
        // Flattened, single-file stylesheet (built by scripts/build-frontoffice-css.php).
        // Falls back to the @import-based style.css if the bundle has not been generated.
        $cssBundlePath = public_path('assets/css/frontoffice/style.bundle.css');
        $cssMain = is_file($cssBundlePath)
            ? 'assets/css/frontoffice/style.bundle.css'
            : 'assets/css/frontoffice/style.css';
        $cssMainVer = @filemtime(public_path($cssMain)) ?: '1';
    @endphp

    {{-- Non-critical CSS: load without blocking render (print-swap pattern + noscript fallback) --}}
    <link rel="stylesheet" media="print" onload="this.media='all'"
        href="{{ asset($cssMain) }}?v={{ $cssMainVer }}">
    <link rel="stylesheet" media="print" onload="this.media='all'"
        href="{{ asset('assets/css/frontoffice/footer.css') }}">
    <link rel="stylesheet" media="print" onload="this.media='all'"
        href="{{ asset('assets/css/gls-form.css') }}">
    <link rel="stylesheet" media="print" onload="this.media='all'"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" media="print" onload="this.media='all'"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    @if (app()->getLocale() == 'ar')
        <link rel="stylesheet" media="print" onload="this.media='all'"
            href="{{ asset('assets/css/rtl.css') }}">
    @endif
    <link rel="stylesheet" media="print" onload="this.media='all'"
        href="{{ asset('assets/css/frontoffice/att-form-fields.css') }}?v={{ @filemtime(public_path('assets/css/frontoffice/att-form-fields.css')) ?: '1' }}">
    <link rel="stylesheet" media="print" onload="this.media='all'"
        href="{{ asset('assets/css/frontoffice/att-form-loading.css') }}?v={{ @filemtime(public_path('assets/css/frontoffice/att-form-loading.css')) ?: '1' }}">

    <noscript>
        <link rel="stylesheet" href="{{ asset($cssMain) }}?v={{ $cssMainVer }}">
        <link rel="stylesheet" href="{{ asset('assets/css/frontoffice/footer.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/gls-form.css') }}">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        @if (app()->getLocale() == 'ar')
            <link rel="stylesheet" href="{{ asset('assets/css/rtl.css') }}">
        @endif
    </noscript>

    @stack('styles')

    {{-- Tracking config (consumed by consent-loader.js). Scripts load ONLY after cookie consent. --}}
    <script>
        window.GLS_TRACKING = {
            pixelId: '407443676615251',
            gtagId: 'AW-17817493313',
            ahrefsKey: 'vKoc9I4c7spqw+TRXsjGtw',
            tawkSrc: 'https://embed.tawk.to/69af4ebd7d962c1c35e7812e/1jjacn54k',
            tawkDelay: 10000,
            tawkStyle: {
                visibility: {
                    desktop: { position: 'br', xOffset: 24, yOffset: 90 },
                    mobile: { position: 'br', xOffset: 15, yOffset: 90 }
                }
            }
        };
    </script>
    <script src="{{ asset('assets/js/consent-loader.js') }}?v={{ @filemtime(public_path('assets/js/consent-loader.js')) ?: '1' }}" defer></script>
    {{-- The no-JS Meta Pixel <noscript> fallback was removed: it would fire without
         consent (can't be gated without JS), conflicting with the consent-only policy. --}}
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
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:620px;">
            <div class="modal-content p-0" style="background:none;border:none;border-radius:0;">
                <div class="modal-body p-0">
                    @include('frontoffice.templates.gls-form')
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle (deferred; ordered before scripts that depend on it) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

    <script src="{{ asset('assets/js/header.js') }}" defer></script>
    <script src="{{ asset('assets/js/reveal.js') }}" defer></script>
    <script src="{{ asset('assets/js/autoscroller.js') }}" defer></script>
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

    <button id="backToTop" type="button" aria-label="Back to top">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
            <path d="M6 14l6-6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
    </button>


    @include('frontoffice.templates.group-apply-modals', [
        'applyGroups' => $applyGroups ?? collect(),
    ])
    @include('frontoffice.legal.cookies')

    <script defer
        src="{{ asset('assets/js/att-form-fields.js') }}?v={{ @filemtime(public_path('assets/js/att-form-fields.js')) ?: '1' }}">
    </script>
    <script defer
        src="{{ asset('assets/js/att-form-loading.js') }}?v={{ @filemtime(public_path('assets/js/att-form-loading.js')) ?: '1' }}">
    </script>
    @stack('scripts')

    @include('frontoffice.templates.consultation-form')

    {{-- Tracking (Google Ads/GTM, Ahrefs, Meta Pixel, Tawk.to) is loaded by
         assets/js/consent-loader.js ONLY after the visitor accepts cookies. --}}

    {{-- Disable Inspect/DevTools (Production Only) - temporarily commented out --}}
    
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
   
</body>

</html>
