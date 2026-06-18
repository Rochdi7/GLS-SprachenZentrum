@extends('layouts.main')

@section('title', 'Créer un planning')
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', 'Créer')

@section('content')

    @php $todayKey = now()->format('Y-m-d'); @endphp

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h5 class="mb-1"><i class="ph-duotone ph-calendar-plus me-1 text-primary"></i> Créer un planning</h5>
            <div class="text-muted small">
                Choisissez un employé et une semaine, puis renseignez les horaires jour par jour.
            </div>
        </div>
        <a href="{{ route('backoffice.schedules.week') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
        </a>
    </div>

    {{-- ── Step 1: pick employee + week (GET, auto-reload) ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold mb-1">
                        <i class="ph-duotone ph-user me-1"></i> Employé <span class="text-danger">*</span>
                    </label>
                    <select name="user_id" id="employee-select" class="form-select" required>
                        <option value="">— Choisir un employé —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $target && $target->id === $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} · {{ $emp->staff_role ?? 'Sans rôle' }} ({{ $emp->site?->name ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold mb-1">
                        <i class="ph-duotone ph-calendar me-1"></i> Semaine
                    </label>
                    <input type="date" name="week" class="form-control" value="{{ $weekStart->toDateString() }}"
                           onchange="this.form.submit()">
                </div>
                <div class="col-12 col-md-2 text-md-end">
                    <span class="badge bg-light-primary text-primary">
                        {{ $weekStart->locale('fr')->isoFormat('DD/MM') }} – {{ $weekEnd->locale('fr')->isoFormat('DD/MM') }}
                    </span>
                </div>
            </form>
        </div>
    </div>

    @if(! $target)
        {{-- Nothing selected yet --}}
        <div class="alert alert-light border d-flex align-items-center gap-2">
            <i class="ph-duotone ph-arrow-up fs-5 text-primary"></i>
            <span class="text-muted mb-0">Sélectionnez un employé pour saisir son planning.</span>
        </div>
    @else
        {{-- ── Step 2: per-day weekly table (POST → saveWeek) ── --}}
        <form method="POST" action="{{ route('backoffice.schedules.week.save') }}">
            @csrf
            <input type="hidden" name="user_id" value="{{ $target->id }}">
            <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="ph-duotone ph-pencil-simple text-primary"></i>
                <h6 class="mb-0">Planning de {{ $target->name }} — semaine du {{ $weekStart->locale('fr')->isoFormat('DD/MM') }}</h6>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light position-sticky top-0" style="z-index: 2;">
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
                                        $isToday   = $day['key'] === $todayKey;
                                    @endphp
                                    <tr class="{{ $isWeekend ? 'table-light text-muted' : '' }} {{ $isToday ? 'table-primary' : '' }}"
                                        data-row @if($isWeekend) data-weekend @endif>
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
    @endif

@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // ===== Searchable employee dropdown =====
            const empSelect = document.getElementById('employee-select');
            if (empSelect && typeof Choices !== 'undefined') {
                const choicesInst = new Choices(empSelect, {
                    searchEnabled: true,
                    searchPlaceholderValue: 'Rechercher...',
                    itemSelectText: '',
                    shouldSort: false,
                    noResultsText: 'Aucun résultat',
                    noChoicesText: 'Aucun employé',
                });
                empSelect.addEventListener('change', function() {
                    empSelect.closest('form').submit();
                });
            } else if (empSelect) {
                empSelect.addEventListener('change', function() {
                    empSelect.closest('form').submit();
                });
            }
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

            // ===== Apply first row to weekdays only (excl. samedi/dimanche) =====
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
                        if (row.hasAttribute('data-weekend')) return; // skip weekend
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
