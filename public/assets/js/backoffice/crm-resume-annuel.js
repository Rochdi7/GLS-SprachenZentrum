'use strict';

(function () {
    const cfg = document.getElementById('ra-config');
    if (!cfg) return;
    const { endpoint } = JSON.parse(cfg.textContent);

    const MEDALS = ['🥇', '🥈', '🥉'];
    const form = document.getElementById('ra-form');
    const btn = document.getElementById('ra-btn');
    const btnLabel = btn.querySelector('.ra-btn-label');

    function fmtDH(v) {
        v = Number(v) || 0;
        if (v >= 1e6) return (v / 1e6).toFixed(2).replace(/\.?0+$/, '') + ' M DH';
        if (v >= 1e3) return (v / 1e3).toFixed(1).replace('.0', '') + ' k DH';
        return v.toLocaleString('fr-MA') + ' DH';
    }
    function fullDH(v) { return (Number(v) || 0).toLocaleString('fr-MA') + ' DH'; }
    function pad(n) { return String(n).padStart(2, '0'); }
    function toIso(d) { return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`; }
    function medal(i) { return i < 3 ? MEDALS[i] : (i + 1); }
    function esc(s) { return String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c])); }

    function setState(state, msg) {
        ['loading', 'error', 'empty', 'results'].forEach(s => {
            document.getElementById('ra-' + s).classList.toggle('d-none', s !== state);
        });
        if (state === 'error') document.getElementById('ra-error').textContent = msg || 'Erreur.';
        btn.disabled = (state === 'loading');
        btnLabel.textContent = state === 'loading' ? 'Analyse…' : 'Analyser';
    }

    // ── Presets ──────────────────────────────────────────────────────
    document.querySelectorAll('.ra-preset').forEach(b => {
        b.addEventListener('click', function () {
            const t = new Date();
            let s, e;
            switch (this.dataset.preset) {
                case 'ytd': s = new Date(t.getFullYear(), 0, 1); e = t; break;
                case 'lastyear': s = new Date(t.getFullYear() - 1, 0, 1); e = new Date(t.getFullYear() - 1, 11, 31); break;
                case 'quarter': s = new Date(t.getFullYear(), Math.floor(t.getMonth() / 3) * 3, 1); e = t; break;
                case 'month': s = new Date(t.getFullYear(), t.getMonth(), 1); e = t; break;
            }
            document.getElementById('ra-start-date').value = toIso(s);
            document.getElementById('ra-end-date').value = toIso(e);
            run();
        });
    });

    form.addEventListener('submit', function (ev) { ev.preventDefault(); run(); });

    function run() {
        const start = document.getElementById('ra-start-date').value;
        const end = document.getElementById('ra-end-date').value;
        if (!start || !end) { setState('error', 'Sélectionnez une date de début et de fin.'); return; }
        setState('loading');
        const params = new URLSearchParams({ startDate: start, endDate: end });
        fetch(`${endpoint}?${params}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(json => {
                if (json.error) { setState('error', json.error); return; }
                if (!json.rows || !json.rows.length) { setState('empty'); return; }
                render(json);
                setState('results');
            })
            .catch(err => setState('error', 'Erreur réseau : ' + err.message));
    }

    function render(json) {
        const w = json.winner;
        document.getElementById('ra-winner').innerHTML = w ? `
            <div class="crown">👑</div>
            <div>
                <div class="text-muted small text-uppercase fw-semibold">Meilleur centre — prime recommandée</div>
                <div class="fs-4 fw-bold">${esc(w.store_name)}</div>
                <div class="d-flex gap-2 flex-wrap mt-1">
                    <span class="badge bg-warning text-dark metric-pill">Score ${w.score}/100</span>
                    <span class="badge bg-primary metric-pill">${fmtDH(w.encaisse)} encaissé</span>
                    <span class="badge bg-success metric-pill">${w.recovery_rate ?? '—'}% recouvrement</span>
                    <span class="badge bg-info metric-pill">${w.inscriptions} inscriptions</span>
                </div>
            </div>` : '';

        const g = json.grand;
        document.getElementById('ra-grand').innerHTML = `
            ${grandCard('Encaissé total', fullDH(g.encaisse), 'primary', 'ph-money')}
            ${grandCard('Reste à payer total', fullDH(g.reste), 'danger', 'ph-warning-circle')}
            ${grandCard('Inscriptions totales', (g.inscriptions).toLocaleString('fr-MA'), 'info', 'ph-user-plus')}`;

        // Composite ranking
        const maxScore = Math.max(...json.by_score.map(r => r.score), 1);
        document.getElementById('ra-score-tbody').innerHTML = json.by_score.map((r, i) => `
            <tr>
                <td>${medal(i)}</td>
                <td><strong>${esc(r.store_name)}</strong></td>
                <td class="text-end text-primary">${fmtDH(r.encaisse)}</td>
                <td class="text-end text-danger">${fmtDH(r.reste)}</td>
                <td class="text-end">${r.recovery_rate ?? '—'}%</td>
                <td class="text-end">${r.inscriptions}</td>
                <td class="text-end fw-bold">${r.score}</td>
                <td><div class="progress score-bar"><div class="progress-bar ${i === 0 ? 'bg-warning' : 'bg-primary'}" style="width:${Math.round(r.score / maxScore * 100)}%"></div></div></td>
            </tr>`).join('');

        miniTable('ra-enc-tbody', json.by_encaisse, r => fmtDH(r.encaisse), 'text-primary');
        miniTable('ra-rec-tbody', json.by_recouvrement, r => (r.recovery_rate ?? '—') + '%', 'text-success');
        miniTable('ra-insc-tbody', json.by_inscriptions, r => r.inscriptions + ' insc.', 'text-info');
    }

    function grandCard(label, val, color, icon) {
        return `<div class="col-md-4"><div class="card border-start border-${color} border-3 h-100"><div class="card-body py-3">
            <div class="text-muted small mb-1"><i class="ph-duotone ${icon} me-1 text-${color}"></i>${label}</div>
            <div class="fw-bold fs-5 text-${color}">${val}</div></div></div></div>`;
    }

    function miniTable(id, rows, valFn, cls) {
        document.getElementById(id).innerHTML = rows.slice(0, 8).map((r, i) => `
            <tr>
                <td style="width:34px">${medal(i)}</td>
                <td>${esc(r.store_name)}</td>
                <td class="text-end fw-semibold ${cls}">${valFn(r)}</td>
            </tr>`).join('');
    }

    // Auto-load year-to-date on first visit.
    const t = new Date();
    document.getElementById('ra-start-date').value = toIso(new Date(t.getFullYear(), 0, 1));
    document.getElementById('ra-end-date').value = toIso(t);
    run();
})();
