'use strict';

(function () {
    const el = document.getElementById('crm-expenses-chart-data');
    if (!el) return;

    const { months, series } = JSON.parse(el.textContent);
    if (!months || !months.length) return;

    const labels = months.map(function (m) {
        const parts = m.split('-');
        const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, 1);
        return d.toLocaleDateString('fr-MA', { month: 'short', year: '2-digit' });
    });

    const COLORS = ['#4680ff', '#1cc88a', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1', '#fd7e14', '#20c997'];

    new ApexCharts(document.getElementById('crm-expenses-chart'), {
        chart: { type: 'bar', height: 300, stacked: false, toolbar: { show: false } },
        series: series,
        xaxis: { categories: labels },
        yaxis: {
            labels: {
                formatter: function (v) {
                    if (v >= 1e6) return (v / 1e6).toFixed(1) + ' M';
                    if (v >= 1e3) return (v / 1e3).toFixed(0) + ' k';
                    return v.toLocaleString('fr-MA');
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (v) {
                    return new Intl.NumberFormat('fr-MA', { minimumFractionDigits: 2 }).format(v) + ' DH';
                }
            }
        },
        colors: COLORS,
        dataLabels: { enabled: false },
        legend: { position: 'top' },
        plotOptions: { bar: { columnWidth: '60%', borderRadius: 3 } },
    }).render();
})();
