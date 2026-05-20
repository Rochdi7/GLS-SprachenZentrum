@extends('layouts.main')

@section('title', 'CRM — Chèques')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Chèques')

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', ['fields' => [
        'checkNumber'          => ['label' => 'N° chèque'],
        'studentId'            => ['label' => 'Étudiant', 'type' => 'student-autocomplete'],
        'paymentCheckStatusId' => ['label' => 'Statut chèque', 'type' => 'select', 'options' => $lovPaymentCheckStatuses, 'empty' => '— Tous les statuts —'],
        'bankId'               => ['label' => 'Banque', 'type' => 'select', 'options' => $lovBanks, 'empty' => '— Toutes les banques —'],
        'dueDateMin'           => ['label' => 'Échéance min', 'type' => 'date'],
        'dueDateMax'           => ['label' => 'Échéance max', 'type' => 'date'],
        'schoolYearId'         => ['label' => 'Année scolaire', 'type' => 'select', 'options' => $lovSchoolYears, 'empty' => '— Toutes les années —'],
    ]])

    <div class="card"><div class="card-body">
        @include('backoffice.crm.partials._table')
    </div></div>
@endsection
