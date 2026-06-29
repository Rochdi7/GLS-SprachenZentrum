'use strict';
(function () {
    if (typeof ApexCharts === 'undefined') return;

    const d = document.getElementById('crm-comparaison-data');
    if (!d) return;
    const { endpoint, storeColors } = JSON.parse(d.textContent);
    const COLORS = storeColors;

    let chartLine = null, chartBar = null, chartPie = null;
    let currentData = null;
    let chartType = 'line';

    const medals = ['🥇','🥈','🥉'];

    function fmtDH(v) {
        if (v >= 1e6) return (v / 1e6).toFixed(2).replace(/\.?0+$/, '') + ' M DH';
        if (v >= 1e3) return (v / 1e3).toFixed(1).replace('.0', '') + ' k DH';
        return v.toLocaleString('fr-MA') + ' DH';
    }
    function fullDH(v) { return new Intl.NumberFormat('fr-MA', { minimumFractionDigits: 2 }).format(v) + ' DH'; }

    document.querySelectorAll('[data-ct]').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('[data-ct]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            chartType = this.dataset.ct;
            if (currentData) renderLineChart(currentData);
        });
    });

    document.getElementById('comp-search').addEventListener('click', doFetch);

    function doFetch() {
        const start  = document.getElementById('comp-start').value;
        const end    = document.getElementById('comp-end').value;
        const stores = [...document.querySelectorAll('.store-cb:checked')].map(c => c.value);
        if (!start || !end || !stores.length) return;

        const params = new URLSearchParams({ startDate: start, endDate: end, groupBy: 'month' });
        stores.forEach(s => params.append('stores[]', s));
        setState('loading');

        window.fetch(`${endpoint}?${params}`)
            .then(r => r.json())
            .then(data => {
                if (data.error) { setState('error', data.error); return; }
                currentData = data;
                if (!Object.keys(data.stores).length) { setState('empty'); return; }
                setState('results');
                renderKpis(data);
                renderLineChart(data);
                renderBarChart(data);
                renderPieChart(data);
                renderRankTable(data);
            })
            .catch(e => setState('error', e.message));
    }

    function setState(s, msg) {
        ['comp-loading','comp-error','comp-results','comp-empty'].forEach(id =>
            document.getElementById(id).classList.add('d-none'));
        if (s === 'loading')  document.getElementById('comp-loading').classList.remove('d-none');
        if (s === 'error')  { document.getElementById('comp-error').textContent = msg; document.getElementById('comp-error').classList.remove('d-none'); }
        if (s === 'results')  document.getElementById('comp-results').classList.remove('d-none');
        if (s === 'empty')    document.getElementById('comp-empty').classList.remove('d-none');
    }

    function renderKpis(data) {
        const entries    = Object.entries(data.stores);
        const grandTotal = entries.reduce((s, [,v]) => s + v.total, 0);
        const grandNb    = entries.reduce((s, [,v]) => s + v.nb, 0);
        const top        = entries.reduce((best, curr) => curr[1].total > best[1].total ? curr : best, entries[0]);
        const kpis = [
            { label: 'Total encaissé',  val: fullDH(grandTotal),  color: '#4680ff' },
            { label: 'Nb paiements',    val: grandNb.toLocaleString('fr-MA'), color: '#1cc88a' },
            { label: 'Centres actifs',  val: entries.length,      color: '#0dcaf0' },
            { label: 'Meilleur centre', val: top ? `${top[1].name} — ${fmtDH(top[1].total)}` : '—', color: '#ffc107' },
        ];
        document.getElementById('comp-kpis').innerHTML = kpis.map(k => `
            <div class="col-sm-6 col-xl-3">
                <div class="kpi-box" style="border-color:${k.color}">
                    <div class="kpi-label">${k.label}</div>
                    <p class="kpi-val" style="color:${k.color}">${k.val}</p>
                </div>
            </div>`).join('');
    }

    const STORE_COLOR_CACHE = {};
    let colorIdx = 0;
    function buildColorMap(ids) {
        ids.forEach(id => { if (!STORE_COLOR_CACHE[id]) STORE_COLOR_CACHE[id] = COLORS[colorIdx++ % COLORS.length]; });
        return STORE_COLOR_CACHE;
    }

    function renderLineChart(data) {
        if (chartLine) { chartLine.destroy(); chartLine = null; }
        const entries  = Object.entries(data.stores);
        const colorMap = buildColorMap(entries.map(([id]) => id));
        const isBar    = chartType === 'bar';
        const series   = entries.map(([sid, info]) => ({
            name: info.name,
            data: data.periods.map(p => data.pivot[sid]?.[p]?.total ?? 0),
        }));
        const base = {
            series,
            colors: entries.map(([id]) => colorMap[id]),
            xaxis: { categories: data.periods, labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { formatter: v => fmtDH(v) } },
            tooltip: { y: { formatter: v => fullDH(v) } },
            legend: { position: 'top' },
            grid: { borderColor: '#f0f0f0' },
            dataLabels: { enabled: false },
        };
        const opts = isBar
            ? { ...base, chart: { type: 'bar', height: 380, toolbar: { show: false } }, plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } } }
            : { ...base, chart: { type: 'line', height: 380, toolbar: { show: false } }, stroke: { curve: 'smooth', width: 3 }, markers: { size: data.periods.length <= 2 ? 6 : 4, hover: { size: 8 } }, yaxis: { labels: { formatter: v => fmtDH(v) }, min: 0 } };
        chartLine = new ApexCharts(document.getElementById('chartLine'), opts);
        chartLine.render();
    }

    function renderBarChart(data) {
        if (chartBar) { chartBar.destroy(); chartBar = null; }
        const entries  = Object.entries(data.stores);
        const colorMap = buildColorMap(entries.map(([id]) => id));
        chartBar = new ApexCharts(document.getElementById('chartBar'), {
            series: [{ name: 'Total', data: entries.map(([,v]) => v.total) }],
            chart: { type: 'bar', height: 380, toolbar: { show: false } },
            plotOptions: { bar: { horizontal: true, borderRadius: 4, dataLabels: { position: 'top' } } },
            colors: entries.map(([id]) => colorMap[id]),
            xaxis: { categories: entries.map(([,v]) => v.name), labels: { formatter: v => fmtDH(v) } },
            tooltip: { y: { formatter: v => fullDH(v) } },
            dataLabels: { enabled: true, formatter: v => fmtDH(v), offsetX: 8, style: { fontSize: '11px' } },
            legend: { show: false },
            grid: { borderColor: '#f0f0f0' },
        });
        chartBar.render();
    }

    function renderPieChart(data) {
        if (chartPie) { chartPie.destroy(); chartPie = null; }
        const entries  = Object.entries(data.stores);
        const colorMap = buildColorMap(entries.map(([id]) => id));
        chartPie = new ApexCharts(document.getElementById('chartPie'), {
            series: entries.map(([,v]) => v.total),
            labels: entries.map(([,v]) => v.name),
            colors: entries.map(([id]) => colorMap[id]),
            chart: { type: 'donut', height: 320 },
            tooltip: { y: { formatter: v => fullDH(v) } },
            legend: { position: 'bottom', fontSize: '12px' },
            dataLabels: { formatter: val => val.toFixed(1) + '%' },
            plotOptions: { pie: { donut: { size: '55%' } } },
        });
        chartPie.render();
    }

    function renderRankTable(data) {
        const entries  = Object.entries(data.stores).sort((a, b) => b[1].total - a[1].total);
        const colorMap = buildColorMap(entries.map(([id]) => id));
        document.getElementById('comp-rank-tbody').innerHTML = entries.map(([id, info], i) => {
            return `<tr>
                <td class="text-center fw-bold">${medals[i] ?? (i + 1)}</td>
                <td><span class="fw-semibold" style="color:${colorMap[id]}">${info.name}</span></td>
                <td class="text-end fw-semibold">${fullDH(info.total)}</td>
                <td class="text-end text-muted">${info.nb.toLocaleString('fr-MA')}</td>
            </tr>`;
        }).join('');
    }

    doFetch();
})();
