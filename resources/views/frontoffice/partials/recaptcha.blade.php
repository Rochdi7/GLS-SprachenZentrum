@if(config('recaptcha.enabled') && config('recaptcha.site_key') && ! request()->routeIs('front.discover-your-level.quiz'))
{{-- reCAPTCHA v3 — invisible. No badge, no layout shift, no design change. --}}
<script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}" async defer></script>
<style>.grecaptcha-badge{visibility:hidden!important;}</style>
<script>
(function () {
    var SITE_KEY = @json(config('recaptcha.site_key'));
    var FIELD = 'g-recaptcha-response';

    function ready(cb) {
        var tries = 0;
        (function poll() {
            if (window.grecaptcha && window.grecaptcha.execute) return window.grecaptcha.ready(cb);
            if (++tries > 100) return cb(); // give up after ~10s, let request through
            setTimeout(poll, 100);
        })();
    }

    function getToken(action) {
        return new Promise(function (resolve) {
            ready(function () {
                if (!window.grecaptcha || !window.grecaptcha.execute) return resolve(null);
                try {
                    window.grecaptcha.execute(SITE_KEY, { action: action || 'submit' })
                        .then(resolve).catch(function () { resolve(null); });
                } catch (e) { resolve(null); }
            });
        });
    }

    window.glsRecaptchaToken = getToken;

    function setField(form, token) {
        if (!token) return;
        var input = form.querySelector('input[name="' + FIELD + '"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = FIELD;
            form.appendChild(input);
        }
        input.value = token;
    }

    // ---- 1. Classic (non-AJAX) form submits -------------------------------
    // NOTE: this listener runs in the CAPTURE phase, so it fires BEFORE a form's
    // own submit handler. It must only drive forms that rely on a native browser
    // submit. A form handled in JS (fetch/axios) calls preventDefault() itself and
    // already gets its token from the fetch/axios wrappers below - re-submitting it
    // here via requestSubmit() would fire its handler a second time and POST twice.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.method && form.method.toLowerCase() !== 'post') return;
        if (form.hasAttribute('data-no-recaptcha')) return;
        if (form.dataset.recaptchaDone === '1') { form.dataset.recaptchaDone = ''; return; }
        // Forms posting to an external host are left alone.
        if (form.action && form.action.indexOf(window.location.origin) !== 0 && /^https?:/i.test(form.action)) return;

        // Detect whether a downstream (AJAX) handler cancels this event. We must
        // preventDefault() ourselves to hold back the native submit while the token
        // is fetched, so e.defaultPrevented cannot tell us apart from them - patch
        // preventDefault on this event and watch for a call we did not make.
        var cancelledDownstream = false;
        var nativePreventDefault = e.preventDefault.bind(e);
        var ours = false;
        e.preventDefault = function () {
            if (!ours) cancelledDownstream = true;
            return nativePreventDefault();
        };

        ours = true;
        e.preventDefault();
        ours = false;

        // Let the event finish propagating to the form's own handlers first.
        setTimeout(function () {
            e.preventDefault = nativePreventDefault;
            // A handler cancelled it -> the form posts itself over AJAX (token is
            // added by the fetch/axios wrappers). Re-submitting would POST twice.
            if (cancelledDownstream) return;
            runClassicSubmit(e, form);
        }, 0);
    }, true);

    function runClassicSubmit(e, form) {
        var action = (form.dataset.recaptchaAction || 'submit').replace(/[^A-Za-z0-9_\/]/g, '_');

        getToken(action).then(function (token) {
            setField(form, token);
            form.dataset.recaptchaDone = '1';
            if (typeof form.requestSubmit === 'function') {
                var submitter = e.submitter || form.querySelector('[type="submit"]');
                form.requestSubmit(submitter || undefined);
            } else {
                form.submit();
            }
        });
    }

    // ---- 2. Axios (AJAX) requests ----------------------------------------
    function attachAxios(ax) {
        if (!ax || !ax.interceptors || ax.__glsRecaptcha) return;
        ax.__glsRecaptcha = true;
        ax.interceptors.request.use(function (cfg) {
            var m = (cfg.method || 'get').toLowerCase();
            if (m === 'get' || m === 'head') return cfg;
            var url = cfg.url || '';
            if (/^https?:/i.test(url) && url.indexOf(window.location.origin) !== 0) return cfg;

            return getToken('ajax').then(function (token) {
                if (!token) return cfg;
                cfg.headers = cfg.headers || {};
                cfg.headers['X-Recaptcha-Token'] = token;
                if (cfg.data instanceof FormData) cfg.data.append(FIELD, token);
                else if (cfg.data && typeof cfg.data === 'object') cfg.data[FIELD] = token;
                return cfg;
            });
        });
    }
    attachAxios(window.axios);
    // axios may be loaded after this script
    var axTries = 0;
    var axTimer = setInterval(function () {
        if (window.axios) { attachAxios(window.axios); clearInterval(axTimer); }
        if (++axTries > 60) clearInterval(axTimer);
    }, 250);

    // ---- 3. fetch() requests ---------------------------------------------
    if (window.fetch && !window.fetch.__glsRecaptcha) {
        var nativeFetch = window.fetch.bind(window);
        var wrapped = function (input, init) {
            init = init || {};
            var method = (init.method || (input && input.method) || 'GET').toUpperCase();
            var url = typeof input === 'string' ? input : (input && input.url) || '';
            if (method === 'GET' || method === 'HEAD') return nativeFetch(input, init);
            if (/^https?:/i.test(url) && url.indexOf(window.location.origin) !== 0) return nativeFetch(input, init);

            return getToken('ajax').then(function (token) {
                if (!token) return nativeFetch(input, init);
                var headers = new Headers(init.headers || (input && input.headers) || {});
                headers.set('X-Recaptcha-Token', token);
                init.headers = headers;
                if (init.body instanceof FormData) init.body.append(FIELD, token);
                else if (init.body instanceof URLSearchParams) init.body.append(FIELD, token);
                else if (typeof init.body === 'string') {
                    try {
                        var parsed = JSON.parse(init.body);
                        if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                            parsed[FIELD] = token;
                            init.body = JSON.stringify(parsed);
                        }
                    } catch (e) { /* not JSON — header carries the token */ }
                }
                return nativeFetch(input, init);
            });
        };
        wrapped.__glsRecaptcha = true;
        window.fetch = wrapped;
    }
})();
</script>
@endif
