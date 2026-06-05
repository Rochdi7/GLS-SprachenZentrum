@extends('layouts.main')

@section('title', 'CRM (Wimschool API)')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Accueil')

@section('content')
    @include('backoffice.crm.partials._center')

    {{-- Status banner --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">
                        <i class="ti ti-cloud-data-connection text-primary me-1"></i>
                        Wimschool External API v1
                    </h5>
                    <p class="text-muted small mb-0">
                        Lecture seule. Données interrogées en direct depuis
                        <code>{{ config('crm.base_url') }}</code>
                    </p>
                </div>
                <div>
                    @if(empty(config('crm.token')))
                        <span class="badge bg-light-danger text-danger">
                            <i class="ti ti-alert-triangle me-1"></i> Token absent
                        </span>
                    @else
                        <span class="badge bg-light-success text-success">
                            <i class="ti ti-check me-1"></i> Token OK
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @php
        // [label, route, icon, scope, description]
        $cards = [
            ['Statistiques',          'backoffice.crm.stats',                'ti-chart-bar',       'all',                   'Évolution KPI par centre + tendances'],
            ['Doublons',              'backoffice.crm.duplicates',           'ti-copy-check',      'students:read',         'Détecter les fiches dupliquées'],
            ['Étudiants',             'backoffice.crm.students',             'ti-users',           'students:read',         'Fiches étudiant + filtres avancés'],
            ['Présences sessions',    'backoffice.crm.session-presence',     'ti-checklist',       'session-presence:read', 'Suivi des absences par séance'],
            ['Inscriptions',          'backoffice.crm.registrations',        'ti-clipboard-list',  'registrations:read',    'Inscriptions actives & historiques'],
            ['Paiements',             'backoffice.crm.payments',             'ti-cash',            'payments:read',         'Encaissements et reçus'],
            ['Chèques',               'backoffice.crm.payment-checks',       'ti-receipt',         'payment-checks:read',   'Suivi des chèques en attente'],
            ['Allocations paiement',  'backoffice.crm.payment-allocations',  'ti-arrows-split',    'payments:read',         'Répartition des paiements'],
            ['Recouvrement',          'backoffice.crm.payment-collection',   'ti-report-money',    'payments:read',         'Créances et échéances en attente'],
            ['Classes',               'backoffice.crm.groups.classes',       'ti-school',          'groups:read',           'Groupes / classes en cours'],
            ['Level sessions',        'backoffice.crm.groups.level-sessions','ti-calendar-event',  'groups:read',           'Sessions par niveau'],
        ];

        $lov = [
            ['banks',                              'Banques',                       'ti-building-bank'],
            ['categories',                         'Catégories',                    'ti-category'],
            ['school-levels',                      'Niveaux',                       'ti-stairs-up'],
            ['level-session-packages',             'Packages',                      'ti-package'],
            ['payment-types',                      'Types de paiement',             'ti-coin'],
            ['payment-methods',                    'Méthodes',                      'ti-credit-card'],
            ['payment-statuses',                   'Statuts paiement',              'ti-circle-check'],
            ['payment-check-statuses',             'Statuts chèques',               'ti-checks'],
            ['registration-statuses',              'Statuts inscription',           'ti-list-check'],
            ['registration-conventions',           'Conventions',                   'ti-file-text'],
            ['registration-change-status-reasons', 'Raisons changement',            'ti-message-circle'],
        ];
    @endphp

    {{-- Main endpoints --}}
    <h6 class="text-muted text-uppercase small mb-2 mt-3">
        <i class="ti ti-database me-1"></i> Données transactionnelles
    </h6>
    <div class="row g-3 mb-4">
        @foreach($cards as [$label, $route, $icon, $scope, $desc])
            <div class="col-12 col-sm-6 col-lg-3">
                <a href="{{ route($route) }}" class="card text-decoration-none h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <span class="avtar avtar-l bg-light-primary rounded">
                                <i class="ti {{ $icon }} f-24 text-primary"></i>
                            </span>
                            <i class="ti ti-arrow-up-right text-muted"></i>
                        </div>
                        <h6 class="mb-1">{{ $label }}</h6>
                        <p class="small text-muted mb-2">{{ $desc }}</p>
                        <span class="badge bg-light text-muted small">{{ $scope }}</span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- LOV section --}}
    <h6 class="text-muted text-uppercase small mb-2">
        <i class="ti ti-list me-1"></i> Listes de valeurs (référentiels)
    </h6>
    <div class="card">
        <div class="card-body">
            <div class="row g-2">
                @foreach($lov as [$kind, $label, $icon])
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <a href="{{ route('backoffice.crm.lov', ['kind' => $kind]) }}"
                           class="d-flex align-items-center gap-2 p-2 rounded text-decoration-none text-body lov-chip">
                            <span class="avtar avtar-xs bg-light-secondary rounded">
                                <i class="ti {{ $icon }}"></i>
                            </span>
                            <span class="small flex-grow-1">{{ $label }}</span>
                            <i class="ti ti-chevron-right text-muted small"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice/crm-index.css') }}">
@endsection
