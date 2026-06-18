@extends('layouts.main')

@section('title', 'Gestion planning')
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', 'Gestion')

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

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h5 class="mb-1"><i class="ph-duotone ph-calendar-dots me-1 text-primary"></i> Gestion planning</h5>
            <div class="text-muted small">Plannings groupés par employé et par semaine. Sélectionnez une semaine pour voir, éditer ou supprimer les jours.</div>
        </div>
        @can('schedules.create')
            <a href="{{ route('backoffice.schedules.create') }}" class="btn btn-primary">
                <i class="ph-duotone ph-plus me-1"></i> Créer un planning
            </a>
        @endcan
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-sm-auto">
                    <label class="form-label fw-semibold mb-1"><i class="ph-duotone ph-funnel me-1"></i> Filtrer</label>
                </div>
                <div class="col">
                    <select name="user_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les employés</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}" {{ request('user_id') == $e->id ? 'selected' : '' }}>
                                {{ $e->name }} · {{ $e->staff_role ?? '—' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employé</th>
                            <th>Semaine</th>
                            <th class="text-center" style="width: 90px;">Jours</th>
                            <th class="text-end" style="width: 110px;">Travaillé</th>
                            <th class="text-end" style="width: 240px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($weeks as $w)
                            <tr>
                                <td class="fw-medium">
                                    {{ $w['user']->name ?? '—' }}
                                    <div class="text-muted small">{{ $w['user']->staff_role ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="text-capitalize">{{ $w['week_start']->locale('fr')->isoFormat('DD MMM') }}</span>
                                    – {{ $w['week_end']->locale('fr')->isoFormat('DD MMM YYYY') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light-primary text-primary">{{ $w['days'] }}</span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    {{ \App\Models\UserSchedule::formatMinutes($w['worked']) }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('backoffice.schedules.create', ['user_id' => $w['user_id'], 'week' => $w['week_start']->toDateString()]) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="ph-duotone ph-eye me-1"></i> Voir / Éditer
                                    </a>
                                    @can('schedules.delete')
                                        <form method="POST" action="{{ route('backoffice.schedules.week.destroy') }}" class="d-inline"
                                              onsubmit="return confirm('Supprimer toute la semaine du {{ $w['week_start']->format('d/m/Y') }} pour {{ addslashes($w['user']->name ?? '') }} ? Cette action est irréversible.');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="user_id" value="{{ $w['user_id'] }}">
                                            <input type="hidden" name="week_start" value="{{ $w['week_start']->toDateString() }}">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="ph-duotone ph-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="ph-duotone ph-calendar-x fs-3 d-block mb-2"></i>
                                    Aucun planning enregistré.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) { new bootstrap.Toast(toastEl).show(); }
        });
    </script>
@endsection
