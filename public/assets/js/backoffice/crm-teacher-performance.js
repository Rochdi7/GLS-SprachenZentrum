'use strict';

(function () {
    const cfg = document.getElementById('tp-config');
    if (!cfg) return;
    const { endpoint } = JSON.parse(cfg.textContent);

    const MEDALS = ['🥇', '🥈', '🥉'];
    const form = document.getElementById('tp-form');
    const btn = document.getElementById('tp-btn');
    const btnLabel = btn.querySelector('.tp-btn-label');
    let chart = null;

    function pad(n) { return String(n).padStart(2, '0'); }
    function toIso(d) { return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`; }
    function medal(i) { return i < 3 ? MEDALS[i] : (i + 1); }
    function esc(s) { return String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c])); }

    function setState(state, msg) {
        ['loading', 'error', 'empty', 'results'].forEach(s => {
            document.getElementById('tp-' + s).classList.toggle('d-none', s !== state);
        });
        if (state === 'error') document.getElementById('tp-error').textContent = msg || 'Erreur.';
        btn.disabled = (state === 'loading');
        btnLabel.textContent = state === 'loading' ? 'Analyse…' : 'Analyser';
    }

    document.querySelectorAll('.tp-preset').forEach(b => {
        b.addEventListener('click', function () {
            const t = new Date();
            let s, e;
            switch (this.dataset.preset) {
                case 'ytd': s = new Date(t.getFullYear(), 0, 1); e = t; break;
                case 'lastyear': s = new Date(t.getFullYear() - 1, 0, 1); e = new Date(t.getFullYear() - 1, 11, 31); break;
                case 'quarter': s = new Date(t.getFullYear(), Math.floor(t.getMonth() / 3) * 3, 1); e = t; break;
            }
            document.getElementById('tp-start-date').value = toIso(s);
            document.getElementById('tp-end-date').value = toIso(e);
            run();
        });
    });

    form.addEventListener('submit', function (ev) { ev.preventDefault(); run(); });
    document.getElementById('tp-store')?.addEventListener('change', run);

    function run() {
        const start = document.getElementById('tp-start-date').value;
        const end = document.getElementById('tp-end-date').value;
        if (!start || !end) { setState('error', 'Sélectionnez une date de début et de fin.'); return; }
        setState('loading');
        const params = new URLSearchParams({ startDate: start, endDate: end });
        const store = document.getElementById('tp-store')?.value;
        if (store) params.set('strStoreId', store);
        fetch(`${endpoint}?${params}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(json => {
                if (json.error) { setState('error', json.error); return; }
                if (!json.teachers || !json.teachers.length) { setState('empty'); return; }
                render(json);
                setState('results');
            })
            .catch(err => setState('error', 'Erreur réseau : ' + err.message));
    }

    function render(json) {
        const teachers = json.teachers;

        // Honest coverage note: the source snapshot is a YTD aggregate rebuilt
        // every 2h, so its real end date can trail the requested end by a few days.
        const cov = document.getElementById('tp-coverage');
        if (cov) {
            const reqEnd = document.getElementById('tp-end-date').value;
            if (json.coverage && json.coverage.end && json.coverage.end < reqEnd) {
                cov.querySelector('span').textContent = `Données calculées jusqu'au ${json.coverage.end} (dernier snapshot). Mise à jour automatique toutes les 2h.`;
                cov.classList.remove('d-none');
            } else {
                cov.classList.add('d-none');
            }
        }

        // Top teacher = most active students kept (actifs), tie-break débuts.
        const top = [...teachers].sort((a, b) => (b.actifs - a.actifs) || (b.debuts - a.debuts))[0];
        document.getElementById('tp-top').innerHTML = top ? `
            <div style="font-size:2.4rem;line-height:1">🏆</div>
            <div>
                <div class="text-muted small text-uppercase fw-semibold">Top professeur</div>
                <div class="fs-4 fw-bold">${esc(top.teacher_name)}</div>
                <div class="d-flex gap-2 flex-wrap mt-1">
                    <span class="badge bg-success metric-pill">${top.actifs} actifs</span>
                    <span class="badge bg-primary metric-pill">${top.debuts} débuts</span>
                    <span class="badge bg-danger metric-pill">${top.quittants} perdus</span>
                    <span class="badge bg-info metric-pill">${top.retention ?? '—'}% rétention</span>
                </div>
            </div>` : '';

        const tt = json.totals;
        document.getElementById('tp-totals').innerHTML = `
            ${card('Professeurs', tt.teachers, 'success', 'ph-chalkboard-teacher')}
            ${card('Débuts (au départ)', tt.debuts, 'primary', 'ph-flag-checkered')}
            ${card('Étudiants perdus', tt.quittants, 'danger', 'ph-user-minus')}
            ${card('Actifs actuellement', tt.actifs, 'info', 'ph-users')}`;

        // Chart: top 12 teachers by actifs, débuts vs perdus.
        const top12 = [...teachers].sort((a, b) => b.actifs - a.actifs).slice(0, 12);
        if (chart) { chart.destroy(); chart = null; }
        chart = new ApexCharts(document.getElementById('tp-chart'), {
            chart: { type: 'bar', height: 360, toolbar: { show: false }, fontFamily: 'inherit', stacked: false },
            series: [
                { name: 'Débuts', data: top12.map(t => t.debuts) },
                { name: 'Ajouts', data: top12.map(t => t.ajouts) },
                { name: 'Perdus', data: top12.map(t => t.quittants) },
            ],
            colors: ['#4680ff', '#0dcaf0', '#dc3545'],
            plotOptions: { bar: { columnWidth: '65%', borderRadius: 3 } },
            dataLabels: { enabled: false },
            xaxis: { categories: top12.map(t => t.teacher_name), labels: { style: { fontSize: '11px' }, rotate: -35, trim: true, maxHeight: 90 }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { formatter: v => Math.round(v), style: { fontSize: '11px' } } },
            legend: { position: 'bottom', markers: { radius: 50 } },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
            tooltip: { shared: true, intersect: false },
        });
        chart.render();

        // Retention ranking (need some intake to qualify).
        const ret = teachers.filter(t => t.retention !== null).sort((a, b) => b.retention - a.retention);
        document.getElementById('tp-ret-tbody').innerHTML = ret.slice(0, 8).map((t, i) => `
            <tr>
                <td style="width:34px">${medal(i)}</td>
                <td>${esc(t.teacher_name)}</td>
                <td class="text-end fw-semibold text-info">${t.retention}%</td>
                <td class="text-end text-muted small">${t.quittants} perdus</td>
            </tr>`).join('');

        // Full table sorted by actifs.
        const maxRet = Math.max(...teachers.map(t => t.retention || 0), 1);
        document.getElementById('tp-tbody').innerHTML = teachers.map((t, i) => `
            <tr>
                <td>${medal(i)}</td>
                <td><strong>${esc(t.teacher_name)}</strong></td>
                <td class="text-end">${t.classes}</td>
                <td class="text-end text-primary fw-semibold">${t.debuts}</td>
                <td class="text-end text-info">${t.ajouts}</td>
                <td class="text-end text-danger fw-semibold">${t.quittants}</td>
                <td class="text-end">${t.actifs}</td>
                <td class="text-end">${t.retention ?? '—'}${t.retention !== null ? '%' : ''}</td>
                <td><div class="progress ret-bar"><div class="progress-bar bg-info" style="width:${t.retention !== null ? Math.round(t.retention / maxRet * 100) : 0}%"></div></div></td>
            </tr>`).join('');
    }

    function card(label, val, color, icon) {
        return `<div class="col-sm-6 col-xl-3"><div class="card border-start border-${color} border-3 h-100"><div class="card-body py-3">
            <div class="text-muted small mb-1"><i class="ph-duotone ${icon} me-1 text-${color}"></i>${label}</div>
            <div class="fw-bold fs-5 text-${color}">${Number(val).toLocaleString('fr-MA')}</div></div></div></div>`;
    }

    // Auto-load year-to-date.
    const t = new Date();
    document.getElementById('tp-start-date').value = toIso(new Date(t.getFullYear(), 0, 1));
    document.getElementById('tp-end-date').value = toIso(t);
    run();
})();
