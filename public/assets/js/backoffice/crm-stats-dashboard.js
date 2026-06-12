'use strict';

(function () {
    const d = document.getElementById('crm-stats-dashboard-config');
    if (!d) return;
    const { encRangeEndpoint, recRangeEndpoint, storeId } = JSON.parse(d.textContent);

    const COLORS = ['#4680ff','#1cc88a','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14'];
    let encChart = null;
    let recChart = null;
    const recForm = document.getElementById('rec-range-form');
    const recButton = document.getElementById('rec-range-btn');
    const recButtonLabel = recButton?.querySelector('.rec-range-btn-label');
    const recResults = document.getElementById('rec-range-results');

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

    // ── Encaissement range (kept for backwards compat but hidden) ──────
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
            fetchEnc();
        });
    });
    document.getElementById('enc-range-btn')?.addEventListener('click', fetchEnc);

    function fetchEnc() {
        const start = document.getElementById('enc-start-date')?.value;
        const end   = document.getElementById('enc-end-date')?.value;
        if (!start || !end) return;
        setState('enc', 'loading');
        const params = new URLSearchParams({ startDate: start, endDate: end });
        if (storeId) params.set('strStoreId', storeId);
        fetch(`${encRangeEndpoint}?${params}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(json => {
                setState('enc', 'idle');
                if (json.error || !json.data?.length) { setState('enc', json.error ? 'error' : 'empty', json.error); return; }
                renderEnc(json);
            })
            .catch(err => { setState('enc', 'error', 'Erreur réseau : ' + err.message); });
    }

    function renderEnc(json) {
        const { data, grand_total, grand_nb } = json;
        document.getElementById('enc-range-kpis').innerHTML = `
            <div class="col-sm-6 col-xl-3">
                <div class="card border-start border-primary border-3 h-100"><div class="card-body py-3">
                    <div class="text-muted small mb-1">Total encaissé</div>
                    <div class="fw-bold fs-5 text-primary">${fullDH(grand_total)}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-start border-success border-3 h-100"><div class="card-body py-3">
                    <div class="text-muted small mb-1">Nb paiements</div>
                    <div class="fw-bold fs-5 text-success">${grand_nb.toLocaleString('fr-MA')}</div>
                </div></div>
            </div>`;
        if (encChart) { encChart.destroy(); encChart = null; }
        encChart = new ApexCharts(document.getElementById('enc-range-chart'), {
            chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Encaissé', data: data.map(r => r.total) }],
            colors: data.map((_, i) => COLORS[i % COLORS.length]),
            plotOptions: { bar: { distributed: true, borderRadius: 5, columnWidth: '50%' } },
            dataLabels: { enabled: false },
            xaxis: { categories: data.map(r => r.store_name), axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { fontSize: '12px', fontWeight: 600 } } },
            yaxis: { labels: { formatter: fmtDH, style: { fontSize: '11px' } } },
            legend: { show: false },
            tooltip: { y: { formatter: v => fullDH(v) } },
        });
        encChart.render();
        const tbody = document.getElementById('enc-range-tbody');
        tbody.innerHTML = data.map((r, i) => {
            const pct = grand_total > 0 ? (r.total / grand_total * 100).toFixed(1) : 0;
            const color = COLORS[i % COLORS.length];
            return `<tr>
                <td class="text-muted">${i + 1}</td>
                <td><span class="badge" style="background:${color}20;color:${color};font-size:.8rem">${r.store_name}</span></td>
                <td class="text-end fw-semibold text-primary">${fullDH(r.total)}</td>
                <td class="text-end">${r.nb.toLocaleString('fr-MA')}</td>
                <td><div class="progress" style="height:6px"><div class="progress-bar" style="width:${pct}%;background:${color}"></div></div></td>
            </tr>`;
        }).join('');
        document.getElementById('enc-range-tfoot').innerHTML = `<tr>
            <td colspan="2">Total</td>
            <td class="text-end">${fullDH(grand_total)}</td>
            <td class="text-end">${grand_nb.toLocaleString('fr-MA')}</td>
            <td></td>
        </tr>`;
        document.getElementById('enc-range-snapshot').textContent = json.snapshot ? 'Snapshot : ' + json.snapshot : '';
        document.getElementById('enc-range-results').classList.remove('d-none');
    }

    // ── Recouvrement range ─────────────────────────────────────────────
    document.querySelectorAll('.rec-preset').forEach(btn => {
        btn.addEventListener('click', function () {
            const today = new Date();
            let s, e;
            switch (this.dataset.preset) {
                case 'today': s = e = today; break;
                case '7d':    s = new Date(today); s.setDate(today.getDate() - 6); e = today; break;
                case '30d':   s = new Date(today); s.setDate(today.getDate() - 29); e = today; break;
                case 'month': s = new Date(today.getFullYear(), today.getMonth(), 1); e = today; break;
            }
            document.getElementById('rec-start-date').value = toIso(s);
            document.getElementById('rec-end-date').value   = toIso(e);
            document.querySelectorAll('.rec-preset').forEach(b => {
                b.classList.remove('active', 'btn-warning');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('active', 'btn-warning');
            fetchRec({ manual: true });
        });
    });
    recForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        fetchRec({ manual: true });
    });
    if (!recForm) {
        recButton?.addEventListener('click', function () {
            fetchRec({ manual: true });
        });
    }

    // Auto-load current month on page load
    (function autoLoad() {
        const today = new Date();
        const s = new Date(today.getFullYear(), today.getMonth(), 1);
        document.getElementById('rec-start-date').value = toIso(s);
        document.getElementById('rec-end-date').value   = toIso(today);
        fetchRec({ manual: false });
    })();

    function fetchRec(options = {}) {
        const { manual = false } = options;
        const start = document.getElementById('rec-start-date').value;
        const end   = document.getElementById('rec-end-date').value;
        if (!start || !end) { setState('rec', 'error', 'Veuillez choisir une date de début et de fin.'); return; }
        if (start > end)    { setState('rec', 'error', 'La date de début doit être ≤ la date de fin.'); return; }

        setState('rec', 'loading');
        setRecButtonLoading(true);

        const params = new URLSearchParams({ startDate: start, endDate: end });
        if (storeId) params.set('strStoreId', storeId);

        fetch(`${recRangeEndpoint}?${params}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(json => {
                setState('rec', 'idle');
                if (json.error)               { setState('rec', 'error', json.error); return; }
                if (!json.data?.length)        { setState('rec', 'empty'); return; }
                renderRec(json);
                if (manual) {
                    recResults?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            })
            .catch(err => { setState('rec', 'error', 'Erreur réseau : ' + err.message); });
    }

    function renderRec(json) {
        const { data, grand_reste, grand_ca, grand_cnt } = json;

        // KPI cards
        document.getElementById('rec-range-kpis').innerHTML = `
            <div class="col-sm-6 col-xl-3">
                <div class="card border-start border-warning border-3 h-100"><div class="card-body py-3">
                    <div class="text-muted small mb-1">Reste à payer</div>
                    <div class="fw-bold fs-5 text-warning">${fullDH(grand_reste)}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-start border-primary border-3 h-100"><div class="card-body py-3">
                    <div class="text-muted small mb-1">CA total (échéances)</div>
                    <div class="fw-bold fs-5 text-primary">${fullDH(grand_ca)}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-start border-danger border-3 h-100"><div class="card-body py-3">
                    <div class="text-muted small mb-1">Taux impayé</div>
                    <div class="fw-bold fs-5 text-danger">${grand_ca > 0 ? (grand_reste / grand_ca * 100).toFixed(1) : 0}%</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-start border-secondary border-3 h-100"><div class="card-body py-3">
                    <div class="text-muted small mb-1">Nb échéances impayées</div>
                    <div class="fw-bold fs-5 text-secondary">${grand_cnt.toLocaleString('fr-MA')}</div>
                </div></div>
            </div>`;

        // Chart — two series: reste + collecté
        if (recChart) { recChart.destroy(); recChart = null; }
        recChart = new ApexCharts(document.getElementById('rec-range-chart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit', stacked: false },
            series: [
                { name: 'Reste à payer', data: data.map(r => +r.total_reste.toFixed(2)) },
                { name: 'CA total',      data: data.map(r => +r.total_ca.toFixed(2)) },
            ],
            colors: ['#ffc107', '#4680ff'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '60%', dataLabels: { position: 'top' } } },
            dataLabels: { enabled: false },
            xaxis: {
                categories: data.map(r => r.store_name),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { fontSize: '12px', fontWeight: 600 } },
            },
            yaxis: { labels: { formatter: fmtDH, style: { fontSize: '11px' } } },
            legend: { position: 'bottom', fontSize: '13px' },
            tooltip: { shared: true, intersect: false, y: { formatter: v => fullDH(v) } },
        });
        recChart.render();

        // Table
        document.getElementById('rec-range-tbody').innerHTML = data.map((r, i) => {
            const pct   = grand_reste > 0 ? (r.total_reste / grand_reste * 100).toFixed(1) : 0;
            const color = COLORS[i % COLORS.length];
            return `<tr>
                <td class="text-muted">${i + 1}</td>
                <td><span class="badge" style="background:${color}20;color:${color};font-size:.8rem">${r.store_name}</span></td>
                <td class="text-end fw-semibold text-warning">${fullDH(r.total_reste)}</td>
                <td class="text-end text-primary">${fullDH(r.total_ca)}</td>
                <td class="text-end">${r.cnt.toLocaleString('fr-MA')}</td>
                <td><div class="progress" style="height:6px"><div class="progress-bar bg-warning" style="width:${pct}%"></div></div></td>
            </tr>`;
        }).join('');
        document.getElementById('rec-range-tfoot').innerHTML = `<tr>
            <td colspan="2">Total</td>
            <td class="text-end text-warning">${fullDH(grand_reste)}</td>
            <td class="text-end text-primary">${fullDH(grand_ca)}</td>
            <td class="text-end">${grand_cnt.toLocaleString('fr-MA')}</td>
            <td></td>
        </tr>`;

        document.getElementById('rec-range-snapshot').textContent = json.snapshot ? 'Snapshot : ' + json.snapshot : '';
        document.getElementById('rec-range-results').classList.remove('d-none');
    }

    // ── State helpers ──────────────────────────────────────────────────
    function setState(prefix, state, msg) {
        ['loading','error','results','empty'].forEach(s =>
            document.getElementById(`${prefix}-range-${s}`)?.classList.add('d-none'));
        if (prefix === 'rec' && state !== 'loading') {
            setRecButtonLoading(false);
        }
        if (prefix === 'rec') {
            if (recChart) { recChart.destroy(); recChart = null; }
            document.getElementById('rec-range-kpis').innerHTML  = '';
            document.getElementById('rec-range-tbody').innerHTML = '';
            document.getElementById('rec-range-tfoot').innerHTML = '';
        } else {
            if (encChart) { encChart.destroy(); encChart = null; }
            document.getElementById('enc-range-kpis').innerHTML  = '';
            document.getElementById('enc-range-tbody').innerHTML = '';
            document.getElementById('enc-range-tfoot').innerHTML = '';
        }
        if (state === 'loading') {
            document.getElementById(`${prefix}-range-loading`).classList.remove('d-none');
        } else if (state === 'error') {
            const el = document.getElementById(`${prefix}-range-error`);
            el.textContent = msg || 'Erreur inconnue.';
            el.classList.remove('d-none');
        } else if (state === 'empty') {
            document.getElementById(`${prefix}-range-empty`).classList.remove('d-none');
        }
    }

    function setRecButtonLoading(isLoading) {
        if (!recButton) return;
        recButton.disabled = isLoading;
        recButton.setAttribute('aria-busy', isLoading ? 'true' : 'false');
        if (recButtonLabel) {
            recButtonLabel.textContent = isLoading ? 'Chargement...' : 'Afficher';
        }
    }
})();
