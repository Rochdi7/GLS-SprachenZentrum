@extends('layouts.main')

@section('title', 'CRM — Vue 360')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Vue 360')

@section('content')
    @include('backoffice.crm.partials._center')

    {{-- KPI strip — computed on the same filtered query as the table below --}}
    <div class="row g-2 mb-3">
        @php
            $kpis = [
                ['Lignes',        number_format($totals['rows'], 0, ',', ' '),          'ti-list',        'primary'],
                ['Étudiants',     number_format($totals['students'], 0, ',', ' '),      'ti-users',       'info'],
                ['Inscriptions',  number_format($totals['registrations'], 0, ',', ' '), 'ti-file-text',   'secondary'],
                ['Paiements',     number_format($totals['payments'], 0, ',', ' '),      'ti-receipt',     'success'],
                ['Total encaissé', number_format($totals['total'], 2, ',', ' ').' DH',  'ti-cash',        'success'],
                ['Reste à payer', number_format($totals['rest'], 2, ',', ' ').' DH',    'ti-alert-circle','danger'],
            ];
        @endphp
        @foreach($kpis as [$label, $value, $icon, $color])
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card mb-0 h-100">
                    <div class="card-body py-3">
                        <div class="text-muted small mb-1">
                            <i class="ti {{ $icon }} me-1 text-{{ $color }}"></i>{{ $label }}
                        </div>
                        <div class="fw-bold fs-6 text-{{ $color }}">{{ $value }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters. Plain GET form so every filter state is a shareable URL and
         the export button can just replay the same query string. --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Recherche</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                           class="form-control form-control-sm" placeholder="Nom, téléphone, email, réf. paiement">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Groupe</label>
                    <select name="classId" class="form-select form-select-sm">
                        <option value="">— Tous les groupes —</option>
                        @foreach($options['classes'] as $id => $name)
                            <option value="{{ $id }}" {{ (string)($filters['classId'] ?? '') === (string)$id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Statut inscription</label>
                    <select name="registrationStatus" class="form-select form-select-sm">
                        <option value="">— Tous —</option>
                        @foreach($options['statuses'] as $v)
                            <option value="{{ $v }}" {{ ($filters['registrationStatus'] ?? '') === $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Méthode</label>
                    <select name="paymentMethod" class="form-select form-select-sm">
                        <option value="">— Toutes —</option>
                        @foreach($options['methods'] as $v)
                            <option value="{{ $v }}" {{ ($filters['paymentMethod'] ?? '') === $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Type paiement</label>
                    <select name="paymentType" class="form-select form-select-sm">
                        <option value="">— Tous —</option>
                        @foreach($options['types'] as $v)
                            <option value="{{ $v }}" {{ ($filters['paymentType'] ?? '') === $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Paiement du</label>
                    <input type="date" name="startDate" value="{{ $filters['startDate'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Paiement au</label>
                    <input type="date" name="endDate" value="{{ $filters['endDate'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Inscrit du</label>
                    <input type="date" name="regStartDate" value="{{ $filters['regStartDate'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Inscrit au</label>
                    <input type="date" name="regEndDate" value="{{ $filters['regEndDate'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Paiement</label>
                    <select name="paymentPresence" class="form-select form-select-sm">
                        <option value="">— Tous —</option>
                        <option value="with"    {{ ($filters['paymentPresence'] ?? '') === 'with' ? 'selected' : '' }}>Avec paiement</option>
                        <option value="without" {{ ($filters['paymentPresence'] ?? '') === 'without' ? 'selected' : '' }}>Sans paiement</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Par page</label>
                    <select name="perPage" class="form-select form-select-sm">
                        @foreach([25, 50, 100, 200] as $n)
                            <option value="{{ $n }}" {{ $perPage === $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 align-items-center pt-1">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="checkbox" name="unpaidOnly" value="1"
                               id="unpaidOnly" {{ !empty($filters['unpaidOnly']) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="unpaidOnly">Impayés seulement (reste &gt; 0)</label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ti ti-filter me-1"></i> Filtrer
                    </button>
                    <a href="{{ route('backoffice.crm.unified-360') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-x me-1"></i> Réinitialiser
                    </a>
                    {{-- Replays the current query string so the export matches the screen --}}
                    <a href="{{ route('backoffice.crm.unified-360.export', request()->query()) }}"
                       class="btn btn-sm btn-success ms-auto">
                        <i class="ti ti-file-spreadsheet me-1"></i> Exporter Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-striped mb-0 align-middle" style="font-size:.82rem">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">#</th>
                            @foreach($columns as $label)
                                <th class="text-nowrap">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                            <tr>
                                <td class="text-muted">{{ $rows->firstItem() + $i }}</td>
                                @foreach(array_keys($columns) as $field)
                                    @php $v = $row->{$field} ?? null; @endphp
                                    <td class="text-nowrap">
                                        @if($v === null || $v === '')
                                            <span class="text-muted">—</span>
                                        @elseif(in_array($field, ['payment_amount', 'payment_rest']))
                                            <span class="{{ $field === 'payment_rest' && (float)$v > 0 ? 'text-danger fw-semibold' : '' }}">
                                                {{ number_format((float) $v, 2, ',', ' ') }}
                                            </span>
                                        @elseif(str_contains($field, 'date'))
                                            {{ \Carbon\Carbon::parse($v)->format('d/m/Y') }}
                                        @elseif($field === 'registration_status')
                                            <span class="badge bg-light-secondary text-secondary">{{ $v }}</span>
                                        @else
                                            {{ $v }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + 1 }}" class="text-center text-muted py-5">
                                    <i class="ti ti-database-off d-block fs-3 mb-2"></i>
                                    Aucune ligne. Vérifiez les filtres, ou lancez la synchronisation CRM
                                    (<code>php artisan crm:sync-all</code>) si les tables miroir sont vides.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rows->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    {{ $rows->firstItem() }}–{{ $rows->lastItem() }} sur {{ number_format($rows->total(), 0, ',', ' ') }}
                </div>
                {{ $rows->links() }}
            </div>
        @endif
    </div>
@endsection
