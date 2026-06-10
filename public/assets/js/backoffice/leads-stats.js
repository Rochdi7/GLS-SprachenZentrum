'use strict';
(function () {
    if (typeof ApexCharts === 'undefined') return;

    const d = document.getElementById('leads-stats-data');
    if (!d) return;
    const { monthlyStats, centreStats } = JSON.parse(d.textContent);

    const barEl = document.getElementById('leads-monthly-chart');
    if (barEl && monthlyStats.length > 0) {
        new ApexCharts(barEl, {
            chart: { type: 'bar', height: 380, toolbar: { show: true } },
            series: [
                { name: 'Inscriptions',  data: monthlyStats.map(m => m.inscriptions),  color: '#2ca87f' },
                { name: 'Consultations', data: monthlyStats.map(m => m.consultations), color: '#3ec9d6' },
                { name: 'Applications', data: monthlyStats.map(m => m.applications),  color: '#e58a00' },
            ],
            xaxis: { categories: monthlyStats.map(m => m.label) },
            yaxis: { title: { text: 'Nombre de Leads' }, forceNiceScale: true, min: 0 },
            plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
            dataLabels: { enabled: true, style: { fontSize: '11px' } },
            legend: { position: 'top' },
            tooltip: { shared: true, intersect: false },
            responsive: [{
                breakpoint: 768,
                options: {
                    plotOptions: { bar: { columnWidth: '70%' } },
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom' },
                },
            }],
        }).render();
    }

    const donutEl = document.getElementById('leads-centre-chart');
    if (donutEl && centreStats.length > 0) {
        new ApexCharts(donutEl, {
            chart: { type: 'donut', height: 350 },
            series: centreStats.map(c => c.total),
            labels: centreStats.map(c => c.name),
            colors: ['#2ca87f','#3ec9d6','#e58a00','#dc2626','#7c3aed','#0ea5e9','#f59e0b','#10b981'],
            legend: { position: 'bottom' },
            plotOptions: {
                pie: { donut: { size: '55%', labels: {
                    show: true,
                    total: { show: true, label: 'Total', fontSize: '16px' },
                }}},
            },
            responsive: [{ breakpoint: 768, options: { chart: { height: 300 } } }],
        }).render();
    }
})();
