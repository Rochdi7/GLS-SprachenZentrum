@extends('layouts.main')

@section('title', 'CRM — Recouvrement')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Recouvrement')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice/crm-payment-collection.css') }}">
@endsection

@section('content')
<div class="recouv-page">

    @include('backoffice.crm.partials._center')



    {{-- Filters --}}
    <form method="GET" class="card mb-3 recouv-filterbar">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">Groupe</label>
                    <select name="classId" class="form-select form-select-sm">
                        <option value="">Choisir une formation</option>
                        @foreach($lovClasses as $c)
                            @php
                                $id = $c['CLASS_ID'] ?? $c['ID'] ?? null;
                                $name = $c['NAME'] ?? $c['REFERENCE'] ?? '#'.$id;
                            @endphp
                            @if($id)
                                <option value="{{ $id }}" {{ (string) request('classId') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">Offres</label>
                    <select name="levelSessionPackageId" class="form-select form-select-sm">
                        <option value="">Choisir un élément</option>
                        @foreach($lovPackages as $p)
                            @php $id = $p['ID'] ?? null; $name = $p['NAME'] ?? $p['REFERENCE'] ?? '#'.$id; @endphp
                            @if($id)
                                <option value="{{ $id }}" {{ (string) request('levelSessionPackageId') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">Frais</label>
                    <select name="prestationTypeId" class="form-select form-select-sm">
                        <option value="">Choisir un élément</option>
                        @foreach($lovPaymentTypes as $t)
                            @php $id = $t['ID'] ?? null; $name = $t['NAME'] ?? '#'.$id; @endphp
                            @if($id)
                                <option value="{{ $id }}" {{ (string) request('prestationTypeId') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">Statut</label>
                    <select name="registrationStatusId" class="form-select form-select-sm">
                        <option value="">Choisir un statut</option>
                        @foreach($lovRegistrationStatus as $s)
                            @php $id = $s['ID'] ?? null; $name = $s['NAME'] ?? '#'.$id; @endphp
                            @if($id)
                                <option value="{{ $id }}" {{ (string) request('registrationStatusId') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">Type</label>
                    <select name="serviceTypeIds" class="form-select form-select-sm">
                        <option value="">Choisir un type</option>
                        @foreach($lovPaymentTypes as $t)
                            @php $id = $t['ID'] ?? null; $name = $t['NAME'] ?? '#'.$id; @endphp
                            @if($id)
                                <option value="{{ $id }}" {{ (string) request('serviceTypeIds') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">Date de début</label>
                    <input type="date" name="dueDateStartDate" value="{{ $dueDateStart }}" class="form-control form-control-sm">
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">Date de fin</label>
                    <input type="date" name="dueDateEndDate" value="{{ $dueDateEnd }}" class="form-control form-control-sm">
                </div>

                <div class="col-12 col-md-6 col-lg-3 d-flex align-items-end gap-2">
                    <a href="{{ route('backoffice.crm.payment-collection') }}" class="btn btn-sm btn-outline-secondary" title="Réinitialiser">
                        <i class="ti ti-refresh"></i>
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                        <i class="ti ti-search me-1"></i> Chercher
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#recouv-extra-filters" title="Plus de filtres">
                        <i class="ti ti-adjustments-horizontal"></i>
                    </button>
                </div>
            </div>

            <div id="recouv-extra-filters" class="collapse mt-3">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label">Étudiant</label>
                        <div class="crm-student-ac position-relative">
                            <input type="hidden" name="studentId" value="{{ request('studentId') }}" data-role="value">
                            <input type="text"
                                   class="form-control form-control-sm crm-student-ac-search"
                                   placeholder="Tapez un nom (≥ 2 caractères)…"
                                   value="{{ request('studentId') ? '#' . request('studentId') : '' }}"
                                   autocomplete="off"
                                   data-role="search">
                            <div class="crm-student-ac-menu list-group shadow-sm" data-role="menu"
                                 style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1050; max-height:280px; overflow-y:auto;"></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Inscription ID</label>
                        <input type="number" name="registrationId" value="{{ request('registrationId') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Retard min (jours)</label>
                        <input type="number" name="startDay" value="{{ request('startDay') }}" class="form-control form-control-sm" min="0">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Retard max (jours)</label>
                        <input type="number" name="endDay" value="{{ request('endDay') }}" class="form-control form-control-sm" min="0">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Année scolaire ID</label>
                        <input type="number" name="schoolYearId" value="{{ request('schoolYearId') }}" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
        </div>
    </form>

    @include('backoffice.crm.partials._student_autocomplete')

    {{-- Error / empty / table --}}
    @if($error)
        {{-- Reuse the friendly error block from the generic table partial --}}
        @include('backoffice.crm.partials._table')
    @elseif(!$payload || empty($payload['data']))
        <div class="alert alert-info mb-0">
            <i class="ti ti-info-circle me-1"></i>
            Aucun recouvrement trouvé pour la période sélectionnée.
        </div>
    @else
        @php
            $rows       = $payload['data'];
            $pagination = $payload['pagination'] ?? null;

            $pickFirst = function (array $row, array $keys, $default = null) {
                foreach ($keys as $k) {
                    if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') {
                        return $row[$k];
                    }
                }
                return $default;
            };
        @endphp

        {{-- Summary bar — sum is computed by the controller across ALL pages,
             so it matches what the reference CRM shows (not just current page). --}}
        <div class="d-flex justify-content-start align-items-center mb-2 flex-wrap gap-2">
            <div class="recouv-summary">
                <i class="ti ti-cash text-primary"></i>
                Montant total : {{ number_format($sumRemaining, 0, ',', ' ') }} DH
                @if($totalRowsSummed > 0)
                    <span class="text-muted small fw-normal ms-1">({{ $totalRowsSummed }} ligne{{ $totalRowsSummed > 1 ? 's' : '' }})</span>
                @endif
            </div>
        </div>

        {{-- Row data stashed in a single JSON script so the row-detail modal
             can pull full row objects by index (same pattern as _table.blade.php). --}}
        @php $tableId = uniqid(); @endphp
        <script type="application/json" id="crm-rows-data-{{ $tableId }}">{!! json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle recouv-table mb-0" data-crm-table-id="{{ $tableId }}">
                        <thead>
                            <tr>
                                <th style="width: 38px;"><input type="checkbox" class="form-check-input"></th>
                                <th>Référence</th>
                                <th>Étudiant</th>
                                <th>Statut</th>
                                <th>Téléphone</th>
                                <th>Frais</th>
                                <th>Dernière présence</th>
                                <th>Date d'échéance</th>
                                <th>Retard</th>
                                <th>Reste à payer</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $rowIndex => $row)
                                @php
                                    $reference   = $pickFirst($row, ['REGISTRATION_REFERENCE', 'REFERENCE']);
                                    $studentName = $pickFirst($row, ['STUDENT_FULL_NAME', 'STUDENT_NAME']);
                                    $statusName  = $pickFirst($row, ['REGISTRATION_STATUS_NAME', 'STATUS_NAME']);
                                    $phone       = $pickFirst($row, ['PHONE_NUMBER', 'STUDENT_PHONE', 'PHONE']);
                                    $whatsapp    = $pickFirst($row, ['WHATSAPP_NUMBER', 'STUDENT_WHATSAPP']) ?: $phone;
                                    $fees        = $pickFirst($row, ['PRESTATION_NAME', 'SERVICE_TYPE_NAME', 'PRESTATION_TYPE_NAME', 'SUBSCRIPTION_SERVICE_NAME']);
                                    $lastPres    = $pickFirst($row, ['LAST_PRESENCE_DATE', 'LAST_ATTENDANCE_DATE']);
                                    $dueDate     = $pickFirst($row, ['DUE_DATE', 'DUE_DATE_PAYMENT', 'EFFECTIVE_DATE']);
                                    $delay       = $pickFirst($row, ['DELAY_DAYS', 'DAYS_LATE', 'DAY_DELAY', 'NB_DAYS']);
                                    $remaining   = $pickFirst($row, ['REST_AMOUNT', 'OPEN_AMOUNT', 'REMAINING_AMOUNT', 'AMOUNT_DUE', 'AMOUNT']);

                                    $statusKey = strtolower((string) $statusName);
                                    $statusClass = match (true) {
                                        str_contains($statusKey, 'active') || str_contains($statusKey, 'cours') || str_contains($statusKey, 'inscrit') => 'recouv-status-active',
                                        str_contains($statusKey, 'annul') || str_contains($statusKey, 'cancel') || str_contains($statusKey, 'arch')   => 'recouv-status-inactive',
                                        str_contains($statusKey, 'attente') || str_contains($statusKey, 'pending') || str_contains($statusKey, 'pause') => 'recouv-status-pending',
                                        default => 'recouv-status-default',
                                    };

                                    try { $dueFmt = $dueDate ? \Carbon\Carbon::parse($dueDate)->format('d/m/Y') : '—'; } catch (\Throwable $e) { $dueFmt = $dueDate; }
                                    try { $lastFmt = $lastPres ? \Carbon\Carbon::parse($lastPres)->format('d/m/Y') : '—'; } catch (\Throwable $e) { $lastFmt = $lastPres; }

                                    $delayInt = is_numeric($delay) ? (int) $delay : null;
                                    $delayClass = match (true) {
                                        $delayInt === null    => 'text-muted',
                                        $delayInt === 0       => 'recouv-delay-0',
                                        $delayInt <= 7        => 'recouv-delay-low',
                                        default               => 'recouv-delay-high',
                                    };

                                    $waNumber = $whatsapp ? preg_replace('/\D+/', '', (string) $whatsapp) : null;
                                @endphp
                                <tr>
                                    <td><input type="checkbox" class="form-check-input"></td>
                                    <td class="fw-medium">{{ $reference ?: '—' }}</td>
                                    <td>{{ $studentName ?: '—' }}</td>
                                    <td>
                                        @if($statusName)
                                            <span class="recouv-status {{ $statusClass }}">{{ $statusName }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="recouv-phone">
                                        @if($phone)
                                            <div><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}"><i class="ti ti-phone"></i>{{ $phone }}</a></div>
                                        @endif
                                        @if($waNumber)
                                            <div><a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="wa"><i class="ti ti-brand-whatsapp"></i>{{ $whatsapp }}</a></div>
                                        @endif
                                        @if(!$phone && !$waNumber)
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $fees ?: '—' }}</td>
                                    <td class="text-nowrap">{{ $lastFmt }}</td>
                                    <td class="text-nowrap">{{ $dueFmt }}</td>
                                    <td class="{{ $delayClass }} text-nowrap">
                                        {{ $delayInt !== null ? $delayInt.' Jours' : '—' }}
                                    </td>
                                    <td class="recouv-amount">
                                        {{ is_numeric($remaining) ? number_format((float) $remaining, 0, ',', ' ').' DH' : '—' }}
                                    </td>
                                    <td class="text-end recouv-actions text-nowrap">
                                        <button type="button"
                                                class="btn btn-light btn-sm crm-row-view"
                                                data-row-kind="generic"
                                                data-table-id="{{ $tableId }}"
                                                data-row-index="{{ $rowIndex }}"
                                                title="Voir les détails">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($pagination)
            <div class="mt-3">
                @include('backoffice.crm.partials._pagination', ['pagination' => $pagination])
            </div>
        @endif

        {{-- Row detail modal + JS (emitted once per page) --}}
        @once
            @include('backoffice.crm.partials._row_modal')
        @endonce
    @endif

</div>
@endsection
