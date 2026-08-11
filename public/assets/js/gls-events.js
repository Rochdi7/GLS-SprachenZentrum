/* =========================================================
   GLS gls-events.js
   ---------------------------------------------------------
   Small helper to send Google Ads/GA (gtag.js) events for key
   conversion buttons across the site. Reuses the gtag instance
   already loaded (consent-gated) by consent-loader.js.

   window.glsTrack(eventName, params) — no-ops silently if gtag
   isn't loaded (no consent given yet), so it's always safe to call.

   Delegated click tracking: add data-gtm-event="event_name" to any
   element and it will fire automatically on click, with optional
   data-gtm-label / data-gtm-value read as extra params.
========================================================= */
(function () {
    'use strict';

    /* Consent gate: the ONLY way an event reaches GA4 is via window.gtag, which
       consent-loader.js creates exclusively after the visitor accepts cookies.
       No consent → no gtag → nothing is sent or queued. */
    function hasConsent() {
        try {
            return localStorage.getItem('gls_cookie_choice') === 'accepted';
        } catch (e) {
            return false;
        }
    }

    /* Page-load conversion events (success pages, which fire on DOMContentLoaded) can
       race the async gtag.js download and be silently dropped. When consent IS granted
       but gtag has not appeared yet, retry briefly instead of discarding the event.
       Without consent we drop immediately — never queue, never send later. */
    var PENDING_RETRY_MS = 500;
    var PENDING_MAX_TRIES = 20; // ~10s ceiling

    function glsTrack(eventName, params, _try) {
        if (typeof window.gtag === 'function') {
            window.gtag('event', eventName, params || {});
            return;
        }
        if (!hasConsent()) return;

        var attempt = _try || 0;
        if (attempt >= PENDING_MAX_TRIES) return;
        setTimeout(function () {
            glsTrack(eventName, params, attempt + 1);
        }, PENDING_RETRY_MS);
    }
    window.glsTrack = glsTrack;

    document.addEventListener('click', function (e) {
        // 1) Explicit opt-in via data-gtm-event on the element itself
        var explicit = e.target.closest('[data-gtm-event]');
        if (explicit) {
            var eventName = explicit.getAttribute('data-gtm-event');
            var params = {};
            if (explicit.dataset.gtmCategory) params.event_category = explicit.dataset.gtmCategory;
            if (explicit.dataset.gtmLabel) params.event_label = explicit.dataset.gtmLabel;
            if (explicit.dataset.gtmValue) params.value = explicit.dataset.gtmValue;
            glsTrack(eventName, params);
            return;
        }

        // 2) Auto-detect: any button/link that opens a tracked modal
        //    (data-bs-target="#glsEnrollModal" or "#consultationModal"), anywhere on
        //    the site. Tells you WHICH page drove the click, before we even know if
        //    they finish the form.
        var modalTarget = e.target.closest('[data-bs-target="#glsEnrollModal"], [data-bs-target="#consultationModal"]');
        if (modalTarget) {
            var isEnroll = modalTarget.getAttribute('data-bs-target') === '#glsEnrollModal';
            glsTrack(isEnroll ? 'Enroll_Button_Click' : 'Consultation_Button_Click', {
                event_category: 'GLS Inscription',
                event_label: window.location.pathname
            });
            return;
        }

        // 3) Auto-detect: the certificate PDF download button.
        var certDownload = e.target.closest('.vc-download-btn');
        if (certDownload) {
            glsTrack('Certificate_Download_Click', {
                event_category: 'Certificate',
                event_label: window.location.pathname
            });
            return;
        }

        // 4) Auto-detect: any outbound link to wa.me (WhatsApp), Google Maps,
        //    tel:, mailto:, a known social platform, or the fc-marokko partner site —
        //    anywhere on the site, without needing per-page markup changes.
        var link = e.target.closest('a[href]');
        if (!link) return;
        var href = link.href || '';

        if (/(^|\/\/)(api\.)?wa\.me\//i.test(href) || /wa\.me\//i.test(href)) {
            glsTrack('WhatsApp_Button_Click', {
                event_category: 'WhatsApp',
                event_label: window.location.pathname
            });
            return;
        }

        if (/google\.[^/]+\/maps|maps\.app\.goo\.gl|goo\.gl\/maps/i.test(href)) {
            glsTrack('Google_Maps_Click', {
                event_category: 'Google Maps',
                event_label: window.location.pathname
            });
            return;
        }

        if (/^tel:/i.test(href)) {
            glsTrack('Phone_Number_Click', {
                event_category: 'Contact',
                event_label: window.location.pathname
            });
            return;
        }

        if (/^mailto:/i.test(href)) {
            glsTrack('Email_Link_Click', {
                event_category: 'Contact',
                event_label: window.location.pathname
            });
            return;
        }

        var socialMatch = href.match(/:\/\/(?:www\.|api\.)?(instagram|facebook|youtube|tiktok|linkedin|twitter|x)\.com(?:[/?#]|$)/i);
        if (socialMatch) {
            glsTrack('Social_Media_Click', {
                event_category: 'Social Media',
                event_label: socialMatch[1].toLowerCase()
            });
            return;
        }

        if (/fc-marokko\.de/i.test(href)) {
            glsTrack('Partner_Link_Click', {
                event_category: 'Partner',
                event_label: 'fc-marokko'
            });
            return;
        }

        var langBtn = e.target.closest('.nav-lang-btn');
        if (langBtn) {
            glsTrack('Language_Switch_Click', {
                event_category: 'Navigation',
                event_label: langBtn.textContent.trim()
            });
        }
    });

    // 5) Tawk.to live chat — fires when the visitor actually opens the widget.
    // IMPORTANT: do NOT create window.Tawk_API here if it doesn't exist yet —
    // consent-loader.js's loadTawk() uses `if (window.Tawk_API) return;` to avoid
    // double-loading the widget, so pre-creating it here would make Tawk think
    // it's already loaded and skip injecting the real script entirely.
    function hookTawkOnChatMaximized() {
        var prevOnChatMaximized = window.Tawk_API.onChatMaximized;
        window.Tawk_API.onChatMaximized = function () {
            glsTrack('Tawk_Chat_Opened', { event_category: 'Live Chat' });
            if (typeof prevOnChatMaximized === 'function') prevOnChatMaximized();
        };
    }

    if (window.Tawk_API) {
        hookTawkOnChatMaximized();
    } else {
        // Poll until consent-loader.js creates window.Tawk_API (after consent + tawkDelay).
        var tawkWait = setInterval(function () {
            if (window.Tawk_API) {
                clearInterval(tawkWait);
                hookTawkOnChatMaximized();
            }
        }, 500);
    }
})();
