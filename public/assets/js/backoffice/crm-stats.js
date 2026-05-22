/*
 * CRM stats — annual summary chart (ApexCharts area).
 * Loaded by resources/views/backoffice/crm/stats.blade.php.
 *
 * Reads the annual data series from a JSON data island in the page:
 *     <script type="application/json" id="crm-annual-summary-data">…</script>
 * The blade emits that block with @json($annual).
 */
document.addEventListener('DOMContentLoaded', function () {
    const dataEl = document.getElementById('crm-annual-summary-data');
    const el     = document.getElementById('annualSummaryChart');
    if (!dataEl || !el || typeof ApexCharts === 'undefined') return;

    let annual;
    try {
        annual = JSON.parse(dataEl.textContent);
    } catch (err) {
        console.error('[CRM stats] failed to parse annual summary data', err);
        return;
    }

    const fmtMoney = v => new Intl.NumberFormat('fr-FR').format(Math.round(v));

    new ApexCharts(el, {
        chart: {
            type: 'area',
            height: 360,
            toolbar: { show: false },
            zoom: { enabled: false },
        },
        series: [
            { name: "Chiffre d'affaire", data: annual.chiffre_affaire },
            { name: 'Collecté',          data: annual.collecte },
            { name: 'Reste à payer',     data: annual.reste_a_payer },
            { name: 'Dépenses',          data: annual.depenses },
            { name: 'Encaissments',      data: annual.encaissments },
        ],
        xaxis: {
            categories: annual.labels,
            axisBorder: { show: false },
            axisTicks:  { show: false },
            labels: { style: { fontSize: '11px', colors: '#6c757d' } },
        },
        yaxis: {
            labels: {
                style: { fontSize: '11px', colors: '#6c757d' },
                formatter: v => v >= 1000 ? (v / 1000).toFixed(0) + 'k' : Math.round(v),
            },
        },
        // Order matches the series above: red, blue, orange, green, light red
        colors: ['#dc2626', '#2563eb', '#f59e0b', '#10b981', '#ef4444'],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] },
        },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: { enabled: false },
        markers: { size: 0, hover: { size: 5 } },
        grid: { borderColor: '#e9ecef', strokeDashArray: 3, padding: { left: 10, right: 10 } },
        legend: { position: 'bottom', horizontalAlign: 'center', fontSize: '13px', markers: { width: 10, height: 10, radius: 10 } },
        tooltip: {
            shared: true,
            intersect: false,
            y: { formatter: v => fmtMoney(v) },
        },
    }).render();
});
