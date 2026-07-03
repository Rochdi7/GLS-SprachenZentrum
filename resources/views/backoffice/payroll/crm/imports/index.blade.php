@extends('layouts.main')

@section('title', 'Historique — ' . $group->name)
@section('breadcrumb-item', 'Paiement Professeurs CRM')
@section('breadcrumb-item-link', route('backoffice.payroll.crm.legacy.dashboard'))
@section('breadcrumb-item-active', 'Historique')

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Paiement Professeurs CRM</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    @php
        $modeMeta = [
            'weekly' => ['Hebdo', 'secondary', 'ph-calendar-blank'],
            'period' => ['Période', 'success', 'ph-calendar-check'],
            'hourly' => ['Horaire', 'primary', 'ph-clock'],
        ];
        $statusMeta = [
            'draft'     => ['Brouillon', 'secondary'],
            'validated' => ['Validé', 'info'],
            'paid'      => ['Payé', 'success'],
            'locked'    => ['Verrouillé', 'dark'],
        ];
    @endphp

    {{-- ── HEADER ─────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">{{ $group->name }}</h5>
                    <span class="text-muted">
                        Professeur : <strong>{{ $group->teacher?->name ?? $imports->first()?->crm_teacher_name ?? '—' }}</strong>
                        | Niveau : <span class="badge bg-light-primary">{{ $group->level }}</span>
                        | CRM Class ID : <span class="badge bg-light-success">{{ $group->crm_class_id ?? 'Non configuré' }}</span>
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('backoffice.payroll.crm.legacy.import.create-modes', ['crm_class_id' => $group->crm_class_id]) }}"
                       class="btn btn-success btn-sm">
                        <i class="ph-duotone ph-plus-circle me-1"></i> Nouveau paiement
                    </a>
                    <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}" class="btn btn-outline-secondary btn-sm">Retour</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── FILTERS ────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Mode</label>
                    <select name="mode" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($modeMeta as $key => [$lbl])
                            <option value="{{ $key }}" {{ request('mode') === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">Année</label>
                    <input type="number" name="attached_year" class="form-control form-control-sm" min="2000" max="2100"
                           value="{{ request('attached_year') }}" placeholder="Toutes">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">N° mois groupe</label>
                    <input type="number" name="group_month_number" class="form-control form-control-sm" min="1" max="60"
                           value="{{ request('group_month_number') }}" placeholder="Tous">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button class="btn btn-primary btn-sm"><i class="ph-duotone ph-funnel me-1"></i>Filtrer</button>
                    <a href="{{ route('backoffice.payroll.crm.legacy.group.imports', $group) }}" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── HISTORY GROUPED BY GROUP MONTH NUMBER ──────────── --}}
    @forelse($byMonthNumber as $monthNumber => $group_imports)
        <div class="card mb-3">
            <div class="card-header bg-light d-flex align-items-center justify-content-between py-2">
                <h6 class="mb-0 fw-bold">
                    @if($monthNumber)
                        <i class="ph-duotone ph-calendar-dots me-1 text-primary"></i>Mois {{ $monthNumber }}
                    @else
                        <i class="ph-duotone ph-archive me-1 text-muted"></i>Sans numéro de mois (imports hebdo)
                    @endif
                    <span class="badge bg-secondary ms-1">{{ $group_imports->count() }}</span>
                </h6>
                <span class="text-muted small">
                    Total : <strong>{{ number_format($group_imports->sum(fn($i) => (float)($i->paymentSummary?->total_payment ?? $i->final_total ?? 0)), 2) }} DH</strong>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Version</th>
                                <th>Mode</th>
                                <th>Rattaché</th>
                                <th>Période</th>
                                <th>Étudiants</th>
                                <th>Total</th>
                                <th>Paiement</th>
                                <th>Créé le</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group_imports as $import)
                                @php
                                    $mode = $import->payment_mode ?? 'weekly';
                                    [$modeLabel, $modeColor, $modeIcon] = $modeMeta[$mode] ?? $modeMeta['weekly'];
                                    $total = $import->paymentSummary?->total_payment ?? $import->final_total;
                                @endphp
                                <tr>
                                    <td><span class="badge bg-primary">v{{ $import->version }}</span></td>
                                    <td><span class="badge bg-light-{{ $modeColor }} text-{{ $modeColor }}"><i class="ph-duotone {{ $modeIcon }} me-1"></i>{{ $modeLabel }}</span></td>
                                    <td>
                                        @if($import->attached_month && $import->attached_year)
                                            {{ \Carbon\Carbon::create()->month($import->attached_month)->locale('fr')->isoFormat('MMM') }} {{ $import->attached_year }}
                                        @else — @endif
                                    </td>
                                    <td>{{ $import->date_start->format('d/m') }} — {{ $import->date_end->format('d/m/Y') }}</td>
                                    <td>{{ $import->students_count }}</td>
                                    <td>@if($total !== null)<strong>{{ number_format((float)$total, 2) }} DH</strong>@else — @endif</td>
                                    <td>
                                        @if($import->payment_date)
                                            <span class="badge bg-success"><i class="ph-duotone ph-check-circle me-1"></i>{{ $import->payment_date->format('d/m/Y') }}</span>
                                            <div class="text-muted" style="font-size:.68rem">{{ $import->paymentMethodLabel() }}@if($import->payment_reference) · {{ $import->payment_reference }}@endif</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $import->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('backoffice.payroll.crm.legacy.import.show', ['group' => $group->id, 'import' => $import->id]) }}"
                                           class="btn btn-sm btn-outline-primary" title="Voir"><i class="ph-duotone ph-eye"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="ph-duotone ph-folder-open fs-1 d-block mb-2"></i>
            Aucun paiement pour ce groupe avec ces filtres.
            @if($group->crm_class_id)
                <br><a href="{{ route('backoffice.payroll.crm.legacy.import.create-modes', ['crm_class_id' => $group->crm_class_id]) }}" class="btn btn-success btn-sm mt-2">Créer le premier paiement</a>
            @endif
        </div></div>
    @endforelse

@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
