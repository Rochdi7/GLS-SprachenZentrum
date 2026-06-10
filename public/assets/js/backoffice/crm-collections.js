'use strict';
(function () {

    const toastEl = document.getElementById('liveToast');
    if (toastEl) new bootstrap.Toast(toastEl).show();

    // ── Drill-down modal ─────────────────────────────────────────────────────
    const d = document.getElementById('crm-collections-data');
    if (!d) return;
    const { drillUrl, storeId } = JSON.parse(d.textContent);

    let allRows = [];

    document.querySelectorAll('.drill-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const type  = this.dataset.type;
            const label = this.dataset.label;
            document.getElementById('drillModalTitle').textContent = label;
            document.getElementById('drillModalCount').textContent = '';
            document.getElementById('drillModalTotal').textContent = '';
            document.getElementById('drillLoading').classList.remove('d-none');
            document.getElementById('drillContent').classList.add('d-none');
            document.getElementById('drillEmpty').classList.add('d-none');
            document.getElementById('drillSearch').value = '';
            new bootstrap.Modal(document.getElementById('drillModal')).show();

            const params = new URLSearchParams({ type });
            if (storeId) params.set('strStoreId', storeId);
            fetch(drillUrl + '?' + params)
                .then(r => r.json())
                .then(data => {
                    allRows = data.rows;
                    document.getElementById('drillLoading').classList.add('d-none');
                    document.getElementById('drillModalCount').textContent = data.count + ' dossier(s)';
                    document.getElementById('drillModalTotal').textContent = new Intl.NumberFormat('fr-MA').format(data.total) + ' DH';
                    if (data.count === 0) {
                        document.getElementById('drillEmpty').classList.remove('d-none');
                    } else {
                        document.getElementById('drillContent').classList.remove('d-none');
                        renderDrillRows(allRows);
                    }
                });
        });
    });

    document.getElementById('drillSearch').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        renderDrillRows(allRows.filter(r =>
            (r.student_name || '').toLowerCase().includes(q) ||
            (r.store_name   || '').toLowerCase().includes(q)
        ));
    });

    function renderDrillRows(rows) {
        document.getElementById('drillTbody').innerHTML = rows.map((r, i) => {
            const overdueClass = r.overdue_days > 90 ? 'bg-dark'
                : r.overdue_days > 60 ? 'bg-danger'
                : r.overdue_days > 30 ? 'bg-warning text-dark'
                : r.overdue_days > 0  ? 'bg-secondary'
                : 'bg-light-success text-success';
            const overdueLabel = r.overdue_days > 0 ? r.overdue_days + ' j' : 'À jour';
            const dueDate = r.due_date
                ? new Date(r.due_date).toLocaleDateString('fr-MA', { day: '2-digit', month: '2-digit', year: 'numeric' })
                : '—';
            return `<tr>
                <td class="text-muted">${i + 1}</td>
                <td><strong>${r.student_name}</strong><br><small class="text-muted">#${r.student_id}</small></td>
                <td><span class="badge bg-light-primary">${r.store_name}</span></td>
                <td><small>${dueDate}</small></td>
                <td><span class="badge ${overdueClass}">${overdueLabel}</span></td>
                <td class="text-end fw-semibold text-danger">${new Intl.NumberFormat('fr-MA').format(r.amount)} DH</td>
            </tr>`;
        }).join('');
    }

    // ── Pagination: Upcoming Dues ────────────────────────────────────────────
    (function () {
        const tbody = document.getElementById('duesTbody');
        if (!tbody) return;
        const allRows = Array.from(tbody.querySelectorAll('tr'));
        let filtered  = allRows;
        let page      = 1;
        const size    = 10;

        function pages()  { return Math.max(1, Math.ceil(filtered.length / size)); }
        function render() {
            const start = (page - 1) * size;
            allRows.forEach(r => r.style.display = 'none');
            filtered.slice(start, start + size).forEach(r => r.style.display = '');
            document.getElementById('duesPages').textContent = `Page ${page} / ${pages()}`;
            document.getElementById('duesInfo').textContent  = `${filtered.length} résultat(s)`;
            document.getElementById('duesPrev').disabled = page === 1;
            document.getElementById('duesNext').disabled = page === pages();
        }
        document.getElementById('duesPrev').addEventListener('click', () => { if (page > 1) { page--; render(); } });
        document.getElementById('duesNext').addEventListener('click', () => { if (page < pages()) { page++; render(); } });
        document.getElementById('duesSearch').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            filtered = allRows.filter(r => r.textContent.toLowerCase().includes(q));
            page = 1; render();
        });
        render();
    })();

    // ── CA Chart ─────────────────────────────────────────────────────────────
    (function () {
        if (typeof ApexCharts === 'undefined') return;

        const { caEndpoint } = JSON.parse(document.getElementById('crm-collections-data').textContent);
        const COLORS = ['#1cc88a','#4680ff','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14'];
        let caChart  = null;

        function pad(n)  { return String(n).padStart(2, '0'); }
        function toIso(d) { return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`; }
        function fmtDH(v) {
            if (v >= 1e6) return (v / 1e6).toFixed(1).replace('.0', '') + ' M';
            if (v >= 1e3) return (v / 1e3).toFixed(0) + ' k';
            return v > 0 ? String(v) : '';
        }
        function fullDH(v) { return new Intl.NumberFormat('fr-MA', { minimumFractionDigits: 2 }).format(v) + ' DH'; }
        function monthLabel(p) {
            if (/^\d{4}-\d{2}$/.test(p)) {
                const [y, m] = p.split('-');
                return new Date(y, m - 1).toLocaleDateString('fr-FR', { month: 'short', year: '2-digit' });
            }
            return p;
        }

        function applyPreset(preset) {
            const today = new Date();
            let s, e;
            switch (preset) {
                case '2m':   s = new Date(today.getFullYear(), today.getMonth() - 1, 1); e = new Date(today.getFullYear(), today.getMonth() + 1, 0); break;
                case '6m':   s = new Date(today.getFullYear(), today.getMonth() - 5, 1); e = new Date(today.getFullYear(), today.getMonth() + 1, 0); break;
                case 'year': s = new Date(today.getFullYear(), 0, 1); e = new Date(today.getFullYear(), 11, 31); break;
            }
            document.getElementById('ca-start').value = toIso(s);
            document.getElementById('ca-end').value   = toIso(e);
        }

        applyPreset('2m');

        document.querySelectorAll('.ca-preset').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.ca-preset').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                applyPreset(this.dataset.preset);
                fetchCA();
            });
        });

        document.getElementById('ca-fetch-btn').addEventListener('click', fetchCA);

        function fetchCA() {
            const start = document.getElementById('ca-start').value;
            const end   = document.getElementById('ca-end').value;
            if (!start || !end) return;

            document.getElementById('ca-loading').classList.remove('d-none');
            document.getElementById('ca-error').classList.add('d-none');
            document.getElementById('ca-chart-wrap').style.opacity = '0.3';
            document.getElementById('ca-total-row').classList.add('d-none');

            const params = new URLSearchParams({ startDate: start, endDate: end, groupBy: 'month' });
            if (storeId) params.set('strStoreId', storeId);

            fetch(`${caEndpoint}?${params}`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(json => {
                    document.getElementById('ca-loading').classList.add('d-none');
                    document.getElementById('ca-chart-wrap').style.opacity = '1';
                    if (json.error) {
                        document.getElementById('ca-error').textContent = json.error;
                        document.getElementById('ca-error').classList.remove('d-none');
                        return;
                    }
                    renderCA(json);
                })
                .catch(err => {
                    document.getElementById('ca-loading').classList.add('d-none');
                    document.getElementById('ca-chart-wrap').style.opacity = '1';
                    document.getElementById('ca-error').textContent = 'Erreur réseau : ' + err.message;
                    document.getElementById('ca-error').classList.remove('d-none');
                });
        }

        function renderCA(json) {
            const { periods, datasets, grand_total } = json;
            if (caChart) { caChart.destroy(); caChart = null; }

            caChart = new ApexCharts(document.getElementById('ca-chart-wrap'), {
                chart: { type: 'bar', height: 340, toolbar: { show: false }, fontFamily: 'inherit' },
                series: datasets.map(ds => ({ name: ds.store_name, data: ds.data })),
                colors: COLORS,
                plotOptions: { bar: { columnWidth: '60%', borderRadius: 3 } },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 1, colors: ['transparent'] },
                grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
                xaxis: {
                    categories: periods.map(monthLabel),
                    axisBorder: { show: false }, axisTicks: { show: false },
                    labels: { style: { fontSize: '12px', fontWeight: 600 } },
                },
                yaxis: { labels: { formatter: v => fmtDH(v) + ' DH', style: { fontSize: '11px' } } },
                legend: { position: 'bottom', fontSize: '12px', markers: { width: 10, height: 10, radius: 50 } },
                tooltip: { shared: true, intersect: false, y: { formatter: fullDH } },
            });
            caChart.render();

            const kpiRow = document.getElementById('ca-total-row');
            kpiRow.innerHTML = datasets.map((ds, i) => {
                const color = COLORS[i % COLORS.length];
                return `<div class="d-flex align-items-center gap-2 px-3 py-2 rounded" style="background:${color}15;border:1px solid ${color}40">
                    <span style="width:10px;height:10px;border-radius:50%;background:${color};display:inline-block"></span>
                    <span class="fw-semibold" style="color:${color}">${ds.store_name}</span>
                    <span class="text-muted small">→</span>
                    <span class="fw-bold">${fullDH(ds.total)}</span>
                </div>`;
            }).join('') + `<div class="d-flex align-items-center gap-2 px-3 py-2 rounded bg-light">
                <span class="fw-bold">Total :</span>
                <span class="fw-bold text-success">${fullDH(grand_total)}</span>
            </div>`;
            kpiRow.classList.remove('d-none');
        }

        fetchCA();
    })();
})();
