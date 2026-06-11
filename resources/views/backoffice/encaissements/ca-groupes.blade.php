@extends('layouts.main')

@section('title', 'CA par Groupe et par Jour')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'CA par Groupe')

@section('content')

@php
    $grandTotal   = 0;
    $totalOps     = 0;
    $activeGroups = count($byGroup);
    $activeDays   = count($allDays);

    foreach ($byGroup as $dayData) {
        $grandTotal += array_sum(array_column($dayData, 'total'));
        $totalOps   += array_sum(array_column($dayData, 'count'));
    }

    $avgPerGroup = $activeGroups > 0 ? $grandTotal / $activeGroups : 0;

    // Parse month label
    $monthLabel = $month
        ? \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y')
        : now()->translatedFormat('F Y');
@endphp

{{-- ── Filters ─────────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-auto">
                <label class="form-label fw-semibold mb-0">
                    <i class="ph-duotone ph-funnel me-1"></i> Filtres
                </label>
            </div>

            @if(count($sites) > 1)
            <div class="col-12 col-sm-3">
                <select name="site_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les centres</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" {{ $siteId == $site->id ? 'selected' : '' }}>
                            {{ $site->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-12 col-sm-3">
                <input type="month" name="month" class="form-control"
                       value="{{ $month }}" onchange="this.form.submit()">
            </div>

            @if(request()->hasAny(['site_id', 'month']))
            <div class="col-12 col-sm-auto">
                <a href="{{ route('backoffice.encaissements.ca-groupes') }}"
                   class="btn btn-outline-secondary">
                    <i class="ph-duotone ph-x me-1"></i> Reset
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- ── KPI Cards ────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card bg-light-primary h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="flex-shrink-0 avtar avtar-s bg-primary">
                    <i class="ph-duotone ph-currency-circle-dollar text-white" style="font-size:1.25rem;"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-muted mb-0" style="font-size:.75rem;">Total encaissé</p>
                    <h5 class="mb-0 text-primary text-truncate">
                        {{ number_format($grandTotal, 0, ',', ' ') }} <small class="fw-normal">DH</small>
                    </h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-light-success h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="flex-shrink-0 avtar avtar-s bg-success">
                    <i class="ph-duotone ph-receipt text-white" style="font-size:1.25rem;"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-muted mb-0" style="font-size:.75rem;">Opérations</p>
                    <h5 class="mb-0 text-success">{{ number_format($totalOps) }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-light-warning h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="flex-shrink-0 avtar avtar-s bg-warning">
                    <i class="ph-duotone ph-users-three text-white" style="font-size:1.25rem;"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-muted mb-0" style="font-size:.75rem;">Groupes actifs</p>
                    <h5 class="mb-0 text-warning">{{ $activeGroups }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-light-info h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="flex-shrink-0 avtar avtar-s bg-info">
                    <i class="ph-duotone ph-calendar-check text-white" style="font-size:1.25rem;"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-muted mb-0" style="font-size:.75rem;">Jours actifs</p>
                    <h5 class="mb-0 text-info">{{ $activeDays }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Table ─────────────────────────────────────────────────────────────── --}}
<div class="card table-card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="ph-duotone ph-table text-primary" style="font-size:1.1rem;"></i>
            <h5 class="mb-0">CA par Groupe &amp; par Jour</h5>
            <span class="badge bg-light-primary text-primary">{{ $monthLabel }}</span>
        </div>
        @if($grandTotal > 0)
        <span class="badge bg-success fs-6 px-3 py-2">
            Total : {{ number_format($grandTotal, 0, ',', ' ') }} DH
        </span>
        @endif
    </div>

    <div class="card-body p-0">
        @if(count($byGroup))

        <div class="table-responsive" style="overflow-x: auto; max-height: 70vh; overflow-y: auto;">
            <table class="table table-hover table-sm table-bordered align-middle text-center mb-0"
                   style="min-width: max-content;">
                <thead style="position: sticky; top: 0; z-index: 3;">
                    <tr style="background: #1e2a3a;">
                        <th class="text-start ps-3"
                            style="min-width: 220px; position: sticky; left: 0; background: #1e2a3a; z-index: 4; color: #fff; border-right: 2px solid #4a5568;">
                            <i class="ph-duotone ph-stack me-1" style="opacity:.7;"></i> Groupe
                        </th>
                        @foreach($allDays as $day)
                            @php
                                $parsed   = \Carbon\Carbon::parse($day);
                                $isWeekend = $parsed->isWeekend();
                            @endphp
                            <th style="min-width: 85px; background: {{ $isWeekend ? '#2d3a4a' : '#1e2a3a' }}; color: {{ $isWeekend ? '#94a3b8' : '#fff' }};">
                                <span style="font-size:.8rem;">{{ $parsed->format('d/m') }}</span><br>
                                <small style="font-size:.7rem; opacity:.7; font-weight:400;">{{ $parsed->translatedFormat('D') }}</small>
                            </th>
                        @endforeach
                        <th style="min-width: 120px; background: #d97706; color: #fff; font-size:.85rem; border-left: 2px solid #92400e;">
                            <i class="ph-duotone ph-sigma me-1"></i> Total
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($byGroup as $groupName => $dayData)
                        @php
                            $groupTotal = array_sum(array_column($dayData, 'total'));
                            $groupOps   = array_sum(array_column($dayData, 'count'));
                        @endphp
                        <tr>
                            <td class="text-start ps-3 fw-semibold"
                                style="position: sticky; left: 0; background: #f8fafc; z-index: 2; border-right: 2px solid #e2e8f0; max-width: 220px;">
                                <span class="d-block text-truncate" title="{{ $groupName }}"
                                      style="max-width: 200px;">
                                    {{ $groupName }}
                                </span>
                            </td>
                            @foreach($allDays as $day)
                                @php $isWeekend = \Carbon\Carbon::parse($day)->isWeekend(); @endphp
                                <td style="{{ $isWeekend ? 'background: #fafafa;' : '' }}">
                                    @if(isset($dayData[$day]))
                                        <span class="fw-semibold" style="color: #059669; font-size:.85rem;">
                                            {{ number_format($dayData[$day]['total'], 0, ',', ' ') }}
                                        </span>
                                        <br>
                                        <small class="text-muted" style="font-size:.7rem;">
                                            {{ $dayData[$day]['count'] }} op.
                                        </small>
                                    @else
                                        <span class="text-muted" style="opacity:.3; font-size:.9rem;">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="fw-bold" style="background: #fef3c7; border-left: 2px solid #fde68a;">
                                <span style="color: #92400e; font-size:.85rem;">
                                    {{ number_format($groupTotal, 0, ',', ' ') }} DH
                                </span>
                                <br>
                                <small class="text-muted" style="font-size:.7rem;">{{ $groupOps }} op.</small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot style="position: sticky; bottom: 0; z-index: 3;">
                    <tr style="background: #334155;">
                        <td class="text-start ps-3 fw-bold"
                            style="position: sticky; left: 0; background: #334155; z-index: 4; color: #fff; border-right: 2px solid #475569; font-size:.8rem;">
                            <i class="ph-duotone ph-sigma me-1"></i> Total / jour
                        </td>
                        @foreach($allDays as $day)
                            @php
                                $dayTotal = 0;
                                $dayOps   = 0;
                                foreach ($byGroup as $dayData) {
                                    $dayTotal += $dayData[$day]['total'] ?? 0;
                                    $dayOps   += $dayData[$day]['count'] ?? 0;
                                }
                                $isWeekend = \Carbon\Carbon::parse($day)->isWeekend();
                            @endphp
                            <td style="background: {{ $isWeekend ? '#3d4f63' : '#334155' }};">
                                @if($dayTotal > 0)
                                    <span class="fw-semibold" style="color: #86efac; font-size:.85rem;">
                                        {{ number_format($dayTotal, 0, ',', ' ') }}
                                    </span>
                                    <br>
                                    <small style="color: #94a3b8; font-size:.7rem;">{{ $dayOps }} op.</small>
                                @else
                                    <span style="color: #475569; opacity:.5;">—</span>
                                @endif
                            </td>
                        @endforeach
                        <td style="background: #d97706; border-left: 2px solid #92400e;">
                            <span class="fw-bold" style="color: #fff; font-size:.85rem;">
                                {{ number_format($grandTotal, 0, ',', ' ') }} DH
                            </span>
                            <br>
                            <small style="color: #fef3c7; font-size:.7rem;">{{ $totalOps }} op.</small>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Legend --}}
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-top"
             style="font-size:.75rem; color: #64748b; background: #f8fafc;">
            <span><span class="d-inline-block rounded me-1"
                style="width:10px;height:10px;background:#059669;"></span> Montant encaissé</span>
            <span><span class="d-inline-block rounded me-1"
                style="width:10px;height:10px;background:#e2e8f0;"></span> Weekend</span>
            <span><i class="ph-duotone ph-info me-1"></i>
                Les chiffres sont en DH — <em>op.</em> = nombre d'opérations
            </span>
        </div>

        @else

        {{-- Empty state --}}
        <div class="text-center py-5">
            <div class="mb-3" style="font-size: 3rem; opacity: .3;">
                <i class="ph-duotone ph-chart-bar"></i>
            </div>
            <h5 class="text-muted mb-1">Aucun encaissement pour cette période</h5>
            <p class="text-muted mb-3" style="font-size:.875rem;">
                Aucune donnée trouvée pour <strong>{{ $monthLabel }}</strong>
                @if($siteId)
                    sur ce centre.
                @else
                    sur tous les centres.
                @endif
            </p>
            <a href="{{ route('backoffice.encaissements.ca-groupes') }}"
               class="btn btn-outline-primary btn-sm">
                <i class="ph-duotone ph-arrow-counter-clockwise me-1"></i> Voir le mois actuel
            </a>
        </div>

        @endif
    </div>
</div>

@endsection
