@extends('layouts.main')

@section('title', 'Suivi niveau')
@section('breadcrumb-item', 'Dashboard')
@section('breadcrumb-item-active', 'Suivi niveau')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .level-stepper {
            --circle-size: 44px;
            --track-size: 10px;
            --track-color: #e4e4e4;
            --fill-color: #2f7ed8;
            --done-color: #09b10f;
            --current-color: #2f7ed8;
            position: relative;
            padding-top: 8px;
        }

        .level-stepper__track,
        .level-stepper__fill {
            position: absolute;
            top: calc((var(--circle-size) / 2) - (var(--track-size) / 2) + 8px);
            height: var(--track-size);
            border-radius: 999px;
        }

        .level-stepper__track {
            left: calc(var(--circle-size) / 2);
            right: calc(var(--circle-size) / 2);
            background: var(--track-color);
        }

        .level-stepper__fill {
            left: calc(var(--circle-size) / 2 + ((100% - var(--circle-size)) * var(--level-fill-start) / 100));
            width: calc((100% - var(--circle-size)) * var(--level-fill-width) / 100);
            background: var(--fill-color);
        }

        .level-stepper__steps {
            position: relative;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0;
        }

        .level-stepper__step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .level-stepper__circle {
            width: var(--circle-size);
            height: var(--circle-size);
            border-radius: 50%;
            border: 4px solid #8d8d8d;
            background: #fff;
            position: relative;
            box-shadow: 0 0 0 4px #fff;
        }

        .level-stepper__label {
            margin-top: 12px;
            font-size: 1rem;
            font-weight: 700;
            color: #121212;
            line-height: 1.2;
        }

        .level-stepper__step--done .level-stepper__circle {
            background: var(--done-color);
            border-color: var(--done-color);
        }

        .level-stepper__step--done .level-stepper__circle::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            width: 13px;
            height: 7px;
            border-left: 4px solid #fff;
            border-bottom: 4px solid #fff;
            transform: translate(-50%, -65%) rotate(-45deg);
        }

        .level-stepper__step--done .level-stepper__label {
            color: var(--done-color);
        }

        .level-stepper__step--current .level-stepper__label {
            color: var(--current-color);
        }

        .level-stepper__step--current .level-stepper__circle {
            border-color: var(--current-color);
            background: #eaf3ff;
        }

        .level-stepper__step--current .level-stepper__circle::after {
            content: "";
            position: absolute;
            inset: 50% auto auto 50%;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--current-color);
            transform: translate(-50%, -50%);
        }

        .level-stepper__step--inactive .level-stepper__circle {
            border-color: #cfcfcf;
            background: #f7f7f7;
        }

        .level-stepper__step--inactive .level-stepper__label {
            color: #999;
        }

        .level-stepper__meta {
            margin-top: 10px;
        }

        .followup-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
            gap: 22px;
        }

        .followup-card {
            border: 1px solid #e6ebf2;
            border-radius: 24px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
            box-shadow: 0 18px 40px rgba(24, 39, 75, 0.08);
            overflow: hidden;
        }

        .followup-card__header {
            padding: 22px 22px 14px;
            background: radial-gradient(circle at top right, rgba(47, 126, 216, 0.12), transparent 34%), #fff;
            border-bottom: 1px solid #eef2f7;
        }

        .followup-card__title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
            color: #243042;
        }

        .followup-card__subtitle {
            margin-top: 6px;
            color: #667085;
            font-size: 0.92rem;
        }

        .followup-card__chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .followup-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .followup-chip--center {
            background: #eef6ff;
            color: #2f7ed8;
        }

        .followup-chip--teacher {
            background: #f4f5f7;
            color: #475467;
        }

        .followup-chip--due {
            background: #fff4df;
            color: #ad6800;
        }

        .followup-card__body {
            padding: 22px;
        }

        .followup-card__stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .followup-stat {
            border: 1px solid #edf1f7;
            border-radius: 18px;
            padding: 14px 16px;
            background: #fff;
        }

        .followup-stat__label {
            display: block;
            margin-bottom: 4px;
            color: #7a8599;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .followup-stat__value {
            color: #243042;
            font-size: 1rem;
            font-weight: 800;
        }

        .followup-stepper-wrap {
            border: 1px solid #edf1f7;
            border-radius: 22px;
            padding: 18px 18px 14px;
            background: linear-gradient(180deg, #fff 0%, #f8fbff 100%);
        }

        .followup-card__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #eef2f7;
        }

        .followup-card__status {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .followup-card__actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }

        .followup-meta-note {
            color: #667085;
            font-size: 0.86rem;
        }

        @media (max-width: 767.98px) {
            .followup-grid {
                grid-template-columns: 1fr;
            }

            .followup-card__stats {
                grid-template-columns: 1fr;
            }

            .followup-card__footer {
                flex-direction: column;
                align-items: stretch;
            }

            .followup-card__actions {
                margin-left: 0;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Suivi niveau (rappels profs)</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light-primary">{{ $dueFollowups->count() }} rappel(s) du(s)</span>
                        @can('level_followups.edit')
                        <form method="POST" action="{{ route('backoffice.level_followups.sync') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-light-secondary"
                                    onclick="return confirm('Synchroniser le suivi niveau pour tous les groupes actifs ?')"
                                    title="Synchronise les niveaux pour tous les groupes actifs">
                                <i class="ti ti-refresh me-1"></i> Sync groupes
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('backoffice.level_followups.index') }}" class="row g-3 align-items-end mb-4">
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label">Centre</label>
                            <select name="center" class="form-select">
                                <option value="">Tous les centres</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" @selected((string) request('center') === (string) $site->id)>{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label">Prof</label>
                            <select name="teacher" class="form-select">
                                <option value="">Tous les profs</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" @selected((string) request('teacher') === (string) $teacher->id)>{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary">Filtrer</button>
                            <a href="{{ route('backoffice.level_followups.index') }}" class="btn btn-light-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            @if($followups->isEmpty())
                <div class="alert alert-info mt-4 mb-0">
                    Aucun suivi niveau genere.
                </div>
            @endif

            @if($followups->isNotEmpty())
                <section class="mt-4">
                    <div class="followup-grid">
                    @foreach($followups as $followup)
                        @php
                            $order = ['A1', 'A2', 'B1', 'B2'];
                            $startLevel = $followup->group?->level;
                            $startIndex = is_string($startLevel) ? array_search($startLevel, $order, true) : 0;
                            $startIndex = ($startIndex === false) ? 0 : $startIndex;

                            $allForGroup = $levelFollowupsByGroup[$followup->group_id] ?? collect();
                            $activeSegments = $allForGroup
                                ->whereIn('level', array_slice($order, $startIndex))
                                ->sortBy('level_start_date')
                                ->values();

                            $totalDays = 0;
                            $elapsedDays = 0;
                            $currentLevel = null;

                            // Count only weekdays (Mon-Fri)
                            $countWeekdays = function($from, $to) {
                                $count = 0;
                                $cur = $from->copy();
                                while ($cur->lte($to)) {
                                    if (!$cur->isWeekend()) {
                                        $count++;
                                    }
                                    $cur->addDay();
                                }
                                return $count;
                            };

                            foreach ($activeSegments as $seg) {
                                $segStart = $seg->level_start_date ? \Carbon\Carbon::parse($seg->level_start_date)->startOfDay() : null;
                                $segEnd = $seg->level_end_date ? \Carbon\Carbon::parse($seg->level_end_date)->startOfDay() : null;
                                if (!$segStart || !$segEnd || $segEnd->lt($segStart)) continue;

                                $segDays = $countWeekdays($segStart, $segEnd);
                                $totalDays += $segDays;

                                if ($now->lt($segStart)) {
                                    continue;
                                }

                                if ($now->gt($segEnd)) {
                                    $elapsedDays += $segDays;
                                    continue;
                                }

                                $currentLevel = $seg->level;
                                $elapsedDays += $countWeekdays($segStart, $now->copy()->startOfDay());
                            }

                            $percent = $totalDays > 0 ? (int) round(($elapsedDays / $totalDays) * 100) : 0;
                            $stepCount = count($order);
                            $fillStartPercent = $stepCount > 1 ? ($startIndex / ($stepCount - 1)) * 100 : 0;
                            $fillWidthPercent = (($stepCount > 1 ? (100 - $fillStartPercent) : 0) * $percent) / 100;

                            $levelState = [];
                            foreach ($order as $lvl) {
                                $levelState[$lvl] = 'inactive';
                            }

                            foreach ($activeSegments as $seg) {
                                $lvl = $seg->level;
                                $segStart = $seg->level_start_date ? \Carbon\Carbon::parse($seg->level_start_date)->startOfDay() : null;
                                $segEnd = $seg->level_end_date ? \Carbon\Carbon::parse($seg->level_end_date)->startOfDay() : null;
                                if (!$segStart || !$segEnd) continue;

                                if ($now->gt($segEnd)) {
                                    $levelState[$lvl] = 'done';
                                } elseif ($now->betweenIncluded($segStart, $segEnd)) {
                                    $levelState[$lvl] = 'current';
                                } else {
                                    $levelState[$lvl] = 'pending';
                                }
                            }

                            $isDue = ($followup->status === 'pending') && $followup->due_date && \Carbon\Carbon::parse($followup->due_date)->lte($now);
                            $isCurrentStatus = $followup->status !== 'done' && $currentLevel !== null;
                            $statusClass = $followup->status === 'done'
                                ? 'bg-light-success text-success'
                                : ($isCurrentStatus ? 'bg-light-primary text-primary' : 'bg-light-warning text-warning');
                            $statusLabel = $followup->status === 'done' ? 'Termine' : ($isCurrentStatus ? 'En cours' : 'En attente');
                            $centerName = $followup->group?->site?->name ?? 'Centre non defini';
                            $teacherName = $followup->group?->teacher?->name ?? 'Prof non assigne';
                        @endphp

                        <article class="followup-card">
                            <div class="followup-card__header">
                                <h3 class="followup-card__title">{{ $followup->group?->name ?? 'Groupe sans nom' }}</h3>
                                <div class="followup-card__subtitle">Vision rapide du suivi de progression</div>

                                <div class="followup-card__chips">
                                    <span class="followup-chip followup-chip--center">
                                        <i class="ti ti-building f-16"></i>{{ $centerName }}
                                    </span>
                                    <span class="followup-chip followup-chip--teacher">
                                        <i class="ti ti-user f-16"></i>{{ $teacherName }}
                                    </span>
                                    <span class="followup-chip followup-chip--due">
                                        <i class="ti ti-calendar-event f-16"></i>
                                        {{ $followup->due_date ? \Carbon\Carbon::parse($followup->due_date)->format('d/m/Y') : 'Aucune echeance' }}
                                    </span>
                                </div>
                            </div>

                            <div class="followup-card__body">
                                <div class="followup-card__stats">
                                    <div class="followup-stat">
                                        <span class="followup-stat__label">Niveau actuel</span>
                                        <div class="followup-stat__value">{{ $currentLevel ?? $followup->level }}</div>
                                    </div>
                                    <div class="followup-stat">
                                        <span class="followup-stat__label">Progression</span>
                                        <div class="followup-stat__value">{{ $percent }}%</div>
                                    </div>
                                </div>

                                <div class="followup-stepper-wrap">
                                    <div class="level-stepper"
                                         style="--level-fill-start: {{ $fillStartPercent }}; --level-fill-width: {{ $fillWidthPercent }};">
                                        <div class="level-stepper__track" aria-hidden="true"></div>
                                        <div class="level-stepper__fill" aria-hidden="true"></div>

                                        <div class="level-stepper__steps">
                                            @foreach($order as $idx => $lvl)
                                                @php
                                                    $inactive = $idx < $startIndex;
                                                    $state = $inactive ? 'inactive' : ($levelState[$lvl] ?? 'pending');
                                                @endphp
                                                <div class="level-stepper__step level-stepper__step--{{ $state }}">
                                                    <span class="level-stepper__circle" aria-hidden="true"></span>
                                                    <span class="level-stepper__label">{{ $lvl }}</span>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="level-stepper__meta text-muted text-sm">
                                            Parcours actif depuis <strong>{{ $startLevel ?? 'A1' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="followup-card__footer">
                                    <div class="followup-card__status">
                                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                        @if($followup->status === 'done')
                                            <span class="followup-meta-note">
                                                Termine le {{ $followup->done_at ? \Carbon\Carbon::parse($followup->done_at)->format('d/m/Y H:i') : '-' }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="followup-card__actions">
                                        <a href="{{ route('backoffice.level_followups.group_show', $followup->group_id) }}"
                                           class="avtar avtar-xs btn-link-secondary"
                                           title="Voir details">
                                            <i class="ti ti-eye f-20"></i>
                                        </a>

                                        <a href="{{ route('backoffice.level_followups.group_pdf', $followup->group_id) }}"
                                           class="avtar avtar-xs btn-link-danger"
                                           title="Exporter PDF">
                                            <i class="ti ti-download f-20"></i>
                                        </a>

                                        @if($followup->status !== 'done')
                                            <form method="POST" action="{{ route('backoffice.level_followups.complete', $followup) }}" class="d-inline-block">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="ti ti-check me-1"></i>Marquer termine
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('backoffice.level_followups.destroy', $followup) }}" method="POST" class="d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                                    onclick="return confirm('Supprimer ce suivi niveau ?')"
                                                    title="Supprimer">
                                                <i class="ti ti-trash f-20"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection
