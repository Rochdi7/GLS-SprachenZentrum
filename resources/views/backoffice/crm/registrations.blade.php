@extends('layouts.main')

@section('title', 'CRM — Inscriptions')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Inscriptions')

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', ['fields' => [
        'reference'            => ['label' => 'Référence'],
        'studentId'            => ['label' => 'Étudiant', 'type' => 'student-autocomplete'],
        'registrationStatusId' => ['label' => 'Statut', 'type' => 'select', 'options' => $lovRegistrationStatus, 'empty' => '— Tous les statuts —'],
        'levelSessionId'       => ['label' => 'Level session', 'type' => 'select', 'options' => $lovLevelSessions, 'empty' => '— Toutes les sessions —'],
        'startDate'            => ['label' => 'Date début', 'type' => 'date'],
        'endDate'              => ['label' => 'Date fin', 'type' => 'date'],
        'schoolYearId'         => ['label' => 'Année scolaire', 'type' => 'select', 'options' => $lovSchoolYears, 'empty' => '— Toutes les années —'],
    ]])

    <div class="card"><div class="card-body">
        @include('backoffice.crm.partials._table')
    </div></div>
@endsection
