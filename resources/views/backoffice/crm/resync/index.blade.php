@extends('layouts.main')

@section('title', 'Re-synchronisation CRM')
@section('breadcrumb-item', 'CRM (API)')
@section('breadcrumb-item-active', 'Re-sync')

@section('content')

<div class="row mb-4 align-items-center">
    <div class="col">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="ph-duotone ph-cloud-arrow-down text-primary"></i>
            Re-synchronisation CRM
        </h4>
        <small class="text-muted">
            Re-pull les données depuis Wimschool pour refléter immédiatement vos modifications
            (absences, paiements, inscriptions…). Chaque sync est tracée avec l'utilisateur déclencheur.
        </small>
    </div>
    @include('backoffice.crm.partials._sync_badge')
</div>

@if ($isLocked)
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
    <i class="ph-duotone ph-lock text-warning f-20"></i>
    <span>Une synchronisation est déjà en cours. Attendez qu'elle se termine avant de relancer.</span>
</div>
@endif

{{-- Domain cards --}}
<div class="row g-3 mb-4">

    @php
    $domainMeta = [
        'attendance'    => ['icon' => 'ph-calendar-check',    'color' => '#1cc88a', 'desc' => 'Statuts présence/absence — mis à jour après saisie réception par email'],
        'collections'   => ['icon' => 'ph-hand-coins',        'color' => '#f59e0b', 'desc' => 'Créances, taux recouvrement, vieillissement'],
        'payments'      => ['icon' => 'ph-money',             'color' => '#3b82f6', 'desc' => 'Snapshot paiements — encaissements, soldes'],
        'registrations' => ['icon' => 'ph-user-plus',         'color' => '#8b5cf6', 'desc' => 'Inscriptions étudiants'],
        'allocations'   => ['icon' => 'ph-arrows-split',      'color' => '#06b6d4', 'desc' => 'Allocations de paiement — évolution groupes'],
        'classes'       => ['icon' => 'ph-chalkboard-teacher','color' => '#64748b', 'desc' => 'Groupes, étudiants, horaires'],
        'all'           => ['icon' => 'ph-arrows-clockwise',  'color' => '#e74c3c', 'desc' => 'Tout re-synchroniser dans l\'ordre complet'],
    ];
    @endphp

    @foreach ($domains as $key => $label)
    @php $meta = $domainMeta[$key] ?? ['icon' => 'ph-cloud', 'color' => '#9ca3af', 'desc' => '']; @endphp
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 domain-card"
             data-domain="{{ $key }}"
             style="border-radius:14px;cursor:pointer;transition:box-shadow .15s"
             onmouseenter="this.style.boxShadow='0 4px 20px rgba(0,0,0,.13)'"
             onmouseleave="this.style.boxShadow=''">
            <div class="card-body d-flex align-items-start gap-3 py-3">
                <div style="width:40px;height:40px;border-radius:10px;background:{{ $meta['color'] }}18;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="ph-duotone {{ $meta['icon'] }} f-22" style="color:{{ $meta['color'] }}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-600 mb-1" style="font-size:.9rem">{{ $label }}</div>
                    <div class="text-muted" style="font-size:.76rem">{{ $meta['desc'] }}</div>
                </div>
                <button class="btn btn-sm sync-btn" data-domain="{{ $key }}"
                        style="background:{{ $meta['color'] }}18;color:{{ $meta['color'] }};border:1px solid {{ $meta['color'] }}40;white-space:nowrap;flex-shrink:0"
                        {{ $isLocked ? 'disabled' : '' }}>
                    <i class="ph-duotone ph-play me-1"></i>Sync
                </button>
            </div>
        </div>
    </div>
    @endforeach

</div>

{{-- Live progress panel --}}
<div id="resync-panel" class="card border-0 shadow-sm mb-4" style="border-radius:14px;display:none">
    <div class="card-header border-0 d-flex align-items-center gap-2 py-3" style="background:#f8f9fa;border-radius:14px 14px 0 0">
        <span id="panel-spinner" class="spinner-border spinner-border-sm text-primary" style="display:none"></span>
        <strong id="panel-title" style="font-size:.9rem">Synchronisation en cours…</strong>
        <span id="panel-badge" class="badge ms-auto" style="display:none"></span>
    </div>
    <div class="card-body p-0">
        <div id="steps-list"></div>
        <div id="panel-message" class="px-4 py-3" style="display:none;font-size:.85rem"></div>
    </div>
