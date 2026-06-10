(function () {
    var island = document.getElementById('crm-ca-chart-data');
    if (!island) return;

    var d = JSON.parse(island.textContent);

    // Month labels: "janv.", "févr.", …
    var monthLabels = d.months.map(function (ym) {
        var parts = ym.split('-');
        var dt = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, 1);
        return dt.toLocaleDateString('fr-FR', { month: 'short' });
    });

    var fmt = function (val) {
        if (val === null || val === undefined) return '0 DH';
        return new Intl.NumberFormat('fr-MA', { maximumFractionDigits: 0 }).format(val) + ' DH';
    };

    var options = {
        chart: {
            type: 'area',
            height: 400,
            toolbar: { show: true, tools: { download: true, zoom: true, reset: true, pan: false } },
            zoom: { enabled: true },
            animations: { enabled: true, speed: 600 },
            fontFamily: 'inherit',
        },
        series: [
            { name: 'Chiffre d\'affaire', data: d.seriesCA },
            { name: 'Collecté',           data: d.seriesCollecte },
            { name: 'Reste à payer',      data: d.seriesReste },
            { name: 'Dépenses',           data: d.seriesDepenses },
            { name: 'Encaissements',      data: d.seriesEncaissements },
        ],
        colors: ['#4680ff', '#1cc88a', '#ffc107', '#dc3545', '#6f42c1'],
        stroke: { curve: 'smooth', width: 2 },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.05,
                stops: [0, 90, 100],
            },
        },
        xaxis: {
            categories: monthLabels,
            labels: { style: { fontSize: '12px' } },
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    if (val >= 1000000) return (val / 1000000).toFixed(1) + ' M';
                    if (val >= 1000) return (val / 1000).toFixed(0) + ' k';
                    return val;
                },
            },
        },
        tooltip: {
            theme: 'dark',
            shared: true,
            intersect: false,
            y: { formatter: fmt },
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            fontSize: '13px',
            markers: { radius: 4 },
        },
        grid: {
            borderColor: '#e9ecef',
            strokeDashArray: 4,
        },
        dataLabels: { enabled: false },
        markers: { size: 0 },
    };

    var chart = new ApexCharts(document.getElementById('crm-ca-chart'), options);
    chart.render();
})();
