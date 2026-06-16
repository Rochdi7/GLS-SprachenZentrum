@extends('layouts.main')

@section('title', 'Statistiques de présence')
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'Statistiques de présence')

@section('css')
<style>
*,*::before,*::after{box-sizing:border-box}
.ps-wrap{--ps-blue:#4680ff;--ps-green:#1cc88a;--ps-red:#e74c3c;--ps-amber:#f6c23e}
.ps-card{background:#fff;border-radius:18px;box-shadow:0 2px 18px rgba(20,22,55,.05);overflow:hidden}

/* Header */
.ps-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px}
.ps-head h4{margin:0 0 4px;font-weight:800;color:#16182d;letter-spacing:-.01em}
.ps-head p{margin:0;color:#8a90a6;font-size:.85rem}

/* Filters */
.ps-filters{display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;background:#fff;border-radius:16px;padding:16px 18px;box-shadow:0 2px 14px rgba(20,22,55,.05);margin-bottom:22px}
.ps-filters .fg{display:flex;flex-direction:column;gap:5px}
.ps-filters label{font-size:.68rem;font-weight:700;color:#9197ad;text-transform:uppercase;letter-spacing:.05em}
.ps-filters input,.ps-filters select{border:1px solid #e6e8f2;border-radius:10px;padding:9px 13px;font-size:.85rem;min-width:160px;background:#fbfcff;transition:border .15s,box-shadow .15s}
.ps-filters input:focus,.ps-filters select:focus{outline:none;border-color:var(--ps-blue);box-shadow:0 0 0 3px rgba(70,128,255,.12)}
.ps-filters .btn{border-radius:10px;font-weight:600;padding:9px 22px}

/* Searchable group combobox */
.gsearch{position:relative}
.gsearch-input{border:1px solid #e6e8f2;border-radius:10px;padding:9px 13px;font-size:.85rem;min-width:200px;background:#fbfcff;transition:border .15s,box-shadow .15s}
.gsearch-input:focus{outline:none;border-color:var(--ps-blue);box-shadow:0 0 0 3px rgba(70,128,255,.12)}
.gsearch-menu{position:absolute;top:calc(100% + 4px);left:0;right:0;min-width:200px;max-height:260px;overflow-y:auto;background:#fff;border:1px solid #e6e8f2;border-radius:10px;box-shadow:0 8px 24px rgba(20,22,55,.12);z-index:50;display:none}
.gsearch-menu.open{display:block}
.gsearch-opt{padding:9px 13px;font-size:.85rem;color:#3a3f54;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.gsearch-opt:hover,.gsearch-opt.active{background:#f0f4ff;color:var(--ps-blue)}
.gsearch-opt.hidden{display:none}

/* KPI tiles */
.ps-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:14px}
.ps-kpi{background:#fff;border-radius:16px;padding:18px 20px;box-shadow:0 2px 14px rgba(20,22,55,.05);position:relative;overflow:hidden}
.ps-kpi::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--ps-blue)}
.ps-kpi .v{font-size:1.7rem;font-weight:800;color:#16182d;line-height:1}
.ps-kpi .l{font-size:.72rem;color:#8a90a6;text-transform:uppercase;letter-spacing:.05em;margin-top:7px;font-weight:600}
.ps-kpi.k-present::before{background:var(--ps-green)}
.ps-kpi.k-absent::before {background:var(--ps-red)}
.ps-kpi.k-rate::before   {background:var(--ps-amber)}

/* Status sub-tiles */
.ps-status{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:22px}
.ps-st{display:flex;align-items:center;gap:14px;background:#fff;border-radius:16px;padding:14px 18px;box-shadow:0 2px 14px rgba(20,22,55,.05)}
.ps-st .ic{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
.ps-st.valide   .ic{background:#e6f9f1;color:#0f9d6b}
.ps-st.brouillon .ic{background:#fff3d6;color:#b78103}
.ps-st.annule   .ic{background:#fdeaea;color:#d9434e}
.ps-st .v{font-size:1.35rem;font-weight:800;color:#16182d;line-height:1}
.ps-st .l{font-size:.74rem;color:#8a90a6;font-weight:600;margin-top:3px}

/* Charts */
.ps-charts{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:22px}
.ps-chart{padding:18px 20px 8px}
.ps-chart h6{font-size:.82rem;font-weight:700;color:#16182d;margin:0 0 4px}
.ps-chart .sub{font-size:.72rem;color:#9197ad;margin-bottom:8px}
@media(max-width:920px){.ps-charts{grid-template-columns:1fr}}

/* Session table */
.ps-table{width:100%;border-collapse:separate;border-spacing:0}
.ps-table thead th{font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;color:#9197ad;font-weight:700;padding:14px 16px;text-align:left;border-bottom:1px solid #f0f1f7;white-space:nowrap;background:#fbfcff}
.ps-table tbody td{padding:13px 16px;font-size:.85rem;color:#3a3f54;border-bottom:1px solid #f6f7fb;vertical-align:middle}
.ps-table tbody tr{cursor:pointer;transition:background .12s}
.ps-table tbody tr:hover{background:#f6f9ff}
.pill{display:inline-flex;align-items:center;justify-content:center;min-width:34px;font-size:.78rem;font-weight:700;padding:3px 10px;border-radius:20px}
.pill.p{background:#e6f9f1;color:#0f9d6b}
.pill.a{background:#fdeaea;color:#d9434e}
.bar{height:7px;border-radius:4px;background:#eef0f7;overflow:hidden;width:96px}
.bar > i{display:block;height:100%;border-radius:4px;background:linear-gradient(90deg,#1cc88a,#36d39a)}
.bar.low > i{background:linear-gradient(90deg,#f6c23e,#f8d36b)}
.bar.bad > i{background:linear-gradient(90deg,#e74c3c,#f06a5d)}
.muted{color:#9197ad}
.empty{text-align:center;padding:52px 20px;color:#9197ad}

/* Drill modal */
.ps-modal{position:fixed;inset:0;background:rgba(16,18,38,.55);backdrop-filter:blur(2px);display:none;align-items:center;justify-content:center;z-index:1080;padding:20px}
.ps-modal.show{display:flex}
.ps-modal-box{background:#fff;border-radius:20px;width:min(740px,100%);max-height:88vh;overflow:auto;box-shadow:0 30px 70px rgba(0,0,0,.35);animation:psIn .18s ease}
@keyframes psIn{from{opacity:0;transform:translateY(12px) scale(.98)}to{opacity:1;transform:none}}
.ps-modal-head{padding:22px 26px;border-bottom:1px solid #f0f1f7;position:sticky;top:0;background:#fff;z-index:2}
.ps-modal-head h5{margin:0;font-weight:800;color:#16182d}
.ps-modal-head .sub{font-size:.8rem;color:#8a90a6;margin-top:5px}
.ps-modal-close{position:absolute;top:20px;right:22px;border:none;background:#f1f3f9;width:34px;height:34px;border-radius:50%;cursor:pointer;font-size:1.15rem;color:#555;line-height:1}
.ps-modal-close:hover{background:#e6e8f2}
.ps-cols{display:grid;grid-template-columns:1fr 1fr;gap:0}
.ps-col{padding:18px 26px 24px}
.ps-col + .ps-col{border-left:1px solid #f0f1f7}
.ps-col h6{font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;display:flex;align-items:center;gap:8px;margin-bottom:14px}
.ps-col h6 .cnt{margin-left:auto;font-weight:800;font-size:.95rem}
.ps-col.present h6{color:#0f9d6b}
.ps-col.absent  h6{color:#d9434e}
.slist{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:6px}
.slist li{font-size:.85rem;color:#3a3f54;padding:9px 12px;border-radius:9px;background:#fafbfd;display:flex;align-items:center;gap:8px}
.slist li .tag{font-size:.64rem;font-weight:700;padding:1px 7px;border-radius:10px;background:#fff3d6;color:#b78103}
.slist li .tag.ex{background:#e8f0ff;color:#3461c9}
@media(max-width:600px){.ps-cols{grid-template-columns:1fr}.ps-col+.ps-col{border-left:none;border-top:1px solid #f0f1f7}}
</style>
@endsection

@section('content')
<div class="ps-wrap">
    <div class="ps-head">
        <div>
            <h4>Statistiques de présence</h4>
            <p>Chaque séance avec son taux de présence. Cliquez une ligne pour voir les présents et les absents.</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="ps-filters">
        <div class="fg">
            <label>Centre</label>
            <select name="centerId">
                <option value="all" {{ $storeId === null ? 'selected' : '' }}>Tous les centres</option>
                @foreach($centers as $c)
                    <option value="{{ $c['id'] }}" {{ $storeId === $c['id'] ? 'selected' : '' }}>{{ $c['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="fg">
            <label>Date début</label>
            <input type="date" name="startDate" value="{{ $startDate }}">
        </div>
        <div class="fg">
            <label>Date fin</label>
            <input type="date" name="endDate" value="{{ $endDate }}">
        </div>
        <div class="fg gsearch" id="groupCombo">
            <label>Groupe</label>
            <input type="hidden" name="classId" id="groupClassId" value="{{ $classId }}">
            <input type="text" id="groupSearch" class="gsearch-input" autocomplete="off"
                   placeholder="Rechercher un groupe…"
                   value="{{ $classId ? collect($classes)->firstWhere('id', $classId)['name'] ?? '' : '' }}">
            <div class="gsearch-menu" id="groupMenu">
                <div class="gsearch-opt" data-id="">Tous les groupes</div>
                @foreach($classes as $c)
                    <div class="gsearch-opt" data-id="{{ $c['id'] }}">{{ $c['name'] }}</div>
                @endforeach
            </div>
        </div>
        <div class="fg">
            <button type="submit" class="btn btn-primary">Filtrer</button>
        </div>
    </form>

    {{-- KPIs --}}
    <div class="ps-kpis">
        <div class="ps-kpi">
            <div class="v">{{ number_format($totals['sessions']) }}</div>
            <div class="l">Séances valides</div>
        </div>
        <div class="ps-kpi k-present">
            <div class="v">{{ number_format($totals['present']) }}</div>
            <div class="l">Présences</div>
        </div>
        <div class="ps-kpi k-absent">
            <div class="v">{{ number_format($totals['absent']) }}</div>
            <div class="l">Absences</div>
        </div>
        <div class="ps-kpi k-rate">
            <div class="v">{{ $totals['taux_presence'] }}%</div>
            <div class="l">Taux de présence</div>
        </div>
    </div>

    {{-- Séance status counts --}}
    <div class="ps-status">
        <div class="ps-st valide">
            <div class="ic"><i class="ph-duotone ph-check-circle"></i></div>
            <div><div class="v">{{ number_format($totals['valide']) }}</div><div class="l">Séances validées</div></div>
        </div>
        <div class="ps-st brouillon">
            <div class="ic"><i class="ph-duotone ph-note-pencil"></i></div>
            <div><div class="v">{{ number_format($totals['brouillon']) }}</div><div class="l">Brouillons (non saisies)</div></div>
        </div>
        <div class="ps-st annule">
            <div class="ic"><i class="ph-duotone ph-x-circle"></i></div>
            <div><div class="v">{{ number_format($totals['annule']) }}</div><div class="l">Séances annulées</div></div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="ps-charts">
        <div class="ps-card ps-chart">
            <h6>Présences vs absences</h6>
            <div class="sub">Évolution jour par jour sur la période</div>
            <div id="psChartTrend" style="min-height:280px"></div>
        </div>
        <div class="ps-card ps-chart">
            <h6>Taux de présence par groupe</h6>
            <div class="sub">Top 12 groupes sur la période</div>
            <div id="psChartGroups" style="min-height:280px"></div>
        </div>
    </div>

    {{-- Sessions table --}}
    <div class="ps-card">
        <div class="table-responsive">
            <table class="ps-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Horaire</th>
                        <th>Groupe</th>
                        <th>Professeur</th>
                        <th>Présents</th>
                        <th>Absents</th>
                        <th>Taux</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $s)
                        <tr onclick="openSession({{ $s['class_id'] }}, '{{ $s['date'] }}')">
                            <td><strong>{{ \Carbon\Carbon::parse($s['date'])->format('d/m/Y') }}</strong></td>
                            <td class="muted">
                                @if($s['start_time']){{ $s['start_time'] }} – {{ $s['end_time'] }}@else—@endif
                            </td>
                            <td>{{ $s['class_name'] }}</td>
                            <td class="muted">{{ $s['teacher'] }}</td>
                            <td><span class="pill p">{{ $s['present'] }}</span></td>
                            <td><span class="pill a">{{ $s['absent'] }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="bar {{ $s['taux'] < 50 ? 'bad' : ($s['taux'] < 70 ? 'low' : '') }}"><i style="width:{{ $s['taux'] }}%"></i></span>
                                    <span style="font-size:.78rem;font-weight:700">{{ $s['taux'] }}%</span>
                                </div>
                            </td>
                            <td class="muted"><i class="ph-duotone ph-caret-right"></i></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty">Aucune séance enregistrée sur cette période.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Drill modal --}}
<div class="ps-modal" id="psModal" onclick="if(event.target===this)closeSession()">
    <div class="ps-modal-box">
        <div class="ps-modal-head">
            <button class="ps-modal-close" onclick="closeSession()">&times;</button>
            <h5 id="psmTitle">Séance</h5>
            <div class="sub" id="psmSub"></div>
        </div>
        <div class="ps-cols">
            <div class="ps-col present">
                <h6><i class="ph-duotone ph-check-circle"></i> Présents <span class="cnt" id="psmPresentCount">0</span></h6>
                <ul class="slist" id="psmPresent"></ul>
            </div>
            <div class="ps-col absent">
                <h6><i class="ph-duotone ph-x-circle"></i> Absents <span class="cnt" id="psmAbsentCount">0</span></h6>
                <ul class="slist" id="psmAbsent"></ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
<script>
const PS_SESSION_URL = "{{ route('backoffice.crm.presence-stats.session') }}";
const PS_CHARTS = @json($charts);

/* ── Charts ─────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    if (typeof ApexCharts === 'undefined') return;

    // Chart 1 — présence vs absence trend (stacked area).
    // Pair each value with its date as a UTC timestamp so the datetime axis plots
    // correctly — a bare numeric series with string categories renders nothing.
    if (PS_CHARTS.trend.labels.length) {
        const toTs = d => new Date(d + 'T00:00:00Z').getTime();
        const presentPairs = PS_CHARTS.trend.labels.map((d, i) => ({ x: toTs(d), y: PS_CHARTS.trend.present[i] }));
        const absentPairs  = PS_CHARTS.trend.labels.map((d, i) => ({ x: toTs(d), y: PS_CHARTS.trend.absent[i] }));
        new ApexCharts(document.querySelector('#psChartTrend'), {
            chart:{type:'area',height:280,stacked:true,toolbar:{show:false},fontFamily:'inherit'},
            series:[
                {name:'Présents', data:presentPairs},
                {name:'Absents',  data:absentPairs},
            ],
            colors:['#1cc88a','#e74c3c'],
            dataLabels:{enabled:false},
            stroke:{curve:'smooth',width:2},
            fill:{type:'gradient',gradient:{opacityFrom:.4,opacityTo:.05}},
            xaxis:{
                type:'datetime',
                labels:{format:'dd/MM',style:{colors:'#9197ad',fontSize:'11px'}},
                axisBorder:{show:false},axisTicks:{show:false},
            },
            yaxis:{labels:{style:{colors:'#9197ad',fontSize:'11px'}}},
            legend:{position:'top',horizontalAlign:'right',fontSize:'12px'},
            grid:{borderColor:'#f0f1f7',strokeDashArray:4},
            tooltip:{x:{format:'dd/MM/yyyy'}},
        }).render();
    } else {
        document.querySelector('#psChartTrend').innerHTML =
            '<div style="text-align:center;color:#9197ad;padding:80px 0;font-size:.85rem">Pas de données</div>';
    }

    // Chart 2 — taux de présence par groupe (horizontal bar)
    if (PS_CHARTS.groups.labels.length) {
        new ApexCharts(document.querySelector('#psChartGroups'), {
            chart:{type:'bar',height:280,toolbar:{show:false},fontFamily:'inherit'},
            series:[{name:'Taux de présence', data:PS_CHARTS.groups.taux}],
            colors:['#4680ff'],
            plotOptions:{bar:{horizontal:true,borderRadius:5,barHeight:'62%',distributed:false}},
            dataLabels:{enabled:true,formatter:v=>v+'%',style:{fontSize:'11px',colors:['#fff']},offsetX:-2},
            xaxis:{
                categories:PS_CHARTS.groups.labels,
                max:100,
                labels:{formatter:v=>v+'%',style:{colors:'#9197ad',fontSize:'11px'}},
                axisBorder:{show:false},axisTicks:{show:false},
            },
            yaxis:{labels:{style:{colors:'#3a3f54',fontSize:'11px'}}},
            grid:{borderColor:'#f0f1f7',strokeDashArray:4},
            tooltip:{y:{formatter:v=>v+'%'}},
        }).render();
    } else {
        document.querySelector('#psChartGroups').innerHTML =
            '<div style="text-align:center;color:#9197ad;padding:80px 0;font-size:.85rem">Pas de données</div>';
    }
});

/* ── Searchable group combobox ──────────────────────────────────── */
(function(){
    const combo  = document.getElementById('groupCombo');
    if(!combo) return;
    const input  = document.getElementById('groupSearch');
    const menu   = document.getElementById('groupMenu');
    const hidden = document.getElementById('groupClassId');
    const opts   = Array.from(menu.querySelectorAll('.gsearch-opt'));

    function openMenu(){ menu.classList.add('open'); }
    function closeMenu(){ menu.classList.remove('open'); }

    function filter(q){
        q = q.trim().toLowerCase();
        opts.forEach(o => {
            // Always keep "Tous les groupes" visible
            const isAll = o.dataset.id === '';
            o.classList.toggle('hidden', !isAll && !o.textContent.toLowerCase().includes(q));
        });
    }

    function pick(opt){
        hidden.value = opt.dataset.id;
        input.value  = opt.dataset.id === '' ? '' : opt.textContent.trim();
        closeMenu();
    }

    input.addEventListener('focus', () => { filter(input.value); openMenu(); });
    input.addEventListener('input', () => { filter(input.value); openMenu(); hidden.value = ''; });
    opts.forEach(o => o.addEventListener('mousedown', e => { e.preventDefault(); pick(o); }));

    document.addEventListener('click', e => { if(!combo.contains(e.target)) closeMenu(); });

    // Keyboard: arrow up/down + enter
    input.addEventListener('keydown', e => {
        const visible = opts.filter(o => !o.classList.contains('hidden'));
        let idx = visible.findIndex(o => o.classList.contains('active'));
        if(e.key === 'ArrowDown'){ e.preventDefault(); openMenu(); idx = Math.min(idx+1, visible.length-1); }
        else if(e.key === 'ArrowUp'){ e.preventDefault(); idx = Math.max(idx-1, 0); }
        else if(e.key === 'Enter' && idx >= 0){ e.preventDefault(); pick(visible[idx]); return; }
        else return;
        opts.forEach(o => o.classList.remove('active'));
        if(visible[idx]){ visible[idx].classList.add('active'); visible[idx].scrollIntoView({block:'nearest'}); }
    });
})();

/* ── Drill modal ────────────────────────────────────────────────── */
function escapeHtml(s){const d=document.createElement('div');d.textContent=s??'';return d.innerHTML;}

function renderList(el, items, empty){
    if(!items.length){ el.innerHTML = `<li class="text-muted" style="background:none">${empty}</li>`; return; }
    el.innerHTML = items.map(p=>{
        let tags='';
        if(p.excuse) tags += '<span class="tag ex">excusé</span>';
        if(p.delay)  tags += '<span class="tag">retard</span>';
        return `<li>${escapeHtml(p.name)}${tags}</li>`;
    }).join('');
}

async function openSession(classId, date){
    const modal = document.getElementById('psModal');
    document.getElementById('psmTitle').textContent = 'Chargement…';
    document.getElementById('psmSub').textContent = '';
    document.getElementById('psmPresent').innerHTML = '';
    document.getElementById('psmAbsent').innerHTML  = '';
    modal.classList.add('show');

    try {
        const url = `${PS_SESSION_URL}?classId=${classId}&date=${encodeURIComponent(date)}`;
        const res = await fetch(url, {headers:{'Accept':'application/json'}});
        const d = await res.json();

        document.getElementById('psmTitle').textContent = d.class_name || 'Séance';
        const time = d.start_time ? ` · ${d.start_time}–${d.end_time}` : '';
        document.getElementById('psmSub').textContent =
            `${new Date(date).toLocaleDateString('fr-FR')}${time} · ${d.teacher||'—'}`;

        document.getElementById('psmPresentCount').textContent = (d.present||[]).length;
        document.getElementById('psmAbsentCount').textContent  = (d.absent||[]).length;
        renderList(document.getElementById('psmPresent'), d.present||[], 'Aucun présent');
        renderList(document.getElementById('psmAbsent'),  d.absent||[],  'Aucun absent');
    } catch(e){
        document.getElementById('psmTitle').textContent = 'Erreur de chargement';
    }
}

function closeSession(){ document.getElementById('psModal').classList.remove('show'); }
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeSession(); });
</script>
@endsection
