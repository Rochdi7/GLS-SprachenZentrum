@extends('layouts.main')

@section('title', 'Présence CRM v' . $import->version . ' — ' . $group->name)
@section('breadcrumb-item', 'Paiement Professeurs CRM')
@section('breadcrumb-item-link', route('backoffice.payroll.crm.legacy.dashboard'))
@section('breadcrumb-item-active', 'Détails v' . $import->version)

@section('css')
<style>
/* ─────────────────────────────────────────────
   SCROLL WRAPPER
───────────────────────────────────────────── */
.att-wrap {
    overflow: auto;
    max-height: 68vh;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    position: relative;
}
.att-wrap table {
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.82rem;
    white-space: nowrap;
}

/* ─────────────────────────────────────────────
   STICKY TOP (thead) + BOTTOM (tfoot)
───────────────────────────────────────────── */
.att-wrap thead th {
    position: sticky; top: 0;
    background: #343a40; color: #fff;
    z-index: 10;
    padding: 6px 4px;
    border: none;
    border-right: 1px solid #495057;
    border-bottom: 2px solid #dee2e6;
    text-align: center;
    font-size: 0.72rem; letter-spacing: .03em;
}
.att-wrap tfoot td {
    position: sticky; bottom: 0;
    background: #fff3cd !important;
    z-index: 10;
    font-weight: 700;
    border-top: 2px solid #dee2e6 !important;
    padding: 6px 8px;
}

