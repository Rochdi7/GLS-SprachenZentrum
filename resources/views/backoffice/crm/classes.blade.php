@extends('layouts.main')

@section('title', 'CRM — Classes')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Classes')

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', [
        'statusId' => $defaultStatusId,
        'fields' => [
            'schoolLevelId'      => ['label' => 'Niveau scolaire', 'type' => 'select', 'options' => $lovSchoolLevels, 'empty' => '— Tous les niveaux —'],
            'employeeTeacherId'  => ['label' => 'Enseignant', 'type' => 'select', 'options' => $lovTeachers, 'empty' => '— Tous les enseignants —'],
            'schoolStageId'      => ['label' => 'Cycle', 'type' => 'select', 'options' => $lovSchoolStages, 'empty' => '— Tous les cycles —'],
            'schoolDepartmentId' => ['label' => 'Département', 'type' => 'select', 'options' => $lovSchoolDepartments, 'empty' => '— Tous les départements —'],
            'statusId'           => ['label' => 'Statut', 'type' => 'select', 'options' => $lovClassStatuses, 'empty' => '— Tous les statuts —'],
            'history'            => ['label' => 'Historique (Y/N)', 'placeholder' => 'N'],
            'schoolYearId'       => ['label' => 'Année scolaire', 'type' => 'select', 'options' => $lovSchoolYears, 'empty' => '— Toutes les années —'],
        ]
    ])

    <div class="card"><div class="card-body">
        @include('backoffice.crm.partials._table', ['rowKind' => 'class'])
    </div></div>
@endsection
