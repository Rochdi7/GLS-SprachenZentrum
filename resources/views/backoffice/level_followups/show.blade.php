@extends('layouts.main')

@section('title', 'Detail suivi niveau')
@section('breadcrumb-item', 'Suivi niveau')
@section('breadcrumb-item-link', route('backoffice.level_followups.index'))
@section('breadcrumb-item-active', 'Detail suivi niveau')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .followup-meta-card {
            border: 1px solid #e6ebf2;
            border-radius: 18px;
            background: #fff;
        }

        .followup-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .followup-meta-item {
            border: 1px solid #edf1f7;
            border-radius: 14px;
            padding: 14px 16px;
            background: #fbfcfe;
        }

        .followup-meta-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #7a8599;
            margin-bottom: 6px;
        }

        .followup-meta-value {
            font-size: 18px;
            font-weight: 700;
            color: #273142;
        }

        .followup-plan-table th {
            font-size: 14px;
            font-weight: 800;
            color: #3a4354;
            background: #f5f7fb;
            white-space: nowrap;
        }

        .followup-plan-table td {
            vertical-align: middle;
        }

        .followup-level-cell {
            font-size: 22px;
            font-weight: 800;
            color: #273142;
        }

        .followup-note {
            min-width: 260px;
        }

        .followup-note textarea {
            min-height: 88px;
            resize: vertical;
        }

        .badge-soft-success,
        .badge-soft-primary,
        .badge-soft-warning {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 700;
        }

        .badge-soft-success {
            background: #d9f6e6;
            color: #24704b;
        }

        .badge-soft-primary {
            background: #dfeafd;
            color: #2b58da;
        }

        .badge-soft-warning {
            background: #fff1cf;
            color: #a05d12;
        }

        @media (max-width: 991.98px) {
            .followup-meta-grid {
                grid-template-columns: 1fr;
            }

            .followup-note {
                min-width: 220px;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $formationStart = $group->date_debut ? \Carbon\Carbon::parse($group->date_debut) : null;
        $formationEnd = $group->date_fin ? \Carbon\Carbon::parse($group->date_fin) : null;

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

        $totalDays = 0;
        $elapsedDays = 0;

        foreach ($followups as $seg) {
            $segStart = $seg->level_start_date ? \Carbon\Carbon::parse($seg->level_start_date)->startOfDay() : null;
            $segEnd = $seg->level_end_date ? \Carbon\Carbon::parse($seg->level_end_date)->startOfDay() : null;
            if (!$segStart || !$segEnd || $segEnd->lt($segStart)) continue;

            $segDays = $countWeekdays($segStart, $segEnd);
            $totalDays += $segDays;

            if ($now->lt($segStart)) continue;

            if ($now->gt($segEnd)) {
                $elapsedDays += $segDays;
                continue;
            }

            $elapsedDays += $countWeekdays($segStart, $now->copy()->startOfDay());
        }

        $progress = $totalDays > 0 ? (int) round(($elapsedDays / $totalDays) * 100) : 0;
    @endphp

    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card followup-meta-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Planning des niveaux</h3>
                            <p class="text-muted mb-0">{{ $group->name ?? '-' }}</p>
                        </div>
                        <a href="{{ route('backoffice.level_followups.index') }}" class="btn btn-outline-secondary">
                            Retour
                        </a>
                    </div>

                    <div class="followup-meta-grid">
                        <div class="followup-meta-item">
                            <span class="followup-meta-label">Prof</span>
                            <div class="followup-meta-value">{{ $group->teacher?->name ?? '-' }}</div>
                        </div>
                        <div class="followup-meta-item">
                            <span class="followup-meta-label">Centre</span>
                            <div class="followup-meta-value">{{ $group->site?->name ?? '-' }}</div>
                        </div>
                        <div class="followup-meta-item">
                            <span class="followup-meta-label">Niveau de depart</span>
                            <div class="followup-meta-value">{{ $group->level ?? '-' }}</div>
                        </div>
                        <div class="followup-meta-item">
                            <span class="followup-meta-label">Debut formation</span>
                            <div class="followup-meta-value">{{ $formationStart ? $formationStart->format('d/m/Y') : '-' }}</div>
                        </div>
                        <div class="followup-meta-item">
                            <span class="followup-meta-label">Fin formation</span>
                            <div class="followup-meta-value">{{ $formationEnd ? $formationEnd->format('d/m/Y') : '-' }}</div>
                        </div>
                        <div class="followup-meta-item">
                            <span class="followup-meta-label">Progression</span>
                            <div class="followup-meta-value">{{ $progress }}%</div>
                            <div style="height:6px;background:#e5e7eb;border-radius:99px;margin-top:8px;">
                                <div style="height:6px;background:#10b981;border-radius:99px;width:{{ $progress }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle followup-plan-table mb-0">
                            <thead>
                                <tr>
                                    <th>Niveau</th>
                                    <th>Debut niveau</th>
                                    <th>Fin niveau</th>
                                    <th>Jours restants</th>
                                    <th>Statut</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($followups as $f)
                                    @php
                                        $start = $f->level_start_date ? \Carbon\Carbon::parse($f->level_start_date) : null;
                                        $end = $f->level_end_date ? \Carbon\Carbon::parse($f->level_end_date) : null;
                                        $finishedAt = $f->done_at ? \Carbon\Carbon::parse($f->done_at) : null;
                                        $isCurrent = $f->status !== 'done' && $start && $end && $now->betweenIncluded($start->copy()->startOfDay(), $end->copy()->endOfDay());
                                        $daysLeft = ($isCurrent && $end) ? (int) $now->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) : null;
                                        $isUrgent = $daysLeft !== null && $daysLeft <= 14 && $daysLeft >= 0;
                                        $isOverdue = $f->status !== 'done' && $end && $now->gt($end->copy()->endOfDay());
                                    @endphp
                                    <tr>
                                        <td class="followup-level-cell">{{ $f->level }}</td>
                                        <td>{{ $start ? $start->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $end ? $end->format('d/m/Y') : '-' }}</td>
                                        <td>
                                            @if($f->status === 'done')
                                                <small class="text-success fw-bold">Termine le {{ $end ? $end->format('d/m/Y') : '-' }}</small>
                                            @elseif($isOverdue)
                                                <small class="text-danger fw-bold"><i class="ti ti-alert-triangle"></i> En retard!</small>
                                            @elseif($isCurrent)
                                                <small class="text-warning fw-bold"><i class="ti ti-clock"></i> {{ $daysLeft }} jour(s) restant(s)</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($f->status === 'done')
                                                <span class="badge-soft-success">Termine</span>
                                            @elseif($isOverdue)
                                                <span class="badge bg-danger text-white" style="border-radius:999px;padding:8px 16px;font-size:14px;font-weight:700;">En retard</span>
                                            @elseif($isCurrent)
                                                <span class="badge-soft-primary">En cours</span>
                                            @else
                                                <span class="badge-soft-warning">En attente</span>
                                            @endif
                                        </td>
                                        <td class="followup-note">
                                            <form method="POST" action="{{ route('backoffice.level_followups.update_notes', $f) }}">
                                                @csrf
                                                @method('PATCH')
                                                <textarea name="done_notes"
                                                          class="form-control form-control-sm mb-2"
                                                          placeholder="Ajouter une note pour ce niveau...">{{ old('done_notes', $f->done_notes) }}</textarea>
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    Enregistrer note
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Aucun suivi niveau trouve pour ce groupe.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
