@extends('layouts.main')

@section('title', 'CRM — Allocations paiement')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Allocations paiement')

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', ['fields' => [
        'studentId'      => ['label' => 'Étudiant', 'type' => 'student-autocomplete'],
        'registrationId' => ['label' => 'Inscription ID', 'type' => 'number'],
        'classId'        => ['label' => 'Groupe', 'type' => 'select', 'options' => $lovClasses, 'empty' => '— Tous les groupes —'],
        'levelSessionId' => ['label' => 'Level session', 'type' => 'select', 'options' => $lovLevelSessions, 'empty' => '— Toutes les sessions —'],
        'startDate'      => ['label' => 'Date début', 'type' => 'date'],
        'endDate'        => ['label' => 'Date fin', 'type' => 'date'],
        'schoolYearId'   => ['label' => 'Année scolaire', 'type' => 'select', 'options' => $lovSchoolYears, 'empty' => '— Toutes les années —'],
    ]])

    <div class="card"><div class="card-body">
        @include('backoffice.crm.partials._table')
    </div></div>
@endsection
