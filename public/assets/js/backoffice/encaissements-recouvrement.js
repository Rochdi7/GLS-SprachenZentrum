'use strict';
(function () {
    if (typeof ApexCharts === 'undefined') return;

    const d = document.getElementById('recouvrement-data');
    if (!d) return;
    const states = JSON.parse(d.textContent);

    const fmt = v => new Intl.NumberFormat('fr-FR').format(v) + ' DH';
    const withData = states.filter(s => s.ca > 0);

    if (withData.length === 0) {
        const el = document.querySelector('#recouvrementChart');
        if (el) el.innerHTML = '<div class="text-center text-muted py-5">Aucune donnée pour ce mois.</div>';
        return;
    }

    new ApexCharts(document.querySelector('#recouvrementChart'), {
        chart: { type: 'bar', height: 380, toolbar: { show: false } },
        series: [
            { name: 'Montant à Recouvrer',  data: withData.map(s => s.impaye) },
            { name: "Chiffre d'affaires",   data: withData.map(s => s.ca) },
            { name: '% des impayés',        data: withData.map(s => s.unpaid_rate) },
        ],
        xaxis: { categories: withData.map(s => s.site_short) },
        yaxis: [
            { seriesName: 'Montant à Recouvrer', labels: { formatter: v => v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v }, title: { text: 'DH' } },
            { seriesName: "Chiffre d'affaires", show: false },
            { seriesName: '% des impayés', opposite: true, labels: { formatter: v => v + '%' }, title: { text: '%' }, max: 100 },
        ],
        colors: ['#dc2626', '#4680FF', '#9ca3af'],
        dataLabels: { enabled: false },
        stroke: { width: [1, 1, 2] },
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
        tooltip: {
            shared: true,
            y: [{ formatter: fmt }, { formatter: fmt }, { formatter: v => v + '%' }],
        },
        legend: { position: 'top' },
    }).render();

    const t = document.getElementById('liveToast');
    if (t) new bootstrap.Toast(t).show();
})();
