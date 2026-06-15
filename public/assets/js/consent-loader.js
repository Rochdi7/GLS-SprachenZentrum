/* =========================================================
   GLS consent-loader.js
   ---------------------------------------------------------
   Centralises loading of third-party scripts to protect Lighthouse
   Performance / Best Practices and to honour cookie consent.

   TWO STRICT LANES:

   1) TRACKING / MARKETING  — Facebook Pixel, Google Tag (Ads/GTM), Ahrefs Analytics,
      and Tawk.to chat.
      Loaded **only** after explicit consent: localStorage 'gls_cookie_choice' === 'accepted'.
      NOT loaded on scroll / interaction / timeout. NOT loaded on a fresh visit with no
      choice, and NOT loaded if the visitor rejected. (GDPR-safe.)

   2) FUNCTIONAL (no tracking) — handled elsewhere:
        - Leaflet map: loaded on viewport (see initGlsMapLazy below, called from the page).
        - Vimeo / YouTube players: loaded on user click (see video-facade.js).
      These do not require consent.

   Config (Pixel ID, Ads ID, Ahrefs key, Tawk src) is passed from Blade via
   window.GLS_TRACKING so we never hardcode IDs here.
========================================================= */
(function () {
    'use strict';

    var STORAGE_KEY = 'gls_cookie_choice';
    var cfg = window.GLS_TRACKING || {};
    var trackingLoaded = false;

    function hasConsent() {
        try {
            return localStorage.getItem(STORAGE_KEY) === 'accepted';
        } catch (e) {
            return false;
        }
    }

    function injectScript(src, attrs) {
        var s = document.createElement('script');
        s.src = src;
        s.async = true;
        if (attrs) {
            Object.keys(attrs).forEach(function (k) {
                s.setAttribute(k, attrs[k]);
            });
        }
        var first = document.getElementsByTagName('script')[0];
        if (first && first.parentNode) {
            first.parentNode.insertBefore(s, first);
        } else {
            document.head.appendChild(s);
        }
        return s;
    }

    /* ---- Facebook / Meta Pixel ---- */
    function loadPixel() {
        if (!cfg.pixelId || window.fbq) return;
        /* Standard Meta snippet, minus the eager injection (we inject ourselves). */
        !(function (f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function () {
                n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s);
        })(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
        window.fbq('init', cfg.pixelId);
        window.fbq('track', 'PageView');
    }

    /* ---- Google Tag (Ads / GTM gtag.js) ---- */
    function loadGtag() {
        if (!cfg.gtagId || window.__glsGtagLoaded) return;
        window.__glsGtagLoaded = true;
        injectScript('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(cfg.gtagId));
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', cfg.gtagId);
    }

    /* ---- Ahrefs Analytics ---- */
    function loadAhrefs() {
        if (!cfg.ahrefsKey || window.__glsAhrefsLoaded) return;
        window.__glsAhrefsLoaded = true;
        injectScript('https://analytics.ahrefs.com/analytics.js', { 'data-key': cfg.ahrefsKey });
    }

    /* ---- Tawk.to live chat (sets its own cookies → consent-gated, +10s idle) ---- */
    function loadTawk() {
        if (!cfg.tawkSrc || window.Tawk_API) return;
        window.Tawk_API = window.Tawk_API || {};
        window.Tawk_LoadStart = new Date();
        if (cfg.tawkStyle) {
            window.Tawk_API.customStyle = cfg.tawkStyle;
        }
        var delay = typeof cfg.tawkDelay === 'number' ? cfg.tawkDelay : 10000;
        var fire = function () {
            /* NOTE: no crossorigin='*' attribute — that triggered the CORS console error. */
            injectScript(cfg.tawkSrc, { charset: 'UTF-8' });
        };
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(function () { setTimeout(fire, delay); }, { timeout: delay + 4000 });
        } else {
            setTimeout(fire, delay);
        }
    }

    /* Load ALL consented trackers exactly once. */
    function loadTracking() {
        if (trackingLoaded || !hasConsent()) return;
        trackingLoaded = true;
        loadPixel();
        loadGtag();
        loadAhrefs();
        loadTawk();
    }

    /* Public API for the cookie banner: call after the user clicks "Accept". */
    window.glsConsentGranted = function () {
        loadTracking();
    };

    /* On every page load: if consent was given on a previous visit, load immediately. */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadTracking, { once: true });
    } else {
        loadTracking();
    }
})();
