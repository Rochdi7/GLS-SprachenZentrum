/*
 * CRM — Évolution par groupe page.
 * Loaded by resources/views/backoffice/crm/group-evolution.blade.php.
 *
 * Reads its chart payload from a JSON data island:
 *   <script type="application/json" id="crm-group-evolution-data">…</script>
 */

// Helper to get category label
function getCategoryLabel(category) {
    const labels = {
        debuts: 'Début',
        ajouts: 'Ajouts',
        quittants: 'Quittant',
        changements: 'Changement',
        actifs: 'Actifs'
    };
    return labels[category] || category;
}

document.addEventListener('DOMContentLoaded', function () {
    const dataEl = document.getElementById('crm-group-evolution-data');
    const el     = document.getElementById('groupEvolutionChart');
    const modal = document.getElementById('studentListModal');
    const modalLabel = document.getElementById('studentListModalLabel');
    const modalBody = document.getElementById('studentListModalBody');
    
    if (!dataEl || !el || typeof ApexCharts === 'undefined') return;

    let groups;
    try {
        groups = JSON.parse(dataEl.textContent);
    } catch (err) {
        console.error('[CRM group-evolution] failed to parse chart data', err);
        return;
    }
    if (!Array.isArray(groups) || groups.length === 0) return;

    new ApexCharts(el, {
        chart: { type: 'bar', height: 420, toolbar: { show: false }, animations: { speed: 350 } },
        plotOptions: { bar: { columnWidth: '70%', borderRadius: 3, dataLabels: { position: 'top' } } },
        dataLabels: {
            enabled: true, offsetY: -18,
            style: { fontSize: '10px', colors: ['#495057'], fontWeight: 600 },
            formatter: v => v > 0 ? v : '',
        },
        stroke: { show: true, width: 1, colors: ['transparent'] },
        series: [
            { name: 'Début',       data: groups.map(g => g.debuts) },
            { name: 'Ajouts',      data: groups.map(g => g.ajouts) },
            { name: 'Quittant',    data: groups.map(g => g.quittants) },
            { name: 'Changement',  data: groups.map(g => g.changements) },
            { name: 'Actifs',      data: groups.map(g => g.actifs) },
        ],
        colors: ['#6f42c1', '#28a745', '#dc3545', '#fd7e14', '#2196f3'],
        xaxis: {
            categories: groups.map(g => g.name),
            labels: { rotate: -35, style: { fontSize: '11px' } },
        },
        yaxis: { title: { text: "Nombre d'élèves" }, decimalsInFloat: 0 },
        legend: { show: false }, // legend rendered manually above the chart
        grid: { borderColor: '#eef0f3' },
        tooltip: { y: { formatter: v => v + ' élève(s)' } },
    }).render();
    
    // Add click handlers for table cells
    document.querySelectorAll('.ge-table tbody [data-group-index][data-category]').forEach(cell => {
        cell.addEventListener('click', function() {
            const groupIndex = parseInt(this.getAttribute('data-group-index'));
            const category = this.getAttribute('data-category');
            const group = groups[groupIndex];
            
            // Get student list based on category
            let students = [];
            switch(category) {
                case 'debuts': students = group.debut_students || []; break;
                case 'ajouts': students = group.ajout_students || []; break;
                case 'quittants': students = group.quittant_students || []; break;
                case 'changements': students = group.changement_students || []; break;
                case 'actifs': students = group.active_students || []; break;
            }
            
            // Update modal content
            modalLabel.textContent = `${group.name} — ${getCategoryLabel(category)} (${students.length} élèves)`;
            
            if (students.length === 0) {
                modalBody.innerHTML = '<p class="text-muted text-center">Aucun élève</p>';
            } else {
                let html = '<ul class="list-group list-group-flush">';
                students.forEach(student => {
                    html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>${student.name}</span>
                        <span class="badge bg-light text-muted">#${student.id}</span>
                    </li>`;
                });
                html += '</ul>';
                modalBody.innerHTML = html;
            }
            
            // Show modal
            if (modal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            } else if (modal) {
                // Fallback if bootstrap not available
                modal.classList.add('show');
                modal.style.display = 'block';
            }
        });
        
        // Add hover effect
        cell.style.textDecoration = 'underline';
        cell.style.cursor = 'pointer';
    });
});

// ─────────────────── Group multi-select behaviour ───────────────────
document.addEventListener('DOMContentLoaded', function () {
    const hidden  = document.getElementById('geClassIdsInput');
    const allCbs  = () => Array.from(document.querySelectorAll('.ge-group-cb'));
    const list    = document.getElementById('geGroupList');
    const search  = document.getElementById('geGroupFilter');
    const btnAll  = document.getElementById('geSelectAll');
    const btnNone = document.getElementById('geSelectNone');
    const startDateInput = document.querySelector('input[name="startDate"]');
    const endDateInput = document.querySelector('input[name="endDate"]');
    
    // Load all groups data from JSON island
    let allGroups = [];
    try {
        const allGroupsEl = document.getElementById('crm-all-groups-data');
        if (allGroupsEl) {
            allGroups = JSON.parse(allGroupsEl.textContent);
        }
    } catch (err) {
        console.error('[CRM group-evolution] failed to parse all groups data', err);
    }
    
    if (!hidden || !list) return;

    // Sync the hidden field and auto-fill dates
    const sync = () => {
        const boxes = allCbs();
        const checked = boxes.filter(b => b.checked).map(b => b.value);
        hidden.value = (checked.length === 0 || checked.length === boxes.length)
            ? ''
            : checked.join(',');
        
        // Auto-fill dates if EXACTLY ONE group is selected
        if (checked.length === 1 && allGroups.length > 0) {
            const selectedGroup = allGroups.find(g => g.class_id === parseInt(checked[0]));
            if (selectedGroup) {
                if (selectedGroup.start_date && startDateInput) {
                    startDateInput.value = selectedGroup.start_date.substring(0, 10);
                }
                if (selectedGroup.end_date && endDateInput) {
                    endDateInput.value = selectedGroup.end_date.substring(0, 10);
                }
            }
        }
    };

    list.addEventListener('change', e => {
        if (e.target.classList.contains('ge-group-cb')) sync();
    });

    btnAll?.addEventListener('click',  () => { allCbs().forEach(b => b.checked = true);  sync(); });
    btnNone?.addEventListener('click', () => { allCbs().forEach(b => b.checked = false); sync(); });

    // Live search filter inside the dropdown.
    search?.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        document.querySelectorAll('.ge-group-item').forEach(item => {
            const name = item.querySelector('.ge-group-name')?.textContent.toLowerCase() ?? '';
            item.style.display = (!q || name.includes(q)) ? '' : 'none';
        });
    });

    sync();
});
