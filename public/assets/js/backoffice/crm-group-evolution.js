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
    if (!Array.isArray(groups) || groups.length === 0) return;

    const SERIES = [
        { key: 'debuts',      label: 'Début',      color: '#6f42c1' },
        { key: 'ajouts',      label: 'Ajouts',     color: '#28a745' },
        { key: 'quittants',   label: 'Quittant',   color: '#dc3545' },
        { key: 'changements', label: 'Changement', color: '#fd7e14' },
        { key: 'actifs',      label: 'Actifs',     color: '#2196f3' },
    ];

    // ── canvas setup ──────────────────────────────────────────────────
    const canvas = document.createElement('canvas');
    wrap.appendChild(canvas);

    const PAD = { top: 24, right: 20, bottom: 90, left: 48 };
    const BAR_GAP   = 2;   // px between bars within a group
    const GRP_GAP   = 0.35; // fraction of group width used as spacing

    let tooltip = null;

    function draw() {
        const W = wrap.clientWidth;
        const H = 420;
        canvas.width  = W;
        canvas.height = H;
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, W, H);

        const chartW = W - PAD.left - PAD.right;
        const chartH = H - PAD.top  - PAD.bottom;

        // max value across all series/groups
        const maxVal = Math.max(1, ...groups.flatMap(g => SERIES.map(s => g[s.key] || 0)));
        const yTick  = niceStep(maxVal);
        const yMax   = Math.ceil(maxVal / yTick) * yTick;

        const n  = groups.length;
        const grpW = chartW / n;
        const barW = Math.max(3, (grpW * (1 - GRP_GAP)) / SERIES.length - BAR_GAP);

        // grid + y-axis
        ctx.strokeStyle = '#eef0f3';
        ctx.lineWidth   = 1;
        ctx.fillStyle   = '#6c757d';
        ctx.font        = '11px system-ui,sans-serif';
        ctx.textAlign   = 'right';
        ctx.textBaseline = 'middle';
        for (let v = 0; v <= yMax; v += yTick) {
            const y = PAD.top + chartH - (v / yMax) * chartH;
            ctx.beginPath(); ctx.moveTo(PAD.left, y); ctx.lineTo(PAD.left + chartW, y); ctx.stroke();
            ctx.fillText(v, PAD.left - 6, y);
        }

        // x-axis labels (rotated)
        ctx.textAlign = 'right';
        ctx.textBaseline = 'top';
        ctx.fillStyle = '#495057';
        ctx.font = '10px system-ui,sans-serif';
        groups.forEach((g, i) => {
            const cx = PAD.left + i * grpW + grpW / 2;
            const y  = PAD.top + chartH + 6;
            ctx.save();
            ctx.translate(cx, y);
            ctx.rotate(-0.6);
            ctx.fillText(g.name, 0, 0);
            ctx.restore();
        });

        // bars + value labels
        groups.forEach((g, i) => {
            const grpStart = PAD.left + i * grpW + grpW * GRP_GAP / 2;
            SERIES.forEach((s, si) => {
                const val = g[s.key] || 0;
                const bh  = (val / yMax) * chartH;
                const bx  = grpStart + si * (barW + BAR_GAP);
                const by  = PAD.top + chartH - bh;

                // rounded top
                ctx.fillStyle = s.color;
                const r = Math.min(3, barW / 2, bh);
                ctx.beginPath();
                ctx.moveTo(bx + r, by);
                ctx.lineTo(bx + barW - r, by);
                ctx.quadraticCurveTo(bx + barW, by, bx + barW, by + r);
                ctx.lineTo(bx + barW, by + bh);
                ctx.lineTo(bx, by + bh);
                ctx.lineTo(bx, by + r);
                ctx.quadraticCurveTo(bx, by, bx + r, by);
                ctx.closePath();
                ctx.fill();

                // value label above bar
                if (val > 0 && barW > 8) {
                    ctx.fillStyle = '#495057';
                    ctx.font = 'bold 9px system-ui,sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillText(val, bx + barW / 2, by - 2);
                }
            });
        });

        // store geometry for hover
        canvas._geo = { groups, grpW, barW, PAD, chartH, yMax, SERIES };
    }

    function niceStep(max) {
        const raw = max / 5;
        const mag = Math.pow(10, Math.floor(Math.log10(raw)));
        for (const f of [1, 2, 5, 10]) { if (raw <= f * mag) return f * mag; }
        return mag * 10;
    }

    // ── tooltip ───────────────────────────────────────────────────────
    function ensureTooltip() {
        if (tooltip) return;
        tooltip = document.createElement('div');
        tooltip.style.cssText = 'position:fixed;background:#333;color:#fff;padding:7px 10px;border-radius:6px;font-size:12px;pointer-events:none;display:none;z-index:9999;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,.25)';
        document.body.appendChild(tooltip);
    }

    canvas.addEventListener('mousemove', function (e) {
        const geo = canvas._geo;
        if (!geo) return;
        ensureTooltip();
        const rect = canvas.getBoundingClientRect();
        const mx   = e.clientX - rect.left;
        const my   = e.clientY - rect.top;
        const { groups, grpW, barW, PAD, chartH, yMax, SERIES } = geo;

        let found = null;
        groups.forEach((g, i) => {
            const grpStart = PAD.left + i * grpW + grpW * GRP_GAP / 2;
            SERIES.forEach((s, si) => {
                const val = g[s.key] || 0;
                const bh  = (val / yMax) * chartH;
                const bx  = grpStart + si * (barW + BAR_GAP);
                const by  = PAD.top + chartH - bh;
                if (mx >= bx && mx <= bx + barW && my >= by && my <= by + bh) {
                    found = { group: g.name, series: s.label, color: s.color, val };
                }
            });
        });

        if (found) {
            tooltip.innerHTML = `<strong>${found.group}</strong><br><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:${found.color};margin-right:5px;"></span>${found.series}: <strong>${found.val}</strong> élève(s)`;
            tooltip.style.display = 'block';
            tooltip.style.left = (e.clientX + 14) + 'px';
            tooltip.style.top  = (e.clientY - 36) + 'px';
        } else {
            tooltip.style.display = 'none';
        }
    });

    canvas.addEventListener('mouseleave', () => { if (tooltip) tooltip.style.display = 'none'; });

    draw();
    window.addEventListener('resize', draw);
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
})();
