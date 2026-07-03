@extends('layouts.main')

@section('title', 'Nouveau paiement professeur')
@section('breadcrumb-item', 'Paiement Professeurs')
@section('breadcrumb-item-link', route('backoffice.payroll.crm.legacy.dashboard'))
@section('breadcrumb-item-active', 'Nouveau paiement')

@section('css')
<style>
    .mode-card { cursor: pointer; border: 2px solid #e9ecef; transition: all .15s; }
    .mode-card:hover { border-color: #b6c2d9; }
    .mode-card.active { border-color: #198754; background: #f2fbf5; }
    .mode-card .ph-duotone { font-size: 1.8rem; }
    .tier-preview { font-size: .78rem; }
    .tier-preview td, .tier-preview th { padding: 3px 8px; }
</style>
@endsection

@section('content')

<div class="d-flex gap-2 mb-3">
    <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph-duotone ph-arrow-left me-1"></i> Tableau de bord
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">
    <div class="col-lg-8 mx-auto">

        {{-- ── MODE PICKER ─────────────────────────────────────── --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="ph-duotone ph-wallet me-2 text-success"></i>Mode de paiement</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card mode-card h-100 {{ $mode === 'period' ? 'active' : '' }}" data-mode="period">
                            <div class="card-body d-flex align-items-center gap-3">
                                <i class="ph-duotone ph-calendar-check text-success"></i>
                                <div>
                                    <div class="fw-bold">Paiement par période / mois</div>
                                    <small class="text-muted">Par étudiant, selon le total de présences sur la période.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mode-card h-100 {{ $mode === 'hourly' ? 'active' : '' }}" data-mode="hourly">
                            <div class="card-body d-flex align-items-center gap-3">
                                <i class="ph-duotone ph-clock text-primary"></i>
                                <div>
                                    <div class="fw-bold">Paiement par heures</div>
                                    <small class="text-muted">Taux horaire × heures + prime de performance.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── PERIOD FORM ─────────────────────────────────────── --}}
        <form action="{{ route('backoffice.payroll.crm.legacy.import.store-period') }}" method="POST"
              id="form-period" class="mode-form {{ $mode === 'period' ? '' : 'd-none' }}">
            @csrf
            <input type="hidden" name="crm_class_name"   class="hidden-class-name">
            <input type="hidden" name="crm_teacher_name" class="hidden-teacher-name">
            <input type="hidden" name="crm_level"        class="hidden-level">

            <div class="card shadow-sm">
                <div class="card-header"><h6 class="mb-0"><i class="ph-duotone ph-calendar-check me-2 text-success"></i>Paiement par période</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Classe / Professeur <span class="text-danger">*</span></label>
                            @include('backoffice.payroll.crm.imports.partials._class_select', ['name' => 'period'])
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date début <span class="text-danger">*</span></label>
                            <input type="date" name="date_start" class="form-control"
                                   value="{{ old('date_start', now()->startOfMonth()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date fin <span class="text-danger">*</span></label>
                            <input type="date" name="date_end" class="form-control"
                                   value="{{ old('date_end', now()->endOfMonth()->toDateString()) }}" required>
                            <div class="form-text">Maximum 62 jours.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Mois rattaché <span class="text-danger">*</span></label>
                            <select name="attached_month" class="form-select" required>
                                @foreach(range(1,12) as $m)
                                    <option value="{{ $m }}" {{ old('attached_month', now()->month) == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->locale('fr')->isoFormat('MMMM') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Année rattachée <span class="text-danger">*</span></label>
                            <input type="number" name="attached_year" class="form-control" min="2000" max="2100"
                                   value="{{ old('attached_year', now()->year) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">N° mois du groupe <span class="text-danger">*</span></label>
                            <input type="number" name="group_month_number" class="form-control" min="1" max="60"
                                   value="{{ old('group_month_number', 1) }}" required>
                            <div class="form-text">Ex : décembre = Mois 1.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prix de base / étudiant <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="base_price" class="form-control period-base-price"
                                       value="{{ old('base_price') }}" step="0.01" min="0" placeholder="500" required>
                                <span class="input-group-text">DH</span>
                            </div>
                            <div class="form-text period-rate-hint"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Libellé période <span class="text-muted fw-normal">(optionnel)</span></label>
                            <input type="text" name="month_label" class="form-control"
                                   value="{{ old('month_label') }}" placeholder="Ex : Décembre 2025 (Mois 1)">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optionnel)</span></label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>

                        {{-- Tier preview (frozen at creation) --}}
                        <div class="col-12">
                            <div class="alert alert-light border mb-0">
                                <div class="fw-semibold mb-1"><i class="ph-duotone ph-info me-1"></i>Paliers appliqués (figés à la création)</div>
                                <table class="table table-sm table-bordered tier-preview mb-1" style="max-width:420px">
                                    <thead class="table-light"><tr><th>Présences</th><th>Semaines</th></tr></thead>
                                    <tbody>
                                        @foreach($tiers as $t)
                                            <tr>
                                                <td>{{ $t['min'] }}@if($t['max'] !== null)–{{ $t['max'] }}@else et +@endif</td>
                                                <td>{{ $t['weeks'] === 'full' ? 'Complet (prix entier)' : $t['weeks'] . ' sem.' }}</td>
                                            </tr>
                                        @endforeach
                                        <tr><td>&lt; {{ $tiers[0]['min'] ?? 5 }}</td><td>0 DH</td></tr>
                                    </tbody>
                                </table>
                                <small class="text-muted">1 semaine = prix de base ÷ {{ $weeksPerPeriod }}.</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn btn-success"><i class="ph-duotone ph-floppy-disk me-1"></i> Créer le paiement</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- ── HOURLY FORM ─────────────────────────────────────── --}}
        <form action="{{ route('backoffice.payroll.crm.legacy.import.store-hourly') }}" method="POST"
              id="form-hourly" class="mode-form {{ $mode === 'hourly' ? '' : 'd-none' }}">
            @csrf
            <input type="hidden" name="crm_class_name"   class="hidden-class-name">
            <input type="hidden" name="crm_teacher_name" class="hidden-teacher-name">
            <input type="hidden" name="crm_level"        class="hidden-level">

            <div class="card shadow-sm">
                <div class="card-header"><h6 class="mb-0"><i class="ph-duotone ph-clock me-2 text-primary"></i>Paiement par heures</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Classe / Professeur <span class="text-danger">*</span></label>
                            @include('backoffice.payroll.crm.imports.partials._class_select', ['name' => 'hourly'])
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mois rattaché <span class="text-danger">*</span></label>
                            <select name="attached_month" class="form-select" required>
                                @foreach(range(1,12) as $m)
                                    <option value="{{ $m }}" {{ old('attached_month', now()->month) == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->locale('fr')->isoFormat('MMMM') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Année <span class="text-danger">*</span></label>
                            <input type="number" name="attached_year" class="form-control" min="2000" max="2100"
                                   value="{{ old('attached_year', now()->year) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">N° mois groupe</label>
                            <input type="number" name="group_month_number" class="form-control" min="1" max="60"
                                   value="{{ old('group_month_number') }}" placeholder="Optionnel">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Taux horaire <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="hourly_rate" class="form-control h-input" data-h="rate"
                                       value="{{ old('hourly_rate') }}" step="0.01" min="0" placeholder="100" required>
                                <span class="input-group-text">DH</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Total heures <span class="text-danger">*</span></label>
                            <input type="number" name="total_hours" class="form-control h-input" data-h="hours"
                                   value="{{ old('total_hours') }}" step="0.01" min="0" placeholder="20" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Libellé période <span class="text-muted fw-normal">(optionnel)</span></label>
                            <input type="text" name="month_label" class="form-control" value="{{ old('month_label') }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="w-100 text-end p-3 rounded" style="background:#eef6ff">
                                <div class="text-muted small">Total calculé</div>
                                <div class="h4 mb-0 fw-bold text-primary" id="hourly-total-preview">0,00 DH</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optionnel)</span></label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary"><i class="ph-duotone ph-floppy-disk me-1"></i> Créer le paiement</button>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('.mode-card');
    const forms = { period: document.getElementById('form-period'), hourly: document.getElementById('form-hourly') };
    const weeksPerPeriod = {{ $weeksPerPeriod }};

    function selectMode(mode) {
        cards.forEach(c => c.classList.toggle('active', c.dataset.mode === mode));
        Object.entries(forms).forEach(([k, f]) => f.classList.toggle('d-none', k !== mode));
    }
    cards.forEach(c => c.addEventListener('click', () => selectMode(c.dataset.mode)));

    // Wire class-select sync inside each form
    document.querySelectorAll('.class-select').forEach(select => {
        const form = select.closest('form');
        function sync() {
            const opt = select.options[select.selectedIndex];
            if (!opt || !opt.value) return;
            form.querySelector('.hidden-class-name').value   = opt.dataset.name    || opt.text;
            form.querySelector('.hidden-teacher-name').value = opt.dataset.teacher || '';
            form.querySelector('.hidden-level').value        = opt.dataset.level   || '';
            const lastRate = opt.dataset.lastRate;
            const base = form.querySelector('.period-base-price');
            const hint = form.querySelector('.period-rate-hint');
            if (base && lastRate && !base.value) base.value = lastRate;
            if (hint && lastRate) hint.innerHTML = 'Dernier taux : <strong>' + lastRate + ' DH</strong>';
        }
        select.addEventListener('change', sync);
        sync();
    });

    // Period: live "= X DH / semaine" hint
    const periodForm = forms.period;
    const basePrice = periodForm.querySelector('.period-base-price');
    const rateHint  = periodForm.querySelector('.period-rate-hint');
    function updatePeriodHint() {
        const v = parseFloat(basePrice.value);
        if (!isNaN(v) && v > 0) rateHint.innerHTML = '= <strong>' + (v / weeksPerPeriod).toFixed(2) + ' DH</strong> / semaine équivalente';
    }
    basePrice.addEventListener('input', updatePeriodHint);
    updatePeriodHint();

    // Hourly: live total preview
    const hourlyForm = forms.hourly;
    const totalEl = document.getElementById('hourly-total-preview');
    function updateHourlyTotal() {
        const g = s => parseFloat(hourlyForm.querySelector(`[data-h="${s}"]`).value) || 0;
        const total = g('rate') * g('hours');
        totalEl.textContent = total.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' DH';
    }
    hourlyForm.querySelectorAll('.h-input').forEach(i => i.addEventListener('input', updateHourlyTotal));
    updateHourlyTotal();
});
</script>
@endsection
