/* =========================================================
   GLS video-facade.js
   Swaps a click-to-load video facade (poster + play button) for the real
   Vimeo / YouTube iframe on user activation. Functional, no tracking → no consent
   required (it only loads when the user explicitly clicks to watch).
   Keyboard accessible: the facade is a <button>, so Enter/Space activate it natively.
========================================================= */
(function () {
    'use strict';

    // Guard against double-initialisation (the script may be included by more than one
    // video section partial on the same page).
    if (window.__glsVideoFacadeInit) return;
    window.__glsVideoFacadeInit = true;

    function buildIframe(provider, id, params) {
        var src;
        var sep = params && params.indexOf('?') === -1 ? '?' : '&';
        if (provider === 'youtube') {
            src = 'https://www.youtube.com/embed/' + encodeURIComponent(id) +
                (params ? '?' + params + '&autoplay=1' : '?autoplay=1');
        } else {
            // vimeo
            src = 'https://player.vimeo.com/video/' + encodeURIComponent(id) +
                (params ? '?' + params + '&autoplay=1' : '?autoplay=1');
        }

        var iframe = document.createElement('iframe');
        iframe.setAttribute('src', src);
        iframe.setAttribute('title', '');
        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('allow',
            'autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share');
        iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
        iframe.setAttribute('allowfullscreen', '');
        return iframe;
    }

    function activate(facade) {
        if (facade.dataset.loaded === '1') return;
        facade.dataset.loaded = '1';

        var provider = facade.getAttribute('data-video-provider') || 'vimeo';
        var id = facade.getAttribute('data-video-id');
        var params = facade.getAttribute('data-video-params') || '';
        var title = facade.getAttribute('aria-label') || '';

        if (!id) return;

        var iframe = buildIframe(provider, id, params);
        if (title) iframe.setAttribute('title', title);

        // Replace the <button> facade with the iframe, preserving the box.
        if (facade.parentNode) {
            facade.parentNode.replaceChild(iframe, facade);
        }
    }

    function onClick(e) {
        var facade = e.target.closest('.gls-video-facade');
        if (!facade) return;
        e.preventDefault();
        activate(facade);
    }

    document.addEventListener('click', onClick);
})();
