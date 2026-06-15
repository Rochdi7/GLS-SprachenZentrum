@extends('layouts.main')

@section('title', 'Suivi niveau')
@section('breadcrumb-item', 'Dashboard')
@section('breadcrumb-item-active', 'Suivi niveau')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        /* ── Level pills ── */
        .lvl-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            font-size: .75rem;
            font-weight: 700;
            border: 2px solid transparent;
        }
        .lvl-pill--done     { background: #e6faf0; color: #09b10f; border-color: #09b10f; }
        .lvl-pill--current  { background: #eaf3ff; color: #2f7ed8; border-color: #2f7ed8; }
        .lvl-pill--pending  { background: #f5f7fa; color: #a0a9b8; border-color: #d0d5de; }
        .lvl-pill--inactive { background: #f9f9f9; color: #ccc;    border-color: #e8e8e8; }

        /* ── Mini progress bar ── */
        .mini-bar {
            height: 5px;
            border-radius: 999px;
            background: #e9ecf1;
            overflow: hidden;
            margin-top: 5px;
            width: 100%;
        }
        .mini-bar__fill { height: 100%; border-radius: 999px; background: #2f7ed8; }

        /* ── Table row ── */
        .fup-table tbody tr { vertical-align: middle; }
        .fup-table .group-name { font-weight: 700; color: #243042; font-size: .92rem; }
        .fup-table .teacher-name { font-size: .83rem; color: #667085; }
        .fup-table td { padding: 14px 12px; border-bottom: 1px solid #f1f4f9; }
        .fup-table thead th { padding: 10px 12px; font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .05em; color: #8a93a8;
            border-bottom: 2px solid #eef2f7; background: #fafbfc; }
        .fup-table tr:last-child td { border-bottom: none; }

        .pill-row { display: flex; gap: 6px; align-items: center; }

        .due-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px; border-radius: 999px;
            font-size: .75rem; font-weight: 600;
        }
        .due-badge--overdue { background: #fff0f0; color: #c0392b; }
        .due-badge--ok      { background: #f0f4ff; color: #3a5fa0; }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- ── Filters card ── --}}
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0">Suivi niveau</h5>
                        @if($dueFollowups->count())
                            <span class="badge bg-danger">{{ $dueFollowups->count() }} en retard</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @can('level_followups.edit')
                        <form method="POST" action="{{ route('backoffice.level_followups.sync') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-light-secondary"
                                    onclick="return confirm('Synchroniser le suivi niveau pour tous les groupes actifs ?')">
                                <i class="ti ti-refresh me-1"></i>Sync
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
                <div class="card-body pb-2">
                    <form method="GET" action="{{ route('backoffice.level_followups.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label">Centre</label>
                            <select name="center" id="filter-center" class="form-select form-select-sm">
                                <option value="">Tous les centres</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" @selected((string)request('center')===(string)$site->id)>{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label">Prof</label>
                            <select name="teacher" id="filter-teacher" class="form-select form-select-sm">
                                <option value="">Tous les profs</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}"
                                            data-sites="{{ implode(',', $teacherSiteMap[$teacher->id] ?? []) }}"
                                            @selected((string)request('teacher')===(string)$teacher->id)>{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                            <a href="{{ route('backoffice.level_followups.index') }}" class="btn btn-sm btn-light-secondary ms-1">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ── Table card ── --}}
            <div class="card">
                <div class="card-body p-0">
                    @if($followups->isEmpty())
                        <p class="text-muted p-4 mb-0">Aucun suivi niveau généré.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table fup-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Groupe · Prof</th>
                                        <th>Niveaux</th>
                                        <th>Progression</th>
                                        <th>Échéance</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($followups as $followup)
                                    @php
                                        $order = ['A1','A2','B1','B2'];
                                        $startLevel = $followup->group?->level;
                                        $startIndex = is_string($startLevel) ? array_search($startLevel, $order, true) : 0;
                                        $startIndex = ($startIndex === false) ? 0 : $startIndex;

                                        $allForGroup   = $levelFollowupsByGroup[$followup->group_id] ?? collect();
                                        $activeSegments = $allForGroup
                                            ->whereIn('level', array_slice($order, $startIndex))
                                            ->sortBy('level_start_date')
                                            ->values();

                                        $totalDays = 0; $elapsedDays = 0; $currentLevel = null;
                                        $countWeekdays = function($from, $to) {
                                            $count = 0; $cur = $from->copy();
                                            while ($cur->lte($to)) { if (!$cur->isWeekend()) $count++; $cur->addDay(); }
                                            return $count;
                                        };

                                        foreach ($activeSegments as $seg) {
                                            $segStart = $seg->level_start_date ? \Carbon\Carbon::parse($seg->level_start_date)->startOfDay() : null;
                                            $segEnd   = $seg->level_end_date   ? \Carbon\Carbon::parse($seg->level_end_date)->startOfDay()   : null;
                                            if (!$segStart || !$segEnd || $segEnd->lt($segStart)) continue;
                                            $segDays = $countWeekdays($segStart, $segEnd);
                                            $totalDays += $segDays;
                                            if ($now->lt($segStart)) continue;
                                            if ($now->gt($segEnd))   { $elapsedDays += $segDays; continue; }
                                            $currentLevel  = $seg->level;
                                            $elapsedDays  += $countWeekdays($segStart, $now->copy()->startOfDay());
                                        }

                                        $percent = $totalDays > 0 ? (int) round(($elapsedDays / $totalDays) * 100) : 0;

                                        $levelState = [];
                                        foreach ($order as $lvl) $levelState[$lvl] = 'inactive';
                                        foreach ($activeSegments as $seg) {
                                            $lvl = $seg->level;
                                            $sS = $seg->level_start_date ? \Carbon\Carbon::parse($seg->level_start_date)->startOfDay() : null;
                                            $sE = $seg->level_end_date   ? \Carbon\Carbon::parse($seg->level_end_date)->startOfDay()   : null;
                                            if (!$sS || !$sE) continue;
                                            if ($now->gt($sE))                      $levelState[$lvl] = 'done';
                                            elseif ($now->betweenIncluded($sS,$sE)) $levelState[$lvl] = 'current';
                                            else                                     $levelState[$lvl] = 'pending';
                                        }

                                        $isDue = ($followup->status === 'pending') && $followup->due_date && \Carbon\Carbon::parse($followup->due_date)->lte($now);
                                        $isCurrentStatus = $followup->status !== 'done' && $currentLevel !== null;
                                        $statusClass = $followup->status === 'done' ? 'bg-light-success text-success'
                                            : ($isCurrentStatus ? 'bg-light-primary text-primary' : 'bg-light-warning text-warning');
                                        $statusLabel = $followup->status === 'done' ? 'Terminé' : ($isCurrentStatus ? 'En cours' : 'En attente');
                                    @endphp
                                    <tr>
                                        {{-- Groupe · Prof --}}
                                        <td>
                                            <div class="group-name">{{ $followup->group?->name ?? '—' }}</div>
                                            <div class="teacher-name">
                                                <i class="ti ti-user f-12 me-1"></i>{{ $followup->group?->teacher?->name ?? '—' }}
                                            </div>
                                        </td>

                                        {{-- Level pills --}}
                                        <td>
                                            <div class="pill-row">
                                                @foreach($order as $idx => $lvl)
                                                    @php $state = $idx < $startIndex ? 'inactive' : ($levelState[$lvl] ?? 'pending'); @endphp
                                                    <span class="lvl-pill lvl-pill--{{ $state }}" title="{{ $lvl }}">{{ $lvl }}</span>
                                                @endforeach
                                            </div>
                                        </td>

                                        {{-- Progression bar --}}
                                        <td style="min-width:100px">
                                            <div style="font-size:.8rem;font-weight:700;color:#243042">{{ $percent }}%</div>
                                            <div class="mini-bar"><div class="mini-bar__fill" style="width:{{ $percent }}%"></div></div>
                                        </td>

                                        {{-- Échéance --}}
                                        <td>
                                            @if($followup->due_date)
                                                <span class="due-badge {{ $isDue ? 'due-badge--overdue' : 'due-badge--ok' }}">
                                                    <i class="ti ti-calendar-event f-13"></i>
                                                    {{ \Carbon\Carbon::parse($followup->due_date)->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted" style="font-size:.8rem">—</span>
                                            @endif
                                        </td>

                                        {{-- Statut --}}
                                        <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>

                                        {{-- Actions --}}
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-1">
                                                <a href="{{ route('backoffice.level_followups.group_show', $followup->group_id) }}"
                                                   class="avtar avtar-xs btn-link-secondary" title="Voir détails">
                                                    <i class="ti ti-eye f-18"></i>
                                                </a>
                                                <a href="{{ route('backoffice.level_followups.group_pdf', $followup->group_id) }}"
                                                   class="avtar avtar-xs btn-link-danger" title="PDF">
                                                    <i class="ti ti-download f-18"></i>
                                                </a>
                                                @if($followup->status !== 'done')
                                                    <form method="POST" action="{{ route('backoffice.level_followups.complete', $followup) }}" class="d-inline-block">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success py-1 px-2" style="font-size:.75rem">
                                                            <i class="ti ti-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('backoffice.level_followups.destroy', $followup) }}" method="POST" class="d-inline-block">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                            class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                                            onclick="return confirm('Supprimer ce suivi niveau ?')" title="Supprimer">
                                                        <i class="ti ti-trash f-18"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($followups->hasPages())
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top">
                                <small class="text-muted">
                                    {{ $followups->firstItem() }}–{{ $followups->lastItem() }} sur {{ $followups->total() }}
                                </small>
                                {{ $followups->withQueryString()->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    const centerSel  = document.getElementById('filter-center');
    const teacherSel = document.getElementById('filter-teacher');
    if (!centerSel || !teacherSel) return;
    const allOpts = Array.from(teacherSel.querySelectorAll('option[data-sites]'));
    function filterTeachers() {
        const cid = centerSel.value;
        const cur = teacherSel.value;
        allOpts.forEach(opt => {
            const sites = opt.dataset.sites ? opt.dataset.sites.split(',') : [];
            const show  = !cid || sites.includes(cid);
            opt.hidden = !show; opt.disabled = !show;
        });
        if (cur) {
            const sel = teacherSel.querySelector(`option[value="${cur}"]`);
            if (sel && sel.hidden) teacherSel.value = '';
        }
    }
    centerSel.addEventListener('change', filterTeachers);
    filterTeachers();
})();
</script>
@endsection
