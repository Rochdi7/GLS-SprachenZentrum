'use strict';
(function () {
    if (typeof ApexCharts === 'undefined') return;

    const d = document.getElementById('encaissements-dashboard-data');
    if (!d) return;
    const { monthlyEvolution, byMethod, methodEvolution } = JSON.parse(d.textContent);

    const fmtDH = v => new Intl.NumberFormat('fr-FR').format(v) + ' DH';
    const fmtK  = v => (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v) + ' DH';

    // ── Revenue Evolution (area) ─────────────────────────────────────────
    if (monthlyEvolution.length > 0) {
        new ApexCharts(document.querySelector('#revenueChart'), {
            chart: { type: 'area', height: 320, toolbar: { show: false } },
            series: [
                { name: 'Recettes', data: monthlyEvolution.map(m => m.revenue) },
                { name: 'Dépenses', data: monthlyEvolution.map(m => m.expenses) },
            ],
            xaxis: { categories: monthlyEvolution.map(m => m.month_label) },
            yaxis: { labels: { formatter: fmtK } },
            colors: ['#4680FF', '#dc2626'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.1 } },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            tooltip: { y: { formatter: fmtDH } },
        }).render();
    }

    // ── Payment Method Donut ─────────────────────────────────────────────
    const methodLabels = { especes: 'Espèces', tpe: 'TPE', virement: 'Virement', cheque: 'Chèque' };
    const methodColors = { especes: '#2ca87f', tpe: '#4680FF', virement: '#e58a00', cheque: '#dc2626' };
    const pieLabels = [], pieSeries = [], pieColors = [];
    Object.keys(methodLabels).forEach(k => {
        if (byMethod[k]) {
            pieLabels.push(methodLabels[k]);
            pieSeries.push(parseFloat(byMethod[k].total));
            pieColors.push(methodColors[k]);
        }
    });
    if (pieSeries.length > 0) {
        new ApexCharts(document.querySelector('#methodPieChart'), {
            chart: { type: 'donut', height: 320 },
            series: pieSeries,
            labels: pieLabels,
            colors: pieColors,
            legend: { position: 'bottom' },
            tooltip: { y: { formatter: fmtDH } },
            plotOptions: {
                pie: { donut: { size: '55%', labels: {
                    show: true,
                    total: { show: true, label: 'Total', formatter: w => {
                        const t = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                        return fmtDH(t);
                    }},
                }}},
            },
        }).render();
    }

    // ── Stacked Method Evolution (bar) ───────────────────────────────────
    if (methodEvolution.length > 0) {
        new ApexCharts(document.querySelector('#methodStackedChart'), {
            chart: { type: 'bar', height: 300, stacked: true, toolbar: { show: false } },
            series: [
                { name: 'Espèces',  data: methodEvolution.map(m => m.especes) },
                { name: 'TPE',      data: methodEvolution.map(m => m.tpe) },
                { name: 'Virement', data: methodEvolution.map(m => m.virement) },
                { name: 'Chèque',   data: methodEvolution.map(m => m.cheque) },
            ],
            xaxis: { categories: methodEvolution.map(m => m.month) },
            yaxis: { labels: { formatter: v => v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v } },
            colors: ['#2ca87f', '#4680FF', '#e58a00', '#dc2626'],
            dataLabels: { enabled: false },
            tooltip: { y: { formatter: fmtDH } },
            plotOptions: { bar: { borderRadius: 4 } },
        }).render();
    }
})();
