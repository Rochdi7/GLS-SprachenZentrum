@extends('layouts.main')

@section('title', 'Détail risque churn — ' . ($score->student_name ?? 'Étudiant'))
@section('breadcrumb-item', 'CRM — Churn')
@section('breadcrumb-item-active', $score->student_name ?? "Étudiant #{$score->crm_student_id}")

@section('content')

    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Header card --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-1">{{ $score->student_name ?? "Étudiant #{$score->crm_student_id}" }}</h4>
                            <small class="text-muted">
                                ID CRM: {{ $score->crm_student_id }}
                                @if($score->registration_id)
                                    &nbsp;·&nbsp; Inscription #{{ $score->registration_id }}
                                @endif
                                @if($score->computed_at)
                                    &nbsp;·&nbsp; Calculé le {{ $score->computed_at->format('d/m/Y à H:i') }}
                                @endif
                            </small>
                        </div>
                        <a href="{{ route('backoffice.crm.churn.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </div>
            </div>

            {{-- Score gauge --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Score de risque</h5>
                </div>
                <div class="card-body">
                    @php
                        $progressClass = match($score->risk_level) {
                            'critical' => 'bg-danger',
                            'high'     => 'bg-warning',
                            'medium'   => 'bg-info',
                            default    => 'bg-success',
                        };
                        $levelLabel = match($score->risk_level) {
                            'critical' => 'Critique',
                            'high'     => 'Élevé',
                            'medium'   => 'Modéré',
                            default    => 'Faible',
                        };
                        $badgeClass = match($score->risk_level) {
                            'critical' => 'bg-danger',
                            'high'     => 'bg-warning text-dark',
                            'medium'   => 'bg-info text-dark',
                            default    => 'bg-success',
                        };
                    @endphp

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fs-5 fw-bold">{{ $score->score }} / 100</span>
                        <span class="badge {{ $badgeClass }} fs-6">{{ $levelLabel }}</span>
                    </div>

                    <div class="progress" style="height: 24px;">
                        <div class="progress-bar {{ $progressClass }}" role="progressbar"
                             style="width: {{ $score->score }}%"
                             aria-valuenow="{{ $score->score }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $score->score }}%
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-success">Faible (0–30)</small>
                        <small class="text-info">Modéré (31–55)</small>
                        <small class="text-warning">Élevé (56–75)</small>
                        <small class="text-danger">Critique (76–100)</small>
                    </div>
                </div>
            </div>

            {{-- Signals breakdown --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Signaux détectés</h5>
                </div>
                <div class="card-body">
                    @php
                        $signals = $score->signals ?? [];
                    @endphp
                    @if(empty($signals))
                        <div class="text-center text-muted py-4">
                            <i class="ph-duotone ph-check-circle fs-3 d-block mb-2 text-success"></i>
                            Aucun signal de risque détecté pour cet étudiant.
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($signals as $signal)
                                @php
                                    // Determine icon and color based on keyword in signal
                                    $icon  = 'ph-warning';
                                    $color = 'text-warning';
                                    if (str_contains($signal, 'absence') || str_contains($signal, 'Absence')) {
                                        $icon = 'ph-user-minus'; $color = 'text-danger';
                                    } elseif (str_contains($signal, 'impayé') || str_contains($signal, 'Impayé')) {
                                        $icon = 'ph-money'; $color = 'text-danger';
                                    } elseif (str_contains($signal, 'expire') || str_contains($signal, 'Inscription')) {
                                        $icon = 'ph-clock'; $color = 'text-warning';
                                    } elseif (str_contains($signal, 'suspendu') || str_contains($signal, 'annulé')) {
                                        $icon = 'ph-prohibit'; $color = 'text-danger';
                                    } elseif (str_contains($signal, 'baisse') || str_contains($signal, 'Assiduité')) {
                                        $icon = 'ph-trend-down'; $color = 'text-warning';
                                    } elseif (str_contains($signal, 'retard') || str_contains($signal, 'paiements')) {
                                        $icon = 'ph-calendar-x'; $color = 'text-warning';
                                    }
                                @endphp
                                <div class="col-12">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body py-2 px-3 d-flex align-items-center gap-3">
                                            <i class="ph-duotone {{ $icon }} fs-4 {{ $color }}"></i>
                                            <span>{{ $signal }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

@endsection
