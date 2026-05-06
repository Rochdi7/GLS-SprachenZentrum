/* =========================================================
   GLS Form Submit Loading
   Listens for any <form> submit on frontoffice pages, and
   shows a spinner on the submit button until either:
   - the page navigates (classic POST/GET), or
   - the button is re-enabled by client JS (AJAX), or
   - a safety timeout fires.

   Opt-out: add data-no-loading to the form or the button.
   ========================================================= */
(function () {
    if (window.__attFormLoadingInit) return;
    window.__attFormLoadingInit = true;

    var SAFETY_MS = 12000; // restore button if nothing else does

    function findSubmitButton(form, evt) {
        // Submitter button (the one actually clicked) is most accurate
        if (evt && evt.submitter) return evt.submitter;
        // Fallback: first submit button inside the form
        return form.querySelector('button[type="submit"], input[type="submit"]');
    }

    function startLoading(btn) {
        if (!btn || btn.dataset.attLoading === '1') return;
        if (btn.hasAttribute('data-no-loading')) return;
        btn.dataset.attLoading = '1';
        btn.classList.add('is-loading');
        // Don't set disabled — that would block <input type=submit> from posting.
        // For <button>, the form has already started submitting at this point.
        btn.setAttribute('aria-busy', 'true');
        // Watch for external code clearing disabled (AJAX done) or innerHTML changes
        scheduleSafety(btn);
    }

    function stopLoading(btn) {
        if (!btn) return;
        btn.dataset.attLoading = '0';
        btn.classList.remove('is-loading');
        btn.removeAttribute('aria-busy');
        if (btn.__attLoadingTimer) {
            clearTimeout(btn.__attLoadingTimer);
            btn.__attLoadingTimer = null;
        }
    }

    function scheduleSafety(btn) {
        if (btn.__attLoadingTimer) clearTimeout(btn.__attLoadingTimer);
        btn.__attLoadingTimer = setTimeout(function () { stopLoading(btn); }, SAFETY_MS);
    }

    // Detect form submissions (after browser-native validation passes)
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        if (form.hasAttribute('data-no-loading')) return;
        // If the submit was prevented (e.g., AJAX form using preventDefault),
        // we still want to show loading — JS handlers usually disable the
        // button afterwards, so we set is-loading immediately.
        var btn = findSubmitButton(form, e);
        if (!btn) return;
        // Wait one tick — gives client code a chance to cancel via
        // event.preventDefault() and run its own validation.
        setTimeout(function () {
            startLoading(btn);
            // If a client re-enables the button (AJAX success/failure), clean up
            observeRestore(btn);
        }, 0);
    }, true);

    // Observe disabled toggling — when client AJAX re-enables the button,
    // remove the spinner.
    function observeRestore(btn) {
        if (btn.__attLoadingObserver) return;
        try {
            var mo = new MutationObserver(function () {
                if (!btn.disabled && btn.classList.contains('is-loading') && btn.dataset.attLoading === '1') {
                    // Was disabled and now isn't — assume AJAX done
                    stopLoading(btn);
                }
            });
            mo.observe(btn, { attributes: true, attributeFilter: ['disabled', 'class'] });
            btn.__attLoadingObserver = mo;
        } catch (_) { /* MutationObserver missing — safety timeout still works */ }
    }

    // pageshow fires when navigating back via bfcache — clear any stuck spinners
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('button[type="submit"][data-att-loading="1"], input[type="submit"][data-att-loading="1"]').forEach(stopLoading);
    });
})();
