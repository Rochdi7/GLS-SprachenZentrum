'use strict';
(function () {
    if (typeof ApexCharts === 'undefined') return;

    const d = document.getElementById('whatsapp-dashboard-data');
    if (!d) return;
    const { dailySeries, statusCounts, totalCampaigns } = JSON.parse(d.textContent);

    const categories = dailySeries.map(d => d.date.slice(5));

    new ApexCharts(document.getElementById('wa-daily-chart'), {
        chart: { type: 'area', height: 320, toolbar: { show: false } },
        series: [
            { name: 'Envoyés',           data: dailySeries.map(d => d.sent) },
            { name: 'Échecs',            data: dailySeries.map(d => d.failed) },
            { name: 'Campagnes créées',  data: dailySeries.map(d => d.campaigns) },
        ],
        colors: ['#16a34a', '#ef4444', '#0369a1'],
        stroke: { curve: 'smooth', width: 2.5 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
        dataLabels: { enabled: false },
        xaxis: { categories },
        legend: { position: 'top' },
        grid: { borderColor: '#e5e7eb' },
    }).render();

    new ApexCharts(document.getElementById('wa-status-chart'), {
        chart: { type: 'donut', height: 280 },
        series: [
            statusCounts.queued, statusCounts.running, statusCounts.paused,
            statusCounts.completed, statusCounts.stopped,
        ],
        labels: ['En file', 'En cours', 'Pause', 'Terminées', 'Arrêtées'],
        colors: ['#6b7280', '#0369a1', '#f59e0b', '#16a34a', '#111827'],
        legend: { position: 'bottom' },
        plotOptions: {
            pie: { donut: { labels: {
                show: true,
                total: { show: true, label: 'Total', formatter: () => totalCampaigns },
            }}},
        },
    }).render();
})();