</div>

{{-- Audit history --}}
<div class="card border-0 shadow-sm" style="border-radius:14px">
    <div class="card-header border-0 py-3 d-flex align-items-center gap-2" style="background:#f8f9fa;border-radius:14px 14px 0 0">
        <i class="ph-duotone ph-clock-counter-clockwise text-muted f-18"></i>
        <strong style="font-size:.9rem">Historique des synchronisations</strong>
        <span class="badge bg-light text-muted ms-1">{{ $history->count() }} entrées</span>
    </div>
    <div class="card-body p-0">
        @if ($history->isEmpty())
        <div class="text-center text-muted py-5" style="font-size:.85rem">
            Aucune synchronisation manuelle enregistrée.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:.82rem">
                <thead style="background:#f8f9fa">
                    <tr>
                        <th class="px-4 py-2 text-muted fw-600">Date / Heure</th>
                        <th class="py-2 text-muted fw-600">Utilisateur</th>
                        <th class="py-2 text-muted fw-600">Domaine</th>
                        <th class="py-2 text-muted fw-600">Centre</th>
                        <th class="py-2 text-muted fw-600">Statut</th>
                        <th class="py-2 text-muted fw-600">Durée</th>
                        <th class="py-2 text-muted fw-600">Détails</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($history as $log)
                @php
                    $statusColor = match($log->status) {
                        'ok'      => '#1cc88a',
                        'partial' => '#f59e0b',
                        'locked'  => '#9ca3af',
                        default   => '#e74c3c',
                    };
                    $statusLabel = match($log->status) {
                        'ok'      => 'Succès',
                        'partial' => 'Partiel',
                        'locked'  => 'Bloqué',
                        default   => 'Erreur',
                    };
                @endphp
                <tr class="border-bottom">
                    <td class="px-4 py-2 text-nowrap">
                        {{ $log->created_at->setTimezone('Africa/Casablanca')->format('d/m/Y H:i') }}
                    </td>
                    <td class="py-2">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:26px;height:26px;border-radius:50%;background:#4680ff22;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:#4680ff;flex-shrink:0">
                                {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
                            </div>
                            <span>{{ $log->user?->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="py-2 fw-500">{{ $log->domain_label }}</td>
                    <td class="py-2 text-muted">{{ $log->crm_store_id ? 'Centre #'.$log->crm_store_id : 'Tous' }}</td>
                    <td class="py-2">
                        <span class="badge" style="background:{{ $statusColor }}22;color:{{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="py-2 text-muted text-nowrap">
                        {{ $log->duration_seconds !== null ? $log->duration_seconds.'s' : '—' }}
                    </td>
                    <td class="py-2">
                        @if (!empty($log->steps))
                        <button class="btn btn-xs btn-light border" onclick="toggleSteps(this)" data-steps="{{ htmlspecialchars(json_encode($log->steps)) }}" style="font-size:.72rem;padding:2px 8px">
                            Voir étapes
                        </button>
                        @endif
                    </td>
                </tr>
                @if (!empty($log->steps))
                <tr class="steps-row d-none border-bottom" style="background:#fafafa">
                    <td colspan="7" class="px-4 py-2">
                        <div class="steps-detail" style="font-size:.76rem">
                            @foreach ($log->steps as $step)
                            <div class="d-flex gap-2 align-items-center py-1 border-bottom">
                                <span style="color:{{ $step['status'] === 'ok' ? '#1cc88a' : '#e74c3c' }};font-weight:700;min-width:12px">
                                    {{ $step['status'] === 'ok' ? '✓' : '✗' }}
                                </span>
                                <code>{{ $step['step'] }}</code>
                                @if (!empty($step['error']))
                                <span class="text-danger ms-2">{{ $step['error'] }}</span>
                                @endif
                                <span class="text-muted ms-auto">{{ $step['elapsed'] ?? '' }}</span>
                            </div>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<script>
const resyncUrl = "{{ route('backoffice.crm.resync.run') }}";
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

document.querySelectorAll('.sync-btn').forEach(btn => {
    btn.addEventListener('click', e => { e.stopPropagation(); triggerSync(btn.dataset.domain); });
});
document.querySelectorAll('.domain-card').forEach(card => {
    card.addEventListener('click', () => triggerSync(card.dataset.domain));
});

function triggerSync(domain) {
    document.querySelectorAll('.sync-btn').forEach(b => {
        b.disabled = true;
        if (b.dataset.domain === domain) b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>En cours…';
    });

    const panel   = document.getElementById('resync-panel');
    const spinner = document.getElementById('panel-spinner');
    const title   = document.getElementById('panel-title');
    const badge   = document.getElementById('panel-badge');
    const steps   = document.getElementById('steps-list');
    const message = document.getElementById('panel-message');

    panel.style.display = '';
    spinner.style.display = '';
    badge.style.display = 'none';
    title.textContent = 'Synchronisation en cours…';
    steps.innerHTML = '<div class="px-4 py-3 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Communication avec Wimschool API…</div>';
    message.style.display = 'none';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    fetch(resyncUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ domain }),
    })
    .then(r => r.json())
    .then(data => {
        spinner.style.display = 'none';

        if (data.status === 'locked') {
            title.textContent = 'Synchronisation bloquée';
            badge.textContent = 'Verrouillé'; badge.style.cssText = 'display:inline;background:#f59e0b22;color:#f59e0b';
            steps.innerHTML = `<div class="px-4 py-2 text-warning">${data.message}</div>`;
            enableButtons(); return;
        }

        const ok = data.status === 'ok';
        title.textContent = data.label ?? domain;
        badge.textContent = ok ? 'Succès' : (data.status === 'partial' ? 'Partiel' : 'Erreur');
        badge.style.cssText = ok
            ? 'display:inline;background:#1cc88a22;color:#1cc88a'
            : (data.status === 'partial' ? 'display:inline;background:#f59e0b22;color:#f59e0b' : 'display:inline;background:#e74c3c22;color:#e74c3c');

        steps.innerHTML = (data.results ?? []).map(r => {
            const color = r.status === 'ok' ? '#1cc88a' : '#e74c3c';
            const err   = r.error ? `<div class="text-danger mt-1" style="font-size:.75rem">${r.error}</div>` : '';
            return `<div class="d-flex align-items-start gap-3 px-4 py-2 border-bottom">
                <span style="color:${color};font-weight:700;min-width:14px">${r.status === 'ok' ? '✓' : '✗'}</span>
                <div class="flex-grow-1"><code style="font-size:.78rem">${r.step}</code>${err}</div>
                <span class="text-muted ms-auto" style="font-size:.75rem;white-space:nowrap">${r.elapsed}</span>
            </div>`;
        }).join('') || '<div class="px-4 py-2 text-muted">Aucune étape exécutée.</div>';

        message.style.display = '';
        message.style.color = ok ? '#1cc88a' : (data.status === 'partial' ? '#f59e0b' : '#e74c3c');
        message.innerHTML = `<i class="ph-duotone ${ok ? 'ph-check-circle' : 'ph-warning'} me-1"></i>${data.message}`;

        enableButtons();
        // Reload so the history table shows the new entry
        setTimeout(() => window.location.reload(), 1500);
    })
    .catch(err => {
        spinner.style.display = 'none';
        title.textContent = 'Erreur de connexion';
        steps.innerHTML = `<div class="px-4 py-2 text-danger">${err.message}</div>`;
        enableButtons();
    });
}

function enableButtons() {
    document.querySelectorAll('.sync-btn').forEach(b => {
        b.disabled = false;
        b.innerHTML = '<i class="ph-duotone ph-play me-1"></i>Sync';
    });
}

function toggleSteps(btn) {
    const row = btn.closest('tr').nextElementSibling;
    if (!row || !row.classList.contains('steps-row')) return;
    row.classList.toggle('d-none');
    btn.textContent = row.classList.contains('d-none') ? 'Voir étapes' : 'Masquer';
}
</script>
@endsection
