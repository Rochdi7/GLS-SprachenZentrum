{{--
    Backoffice page loader overlay.

    Behavior:
      - Visible during navigation to any /backoffice/* page (link click, filter
        submit, sidebar menu).
      - Hidden as soon as the destination page's DOM is ready (data fetched).
      - Also covers fetch() / XHR calls fired from backoffice pages whose URL
        points at /backoffice/.

    Drop-in: included once via layouts.main, so every backoffice page
    (dashboard, CRM, blog, certificates, …) gets the overlay automatically.

    Per-link / per-form opt-out: add `data-no-loader="1"`.
--}}

<div id="bo-loader" class="bo-loader" aria-hidden="true" role="status" aria-live="polite">
    <div class="bo-loader__stage">
        <div class="bo-loader__scene">
            <div class="bo-loader__stack" aria-hidden="true"></div>
        </div>

        <div class="bo-loader__copy">
            <h2 class="bo-loader__title">Chargement<span class="bo-loader__dots"><span>.</span><span>.</span><span>.</span></span></h2>
            <p class="bo-loader__sub" id="bo-loader-sub">Connexion en cours…</p>
        </div>

        <div class="bo-loader__bar" aria-hidden="true">
            <div class="bo-loader__bar-fill"></div>
        </div>
    </div>
</div>

<style>
    .bo-loader {
        position: fixed;
        inset: 0;
        z-index: 10500;
        background:
            radial-gradient(1200px 600px at 50% 40%, rgba(255, 145, 39, 0.10), transparent 60%),
            radial-gradient(900px 500px at 50% 70%, rgba(29, 41, 57, 0.04), transparent 60%),
            rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(10px) saturate(120%);
        -webkit-backdrop-filter: blur(10px) saturate(120%);
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.22s ease;
        font-family: inherit;
    }
    .bo-loader.is-active {
        display: flex;
        opacity: 1;
    }
    .bo-loader__stage {
        text-align: center;
        padding: 32px 40px;
        max-width: 380px;
    }

    /* ---------- stacking-blocks loader ----------
       4 small blocks "drop" one-by-one onto a baseline, then the whole group
       slides off and the cycle repeats. The white shadow under the baseline
       (the `box-shadow: 0 3px 0 #fff` on the host) hides the part of the
       drop animation that happens below the line, so blocks appear to land
       cleanly. Colors swapped to GLS off-black + orange. */
    .bo-loader__scene {
        margin: 0 auto 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 60px;
    }
    .bo-loader__stack {
        width: 90px;
        height: 14px;
        box-shadow: 0 3px 0 #ffffff;  /* baseline mask — must match overlay bg */
        position: relative;
        clip-path: inset(-40px 0 -5px);
    }
    .bo-loader__stack:before {
        content: "";
        position: absolute;
        inset: auto calc(50% - 17px) 0;
        height: 50px;
        --g: no-repeat linear-gradient(#1d2939 0 0);             /* off-black */
        background: var(--g), var(--g), var(--g), var(--g);
        background-size: 16px 14px;
        animation:
            bo-stack-fill  2s infinite linear,
            bo-stack-slide 2s infinite linear;
    }
    @keyframes bo-stack-fill {
        0%, 100%  { background-position: 0 -50px,                 100% -50px; }
        17.5%     { background-position: 0 100%,                  100% -50px, 0 -50px, 100% -50px; }
        35%       { background-position: 0 100%,                  100% 100%,  0 -50px, 100% -50px; }
        52.5%     { background-position: 0 100%,                  100% 100%,  0 calc(100% - 16px), 100% -50px; }
        70%, 98%  { background-position: 0 100%,                  100% 100%,  0 calc(100% - 16px), 100% calc(100% - 16px); }
    }
    @keyframes bo-stack-slide {
        0%, 70%  { transform: translate(0); }
        100%     { transform: translate(200%); }
    }

    /* Recolor the 4th block (last one to land) so the group reads as
       off-black with one accent orange — same composition trick as the
       original color pair, applied via a layered overlay element. */
    .bo-loader__stack:after {
        content: "";
        position: absolute;
        inset: auto calc(50% - 17px) 0;
        height: 50px;
        --o: no-repeat linear-gradient(#ff7a18 0 0);
        background: var(--o);
        background-size: 16px 14px;
        background-position: 100% calc(100% - 16px);
        opacity: 0;
        animation: bo-stack-accent 2s infinite linear;
    }
    @keyframes bo-stack-accent {
        0%, 52.4%  { opacity: 0; transform: translate(0); }
        52.5%, 70% { opacity: 1; transform: translate(0); }
        100%       { opacity: 1; transform: translate(200%); }
    }

    /* ---------- copy ---------- */
    .bo-loader__copy { margin-bottom: 18px; }
    .bo-loader__title {
        margin: 0 0 6px;
        font-weight: 600;
        font-size: 1.55rem;
        color: #1d2939;
        letter-spacing: 0.2px;
    }
    .bo-loader__dots span {
        display: inline-block;
        opacity: 0;
        animation: bo-dot 1.4s infinite;
    }
    .bo-loader__dots span:nth-child(2) { animation-delay: 0.2s; }
    .bo-loader__dots span:nth-child(3) { animation-delay: 0.4s; }
    .bo-loader__sub {
        margin: 0;
        color: #6b7280;
        font-size: 0.95rem;
        min-height: 1.2em;
        transition: opacity 0.25s ease;
    }
    .bo-loader__sub.is-swapping { opacity: 0; }

    /* ---------- shimmer bar ---------- */
    .bo-loader__bar {
        margin: 8px auto 0;
        width: 180px;
        height: 3px;
        border-radius: 99px;
        background: rgba(29, 41, 57, 0.08);
        overflow: hidden;
    }
    .bo-loader__bar-fill {
        width: 40%;
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, transparent, #ff9127 30%, #ff7a18 70%, transparent);
        animation: bo-bar-sweep 1.4s ease-in-out infinite;
    }

    /* ---------- keyframes ---------- */
    @keyframes bo-dot {
        0%, 80%, 100% { opacity: 0; transform: translateY(0); }
        40%           { opacity: 1; transform: translateY(-2px); }
    }
    @keyframes bo-bar-sweep {
        0%   { transform: translateX(-150%); }
        100% { transform: translateX(420%); }
    }

    /* Respect users who want less motion. */
    @media (prefers-reduced-motion: reduce) {
        .bo-loader__stack:before,
        .bo-loader__stack:after,
        .bo-loader__dots span,
        .bo-loader__bar-fill {
            animation: none !important;
        }
    }
</style>

<script>
    (function () {
        var loader = document.getElementById('bo-loader');
        if (!loader) return;

        var sub        = document.getElementById('bo-loader-sub');
        var ACTIVE     = 'is-active';
        var pendingXhr = 0;
        var hideTimer  = null;
        var stageTimer = null;

        // Rotating status messages — give the user a sense of progression so
        // the wait doesn't feel idle. Each line reflects a real step in the
        // request lifecycle.
        var STAGES = [
            'Connexion en cours…',
            'Récupération des données…',
            'Préparation de l’affichage…',
            'Presque prêt…',
        ];
        function startStages() {
            if (!sub) return;
            var i = 0;
            sub.textContent = STAGES[0];
            stopStages();
            stageTimer = setInterval(function () {
                i = (i + 1) % STAGES.length;
                sub.classList.add('is-swapping');
                setTimeout(function () {
                    sub.textContent = STAGES[i];
                    sub.classList.remove('is-swapping');
                }, 220);
            }, 1600);
        }
        function stopStages() {
            if (stageTimer) { clearInterval(stageTimer); stageTimer = null; }
        }

        function show() {
            if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; }
            if (!loader.classList.contains(ACTIVE)) {
                loader.classList.add(ACTIVE);
                loader.setAttribute('aria-hidden', 'false');
                startStages();
            }
        }
        function hide() {
            // Small delay so a quick chain of fetches doesn't flicker the overlay.
            if (hideTimer) clearTimeout(hideTimer);
            hideTimer = setTimeout(function () {
                if (pendingXhr === 0) {
                    loader.classList.remove(ACTIVE);
                    loader.setAttribute('aria-hidden', 'true');
                    stopStages();
                }
            }, 120);
        }

        // Hide on initial page render — data is already on the page.
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            hide();
        } else {
            window.addEventListener('DOMContentLoaded', hide, { once: true });
        }
        // Also hide if bfcache restores the page.
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) { pendingXhr = 0; hide(); }
        });

        // Same-origin backoffice-bound URL? Covers /backoffice/*, /crm/* (legacy),
        // and root-relative links inside the BO sidebar.
        function isBoUrl(url) {
            if (!url) return false;
            try {
                var u = new URL(url, window.location.origin);
                if (u.origin !== window.location.origin) return false;
                return /\/(backoffice|crm)(\/|$|\?)/.test(u.pathname + u.search);
            } catch (e) {
                return false;
            }
        }

        // 1) Show overlay on full-page navigation to another BO page.
        document.addEventListener('click', function (e) {
            var a = e.target.closest('a');
            if (!a) return;
            if (a.target === '_blank' || a.hasAttribute('download')) return;
            if (a.dataset.noLoader === '1') return;
            // Hash-only links (in-page anchors) shouldn't trigger an overlay.
            if (a.getAttribute('href') && a.getAttribute('href').charAt(0) === '#') return;
            // Modifier keys = new tab, leave alone.
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            if (isBoUrl(a.href)) show();
        }, true);

        // 2) Filter forms (BO pages POST/GET to /backoffice/* routes).
        document.addEventListener('submit', function (e) {
            var f = e.target;
            if (!f || f.tagName !== 'FORM') return;
            if (f.dataset.noLoader === '1') return;
            var action = f.getAttribute('action') || window.location.href;
            if (isBoUrl(action)) show();
        }, true);

        // 3) AJAX (fetch + XHR) hitting BO endpoints — autocomplete etc.
        if (window.fetch) {
            var originalFetch = window.fetch;
            window.fetch = function (input, init) {
                var url = (typeof input === 'string') ? input : (input && input.url);
                var watch = isBoUrl(url);
                if (watch) { pendingXhr++; show(); }
                var p = originalFetch.apply(this, arguments);
                if (watch) {
                    p.then(function () { pendingXhr = Math.max(0, pendingXhr - 1); hide(); },
                           function () { pendingXhr = Math.max(0, pendingXhr - 1); hide(); });
                }
                return p;
            };
        }
        var origOpen = window.XMLHttpRequest.prototype.open;
        var origSend = window.XMLHttpRequest.prototype.send;
        window.XMLHttpRequest.prototype.open = function (method, url) {
            this.__boWatch = isBoUrl(url);
            return origOpen.apply(this, arguments);
        };
        window.XMLHttpRequest.prototype.send = function () {
            var self = this;
            if (self.__boWatch) {
                pendingXhr++;
                show();
                self.addEventListener('loadend', function () {
                    pendingXhr = Math.max(0, pendingXhr - 1);
                    hide();
                });
            }
            return origSend.apply(this, arguments);
        };
    })();
</script>
