'use strict';

// ── ApexCharts null-element guard ────────────────────────────────────────────
(function () {
    var _Real = window.ApexCharts;
    if (!_Real) return;
    window.ApexCharts = function (el, opts) {
        if (!el) return { render: function () { return Promise.resolve(); } };
        return new _Real(el, opts);
    };
    window.ApexCharts.prototype = _Real.prototype;
    Object.assign(window.ApexCharts, _Real);
})();

// ── Peity sparklines ─────────────────────────────────────────────────────────
(function () {
    if (typeof peity === 'undefined') return;
    peity.defaults.line  = { delimiter: ',', fill: '#e0f5fe', height: 24, min: 0, stroke: '#04a9f5', strokeWidth: 1, width: 80 };
    peity.defaults.bar   = { delimiter: ',', fill: ['#04a9f5'], height: 24, min: 0, padding: 0.1, width: 80 };
    peity.defaults.donut = { delimiter: null, fill: ['#ffffff', 'rgba(255,255,255,.3)'], height: 26, innerRadius: 8, radius: 12, width: 26 };
    document.querySelectorAll('.line').forEach(e  => peity(e, 'line'));
    document.querySelectorAll('.bar').forEach(e   => peity(e, 'bar'));
    document.querySelectorAll('.donut').forEach(e => peity(e, 'donut'));
})();

if (typeof feather !== 'undefined') feather.replace();

// ── ApexCharts dashboard bars & donuts ───────────────────────────────────────
(function () {
    if (typeof ApexCharts === 'undefined') return;

    window.__glsApex = window.__glsApex || {};

    function safeJson(str, fb) { try { return JSON.parse(str); } catch (e) { return fb; } }
    function getData(el) {
        return {
            series: safeJson(el.getAttribute('data-series') || '[]', []),
            labels: safeJson(el.getAttribute('data-labels') || '[]', []),
        };
    }
    function destroy(key) {
        if (window.__glsApex[key]?.destroy) window.__glsApex[key].destroy();
        window.__glsApex[key] = null;
    }

    function renderBar(elId, key, name) {
        const el = document.getElementById(elId);
        if (!el) return;
        const { series, labels } = getData(el);
        if (!series.length) return;
        destroy(key);
        window.__glsApex[key] = new ApexCharts(el, {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{ name, data: series }],
            xaxis: { categories: labels },
            dataLabels: { enabled: false },
            grid: { strokeDashArray: 4 },
        });
        window.__glsApex[key].render();
    }

    function renderDonut(elId, key) {
        const el = document.getElementById(elId);
        if (!el) return;
        const { series, labels } = getData(el);
        if (!series.length) return;
        destroy(key);
        window.__glsApex[key] = new ApexCharts(el, {
            chart: { type: 'donut', height: 300 },
            series,
            labels,
            legend: { position: 'bottom' },
        });
        window.__glsApex[key].render();
    }

    renderBar('bar-chart-1',  'bar1',   'Articles');
    renderBar('bar-chart-2',  'bar2',   'Certificats');
    renderBar('bar-chart-3',  'bar3',   'Inscriptions');
    renderBar('bar-chart-4',  'bar4',   'Consultations');
    renderDonut('pie-chart-2', 'donut1');
})();

// ── Suivi-niveau widget reorder ───────────────────────────────────────────────
(function () {
    const suivi = document.getElementById('suivi-niveau');
    if (!suivi) return;
    const row       = suivi.closest('.row');
    const separator = document.getElementById('suivi-niveau-separator');
    const content   = document.querySelector('.pc-container .pc-content');
    if (!row || !content) return;
    if (separator) content.appendChild(separator);
    content.appendChild(row);
})();
