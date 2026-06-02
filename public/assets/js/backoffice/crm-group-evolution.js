/*
 * CRM — Évolution par groupe page.
 * Loaded by resources/views/backoffice/crm/group-evolution.blade.php.
 *
 * Reads its chart payload from a JSON data island:
 *   <script type="application/json" id="crm-group-evolution-data">…</script>
 */
document.addEventListener('DOMContentLoaded', function () {
    const dataEl = document.getElementById('crm-group-evolution-data');
    const el     = document.getElementById('groupEvolutionChart');
    if (!dataEl || !el || typeof ApexCharts === 'undefined') return;

    let groups;
    try {
        groups = JSON.parse(dataEl.textContent);
    } catch (err) {
        console.error('[CRM group-evolution] failed to parse chart data', err);
        return;
    }
    if (!Array.isArray(groups) || groups.length === 0) return;

    new ApexCharts(el, {
        chart: { type: 'bar', height: 420, toolbar: { show: false }, animations: { speed: 350 } },
        plotOptions: { bar: { columnWidth: '70%', borderRadius: 3, dataLabels: { position: 'top' } } },
        dataLabels: {
            enabled: true, offsetY: -18,
            style: { fontSize: '10px', colors: ['#495057'], fontWeight: 600 },
            formatter: v => v > 0 ? v : '',
        },
        stroke: { show: true, width: 1, colors: ['transparent'] },
        series: [
            { name: 'Début',       data: groups.map(g => g.debuts) },
            { name: 'Ajouts',      data: groups.map(g => g.ajouts) },
            { name: 'Quittant',    data: groups.map(g => g.quittants) },
            { name: 'Changement',  data: groups.map(g => g.changements) },
            { name: 'Actifs',      data: groups.map(g => g.actifs) },
        ],
        colors: ['#6f42c1', '#28a745', '#dc3545', '#fd7e14', '#2196f3'],
        xaxis: {
            categories: groups.map(g => g.name),
            labels: { rotate: -35, style: { fontSize: '11px' } },
        },
        yaxis: { title: { text: "Nombre d'élèves" }, decimalsInFloat: 0 },
        legend: { show: false }, // legend rendered manually above the chart
        grid: { borderColor: '#eef0f3' },
        tooltip: { y: { formatter: v => v + ' élève(s)' } },
    }).render();
});

// ─────────────────── Group multi-select behaviour ───────────────────
document.addEventListener('DOMContentLoaded', function () {
    const hidden  = document.getElementById('geClassIdsInput');
    const allCbs  = () => Array.from(document.querySelectorAll('.ge-group-cb'));
    const list    = document.getElementById('geGroupList');
    const search  = document.getElementById('geGroupFilter');
    const btnAll  = document.getElementById('geSelectAll');
    const btnNone = document.getElementById('geSelectNone');
    if (!hidden || !list) return;

    // Sync the hidden field. Empty = "show all" (server-side default).
    // If every box is ticked we also store empty to keep URLs short.
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

    // Live search filter inside the dropdown.
    search?.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        document.querySelectorAll('.ge-group-item').forEach(item => {
            const name = item.querySelector('.ge-group-name')?.textContent.toLowerCase() ?? '';
            item.style.display = (!q || name.includes(q)) ? '' : 'none';
        });
    });

    sync();
});
