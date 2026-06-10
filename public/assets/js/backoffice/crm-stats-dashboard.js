'use strict';
// Data is injected inline from the blade via @json() server-side rendering.
// This file only contains the enc-range fetch logic that has no PHP data deps.
// The ApexCharts rendering for encaissement/inscriptions stays in the blade
// since it uses conditional @json() server-rendered data islands.

(function () {
    const d = document.getElementById('crm-stats-dashboard-config');
    if (!d) return;
    const { encRangeEndpoint, storeId } = JSON.parse(d.textContent);

    const COLORS = ['#4680ff','#1cc88a','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14'];
    let rangeChart = null;

    function fmtDH(v) {
        if (v >= 1e6) return (v / 1e6).toFixed(2).replace(/\.?0+$/, '') + ' M DH';
        if (v >= 1e3) return (v / 1e3).toFixed(1).replace('.0', '') + ' k DH';
        return v.toLocaleString('fr-MA') + ' DH';
    }
    function fullDH(v) {
        return new Intl.NumberFormat('fr-MA', { minimumFractionDigits: 2 }).format(v) + ' DH';
    }
    function pad(n)   { return String(n).padStart(2, '0'); }
    function toIso(d) { return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`; }

    document.querySelectorAll('.enc-preset').forEach(btn => {
        btn.addEventListener('click', function () {
            const today = new Date();
            let s, e;
            switch (this.dataset.preset) {
                case 'today': s = e = today; break;
                case '7d':    s = new Date(today); s.setDate(today.getDate() - 6); e = today; break;
                case '30d':   s = new Date(today); s.setDate(today.getDate() - 29); e = today; break;
                case 'month': s = new Date(today.getFullYear(), today.getMonth(), 1); e = today; break;
            }
            document.getElementById('enc-start-date').value = toIso(s);
            document.getElementById('enc-end-date').value   = toIso(e);
            document.querySelectorAll('.enc-preset').forEach(b => {
                b.classList.remove('active', 'btn-primary');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('active', 'btn-primary');
            fetchRange();
        });
    });

    document.getElementById('enc-range-btn')?.addEventListener('click', fetchRange);

    function fetchRange() {
        const start = document.getElementById('enc-start-date').value;
        const end   = document.getElementById('enc-end-date').value;
        if (!start || !end) { showError('Veuillez choisir une date de début et de fin.'); return; }
        if (start > end)    { showError('La date de début doit être ≤ la date de fin.'); return; }

        setLoading(true);
        clearResults();

        const params = new URLSearchParams({ startDate: start, endDate: end });
        if (storeId) params.set('strStoreId', storeId);

        fetch(`${encRangeEndpoint}?${params}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(json => {
                setLoading(false);
                if (json.error)                               { showError(json.error); return; }
                if (!json.data || json.data.length === 0)     { document.getElementById('enc-range-empty').classList.remove('d-none'); return; }
                renderRange(json);
            })
            .catch(err => { setLoading(false); showError('Erreur réseau : ' + err.message); });
    }

    function renderRange(json) {
        const { data, grand_total, grand_nb } = json;

        // KPI cards
        document.getElementById('enc-range-kpis').innerHTML = `
            <div class="col-sm-6 col-xl-3">
                <div class="card border-start border-primary border-3 h-100">
                    <div class="card-body py-3">
                        <div class="text-muted small mb-1">Total encaissé</div>
                        <div class="fw-bold fs-5 text-primary">${fullDH(grand_total)}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-start border-success border-3 h-100">
                    <div class="card-body py-3">
                        <div class="text-muted small mb-1">Nb paiements</div>
                        <div class="fw-bold fs-5 text-success">${grand_nb.toLocaleString('fr-MA')}</div>
                    </div>
                </div>
            </div>`;

        // Chart
        if (rangeChart) { rangeChart.destroy(); rangeChart = null; }
        rangeChart = new ApexCharts(document.getElementById('enc-range-chart'), {
            chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Encaissé', data: data.map(r => r.total) }],
            colors: data.map((_, i) => COLORS[i % COLORS.length]),
            plotOptions: { bar: { distributed: true, borderRadius: 5, columnWidth: '50%' } },
            dataLabels: { enabled: false },
            xaxis: {
                categories: data.map(r => r.store_name),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { fontSize: '12px', fontWeight: 600 } },
            },
            yaxis: { labels: { formatter: fmtDH, style: { fontSize: '11px' } } },
            legend: { show: false },
            tooltip: { y: { formatter: v => new Intl.NumberFormat('fr-MA', { minimumFractionDigits: 2 }).format(v) + ' DH' } },
        });
        rangeChart.render();

        // Table
        const tbody = document.getElementById('enc-range-tbody');
        const tfoot = document.getElementById('enc-range-tfoot');
        tbody.innerHTML = data.map((r, i) => {
            const pct   = grand_total > 0 ? (r.total / grand_total * 100).toFixed(1) : 0;
            const color = COLORS[i % COLORS.length];
            return `<tr>
                <td class="text-muted">${i + 1}</td>
                <td><span class="badge" style="background:${color}20;color:${color};font-size:.8rem">${r.store_name}</span></td>
                <td class="text-end fw-semibold text-primary">${new Intl.NumberFormat('fr-MA', {minimumFractionDigits:2}).format(r.total)} DH</td>
                <td class="text-end">${r.nb.toLocaleString('fr-MA')}</td>
                <td><div class="progress" style="height:6px"><div class="progress-bar" style="width:${pct}%;background:${color}"></div></div></td>
            </tr>`;
        }).join('');
        tfoot.innerHTML = `<tr>
            <td colspan="2">Total</td>
            <td class="text-end">${new Intl.NumberFormat('fr-MA',{minimumFractionDigits:2}).format(grand_total)} DH</td>
            <td class="text-end">${grand_nb.toLocaleString('fr-MA')}</td>
            <td></td>
        </tr>`;

        document.getElementById('enc-range-snapshot').textContent = json.snapshot ? 'Snapshot : ' + json.snapshot : '';
        document.getElementById('enc-range-results').classList.remove('d-none');
    }

    function setLoading(on) {
        document.getElementById('enc-range-loading').classList.toggle('d-none', !on);
    }

    function clearResults() {
        ['enc-range-results','enc-range-empty','enc-range-error'].forEach(id =>
            document.getElementById(id).classList.add('d-none'));
        document.getElementById('enc-range-kpis').innerHTML  = '';
        document.getElementById('enc-range-tbody').innerHTML = '';
        document.getElementById('enc-range-tfoot').innerHTML = '';
        if (rangeChart) { rangeChart.destroy(); rangeChart = null; }
    }

    function showError(msg) {
        const el = document.getElementById('enc-range-error');
        el.textContent = msg;
        el.classList.remove('d-none');
    }
})();