/* ─────────────────────────────────────────────
   STICKY LEFT COLUMNS  (#  Name  P  A)
   explicit left px so they never overlap
   # = 0   Name = 34px   P = 214px   A = 254px
───────────────────────────────────────────── */
.sc-num  { position: sticky; left: 0;     z-index: 4; width: 34px;  min-width: 34px;  max-width: 34px;  background: #fff; }
.sc-name { position: sticky; left: 34px;  z-index: 4; width: 170px; min-width: 170px; max-width: 170px; background: #fff;
           white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
           box-shadow: 3px 0 6px -3px rgba(0,0,0,.18); }
.sc-p    { position: sticky; left: 204px; z-index: 4; width: 34px;  min-width: 34px;  max-width: 34px;  background: #fff;
           text-align: center; font-weight: 700; color: #198754; }
.sc-a    { position: sticky; left: 238px; z-index: 4; width: 34px;  min-width: 34px;  max-width: 34px;  background: #fff;
           text-align: center; font-weight: 700; color: #dc3545;
           box-shadow: 4px 0 8px -4px rgba(0,0,0,.22); }

/* thead overrides */
thead .sc-num, thead .sc-name, thead .sc-p, thead .sc-a {
    z-index: 12;
    background: #343a40 !important; color: #fff;
}
tfoot .sc-num, tfoot .sc-name, tfoot .sc-p, tfoot .sc-a {
    z-index: 11;
    background: #fff3cd !important;
}

/* ─────────────────────────────────────────────
   STICKY RIGHT COLUMN  (Total)
───────────────────────────────────────────── */
.sc-total {
    position: sticky; right: 0; z-index: 4;
    background: #fff;
    min-width: 100px; text-align: right; font-weight: 700;
    box-shadow: -3px 0 6px -3px rgba(0,0,0,.18);
    padding-right: 10px !important;
}
thead .sc-total { z-index: 12; background: #343a40 !important; color: #fff; }
tfoot .sc-total { z-index: 11; background: #fff3cd !important; }

/* ─────────────────────────────────────────────
   PRESENCE CELLS  (day columns)
───────────────────────────────────────────── */
.pc {
    width: 32px; min-width: 32px; max-width: 32px;
    text-align: center; padding: 0 !important;
    font-weight: 700; font-size: 0.75rem;
    border-right: 1px solid #dee2e6 !important;
    vertical-align: middle;
}
.pc.present { background: #d1f5dd; color: #155724; }
.pc.absent  { background: #fde8ea; color: #842029; }
.pc.no_data { background: #f8f9fa; color: #adb5bd; }

/* ─────────────────────────────────────────────
   WEEK COLUMNS  (SEM 1-4)
───────────────────────────────────────────── */
.wk-cell {
    min-width: 130px; max-width: 130px;
    vertical-align: middle;
    padding: 4px 6px !important;
    border-right: 1px solid #dee2e6 !important;
}
.wk-badge {
    display: inline-block;
    font-size: 0.68rem; font-weight: 600;
    padding: 1px 5px; border-radius: 10px;
    margin-bottom: 3px;
    white-space: nowrap;
}
.wk-badge.qual   { background: #d1f5dd; color: #155724; }
.wk-badge.unqual { background: #fde8ea; color: #842029; }
.wk-input {
    width: 76px;
    text-align: right; font-weight: 700; font-size: 0.82rem;
    border: 1px solid #dee2e6; border-radius: 4px;
    padding: 2px 5px; background: #fff;
    transition: border-color .15s;
}
.wk-input.overridden { border-color: #ffc107 !important; background: #fffbf0; }

/* ─────────────────────────────────────────────
   ROW ZEBRA  (explicit so sticky cells inherit)
───────────────────────────────────────────── */
.att-wrap tbody tr:nth-child(odd)  td,
.att-wrap tbody tr:nth-child(odd)  td.sc-num,
.att-wrap tbody tr:nth-child(odd)  td.sc-name,
.att-wrap tbody tr:nth-child(odd)  td.sc-p,
.att-wrap tbody tr:nth-child(odd)  td.sc-a,
.att-wrap tbody tr:nth-child(odd)  td.sc-total { background: #ffffff; }

.att-wrap tbody tr:nth-child(even) td,
.att-wrap tbody tr:nth-child(even) td.sc-num,
.att-wrap tbody tr:nth-child(even) td.sc-name,
.att-wrap tbody tr:nth-child(even) td.sc-p,
.att-wrap tbody tr:nth-child(even) td.sc-a,
.att-wrap tbody tr:nth-child(even) td.sc-total { background: #f7f8fc; }

/* hover */
.att-wrap tbody tr:hover td,
.att-wrap tbody tr:hover td.sc-num,
.att-wrap tbody tr:hover td.sc-name,
.att-wrap tbody tr:hover td.sc-p,
.att-wrap tbody tr:hover td.sc-a,
.att-wrap tbody tr:hover td.sc-total { background: #eef2ff !important; }

/* ─────────────────────────────────────────────
   MISC
───────────────────────────────────────────── */
.kpi-val { font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
.kpi-lbl { font-size: 0.72rem; color: #6c757d; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 2px; }
</style>
@endsection

@section('content')

@if (session('success') || session('error'))
<div class="position-fixed top-0 end-0 p-3" style="z-index:99999">
    <div id="liveToast" class="toast hide" role="alert">
        <div class="toast-header">
            <strong class="me-auto">{{ $group->name }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">{{ session('success') ?? session('error') }}</div>
    </div>
</div>
@endif

@php
    $summary      = $import->paymentSummary;
    $allDates     = $import->students
        ->flatMap(fn($s) => $s->records->pluck('date')->map(fn($d) => (string)$d))
        ->unique()->sort()->values();
    $weekThreshold = $import->getThreshold();
    $weeklyUnit    = $import->getWeeklyUnitAmount();
    $dayCount      = $import->date_start->diffInDays($import->date_end) + 1;
    $numWeeks      = min(4, max(1, (int) ceil($dayCount / 7)));
    $profName      = $import->crm_teacher_name ?? $group->teacher?->name ?? '—';
@endphp

{{-- ── TOP HEADER CARD ─────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="mb-0 fw-bold">{{ $group->name }}</h4>
                    <span class="badge bg-success"><i class="ph-duotone ph-cloud me-1"></i>CRM API</span>
                    <span class="badge bg-primary">v{{ $import->version }}</span>
                </div>
                <div class="d-flex flex-wrap gap-3" style="font-size:.83rem; color:#555">
                    <span><i class="ph-duotone ph-chalkboard-teacher me-1"></i>{{ $profName }}</span>
                    <span><i class="ph-duotone ph-graduation-cap me-1"></i>{{ $group->level }}</span>
                    <span><i class="ph-duotone ph-calendar me-1"></i>
                        @if($import->month_label)
                            <strong>{{ $import->month_label }}</strong> —
                        @endif
                        {{ $import->date_start->format('d/m/Y') }} → {{ $import->date_end->format('d/m/Y') }}
                    </span>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('backoffice.payroll.crm.legacy.import.pdf', ['group' => $group->id, 'import' => $import->id]) }}"
                   class="btn btn-danger btn-sm" target="_blank">
                    <i class="ph-duotone ph-file-pdf me-1"></i> Exporter PDF
                </a>
                <a href="{{ route('backoffice.payroll.crm.legacy.import.create') }}" class="btn btn-success btn-sm">
                    <i class="ph-duotone ph-plus me-1"></i> Nouvel import
                </a>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#recalcModal">
                    <i class="ph-duotone ph-calculator me-1"></i> Recalculer
                </button>
                <a href="{{ route('backoffice.payroll.crm.legacy.group.imports', $group) }}" class="btn btn-outline-primary btn-sm">
                    <i class="ph-duotone ph-list me-1"></i> Historique
                </a>
                <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ph-duotone ph-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── KPI CARDS ────────────────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="kpi-lbl">Total paiement</div>
                <div class="kpi-val text-success" id="kpi-total-paiement">{{ $summary ? number_format($summary->total_payment, 2) : '—' }} DH</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="kpi-lbl">Étudiants actifs</div>
                <div class="kpi-val">{{ $summary?->total_students ?? $import->students->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="kpi-lbl">Taux / étudiant</div>
                <div class="kpi-val">{{ number_format($import->getEffectivePaymentPerStudent() ?? 0, 2) }} <small class="fs-6">DH</small></div>
                <small class="text-muted">{{ number_format($weeklyUnit, 2) }} DH/sem</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="kpi-lbl">Seuil qualification</div>
                <div class="kpi-val">{{ $weekThreshold }} <small class="fs-6">jours/sem</small></div>
                <small class="text-muted">{{ $allDates->count() }} jours de cours</small>
            </div>
        </div>
    </div>
</div>

{{-- ── NO RATE WARNING ─────────────────────────────────────────── --}}
@if (!$import->payment_per_student)
<div class="alert alert-warning d-flex align-items-center gap-3 mb-3">
    <i class="ph-duotone ph-warning fs-5"></i>
    <div class="flex-grow-1"><strong>Taux non défini</strong> — définissez le taux pour calculer le paiement.</div>
    <form action="{{ route('backoffice.payroll.crm.legacy.import.recalculate', ['group'=>$group->id,'import'=>$import->id]) }}" method="POST" class="d-flex gap-2 align-items-center">
        @csrf
        <input type="number" name="payment_per_student" class="form-control form-control-sm" style="width:100px" placeholder="500" step="0.01" min="0" required>
        <span class="text-muted small">DH</span>
        <button class="btn btn-warning btn-sm">Appliquer</button>
    </form>
</div>
@endif

{{-- ── PRESENCE + PAYMENT TABLE ────────────────────────────────── --}}
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center py-2 bg-white">
        <h6 class="mb-0 fw-bold">
            <i class="ph-duotone ph-table me-1"></i>
            Détail de présence — <span class="text-primary">{{ $import->students->count() }} étudiants</span>
        </h6>
        <small class="text-muted fst-italic">Cliquer sur SEM 1–4 pour ajuster le montant</small>
    </div>
    <div class="card-body p-0">
        <div class="att-wrap">
            <table id="presence-table">
                <thead>
                    <tr>
                        <th class="sc-num">#</th>
                        <th class="sc-name" style="text-align:left; padding-left:8px">ÉTUDIANT</th>

                        {{-- Day columns --}}
                        @foreach ($allDates as $date)
                            @php
                                $d     = \Carbon\Carbon::parse($date)->locale('fr');
                                $dayFr = strtoupper(substr($d->isoFormat('ddd'), 0, 3));
                            @endphp
                            <th class="pc" style="padding:4px 0 !important; line-height:1.1">
                                {{ $dayFr }}<br><span style="font-size:.65rem;opacity:.8">{{ $d->format('d') }}</span>
                            </th>
                        @endforeach

                        <th class="sc-p">P</th>
                        <th class="sc-a">A</th>

                        {{-- Week headers --}}
                        @for ($w = 1; $w <= $numWeeks; $w++)
                            <th class="wk-cell text-center">SEM {{ $w }}</th>
                        @endfor

                        <th class="sc-total">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @php $colTotals = array_fill(1, $numWeeks, 0); $grandTotal = 0; @endphp
                    @foreach ($import->students as $student)
                        @php
                            $byDate   = $student->records->keyBy(fn($r) => (string)$r->date);
                            $rowTotal = (float)$student->weighted_amount;
                            $grandTotal += $rowTotal;
                        @endphp
                        <tr>
                            <td class="sc-num text-center text-muted" style="font-size:.72rem; padding:6px 0">{{ $loop->iteration }}</td>
                            <td class="sc-name fw-semibold" style="padding:6px 8px">
                                {{ $student->student_name }}
                                @if ($student->isCancelled())  <span class="badge bg-danger  ms-1" style="font-size:.6rem">Annulé</span>   @endif
                                @if ($student->isTransferred()) <span class="badge bg-warning ms-1" style="font-size:.6rem">Transféré</span> @endif
                            </td>

                            {{-- Day cells --}}
                            @foreach ($allDates as $date)
                                @php $rec = $byDate->get($date); $st = $rec?->status; @endphp
                                <td class="pc {{ $st === 'present' ? 'present' : ($st === 'absent' ? 'absent' : 'no_data') }}"
                                    title="{{ $date }}">
                                    {{ $st === 'present' ? 'P' : ($st === 'absent' ? 'A' : '·') }}
                                </td>
                            @endforeach

                            <td class="sc-p">{{ $student->total_present }}</td>
                            <td class="sc-a">{{ $student->total_absent }}</td>

                            {{-- Week cells --}}
                            @for ($w = 1; $w <= $numWeeks; $w++)
                                @php
                                    $presence  = (int)$student->{"week_{$w}_presence"};
                                    $auto      = (float)$student->{"week_{$w}_amount"};
                                    $override  = $student->{"week_{$w}_amount_override"};
                                    $effective = $override !== null ? (float)$override : $auto;
                                    $qualified = $presence >= $weekThreshold;
                                    $colTotals[$w] += $effective;
                                @endphp
                                <td class="wk-cell">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="wk-badge {{ $qualified ? 'qual' : 'unqual' }}">
                                            {{ $presence }}j {{ $qualified ? '✓' : '✗' }}
                                        </span>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="number"
                                                class="wk-input {{ $override !== null ? 'overridden' : '' }}"
                                                data-student="{{ $student->id }}"
                                                data-week="{{ $w }}"
                                                data-auto="{{ $auto }}"
                                                value="{{ number_format($effective, 2, '.', '') }}"
                                                step="0.01" min="0">
                                            <span class="text-muted" style="font-size:.68rem">DH</span>
                                        </div>
                                    </div>
                                </td>
                            @endfor

                            <td class="sc-total">{{ number_format($rowTotal, 2) }} DH</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td class="sc-num"></td>
                        <td class="sc-name fw-bold" style="padding-left:8px">TOTAL</td>
                        @foreach ($allDates as $date) <td class="pc"></td> @endforeach
                        <td class="sc-p">—</td>
                        <td class="sc-a"></td>
                        @for ($w = 1; $w <= $numWeeks; $w++)
                            <td class="wk-cell text-center fw-bold" id="col-total-{{ $w }}">
                                {{ number_format($colTotals[$w], 2) }} DH
                            </td>
                        @endfor
                        <td class="sc-total" id="grand-total">{{ number_format($grandTotal, 2) }} DH</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ── RECALC MODAL ─────────────────────────────────────────────── --}}
<div class="modal fade" id="recalcModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form action="{{ route('backoffice.payroll.crm.legacy.import.recalculate', ['group'=>$group->id,'import'=>$import->id]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Recalculer le paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-bold">Taux par étudiant (DH)</label>
                    <div class="input-group">
                        <input type="number" name="payment_per_student" class="form-control"
                            value="{{ $import->payment_per_student }}" placeholder="500" step="0.01" min="0" required autofocus>
                        <span class="input-group-text">DH</span>
                    </div>
                    <small class="text-muted mt-1 d-block">= {{ number_format($weeklyUnit, 2) }} DH / semaine / étudiant</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-warning btn-sm"><i class="ph-duotone ph-calculator me-1"></i>Calculer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toastEl = document.getElementById('liveToast');
    if (toastEl) new bootstrap.Toast(toastEl).show();

    const CSRF = '{{ csrf_token() }}';
    const timers = {};

    function updateRowTotal(row) {
        let t = 0;
        row.querySelectorAll('.wk-input').forEach(i => t += parseFloat(i.value) || 0);
        const el = row.querySelector('.sc-total');
        if (el) el.textContent = t.toFixed(2) + ' DH';
    }

    function updateColTotal(week) {
        let t = 0;
        document.querySelectorAll(`.wk-input[data-week="${week}"]`).forEach(i => t += parseFloat(i.value) || 0);
        const el = document.getElementById(`col-total-${week}`);
        if (el) el.textContent = t.toFixed(2) + ' DH';
    }

    function updateGrandTotal() {
        let t = 0;
        document.querySelectorAll('#presence-table tbody .sc-total').forEach(td => t += parseFloat(td.textContent) || 0);
        const el = document.getElementById('grand-total');
        if (el) el.textContent = t.toFixed(2) + ' DH';
        // sync KPI card
        const kpi = document.getElementById('kpi-total-paiement');
        if (kpi) kpi.textContent = t.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' DH';
    }

    function save(input) {
        const sid  = input.dataset.student;
        const week = input.dataset.week;
        const auto = parseFloat(input.dataset.auto);
        const val  = input.value.trim() === '' ? null : parseFloat(input.value);

        input.style.borderColor = '#ffc107';

        fetch(`/backoffice/payroll/presence/student/${sid}/week`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ student_id: sid, week, amount: val })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                input.classList.toggle('overridden', val !== null && val !== auto);
                input.style.borderColor = '#198754';
                setTimeout(() => input.style.borderColor = '', 700);
            } else {
                input.style.borderColor = '#dc3545';
            }
        })
        .catch(() => input.style.borderColor = '#dc3545');
    }

    document.querySelectorAll('.wk-input').forEach(inp => {
        const key = inp.dataset.student + '_' + inp.dataset.week;

        inp.addEventListener('input', function () {
            updateRowTotal(this.closest('tr'));
            updateColTotal(this.dataset.week);
            updateGrandTotal();
            clearTimeout(timers[key]);
            timers[key] = setTimeout(() => save(this), 600);
        });

        inp.addEventListener('change', function () {
            clearTimeout(timers[key]);
            save(this);
        });
    });
});
</script>
@endsection
