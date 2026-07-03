@extends('layouts.main')

@section('title', 'Paiements du ' . $date->format('d/m/Y'))
@section('breadcrumb-item', 'Paiement Professeurs CRM')
@section('breadcrumb-item-link', route('backoffice.payroll.crm.legacy.dashboard'))
@section('breadcrumb-item-active', $date->translatedFormat('d F Y'))

@section('content')

    @php
        $statusMeta = [
            'draft'     => ['Brouillon', 'secondary'],
            'validated' => ['Validé', 'info'],
            'paid'      => ['Payé', 'success'],
            'locked'    => ['Verrouillé', 'dark'],
        ];
        $dayTotal = $imports->sum(fn ($i) => (float) ($i->paymentSummary?->total_payment ?? $i->final_total ?? 0));
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="ph-duotone ph-calendar-check text-primary me-2"></i>
                {{ $date->translatedFormat('l d F Y') }}
            </h4>
            <small class="text-muted">{{ $imports->count() }} paiement(s) enregistré(s) ce jour — {{ number_format($dayTotal, 0, ',', ' ') }} DH au total</small>
        </div>
        <a href="{{ route('backoffice.payroll.crm.legacy.dashboard') }}" class="btn btn-outline-secondary">
            <i class="ph-duotone ph-arrow-left me-1"></i> Retour au calendrier
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body pt-2">
            @if($imports->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="ph-duotone ph-folder-open fs-1 d-block mb-2"></i>
                    Aucun paiement enregistré ce jour.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Heure</th>
                                <th>Groupe</th>
                                <th>Professeur</th>
                                <th>Version</th>
                                <th>Statut</th>
                                <th class="text-end">Montant</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($imports as $import)
                                @php [$label, $color] = $statusMeta[$import->status] ?? ['—', 'secondary']; @endphp
                                <tr>
                                    <td>{{ $import->created_at->format('H:i') }}</td>
                                    <td><strong>{{ $import->group?->name ?? '—' }}</strong></td>
                                    <td>{{ $import->crm_teacher_name ?? $import->group?->teacher?->name ?? '—' }}</td>
                                    <td><span class="badge bg-light-secondary text-secondary">v{{ $import->version }}</span></td>
                                    <td><span class="badge bg-{{ $color }}">{{ $label }}</span></td>
                                    <td class="text-end fw-bold">
                                        {{ number_format((float) ($import->paymentSummary?->total_payment ?? $import->final_total ?? 0), 2) }} DH
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('backoffice.payroll.crm.legacy.import.show', ['group' => $import->group_id, 'import' => $import->id]) }}"
                                           class="btn btn-sm btn-outline-primary" title="Voir le détail">
                                            <i class="ph-duotone ph-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection
