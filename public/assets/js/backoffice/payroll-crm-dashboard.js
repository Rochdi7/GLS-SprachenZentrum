'use strict';
(function () {
    if (typeof ApexCharts === 'undefined') return;

    const d = document.getElementById('payroll-crm-data');
    if (!d) return;
    const { labels, amounts, students, approved, pending, noImport } = JSON.parse(d.textContent);

    const toastEl = document.getElementById('liveToast');
    if (toastEl) new bootstrap.Toast(toastEl).show();

    document.getElementById('site-filter')?.addEventListener('change', function () {
        window.dt?.draw();
    });

    if (labels.length > 0 && document.getElementById('paymentBarChart')) {
        new ApexCharts(document.getElementById('paymentBarChart'), {
            chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [
                { name: 'Montant (DH)', data: amounts },
                { name: 'Étudiants',    data: students },
            ],
            xaxis: { categories: labels, labels: { style: { fontSize: '12px' } } },
            yaxis: [
                { title: { text: 'DH' }, labels: { formatter: v => v.toLocaleString('fr-MA') + ' DH' } },
                { opposite: true, title: { text: 'Étudiants' }, labels: { formatter: v => v + ' étu.' } },
            ],
            colors: ['#4361ee', '#2ec4b6'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f1f1' },
            tooltip: {
                y: [
                    { formatter: v => v.toLocaleString('fr-MA') + ' DH' },
                    { formatter: v => v + ' étudiant(s)' },
                ],
            },
            legend: { position: 'top' },
        }).render();
    }

    if (document.getElementById('statusDonutChart') && (approved + pending + noImport) > 0) {
        new ApexCharts(document.getElementById('statusDonutChart'), {
            chart: { type: 'donut', height: 220, fontFamily: 'inherit' },
            series: [approved, pending, noImport],
            labels: ['Approuvé', 'En attente', 'Sans import'],
            colors: ['#28a745', '#ffc107', '#dee2e6'],
            legend: { show: false },
            dataLabels: { enabled: true, formatter: (val, opts) => opts.w.config.series[opts.seriesIndex] },
            plotOptions: { pie: { donut: { size: '65%', labels: {
                show: true,
                total: { show: true, label: 'Total', formatter: () => (approved + pending + noImport) + ' groupes' },
            }}}},
            tooltip: { y: { formatter: v => v + ' groupe(s)' } },
        }).render();
    }
})();
