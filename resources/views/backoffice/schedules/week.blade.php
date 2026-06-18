@extends('layouts.main')

@section('title', 'Mon Planning')
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', 'Semaine')

@section('css')
<style>
    /* Week-nav btn-group: full width on mobile, auto on ≥576px */
    @media (min-width: 576px) {
        .w-sm-auto { width: auto !important; }
    }
    /* Tap-friendly time inputs in the editor on small screens */
    @media (max-width: 575.98px) {
        .schedule-table input[type="time"],
        .schedule-table input[type="text"] { min-height: 40px; }
    }
</style>
@endsection

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

        // ── View mode ───────────────────────────────────────────────
        // Creating a planning is gated by the `schedules.create` permission
        // (handled below as $canCreate). A plain staff member without that
        // permission (e.g. Réception) only CONSULTS their own planning.
        $todayKey = now()->format('Y-m-d');
    @endphp

    {{-- Title + meta --}}
    <div class="mb-3">
        <h5 class="mb-1">
            @if($isSelf)
                Mon planning
            @else
                Planning de {{ $target->name }}
            @endif
        </h5>
        <div class="text-muted small d-flex flex-wrap align-items-center gap-1">
            <span>Semaine du {{ $weekStart->locale('fr')->isoFormat('DD MMM') }} au {{ $weekEnd->locale('fr')->isoFormat('DD MMM YYYY') }}</span>
            @if($target->site)
                <span class="badge bg-light-info">{{ $target->site->name }}</span>
            @endif
            @if($target->staff_role)
                <span class="badge bg-light-primary">{{ $target->staff_role }}</span>
            @endif
        </div>
    </div>

    {{-- Week navigation + PDF — mobile-first, no overflow --}}
    <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center mb-4">
        <div class="btn-group w-100 w-sm-auto" role="group" aria-label="Navigation semaine">
            <a href="{{ route('backoffice.schedules.week', ['week' => $prevWeek, 'user_id' => $target->id]) }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="ph-duotone ph-caret-left"></i><span class="d-none d-sm-inline ms-1">Précédente</span>
            </a>
            <a href="{{ route('backoffice.schedules.week', ['week' => now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString(), 'user_id' => $target->id]) }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="ph-duotone ph-house"></i><span class="d-none d-sm-inline ms-1">Aujourd'hui</span>
            </a>
            <a href="{{ route('backoffice.schedules.week', ['week' => $nextWeek, 'user_id' => $target->id]) }}"
               class="btn btn-outline-secondary btn-sm">
                <span class="d-none d-sm-inline me-1">Suivante</span><i class="ph-duotone ph-caret-right"></i>
            </a>
        </div>
        <form method="GET" class="flex-grow-1">
            <input type="hidden" name="user_id" value="{{ $target->id }}">
            <input type="date" name="week" class="form-control form-control-sm w-100" value="{{ $weekStart->toDateString() }}" onchange="this.form.submit()">
        </form>
        <a href="{{ route('backoffice.schedules.week.pdf', ['week' => $weekStart->toDateString(), 'user_id' => $target->id]) }}"
           class="btn btn-danger btn-sm" target="_blank">
            <i class="ph-duotone ph-file-pdf me-1"></i> Exporter PDF
        </a>
    </div>

    {{-- Admin-only: switch target user --}}
    @if($isAdmin && $staffOptions->isNotEmpty())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET">
                    <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                    <label class="form-label small mb-1 fw-semibold">
                        <i class="ph-duotone ph-users-three me-1"></i> Gérer le planning de :
                    </label>
                    <div>
                        <select name="user_id" id="week-employee-select" class="form-select">
                            <option value="{{ $authUser->id }}" {{ $target->id === $authUser->id ? 'selected' : '' }}>— Moi-même —</option>
                            @foreach($staffOptions as $u)
                                <option value="{{ $u->id }}" {{ $target->id === $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}{{ $u->staff_role ? ' · ' . $u->staff_role : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- READ-ONLY DASHBOARD — shown to staff (Réception) on their    --}}
    {{-- own planning, and to admins as a quick overview before edit. --}}
    {{-- ============================================================ --}}

    {{-- KPI strip — compact on mobile (2-up), inline on desktop --}}
    <div class="row g-2 g-lg-3 mb-4">
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-2 p-lg-3">
                    <div class="text-muted text-uppercase" style="font-size:.68rem;letter-spacing:.02em;">Heures planifiées</div>
                    <div class="fw-bold text-primary lh-1 mt-1" style="font-size:clamp(1.25rem,5vw,1.6rem);">{{ \App\Models\UserSchedule::formatMinutes($totalWorked) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-2 p-lg-3">
                    <div class="text-muted text-uppercase" style="font-size:.68rem;letter-spacing:.02em;">Jours travaillés</div>
                    <div class="fw-bold text-success lh-1 mt-1" style="font-size:clamp(1.25rem,5vw,1.6rem);">{{ $workingDays }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-2 p-lg-3">
                    <div class="text-muted text-uppercase" style="font-size:.68rem;letter-spacing:.02em;">Jours de repos</div>
                    <div class="fw-bold text-secondary lh-1 mt-1" style="font-size:clamp(1.25rem,5vw,1.6rem);">{{ $daysOff }}</div>
                </div>
            </div>
        </div>
        @if(!is_null($overtime))
            <div class="col-6 col-lg">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-2 p-lg-3">
                        <div class="text-muted text-uppercase" style="font-size:.68rem;letter-spacing:.02em;">Heures supp.</div>
                        <div class="fw-bold lh-1 mt-1 {{ $overtime > 0 ? 'text-warning' : 'text-muted' }}" style="font-size:clamp(1.25rem,5vw,1.6rem);">
                            +{{ \App\Models\UserSchedule::formatMinutes($overtime) }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-12 col-lg">
            <div class="card border-0 shadow-sm h-100 bg-light-primary">
                <div class="card-body p-2 p-lg-3 d-flex align-items-center gap-2">
                    <i class="ph-duotone ph-arrow-fat-right fs-4 text-primary d-none d-lg-block"></i>
                    <div>
                        <div class="text-muted text-uppercase" style="font-size:.68rem;letter-spacing:.02em;">Prochain service</div>
                        @if($nextShift)
                            <div class="fw-bold text-capitalize">
                                {{ $nextShift->date->locale('fr')->isoFormat('ddd DD/MM') }}
                                · {{ substr($nextShift->start_time, 0, 5) }} → {{ substr($nextShift->end_time, 0, 5) }}
                            </div>
                        @else
                            <div class="text-muted small">Aucun service à venir cette semaine</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Change alert (honest: derived from updated_at) --}}
    @if($changedThisWeek)
        <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
            <i class="ph-duotone ph-warning-circle fs-5"></i>
            <div>
                Votre planning a été modifié cette semaine.
                @if($lastUpdated)
                    <span class="text-muted small">· Dernière mise à jour : {{ $lastUpdated->locale('fr')->isoFormat('DD MMM [à] HH:mm') }}</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Weekly planner cards --}}
    <div class="row g-3 mb-4">
        @foreach($days as $day)
            @php
                $s = $day['schedule'];
                $isToday   = $day['key'] === $todayKey;
                $isWeekend = in_array($day['date']->dayOfWeek, [0, 6]);
                $state  = $s ? 'work' : ($isWeekend ? 'off' : 'missing');
                $accent = ['work' => 'success', 'off' => 'secondary', 'missing' => 'warning'][$state];
            @endphp
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card h-100 border-0 shadow-sm @if($isToday) border border-primary border-2 @endif"
                     style="border-left: 4px solid var(--bs-{{ $accent }}) !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="fw-bold text-capitalize">{{ $day['label'] }}</span>
                            @if($isToday)<span class="badge bg-primary">Aujourd'hui</span>@endif
                        </div>
                        @if($state === 'work')
                            <div class="fs-5 fw-semibold">
                                {{ substr($s->start_time, 0, 5) }}
                                <i class="ph ph-arrow-right small"></i>
                                {{ substr($s->end_time, 0, 5) }}
                            </div>
                            @if($s->break_minutes > 0)
                                <div class="text-muted small"><i class="ph-duotone ph-coffee me-1"></i>Pause {{ $s->break_formatted }}</div>
                            @endif
                            <div class="mt-2 badge bg-light-success text-success">{{ $s->worked_formatted }} travaillé</div>
                            @if($s->notes)
                                <div class="small text-muted mt-2 fst-italic">“{{ $s->notes }}”</div>
                            @endif
                        @elseif($state === 'off')
                            <div class="text-secondary py-2"><i class="ph-duotone ph-bed me-1"></i>Jour de repos</div>
                        @else
                            <div class="text-warning py-2"><i class="ph-duotone ph-question me-1"></i>Non planifié</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Reception-specific info --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-muted small text-uppercase">Centre</div>
                    <div class="fw-semibold">{{ $target->site->name ?? '—' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small text-uppercase">Poste</div>
                    <div class="fw-semibold">{{ $target->staff_role ?? '—' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small text-uppercase">Employé</div>
                    <div class="fw-semibold">{{ $target->name }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small text-uppercase">Semaine</div>
                    <div class="fw-semibold">{{ $weekStart->locale('fr')->isoFormat('DD/MM') }} – {{ $weekEnd->locale('fr')->isoFormat('DD/MM') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ACTIONS — creating a planning is permission-gated.            --}}
    {{-- Only users with `schedules.create` see the create button.     --}}
    {{--   • Super Admin → can schedule for every employee.            --}}
    {{--   • Admin / Manager → only employees of their centre(s).      --}}
    {{--   • Réception (no permission) → button hidden, read-only.     --}}
    {{-- ============================================================ --}}
    @php $canCreate = $authUser->can('schedules.create'); @endphp

    @if($canCreate)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="ph-duotone ph-calendar-plus fs-4 text-primary"></i>
                    <div>
                        <div class="fw-semibold">Planifier un employé</div>
                        <div class="text-muted small">
                            Créez le planning d'un employé sur une période donnée
                            (samedis &amp; dimanches exclus automatiquement).
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('backoffice.schedules.create') }}" class="btn btn-primary">
                        <i class="ph-duotone ph-plus me-1"></i> Créer un planning
                    </a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#inlineWeekEditor">
                        <i class="ph-duotone ph-pencil-simple me-1"></i> Éditer cette semaine
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-light border d-flex align-items-center gap-2">
            <i class="ph-duotone ph-info fs-5 text-primary"></i>
            <span class="small text-muted mb-0">
                Votre planning est défini par l'administration. Pour toute modification, contactez votre responsable.
            </span>
        </div>
    @endif

    {{-- Inline weekly editor — collapsed by default, opens on demand. --}}
    @if($canCreate)
    <div class="collapse" id="inlineWeekEditor">
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

        <div class="d-flex align-items-center gap-2 mb-2 mt-1">
            <i class="ph-duotone ph-pencil-simple text-primary"></i>
            <h6 class="mb-0">Éditer la semaine du {{ $weekStart->locale('fr')->isoFormat('DD/MM') }}</h6>
            @if(!$isSelf)
                <span class="badge bg-light-warning text-warning">{{ $target->name }}</span>
            @endif
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 schedule-table">
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
    </div>{{-- /#inlineWeekEditor --}}
    @endif

@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) { new bootstrap.Toast(toastEl).show(); }

            // ===== Searchable employee dropdown =====
            const weekEmpSelect = document.getElementById('week-employee-select');
            if (weekEmpSelect && typeof Choices !== 'undefined') {
                new Choices(weekEmpSelect, {
                    searchEnabled: true,
                    searchPlaceholderValue: 'Rechercher...',
                    itemSelectText: '',
                    shouldSort: false,
                    noResultsText: 'Aucun résultat',
                });
                weekEmpSelect.addEventListener('change', function() {
                    weekEmpSelect.closest('form').submit();
                });
            } else if (weekEmpSelect) {
                weekEmpSelect.addEventListener('change', function() {
                    weekEmpSelect.closest('form').submit();
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
                        // Skip weekend rows (samedi / dimanche) — do not auto-fill them.
                        if (row.hasAttribute('data-weekend')) return;
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
