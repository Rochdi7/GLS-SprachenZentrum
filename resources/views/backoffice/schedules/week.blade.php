@extends('layouts.main')

@section('title', 'Mon Planning')
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', 'Semaine')

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <img src="{{ asset('assets/images/favicon/favicon.svg') }}" class="img-fluid me-2" alt="favicon" style="width: 17px">
                    <strong class="me-auto">GLS Backoffice</strong>
                    <small>Maintenant</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    @php
        $prevWeek = (clone $weekStart)->subWeek()->toDateString();
        $nextWeek = (clone $weekStart)->addWeek()->toDateString();
        $isSelf   = $authUser->id === $target->id;
    @endphp

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h5 class="mb-1">
                @if($isSelf)
                    Mon planning
                @else
                    Planning de {{ $target->name }}
                @endif
            </h5>
            <div class="text-muted small">
                Semaine du {{ $weekStart->locale('fr')->isoFormat('DD MMMM') }} au {{ $weekEnd->locale('fr')->isoFormat('DD MMMM YYYY') }}
                @if($target->site)
                    · <span class="badge bg-light-info">{{ $target->site->name }}</span>
                @endif
                @if($target->staff_role)
                    · <span class="badge bg-light-primary">{{ $target->staff_role }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('backoffice.schedules.week', ['week' => $prevWeek, 'user_id' => $target->id]) }}" class="btn btn-outline-secondary btn-sm">
                <i class="ph-duotone ph-caret-left"></i> Précédente
            </a>
            <form method="GET" class="d-inline">
                <input type="hidden" name="user_id" value="{{ $target->id }}">
                <input type="date" name="week" class="form-control form-control-sm" value="{{ $weekStart->toDateString() }}" onchange="this.form.submit()" style="width: 160px;">
            </form>
            <a href="{{ route('backoffice.schedules.week', ['week' => $nextWeek, 'user_id' => $target->id]) }}" class="btn btn-outline-secondary btn-sm">
                Suivante <i class="ph-duotone ph-caret-right"></i>
            </a>
            <a href="{{ route('backoffice.schedules.week.pdf', ['week' => $weekStart->toDateString(), 'user_id' => $target->id]) }}"
               class="btn btn-danger btn-sm" target="_blank">
                <i class="ph-duotone ph-file-pdf me-1"></i> Exporter PDF
            </a>
        </div>
    </div>

    {{-- Admin-only: switch target user --}}
    @if($isAdmin && $staffOptions->isNotEmpty())
        <div class="card mb-3">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                    <div class="col-auto">
                        <label class="form-label small mb-1 fw-semibold">
                            <i class="ph-duotone ph-users-three me-1"></i> Gérer le planning de :
                        </label>
                    </div>
                    <div class="col">
                        <select name="user_id" class="form-select" onchange="this.form.submit()">
                            <option value="{{ $authUser->id }}" {{ $target->id === $authUser->id ? 'selected' : '' }}>— Moi-même —</option>
                            @foreach($staffOptions as $u)
                                <option value="{{ $u->id }}" {{ $target->id === $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} · {{ $u->staff_role ?? 'Sans rôle' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('backoffice.schedules.week.save') }}">
        @csrf
        <input type="hidden" name="user_id" value="{{ $target->id }}">
        <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 140px;">Jour</th>
                                <th class="text-center" style="width: 120px;">Début</th>
                                <th class="text-center" style="width: 120px;">Fin</th>
                                <th class="text-center" style="width: 120px;">Pause début</th>
                                <th class="text-center" style="width: 120px;">Pause fin</th>
                                <th>Notes</th>
                                <th class="text-end" style="width: 90px;">Travaillé</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($days as $i => $day)
                                @php
                                    $s = $day['schedule'];
                                    $isWeekend = in_array($day['date']->dayOfWeek, [0, 6]);
                                @endphp
                                <tr class="{{ $isWeekend ? 'table-light text-muted' : '' }}" data-row>
                                    <td class="fw-medium">
                                        {{ $day['label'] }}
                                        <input type="hidden" name="days[{{ $i }}][date]" value="{{ $day['key'] }}">
                                        @if($i === 0)
                                            <br>
                                            <button type="button" id="applyAllBtn" class="btn btn-outline-primary btn-sm mt-1 py-0 px-2" style="font-size: 0.7rem; white-space: nowrap;">
                                                <i class="ph-duotone ph-copy me-1"></i>Appliquer à tous
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="time" name="days[{{ $i }}][start_time]" class="form-control form-control-sm" data-field="start"
                                               value="{{ old('days.'.$i.'.start_time', $s ? substr($s->start_time, 0, 5) : '') }}">
                                    </td>
                                    <td>
                                        <input type="time" name="days[{{ $i }}][end_time]" class="form-control form-control-sm" data-field="end"
                                               value="{{ old('days.'.$i.'.end_time', $s ? substr($s->end_time, 0, 5) : '') }}">
                                    </td>
                                    <td>
                                        <input type="time" name="days[{{ $i }}][break_start]" class="form-control form-control-sm" data-field="bstart"
                                               value="{{ old('days.'.$i.'.break_start', $s && $s->break_start ? substr($s->break_start, 0, 5) : '') }}">
                                    </td>
                                    <td>
                                        <input type="time" name="days[{{ $i }}][break_end]" class="form-control form-control-sm" data-field="bend"
                                               value="{{ old('days.'.$i.'.break_end', $s && $s->break_end ? substr($s->break_end, 0, 5) : '') }}">
                                    </td>
                                    <td>
                                        <input type="text" name="days[{{ $i }}][notes]" class="form-control form-control-sm"
                                               maxlength="500" placeholder="—"
                                               value="{{ old('days.'.$i.'.notes', $s?->notes) }}">
                                    </td>
                                    <td class="text-end fw-bold text-success" data-worked>
                                        {{ $s ? $s->worked_formatted : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="6" class="text-end">Total semaine</th>
                                <th class="text-end text-success" id="weekTotal">{{ \App\Models\UserSchedule::formatMinutes($totalWorked) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="text-muted small mb-0">
                <i class="ph-duotone ph-info me-1"></i>
                Laissez Début/Fin vide pour supprimer l'entrée d'un jour.
            </p>
            <button type="submit" class="btn btn-primary">
                <i class="ph-duotone ph-check me-1"></i> Enregistrer la semaine
            </button>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) { new bootstrap.Toast(toastEl).show(); }

            // ===== Real-time worked-time calculation =====
            function toMinutes(val) {
                if (!val || !/^\d{1,2}:\d{2}$/.test(val)) return null;
                const [h, m] = val.split(':').map(Number);
                if (isNaN(h) || isNaN(m)) return null;
                return h * 60 + m;
            }

            function fmt(mins) {
                if (mins <= 0) return '0h00';
                const h = Math.floor(mins / 60);
                const m = mins % 60;
                return `${h}h${String(m).padStart(2, '0')}`;
            }

            function computeRow(row) {
                const start  = toMinutes(row.querySelector('[data-field="start"]').value);
                const end    = toMinutes(row.querySelector('[data-field="end"]').value);
                const bStart = toMinutes(row.querySelector('[data-field="bstart"]').value);
                const bEnd   = toMinutes(row.querySelector('[data-field="bend"]').value);
                const cell   = row.querySelector('[data-worked]');

                if (start === null || end === null || end <= start) {
                    cell.textContent = '—';
                    return 0;
                }

                let worked = end - start;

                if (bStart !== null && bEnd !== null && bEnd > bStart) {
                    // Clamp break to working window
                    const bs = Math.max(bStart, start);
                    const be = Math.min(bEnd, end);
                    const pause = Math.max(0, be - bs);
                    worked -= pause;
                }

                worked = Math.max(0, worked);
                cell.textContent = fmt(worked);
                return worked;
            }

            function recalcAll() {
                let total = 0;
                document.querySelectorAll('[data-row]').forEach(r => { total += computeRow(r); });
                const totalEl = document.getElementById('weekTotal');
                if (totalEl) totalEl.textContent = fmt(total);
            }

            document.querySelectorAll('[data-row] input[type="time"]').forEach(inp => {
                inp.addEventListener('input', recalcAll);
                inp.addEventListener('change', recalcAll);
            });

            recalcAll();

            // ===== Apply first row to all rows =====
            const applyAllBtn = document.getElementById('applyAllBtn');
            if (applyAllBtn) {
                applyAllBtn.addEventListener('click', function () {
                    const rows = document.querySelectorAll('[data-row]');
                    const first = rows[0];
                    const srcStart  = first.querySelector('[data-field="start"]').value;
                    const srcEnd    = first.querySelector('[data-field="end"]').value;
                    const srcBStart = first.querySelector('[data-field="bstart"]').value;
                    const srcBEnd   = first.querySelector('[data-field="bend"]').value;

                    rows.forEach((row, idx) => {
                        if (idx === 0) return;
                        row.querySelector('[data-field="start"]').value  = srcStart;
                        row.querySelector('[data-field="end"]').value    = srcEnd;
                        row.querySelector('[data-field="bstart"]').value = srcBStart;
                        row.querySelector('[data-field="bend"]').value   = srcBEnd;
                    });

                    recalcAll();
                });
            }
        });
    </script>
@endsection
