@extends('layouts.main')

@section('title', 'Statistiques de présence')
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'Statistiques de présence')

@section('css')
<style>
*,*::before,*::after{box-sizing:border-box}
.ps-card{background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.06);overflow:hidden}

/* KPI tiles */
.ps-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:22px}
.ps-kpi{background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 2px 12px rgba(0,0,0,.05);border-left:4px solid #4680ff}
.ps-kpi .v{font-size:1.6rem;font-weight:800;color:#1a1a2e;line-height:1}
.ps-kpi .l{font-size:.74rem;color:#6c757d;text-transform:uppercase;letter-spacing:.05em;margin-top:6px}
.ps-kpi.k-present{border-left-color:#1cc88a}
.ps-kpi.k-absent {border-left-color:#e74c3c}
.ps-kpi.k-rate   {border-left-color:#f6c23e}

/* Filters */
.ps-filters{display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;margin-bottom:22px}
.ps-filters .fg{display:flex;flex-direction:column;gap:4px}
.ps-filters label{font-size:.72rem;font-weight:600;color:#6c757d;text-transform:uppercase;letter-spacing:.04em}
.ps-filters input,.ps-filters select{border:1px solid #e3e6ef;border-radius:8px;padding:8px 12px;font-size:.85rem;min-width:160px}

/* Session table */
.ps-table{width:100%;border-collapse:separate;border-spacing:0}
.ps-table thead th{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#9ca3af;font-weight:600;padding:12px 14px;text-align:left;border-bottom:1px solid #f0f0f5;white-space:nowrap}
.ps-table tbody td{padding:12px 14px;font-size:.85rem;color:#374151;border-bottom:1px solid #f6f6fa;vertical-align:middle}
.ps-table tbody tr{cursor:pointer;transition:background .12s}
.ps-table tbody tr:hover{background:#f7f9ff}
.pill{display:inline-flex;align-items:center;gap:5px;font-size:.78rem;font-weight:700;padding:3px 10px;border-radius:20px}
.pill.p{background:#e6f9f1;color:#0f9d6b}
.pill.a{background:#fdeaea;color:#d9434e}
.bar{height:7px;border-radius:4px;background:#eef0f5;overflow:hidden;min-width:90px}
.bar > i{display:block;height:100%;background:linear-gradient(90deg,#1cc88a,#36d39a)}
.muted{color:#9ca3af}
.empty{text-align:center;padding:48px 20px;color:#9ca3af}

/* Drill modal */
.ps-modal{position:fixed;inset:0;background:rgba(20,22,40,.55);display:none;align-items:center;justify-content:center;z-index:1080;padding:20px}
.ps-modal.show{display:flex}
.ps-modal-box{background:#fff;border-radius:18px;width:min(720px,100%);max-height:88vh;overflow:auto;box-shadow:0 24px 60px rgba(0,0,0,.3)}
.ps-modal-head{padding:20px 24px;border-bottom:1px solid #f0f0f5;position:sticky;top:0;background:#fff;z-index:2}
.ps-modal-head h5{margin:0;font-weight:800;color:#1a1a2e}
.ps-modal-head .sub{font-size:.8rem;color:#6c757d;margin-top:4px}
.ps-modal-close{position:absolute;top:18px;right:20px;border:none;background:#f1f3f8;width:34px;height:34px;border-radius:50%;cursor:pointer;font-size:1.1rem;color:#555}
.ps-cols{display:grid;grid-template-columns:1fr 1fr;gap:0}
.ps-col{padding:18px 24px}
.ps-col + .ps-col{border-left:1px solid #f0f0f5}
.ps-col h6{font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;display:flex;align-items:center;gap:8px;margin-bottom:12px}
.ps-col h6 .cnt{margin-left:auto;font-weight:800}
.ps-col.present h6{color:#0f9d6b}
.ps-col.absent  h6{color:#d9434e}
.slist{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:6px}
.slist li{font-size:.85rem;color:#374151;padding:8px 10px;border-radius:8px;background:#fafbfd;display:flex;align-items:center;gap:8px}
.slist li .tag{font-size:.65rem;font-weight:700;padding:1px 7px;border-radius:10px;background:#fff3d6;color:#b78103}
.slist li .tag.ex{background:#e8f0ff;color:#3461c9}
@media(max-width:600px){.ps-cols{grid-template-columns:1fr}.ps-col+.ps-col{border-left:none;border-top:1px solid #f0f0f5}}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-1">Statistiques de présence</h4>
        <p class="text-muted mb-4" style="font-size:.85rem">
            Chaque séance avec son taux de présence. Cliquez une ligne pour voir les présents et les absents.
        </p>

        {{-- Filters --}}
        <form method="GET" class="ps-filters">
            <div class="fg">
                <label>Date début</label>
                <input type="date" name="startDate" value="{{ $startDate }}">
            </div>
            <div class="fg">
                <label>Date fin</label>
                <input type="date" name="endDate" value="{{ $endDate }}">
            </div>
            <div class="fg">
                <label>Groupe</label>
                <select name="classId">
                    <option value="">Tous les groupes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c['id'] }}" {{ $classId === $c['id'] ? 'selected' : '' }}>{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fg">
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </div>
        </form>

        {{-- KPIs --}}
        <div class="ps-kpis">
            <div class="ps-kpi">
                <div class="v">{{ number_format($totals['sessions']) }}</div>
                <div class="l">Séances</div>
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
                                        <span class="bar"><i style="width:{{ $s['taux'] }}%"></i></span>
                                        <span style="font-size:.78rem;font-weight:700">{{ $s['taux'] }}%</span>
                                    </div>
                                </td>
                                <td class="muted"><i class="ph ph-caret-right"></i></td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><div class="empty">Aucune séance enregistrée sur cette période.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
                <h6><i class="ph-fill ph-check-circle"></i> Présents <span class="cnt" id="psmPresentCount">0</span></h6>
                <ul class="slist" id="psmPresent"></ul>
            </div>
            <div class="ps-col absent">
                <h6><i class="ph-fill ph-x-circle"></i> Absents <span class="cnt" id="psmAbsentCount">0</span></h6>
                <ul class="slist" id="psmAbsent"></ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const PS_SESSION_URL = "{{ route('backoffice.crm.presence-stats.session') }}";

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
