@extends('layouts.main')

@section('title', 'CA par Groupe et par Jour')
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-active', 'CA par Groupe')

@section('content')

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-sm-auto">
                    <label class="form-label fw-semibold">
                        <i class="ph-duotone ph-funnel me-1"></i> Filtres
                    </label>
                </div>
                @if(count($sites) > 1)
                <div class="col-12 col-sm">
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
                <div class="col-12 col-sm">
                    <input type="month" name="month" class="form-control"
                           value="{{ $month }}" onchange="this.form.submit()">
                </div>
                @if(request()->hasAny(['site_id', 'month']))
                    <div class="col-12 col-sm-auto">
                        <a href="{{ route('backoffice.encaissements.ca-groupes') }}" class="btn btn-outline-secondary">
                            <i class="ph-duotone ph-x me-1"></i> Reset
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card table-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">
                CA par Groupe et par Jour
                <span class="badge bg-light-primary text-primary ms-2">{{ $month }}</span>
            </h5>
            @if(count($byGroup))
                @php
                    $grandTotal = 0;
                    foreach ($byGroup as $dayData) {
                        $grandTotal += array_sum(array_column($dayData, 'total'));
                    }
                @endphp
                <span class="fw-bold text-success">
                    {{ number_format($grandTotal, 0, ',', ' ') }} DH
                </span>
            @endif
        </div>
        <div class="card-body pt-3">
            @if(count($byGroup))
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-hover table-sm table-bordered align-middle text-center" style="min-width: max-content;">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-start" style="min-width: 200px; position: sticky; left: 0; background: #1a202c; z-index: 2;">
                                    Groupe
                                </th>
                                @foreach($allDays as $day)
                                    <th style="min-width: 90px;">
                                        {{ \Carbon\Carbon::parse($day)->format('d/m') }}
                                        <br>
                                        <small class="text-muted fw-normal">{{ \Carbon\Carbon::parse($day)->translatedFormat('D') }}</small>
                                    </th>
                                @endforeach
                                <th class="bg-warning text-dark" style="min-width: 110px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byGroup as $groupName => $dayData)
                                @php $groupTotal = array_sum(array_column($dayData, 'total')); @endphp
                                <tr>
                                    <td class="text-start fw-semibold"
                                        style="position: sticky; left: 0; background: white; z-index: 1;">
                                        {{ $groupName }}
                                    </td>
                                    @foreach($allDays as $day)
                                        <td>
                                            @if(isset($dayData[$day]))
                                                <span class="text-success fw-semibold">
                                                    {{ number_format($dayData[$day]['total'], 0, ',', ' ') }}
                                                </span>
                                                <br>
                                                <small class="text-muted">{{ $dayData[$day]['count'] }} op.</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="fw-bold table-warning">
                                        {{ number_format($groupTotal, 0, ',', ' ') }} DH
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td class="text-start"
                                    style="position: sticky; left: 0; background: #e2e3e5; z-index: 1;">
                                    Total / jour
                                </td>
                                @foreach($allDays as $day)
                                    @php
                                        $dayTotal = 0;
                                        foreach ($byGroup as $dayData) {
                                            $dayTotal += $dayData[$day]['total'] ?? 0;
                                        }
                                    @endphp
                                    <td>
                                        @if($dayTotal > 0)
                                            {{ number_format($dayTotal, 0, ',', ' ') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="table-warning">
                                    {{ number_format($grandTotal, 0, ',', ' ') }} DH
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <i class="ph-duotone ph-info me-2"></i>
                    Aucun encaissement pour cette période.
                </div>
            @endif
        </div>
    </div>

@endsection
