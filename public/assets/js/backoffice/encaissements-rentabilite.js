'use strict';
(function () {
    if (typeof ApexCharts === 'undefined') return;

    const d = document.getElementById('rentabilite-data');
    if (!d) return;
    const { evo, charges, comparison, hasEvo, hasComparison } = JSON.parse(d.textContent);

    const fmt  = v => new Intl.NumberFormat('fr-FR').format(v) + ' DH';
    const fmtK = v => (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v);

    if (hasEvo && evo && evo.length > 0) {
        new ApexCharts(document.querySelector('#rentabiliteChart'), {
            chart: { type: 'area', height: 350, toolbar: { show: false } },
            series: [
                { name: 'Recettes', data: evo.map(m => m.revenue) },
                { name: 'Dépenses', data: evo.map(m => m.expenses) },
                { name: 'Marge',    data: evo.map(m => m.margin) },
            ],
            xaxis: { categories: evo.map(m => m.month_label) },
            yaxis: { labels: { formatter: fmtK } },
            colors: ['#2ca87f', '#dc2626', '#4680FF'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.05 } },
            stroke: { curve: 'smooth', width: [3, 2, 2] },
            dataLabels: { enabled: false },
            tooltip: { y: { formatter: fmt } },
        }).render();

        if (charges) {
            const cLabels = ['Charges fixes', 'Profs', 'Primes'];
            const cAll    = [charges.expenses, charges.teacher_payments, charges.primes];
            const cData   = cAll.filter(v => v > 0);
            const cLbl    = cLabels.filter((_, i) => cAll[i] > 0);
            if (cData.length > 0) {
                new ApexCharts(document.querySelector('#chargesDonut'), {
                    chart: { type: 'donut', height: 200 },
                    series: cData,
                    labels: cLbl,
                    colors: ['#e58a00', '#4680FF', '#2ca87f'],
                    legend: { position: 'bottom', fontSize: '11px' },
                    tooltip: { y: { formatter: fmt } },
                    plotOptions: { pie: { donut: { size: '60%' } } },
                }).render();
            }
        }
    }

    if (hasComparison && comparison && comparison.length > 0) {
        new ApexCharts(document.querySelector('#sitesCompareChart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{ name: 'Recettes', data: comparison.map(c => c.total_revenue) }],
            xaxis: { categories: comparison.map(c => c.site_name.replace('GLS Sprachenzentrum ', '')) },
            colors: ['#4680FF'],
            dataLabels: { enabled: true, formatter: fmtK },
            tooltip: { y: { formatter: fmt } },
            plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
        }).render();
    }
})();
