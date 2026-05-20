{{--
    Wires every .crm-student-ac widget on the current page to the JSON student
    search endpoint. Included once per page that uses the autocomplete.

    The partial is self-protecting with @once so it's safe to include from
    multiple parents.
--}}
@once
<script>
(function () {
    const ENDPOINT = @json(route('backoffice.crm.api.students-search'));

    function debounce(fn, ms) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));

    function initWidget(widget) {
        if (widget.dataset.acInited === '1') return;
        widget.dataset.acInited = '1';

        const searchInput = widget.querySelector('[data-role="search"]');
        const valueInput  = widget.querySelector('[data-role="value"]');
        const menu        = widget.querySelector('[data-role="menu"]');
        if (!searchInput || !valueInput || !menu) return;

        const renderMenu = (items, message) => {
            menu.innerHTML = '';
            if (message) {
                const el = document.createElement('div');
                el.className = 'list-group-item small text-muted';
                el.textContent = message;
                menu.appendChild(el);
            }
            items.forEach(it => {
                const el = document.createElement('button');
                el.type = 'button';
                el.className = 'list-group-item list-group-item-action py-2';
                el.innerHTML =
                    `<div class="small fw-medium">${escapeHtml(it.name)}</div>` +
                    (it.reference
                        ? `<div class="text-muted" style="font-size:.7rem;">Réf. ${escapeHtml(it.reference)} · ID ${it.id}</div>`
                        : `<div class="text-muted" style="font-size:.7rem;">ID ${it.id}</div>`);
                el.addEventListener('click', () => {
                    valueInput.value = it.id;
                    searchInput.value = it.name;
                    menu.style.display = 'none';
                });
                menu.appendChild(el);
            });
            menu.style.display = (items.length || message) ? 'block' : 'none';
        };

        const doSearch = debounce(async (q) => {
            if (q.length < 2) { renderMenu([], null); return; }
            try {
                const u = new URL(ENDPOINT, window.location.origin);
                u.searchParams.set('q', q);
                const currentParams = new URLSearchParams(window.location.search);
                if (currentParams.get('strStoreId')) {
                    u.searchParams.set('strStoreId', currentParams.get('strStoreId'));
                }
                const r = await fetch(u.toString(), { headers: { 'Accept': 'application/json' } });
                if (!r.ok) { renderMenu([], 'Erreur de chargement.'); return; }
                const j = await r.json();
                const items = j.data || [];
                renderMenu(items, items.length ? null : 'Aucun étudiant trouvé.');
            } catch {
                renderMenu([], 'Erreur réseau.');
            }
        }, 220);

        searchInput.addEventListener('input', (e) => {
            valueInput.value = ''; // typing clears the previously picked ID
            doSearch(e.target.value.trim());
        });

        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length >= 2) doSearch(searchInput.value.trim());
        });

        document.addEventListener('click', (e) => {
            if (!widget.contains(e.target)) menu.style.display = 'none';
        });
    }

    function initAll(root = document) {
        root.querySelectorAll('.crm-student-ac').forEach(initWidget);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initAll());
    } else {
        initAll();
    }
})();
</script>

<style>
    .crm-student-ac-menu .list-group-item-action { cursor: pointer; }
    .crm-student-ac-menu .list-group-item-action:hover { background: #f8f9fa; }
</style>
@endonce
