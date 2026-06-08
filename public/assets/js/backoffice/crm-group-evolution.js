/*
 * CRM — Évolution par groupe page.
 * Loaded by resources/views/backoffice/crm/group-evolution.blade.php.
 *
 * Reads its chart payload from a JSON data island:
 *   <script type="application/json" id="crm-group-evolution-data">…</script>
 */
(function initGroupEvolutionChart() {
    const dataEl = document.getElementById('crm-group-evolution-data');
    const wrap   = document.getElementById('groupEvolutionChart');
    if (!dataEl || !wrap) return;

    let groups;
    try { groups = JSON.parse(dataEl.textContent); } catch (e) { return; }
    if (!Array.isArray(groups) || groups.length === 0) {
        wrap.innerHTML = '<div class="text-center text-muted py-5"><i class="ph-duotone ph-chart-bar" style="font-size:3rem;opacity:.3;"></i><p class="mt-2 small">Aucun groupe à afficher</p></div>';
        return;
    }

    const SERIES = [
        { key: 'debuts',      label: 'Début',      color: '#6f42c1' },
        { key: 'ajouts',      label: 'Ajouts',     color: '#28a745' },
        { key: 'quittants',   label: 'Quittant',   color: '#dc3545' },
        { key: 'changements', label: 'Changement', color: '#fd7e14' },
        { key: 'actifs',      label: 'Actifs',     color: '#2196f3' },
    ];

    // If ApexCharts is available, use it (reliable resize, no canvas width issues)
    if (typeof ApexCharts !== 'undefined') {
        renderApex(groups);
        return;
    }

    // Fallback: pure HTML horizontal bar chart (no canvas dependency)
    renderHtmlBars(groups);

    function renderApex(groups) {
        const categories = groups.map(g => g.name.length > 20 ? g.name.slice(0, 20) + '…' : g.name);
        const series = SERIES.map(s => ({
            name: s.label,
            data: groups.map(g => g[s.key] || 0),
            color: s.color,
        }));

        new ApexCharts(wrap, {
            chart: {
                type: 'bar',
                height: 400,
                toolbar: { show: false },
                animations: { enabled: true, speed: 400 },
            },
            series,
            xaxis: {
                categories,
                labels: {
                    rotate: -40,
                    rotateAlways: groups.length > 5,
                    trim: true,
                    style: { fontSize: '11px', colors: '#6c757d' },
                },
            },
            yaxis: {
                labels: { style: { fontSize: '11px', colors: '#6c757d' } },
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: groups.length <= 3 ? '40%' : groups.length <= 6 ? '60%' : '80%',
                    borderRadius: 3,
                    dataLabels: { position: 'top' },
                },
            },
            dataLabels: {
                enabled: groups.length <= 6,
                offsetY: -18,
                style: { fontSize: '9px', colors: ['#374151'], fontWeight: 600 },
                formatter: v => v === 0 ? '' : v,
            },
            legend: { show: false }, // legend already rendered in Blade
            tooltip: {
                shared: true,
                intersect: false,
                y: { formatter: v => v + ' élève(s)' },
            },
            grid: {
                borderColor: '#eef0f3',
                strokeDashArray: 3,
            },
            colors: SERIES.map(s => s.color),
        }).render();
    }

    function renderHtmlBars(groups) {
        const maxVal = Math.max(1, ...groups.flatMap(g => SERIES.map(s => g[s.key] || 0)));
        let html = '<div style="overflow-x:auto;padding:8px 0">';
        groups.forEach(g => {
            html += `<div style="margin-bottom:18px">
                <div style="font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${g.name}</div>`;
            SERIES.forEach(s => {
                const val = g[s.key] || 0;
                const pct = Math.round(val / maxVal * 100);
                html += `<div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
                    <div style="width:80px;font-size:.72rem;color:#6c757d;text-align:right;flex-shrink:0">${s.label}</div>
                    <div style="flex:1;background:#f0f0f0;border-radius:4px;height:14px;overflow:hidden">
                        <div style="width:${pct}%;height:100%;background:${s.color};border-radius:4px;transition:width .4s"></div>
                    </div>
                    <div style="width:28px;font-size:.72rem;font-weight:700;color:${s.color};text-align:right;flex-shrink:0">${val}</div>
                </div>`;
            });
            html += '</div>';
        });
        html += '</div>';
        wrap.innerHTML = html;
    }
})();

// ─────────────────── Group multi-select behaviour ───────────────────
(function initGroupMultiSelect() {
    const hidden  = document.getElementById('geClassIdsInput');
    const allCbs  = () => Array.from(document.querySelectorAll('.ge-group-cb'));
    const list    = document.getElementById('geGroupList');
    const search  = document.getElementById('geGroupFilter');
    const btnAll  = document.getElementById('geSelectAll');
    const btnNone = document.getElementById('geSelectNone');
    if (!hidden || !list) return;

    const sync = () => {
        const boxes = allCbs();
        const checked = boxes.filter(b => b.checked).map(b => b.value);
        hidden.value = (checked.length === 0 || checked.length === boxes.length)
            ? ''
            : checked.join(',');
    };

    list.addEventListener('change', e => {
        if (e.target.classList.contains('ge-group-cb')) sync();
    });

    btnAll?.addEventListener('click',  () => { allCbs().forEach(b => b.checked = true);  sync(); });
    btnNone?.addEventListener('click', () => { allCbs().forEach(b => b.checked = false); sync(); });

    search?.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        document.querySelectorAll('.ge-group-item').forEach(item => {
            const name = item.querySelector('.ge-group-name')?.textContent.toLowerCase() ?? '';
            item.style.display = (!q || name.includes(q)) ? '' : 'none';
        });
    });

    sync();
})();
