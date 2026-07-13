{{--
    Real full-CRM-resync button + live sync badge.

    Renders the "Actualiser" button that triggers crm:sync-all in the background
    (POST backoffice.crm.resync), duplicate-click-safe via the shared
    crm.sync-all.lock, plus the sync badge which is swapped in place when the
    sync finishes — no page reload.

    Usage (anywhere a sync badge belongs):
        @include('backoffice.crm.partials._resync_button')

    Replaces bare `@include('backoffice.crm.partials._sync_badge')` — the badge
    is rendered inside here (wrapped in #crmSyncBadge so JS can refresh it).

    NOTE: one button per page. The script uses fixed IDs (crmResyncBtn /
    crmSyncBadge); the @once guard keeps the JS single even if included twice,
    and the handler binds to the first matching button.
--}}
<span class="d-inline-flex align-items-center gap-2">
    <button type="button" id="crmResyncBtn"
            class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
            data-resync-url="{{ route('backoffice.crm.resync') }}"
            data-status-url="{{ route('backoffice.crm.resync.status') }}"
            title="Resynchroniser toutes les données CRM depuis Wimschool">
        <i class="ti ti-refresh" id="crmResyncIcon"></i>
        <span id="crmResyncLabel">Actualiser</span>
    </button>
    <span id="crmSyncBadge">@include('backoffice.crm.partials._sync_badge')</span>
</span>

@once
{{-- layouts.main uses @yield('scripts') (not @stack), so a @push here would be
     dropped. Inline is safe: the script only touches elements rendered just
     above it and defers all real work to event handlers. --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('crmResyncBtn');
    if (!btn) return;

    const icon  = document.getElementById('crmResyncIcon');
    const label = document.getElementById('crmResyncLabel');
    const csrf  = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
               || document.querySelector('input[name="_token"]')?.value;
    let polling = null;

    function setRunning() {
        btn.disabled = true;
        icon.classList.add('ti-loader-2');
        icon.classList.remove('ti-refresh');
        icon.style.animation = 'spin 1s linear infinite';
        label.textContent = 'Sync en cours…';
    }
    function setIdle() {
        btn.disabled = false;
        icon.classList.remove('ti-loader-2');
        icon.classList.add('ti-refresh');
        icon.style.animation = '';
        label.textContent = 'Actualiser';
    }

    function swapBadge(html) {
        const holder = document.getElementById('crmSyncBadge');
        if (!holder || !html) return;
        holder.innerHTML = html;
        // Re-init the Bootstrap tooltip on the freshly-injected badge.
        holder.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            if (window.bootstrap && bootstrap.Tooltip) new bootstrap.Tooltip(el);
        });
    }

    function poll() {
        fetch(btn.dataset.statusUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                if (!d.running) {
                    clearInterval(polling);
                    polling = null;
                    // Sync finished — restore the button and refresh the badge
                    // in place (no full page reload).
                    setIdle();
                    swapBadge(d.badge_html);
                }
            })
            .catch(() => { /* keep polling; transient errors are fine */ });
    }

    function startPolling() {
        setRunning();
        if (!polling) polling = setInterval(poll, 8000);
    }

    btn.addEventListener('click', function () {
        if (btn.disabled) return;
        setRunning();
        fetch(btn.dataset.resyncUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(async r => ({ ok: r.ok, status: r.status, body: await r.json().catch(() => ({})) }))
        .then(({ ok, status, body }) => {
            if (status === 409) {
                // Already running (scheduler or another user) — just watch it finish.
                startPolling();
                return;
            }
            if (!ok) {
                setIdle();
                alert(body.message || 'Erreur lors du lancement de la synchronisation.');
                return;
            }
            startPolling();
        })
        .catch(() => {
            setIdle();
            alert('Erreur réseau. Réessayez.');
        });
    });

    // If a sync (manual or scheduled) is already running when the page loads,
    // reflect that and start watching so the button isn't clickable twice.
    fetch(btn.dataset.statusUrl, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(d => { if (d.running) startPolling(); })
        .catch(() => {});
});
</script>
<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endonce
