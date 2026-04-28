@extends('layouts.main')

@section('title', 'Présence v' . $import->version . ' — ' . $group->name)
@section('breadcrumb-item', 'Paiement Professeurs')
@section('breadcrumb-item-link', route('backoffice.payroll.presence.dashboard'))
@section('breadcrumb-item-active', 'Détails v' . $import->version)

@section('css')
    <style>
        .presence-cell {
            min-width: 36px;
            text-align: center;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 4px !important;
        }
        .presence-cell.present { background-color: #d4edda; color: #155724; }
        .presence-cell.absent { background-color: #f8d7da; color: #721c24; }
        .presence-cell.no_data { background-color: #f8f9fa; color: #6c757d; }
        .summary-card .display-6 { font-size: 2rem; }
        .week-cell { min-width: 130px; }
        .week-cell .week-presence {
            font-size: 0.7rem;
            color: #6c757d;
            display: block;
            text-align: center;
            margin-bottom: 2px;
        }
        .week-cell .week-presence.qualified { color: #198754; font-weight: 600; }
        .week-cell .week-presence.unqualified { color: #dc3545; }
        .week-amount-input {
            text-align: right;
            font-weight: 600;
        }
        .week-amount-input.is-overridden { border-color: #ffc107; background-color: #fff8e1; }

        /* Scrollable attendance table */
        .attendance-scroll {
            max-height: 70vh;
            overflow: auto;
            position: relative;
        }
        .attendance-scroll table { margin-bottom: 0; }

        /* Sticky header row */
        .attendance-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background-color: #f8f9fa;
        }

        /* Sticky left columns: # and Étudiant */
        .attendance-scroll th.col-sticky-num,
        .attendance-scroll td.col-sticky-num {
            position: sticky;
            left: 0;
            z-index: 2;
            background-color: #fff;
            min-width: 40px;
        }
        .attendance-scroll th.col-sticky-name,
        .attendance-scroll td.col-sticky-name {
            position: sticky;
            left: 40px;
            z-index: 2;
            background-color: #fff;
            min-width: 200px;
            box-shadow: 2px 0 4px -2px rgba(0, 0, 0, 0.15);
        }
        .attendance-scroll thead th.col-sticky-num,
        .attendance-scroll thead th.col-sticky-name {
            z-index: 4;
            background-color: #f8f9fa;
        }

        /* Sticky right column: Total */
        .attendance-scroll th.col-sticky-total,
        .attendance-scroll td.col-sticky-total {
            position: sticky;
            right: 0;
            z-index: 2;
            background-color: #fff;
            min-width: 110px;
            box-shadow: -2px 0 4px -2px rgba(0, 0, 0, 0.15);
        }
        .attendance-scroll thead th.col-sticky-total {
            z-index: 4;
            background-color: #f8f9fa;
        }

        /* Sticky footer row */
        .attendance-scroll tfoot td {
            position: sticky;
            bottom: 0;
            z-index: 3;
            background-color: #fff3cd;
        }
        .attendance-scroll tfoot td.col-sticky-num,
        .attendance-scroll tfoot td.col-sticky-name,
        .attendance-scroll tfoot td.col-sticky-total {
            z-index: 5;
            background-color: #fff3cd;
        }
    </style>
@endsection

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Paiement Professeurs</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    @php
        $summary    = $import->paymentSummary;
        $rate       = $import->getEffectivePaymentPerStudent() ?? 0;
        $unitAmount = $import->getWeeklyUnitAmount();
        $threshold  = $import->getThreshold();
        $ratePct    = (float) ($import->weekly_rate_percent ?? \App\Models\PresenceImport::DEFAULT_WEEKLY_RATE_PERCENT);
        $allDates   = $import->students
            ->flatMap(fn($s) => $s->records->pluck('date'))
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values();
    @endphp

    {{-- Import Info --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-2">
                                {{ $group->name }}
                                <span class="badge bg-primary ms-2">v{{ $import->version }}</span>
                                @if($summary?->isApproved())
                                    <span class="badge bg-success ms-1">Approuvé</span>
                                @endif
                            </h5>
                            <div class="row text-muted">
                                <div class="col-auto"><strong>Professeur:</strong> {{ $group->teacher?->name ?? '—' }}</div>
                                <div class="col-auto"><strong>Niveau:</strong> {{ $group->level }}</div>
                                <div class="col-auto"><strong>Mois:</strong> {{ $import->month->translatedFormat('F Y') }}</div>
                                <div class="col-auto"><strong>Période:</strong> {{ $import->date_start->format('d/m') }} — {{ $import->date_end->format('d/m/Y') }}</div>
                                <div class="col-auto"><strong>Semaines:</strong> <span class="badge bg-light-info">{{ $import->total_weeks_label }}</span></div>
                                <div class="col-auto"><strong>Taux mensuel:</strong> {{ number_format($rate, 2) }} DH</div>
                                <div class="col-auto">
                                    <strong>Règle:</strong>
                                    {{ number_format($ratePct, 0) }}% / sem
                                    = <span class="text-success fw-bold">{{ number_format($unitAmount, 2) }} DH</span>
                                    si ≥ {{ $threshold }} présences
                                </div>
                            </div>
                            @if($import->notes)
                                <div class="mt-2"><em>{{ $import->notes }}</em></div>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-end ms-3">
                            @if(!$summary?->isApproved())
                                <form action="{{ route('backoffice.payroll.presence.import.approve', $import) }}"
                                      method="POST"
                                      onsubmit="return confirm('Approuver ce paiement ?')">
                                    @csrf
                                    <button class="btn btn-success btn-sm d-inline-flex align-items-center">
                                        <i class="ph-duotone ph-check-circle me-1"></i> Approuver
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('backoffice.payroll.presence.import.recalculate', $import) }}"
                                  method="POST">
                                @csrf
                                <button class="btn btn-warning btn-sm d-inline-flex align-items-center">
                                    <i class="ph-duotone ph-arrow-clockwise me-1"></i> Recalculer
                                </button>
                            </form>
                            <a href="{{ route('backoffice.payroll.presence.import.export', $import) }}"
                               class="btn btn-info btn-sm d-inline-flex align-items-center">
                                <i class="ph-duotone ph-file-xls me-1"></i> Exporter Excel
                            </a>
                            <a href="{{ route('backoffice.payroll.presence.group.imports', $group) }}"
                               class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                                <i class="ph-duotone ph-clock-counter-clockwise me-1"></i> Historique
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Summary Cards --}}
    @if($summary)
        @php
            $weekTotals = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            $weekQualified = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            foreach ($import->students as $s) {
                foreach ([1,2,3,4] as $w) {
                    $weekTotals[$w] += $s->getWeekEffectiveAmount($w);
                    if ($s->getWeekPresence($w) >= $threshold) {
                        $weekQualified[$w]++;
                    }
                }
            }
        @endphp

        <div class="row mb-3">
            @foreach([1,2,3,4] as $w)
                <div class="col-md-2">
                    <div class="card summary-card text-center">
                        <div class="card-body py-3">
                            <div class="display-6 text-primary">S{{ $w }}</div>
                            <small class="text-muted d-block">
                                {{ $weekQualified[$w] }} étudiant{{ $weekQualified[$w] > 1 ? 's' : '' }} qualifié{{ $weekQualified[$w] > 1 ? 's' : '' }}
                            </small>
                            <strong class="d-block mt-1">{{ number_format($weekTotals[$w], 2) }} DH</strong>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-md-2">
                <div class="card summary-card text-center">
                    <div class="card-body py-3">
                        <div class="display-6 text-info">{{ $summary->total_students }}</div>
                        <small class="text-muted">Étudiants actifs<br>(≥1 sem qualifiée)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card summary-card text-center bg-light-primary">
                    <div class="card-body py-3">
                        <div class="display-6 fw-bold">{{ number_format($summary->total_payment, 2) }}</div>
                        <small class="fw-bold">TOTAL DH</small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Students Attendance + Weekly Amounts Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détail de présence — {{ $import->students->count() }} étudiants</h5>
                    <small class="text-muted">
                        Modifiez un montant hebdo manuellement (laisser vide = auto).
                    </small>
                </div>
                <div class="card-body p-0">
                    <div class="attendance-scroll">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-sticky-num">#</th>
                                    <th class="col-sticky-name">Etudiant</th>
                                    @foreach($allDates as $date)
                                        @php $d = \Carbon\Carbon::parse($date); @endphp
                                        <th class="presence-cell" title="{{ $d->format('d/m/Y') }}">
                                            <small>{{ mb_strtoupper(mb_substr($d->isoFormat('dd'), 0, 2)) }}</small>
                                            <br>{{ $d->format('d') }}
                                        </th>
                                    @endforeach
                                    <th class="text-center" style="min-width: 50px">P</th>
                                    <th class="text-center" style="min-width: 50px">A</th>
                                    @foreach([1,2,3,4] as $w)
                                        <th class="week-cell text-center">Sem {{ $w }}</th>
                                    @endforeach
                                    <th class="col-sticky-total text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($import->students->sortBy('row_number') as $student)
                                    @php
                                        $recordsByDate = $student->records->keyBy(fn($r) => $r->date->format('Y-m-d'));
                                    @endphp
                                    <tr>
                                        <td class="col-sticky-num">{{ $student->row_number }}</td>
                                        <td class="col-sticky-name">
                                            <strong>{{ $student->student_name }}</strong>
                                            @if($student->isCancelled())
                                                <span class="badge bg-danger ms-1">Annulé</span>
                                            @elseif($student->isTransferred())
                                                <span class="badge bg-secondary ms-1">Transféré</span>
                                            @endif
                                        </td>
                                        @foreach($allDates as $date)
                                            @php
                                                $record = $recordsByDate[$date] ?? null;
                                                $status = $record?->status ?? 'no_data';
                                                $display = match($status) {
                                                    'present' => 'P',
                                                    'absent' => 'A',
                                                    default => '-',
                                                };
                                            @endphp
                                            <td class="presence-cell {{ $status }}">{{ $display }}</td>
                                        @endforeach
                                        <td class="text-center fw-bold text-success">{{ $student->total_present }}</td>
                                        <td class="text-center fw-bold text-danger">{{ $student->total_absent }}</td>
                                        @foreach([1,2,3,4] as $w)
                                            @php
                                                $count       = $student->getWeekPresence($w);
                                                $qualified   = $count >= $threshold;
                                                $effective   = $student->getWeekEffectiveAmount($w);
                                                $isOverride  = $student->isWeekOverridden($w);
                                            @endphp
                                            <td class="week-cell">
                                                <span class="week-presence {{ $qualified ? 'qualified' : 'unqualified' }}">
                                                    {{ $count }} présence{{ $count > 1 ? 's' : '' }}
                                                    {{ $qualified ? '✓' : '✗' }}
                                                </span>
                                                <form action="{{ route('backoffice.payroll.presence.student.week', $student) }}"
                                                      method="POST" class="m-0">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="week" value="{{ $w }}">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" step="0.01" min="0"
                                                               name="amount"
                                                               value="{{ number_format($effective, 2, '.', '') }}"
                                                               class="form-control form-control-sm week-amount-input {{ $isOverride ? 'is-overridden' : '' }}"
                                                               title="{{ $isOverride ? 'Modifié manuellement — laisser vide pour revenir au calcul auto' : 'Calcul automatique' }}"
                                                               onchange="this.form.submit()">
                                                        <span class="input-group-text px-1" style="font-size:0.75rem">DH</span>
                                                    </div>
                                                </form>
                                            </td>
                                        @endforeach
                                        <td class="col-sticky-total text-end fw-bold">
                                            {{ number_format($student->getTotalAmount(), 2) }} DH
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if($summary)
                                <tfoot>
                                    <tr class="table-warning fw-bold">
                                        <td class="col-sticky-num"></td>
                                        <td class="col-sticky-name text-end">Total</td>
                                        <td colspan="{{ 2 + $allDates->count() }}" class="text-end">—</td>
                                        @foreach([1,2,3,4] as $w)
                                            <td class="text-end">{{ number_format($weekTotals[$w] ?? 0, 2) }} DH</td>
                                        @endforeach
                                        <td class="col-sticky-total text-end">{{ number_format($summary->total_payment, 2) }} DH</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
