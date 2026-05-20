{{--
    CRM page loader overlay.

    Behavior:
      - Visible during navigation to any /crm/* page (link click, filter submit).
      - Hidden as soon as the destination page's DOM is ready (data fetched).
      - Also covers fetch() / XHR calls fired from CRM pages (autocomplete, etc.)
        when their URL points at /crm/.

    Drop-in: included once via _center partial, so every CRM page gets it.
--}}

<div id="crm-loader" class="crm-loader" aria-hidden="true" role="status" aria-live="polite">
    <div class="crm-loader__stage">
        <div class="crm-loader__scene">
            {{-- Orbital rings (3D, each tilted on a different axis) --}}
            <div class="crm-orbit crm-orbit--x">
                <div class="crm-orbit__ring"></div>
                <div class="crm-orbit__node"></div>
            </div>
            <div class="crm-orbit crm-orbit--y">
                <div class="crm-orbit__ring"></div>
                <div class="crm-orbit__node"></div>
            </div>
            <div class="crm-orbit crm-orbit--z">
                <div class="crm-orbit__ring"></div>
                <div class="crm-orbit__node"></div>
            </div>

            {{-- Central pulsing core --}}
            <div class="crm-core">
                <div class="crm-core__pulse"></div>
                <div class="crm-core__pulse crm-core__pulse--delay"></div>
                <div class="crm-core__glow"></div>
            </div>
        </div>

        <div class="crm-loader__copy">
            <h2 class="crm-loader__title">Chargement<span class="crm-loader__dots"><span>.</span><span>.</span><span>.</span></span></h2>
            <p class="crm-loader__sub" id="crm-loader-sub">Connexion au CRM…</p>
        </div>

        <div class="crm-loader__bar" aria-hidden="true">
            <div class="crm-loader__bar-fill"></div>
        </div>
    </div>
</div>

<style>
    .crm-loader {
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
    .crm-loader.is-active {
        display: flex;
        opacity: 1;
    }
    .crm-loader__stage {
        text-align: center;
        padding: 32px 40px;
        max-width: 360px;
    }

    /* ---------- 3D scene ----------
       Each orbit ring sits on a DIFFERENT axis so the scene reads as a sphere,
       not a stack of flat ellipses. The container handles the camera tilt
       (perspective + small Y rotation); the orbit elements only spin. The node
       is positioned at the top of its ring and rotates with it — phase-offset
       per ring so the three nodes never bunch on the same side. */
    .crm-loader__scene {
        width: 170px;
        height: 170px;
        margin: 0 auto 28px;
        position: relative;
        perspective: 1100px;
        perspective-origin: 50% 50%;
        transform-style: preserve-3d;
        transform: rotateX(-12deg) rotateY(18deg);
    }

    .crm-orbit {
        position: absolute;
        inset: 0;
        transform-style: preserve-3d;
        animation: crm-orbit-spin 3.6s linear infinite;
    }
    /* Three distinct planes — equatorial, tilted, and near-vertical. */
    .crm-orbit--x { --tilt: rotateY(0deg);                animation-duration: 3.4s; }
    .crm-orbit--y { --tilt: rotateX(75deg);               animation-duration: 4.2s; animation-direction: reverse; }
    .crm-orbit--z { --tilt: rotateY(60deg) rotateX(35deg);animation-duration: 5.0s; }

    .crm-orbit__ring {
        position: absolute;
        inset: 14px;
        border-radius: 50%;
        border: 1.25px solid rgba(29, 41, 57, 0.18);
        box-shadow:
            inset 0 0 14px rgba(255, 145, 39, 0.05),
            0 0 0.5px rgba(255, 145, 39, 0.05);
        transform: var(--tilt);
        transform-style: preserve-3d;
    }
    .crm-orbit--x .crm-orbit__ring { border-color: rgba(255, 145, 39, 0.55); }
    .crm-orbit--y .crm-orbit__ring { border-color: rgba(29, 41, 57, 0.40); }
    .crm-orbit--z .crm-orbit__ring { border-color: rgba(255, 145, 39, 0.35); }

    /* Node sits at the "12 o'clock" of its ring before tilt is applied; the
       parent .crm-orbit handles spin, ring handles tilt — nodes ride along. */
    .crm-orbit__node {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 12px;
        height: 12px;
        margin: -6px 0 0 -6px;
        border-radius: 50%;
        background: radial-gradient(circle at 30% 30%, #ffd0a3, #ff7a18 60%, #c14d00 100%);
        box-shadow:
            0 0 10px rgba(255, 122, 24, 0.85),
            0 0 22px rgba(255, 122, 24, 0.4);
        /* tilt the node onto its ring's plane, then offset along the ring radius */
        transform: var(--tilt) translateY(-71px);
    }
    /* Phase-offset each node so they don't all start at the same clock position. */
    .crm-orbit--x { transform: rotateZ(0deg);   }
    .crm-orbit--y { transform: rotateZ(120deg); }
    .crm-orbit--z { transform: rotateZ(240deg); }

    .crm-orbit--y .crm-orbit__node {
        background: radial-gradient(circle at 30% 30%, #6b7587, #1d2939 60%, #0b1220 100%);
        box-shadow:
            0 0 9px rgba(29, 41, 57, 0.6),
            0 0 18px rgba(29, 41, 57, 0.3);
    }
    .crm-orbit--z .crm-orbit__node {
        width: 10px;
        height: 10px;
        margin: -5px 0 0 -5px;
    }

    /* ---------- central core ---------- */
    .crm-core {
        position: absolute;
        top: 50%; left: 50%;
        width: 36px; height: 36px;
        margin: -18px 0 0 -18px;
        border-radius: 50%;
        background: radial-gradient(circle at 30% 30%, #ffffff, #ffd5a8 50%, #ff9127 100%);
        box-shadow:
            0 0 18px rgba(255, 145, 39, 0.55),
            0 0 40px rgba(255, 145, 39, 0.25),
            inset 0 -4px 8px rgba(193, 77, 0, 0.4);
        z-index: 2;
        animation: crm-core-breath 2.4s ease-in-out infinite;
    }
    .crm-core__pulse,
    .crm-core__pulse--delay {
        position: absolute;
        inset: -6px;
        border-radius: 50%;
        border: 2px solid rgba(255, 145, 39, 0.55);
        animation: crm-core-pulse 2.2s ease-out infinite;
    }
    .crm-core__pulse--delay { animation-delay: 1.1s; }
    .crm-core__glow {
        position: absolute;
        inset: -20px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 145, 39, 0.18), transparent 70%);
        filter: blur(6px);
    }

    /* ---------- copy ---------- */
    .crm-loader__copy { margin-bottom: 18px; }
    .crm-loader__title {
        margin: 0 0 6px;
        font-weight: 600;
        font-size: 1.55rem;
        color: #1d2939;
        letter-spacing: 0.2px;
    }
    .crm-loader__dots span {
        display: inline-block;
        opacity: 0;
        animation: crm-dot 1.4s infinite;
    }
    .crm-loader__dots span:nth-child(2) { animation-delay: 0.2s; }
    .crm-loader__dots span:nth-child(3) { animation-delay: 0.4s; }
    .crm-loader__sub {
        margin: 0;
        color: #6b7280;
        font-size: 0.95rem;
        min-height: 1.2em;
        transition: opacity 0.25s ease;
    }
    .crm-loader__sub.is-swapping { opacity: 0; }

    /* ---------- shimmer bar ---------- */
    .crm-loader__bar {
        margin: 8px auto 0;
        width: 180px;
        height: 3px;
        border-radius: 99px;
        background: rgba(29, 41, 57, 0.08);
        overflow: hidden;
    }
    .crm-loader__bar-fill {
        width: 40%;
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, transparent, #ff9127 30%, #ff7a18 70%, transparent);
        animation: crm-bar-sweep 1.4s ease-in-out infinite;
    }

    /* ---------- keyframes ----------
       Spin = full 360° rotation in Z, starting from each ring's phase offset.
       Tilt is applied separately on the ring + node via --tilt, so the ring
       plane stays fixed in space while the node rides around it. */
    .crm-orbit--x { animation-name: crm-orbit-spin-x; }
    .crm-orbit--y { animation-name: crm-orbit-spin-y; }
    .crm-orbit--z { animation-name: crm-orbit-spin-z; }
    @keyframes crm-orbit-spin-x {
        from { transform: rotateZ(0deg);   }
        to   { transform: rotateZ(360deg); }
    }
    @keyframes crm-orbit-spin-y {
        from { transform: rotateZ(120deg); }
        to   { transform: rotateZ(480deg); }
    }
    @keyframes crm-orbit-spin-z {
        from { transform: rotateZ(240deg); }
        to   { transform: rotateZ(600deg); }
    }
    @keyframes crm-core-breath {
        0%, 100% { transform: scale(1); }
        50%      { transform: scale(1.08); }
    }
    @keyframes crm-core-pulse {
        0%   { transform: scale(0.6); opacity: 0.9; }
        70%  { transform: scale(1.8); opacity: 0;   }
        100% { transform: scale(1.8); opacity: 0;   }
    }
    @keyframes crm-dot {
        0%, 80%, 100% { opacity: 0; transform: translateY(0); }
        40%           { opacity: 1; transform: translateY(-2px); }
    }
    @keyframes crm-bar-sweep {
        0%   { transform: translateX(-150%); }
        100% { transform: translateX(420%); }
    }

    /* Respect users who want less motion. */
    @media (prefers-reduced-motion: reduce) {
        .crm-orbit,
        .crm-core,
        .crm-core__pulse,
        .crm-loader__dots span,
        .crm-loader__bar-fill {
            animation: none !important;
        }
    }
</style>

<script>
    (function () {
        var loader = document.getElementById('crm-loader');
        if (!loader) return;

        var sub = document.getElementById('crm-loader-sub');
        var ACTIVE = 'is-active';
        var pendingXhr = 0;
        var hideTimer = null;
        var stageTimer = null;

        // Rotating status messages — give the user a sense of progression so the
        // wait doesn't feel idle. Each message reflects a real step in the
        // request lifecycle (connect → fetch → render).
        var STAGES = [
            'Connexion au CRM…',
            'Récupération des données…',
            'Synchronisation avec Homeschool…',
            'Préparation de l’affichage…',
            'Presque prêt…'
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

        // Same-origin CRM-bound URL? (covers absolute, relative, query-string nav).
        function isCrmUrl(url) {
            if (!url) return false;
            try {
                var u = new URL(url, window.location.origin);
                if (u.origin !== window.location.origin) return false;
                return /\/crm(\/|$|\?)/.test(u.pathname + u.search);
            } catch (e) {
                return false;
            }
        }

        // 1) Show overlay on full-page navigation to another CRM page.
        document.addEventListener('click', function (e) {
            var a = e.target.closest('a');
            if (!a) return;
            if (a.target === '_blank' || a.hasAttribute('download')) return;
            if (a.dataset.noLoader === '1') return;
            // Modifier keys = new tab, leave alone.
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            if (isCrmUrl(a.href)) show();
        }, true);

        // 2) Filter forms (CRM pages POST/GET to /crm/* routes).
        document.addEventListener('submit', function (e) {
            var f = e.target;
            if (!f || f.tagName !== 'FORM') return;
            if (f.dataset.noLoader === '1') return;
            var action = f.getAttribute('action') || window.location.href;
            if (isCrmUrl(action)) show();
        }, true);

        // 3) AJAX (fetch + XHR) hitting CRM endpoints — e.g. student autocomplete.
        if (window.fetch) {
            var originalFetch = window.fetch;
            window.fetch = function (input, init) {
                var url = (typeof input === 'string') ? input : (input && input.url);
                var watch = isCrmUrl(url);
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
            this.__crmWatch = isCrmUrl(url);
            return origOpen.apply(this, arguments);
        };
        window.XMLHttpRequest.prototype.send = function () {
            var self = this;
            if (self.__crmWatch) {
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
