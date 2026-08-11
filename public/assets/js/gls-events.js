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

    function glsTrack(eventName, params) {
        if (typeof window.gtag !== 'function') return;
        window.gtag('event', eventName, params || {});
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

        // 2) Auto-detect: any outbound link to wa.me (WhatsApp) or Google Maps,
        //    anywhere on the site, without needing per-page markup changes.
        var link = e.target.closest('a[href]');
        if (!link) return;
        var href = link.href || '';

        if (/(^|\/\/)(api\.)?wa\.me\//i.test(href) || /wa\.me\//i.test(href)) {
            glsTrack('whatsapp_click', {
                event_category: 'WhatsApp',
                event_label: window.location.pathname
            });
            return;
        }

        if (/google\.[^/]+\/maps|maps\.app\.goo\.gl|goo\.gl\/maps/i.test(href)) {
            glsTrack('google_maps_click', {
                event_category: 'Google Maps',
                event_label: window.location.pathname
            });
        }
    });

    // 3) Tawk.to live chat — fires when the visitor actually opens the widget.
    window.Tawk_API = window.Tawk_API || {};
    var prevOnChatMaximized = window.Tawk_API.onChatMaximized;
    window.Tawk_API.onChatMaximized = function () {
        glsTrack('tawk_chat_open', { event_category: 'Live Chat' });
        if (typeof prevOnChatMaximized === 'function') prevOnChatMaximized();
    };
})();
