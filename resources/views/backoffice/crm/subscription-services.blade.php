@extends('layouts.main')

@section('title', 'CRM — Services d\'abonnement')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Services d\'abonnement')

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', ['fields' => [
        'id'                          => ['label' => 'ID service', 'type' => 'number'],
        'studentId'                   => ['label' => 'Étudiant', 'type' => 'student-autocomplete'],
        'registrationId'              => ['label' => 'ID inscription', 'type' => 'number'],
        'levelSessionId'              => ['label' => 'Level session', 'type' => 'select', 'options' => $lovLevelSessions, 'empty' => '— Toutes —'],
        'levelSessionPackageId'       => ['label' => 'Package', 'type' => 'select', 'options' => $lovPackages, 'empty' => '— Tous —'],
        'subscriptionServiceStatusId' => ['label' => 'ID statut service', 'type' => 'number'],
        'dueDate'                     => ['label' => 'Échéance ≤', 'type' => 'date'],
        'schoolYearId'                => ['label' => 'Année scolaire', 'type' => 'select', 'options' => $lovSchoolYears, 'empty' => '— Toutes les années —'],
    ]])

    <div class="card"><div class="card-body">
        @include('backoffice.crm.partials._table')
    </div></div>
@endsection
