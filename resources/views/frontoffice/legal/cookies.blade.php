<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/legal/terms.css') }}">

<div id="cookie-banner" class="cookie-banner" hidden>
    <p class="cookie-text">
        {{ __('legal/cookies.text') }}
        <a href="{{ route('front.privacy') }}">
            {{ __('legal/cookies.settings') }}
        </a>
    </p>

    <div class="cookie-actions">
        <button id="cookie-accept" class="cookie-btn accept">
            {{ __('legal/cookies.accept') }}
        </button>
        <button id="cookie-reject" class="cookie-btn reject">
            {{ __('legal/cookies.reject') }}
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const banner = document.getElementById('cookie-banner');
    if (!banner) return;

    const accept = document.getElementById('cookie-accept');
    const reject = document.getElementById('cookie-reject');
    const openLinks = document.querySelectorAll('[data-open-cookies]');

    const STORAGE_KEY = 'gls_cookie_choice';

    function showBanner() {
        banner.hidden = false;
    }

    function hideBanner() {
        banner.hidden = true;
    }

    if (!localStorage.getItem(STORAGE_KEY)) {
        showBanner();
    }

    accept?.addEventListener('click', function () {
        localStorage.setItem(STORAGE_KEY, 'accepted');
        hideBanner();
        // Load tracking/marketing scripts now that consent is granted.
        if (typeof window.glsConsentGranted === 'function') {
            window.glsConsentGranted();
        }
    });

    reject?.addEventListener('click', function () {
        localStorage.setItem(STORAGE_KEY, 'rejected');
        hideBanner();
        // No tracking is loaded on reject.
    });

    openLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            showBanner();
        });
    });

});
</script>
