@extends('layouts.main')

@section('title', 'CRM — Paiements')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Paiements')

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', ['fields' => [
        'reference'        => ['label' => 'Référence'],
        'studentId'        => ['label' => 'Étudiant', 'type' => 'student-autocomplete'],
        'paymentTypeId'    => ['label' => 'Type', 'type' => 'select', 'options' => $lovPaymentTypes, 'empty' => '— Tous les types —'],
        'paymentStatusId'  => ['label' => 'Statut', 'type' => 'select', 'options' => $lovPaymentStatuses, 'empty' => '— Tous les statuts —'],
        'paymentMethodeId' => ['label' => 'Méthode', 'type' => 'select', 'options' => $lovPaymentMethods, 'empty' => '— Toutes les méthodes —'],
        'startDate'        => ['label' => 'Date début', 'type' => 'date'],
        'endDate'          => ['label' => 'Date fin', 'type' => 'date'],
        'schoolYearId'     => ['label' => 'Année scolaire', 'type' => 'select', 'options' => $lovSchoolYears, 'empty' => '— Toutes les années —'],
    ]])

    @if(isset($snapshotTotal) && $snapshotTotal !== null)
    <div class="alert alert-primary d-flex align-items-center gap-2 mb-3 py-2">
        <i class="ti ti-cash fs-5"></i>
        <span>
            <strong>Montant total (Réglement) :</strong>
            {{ number_format($snapshotTotal, 0, ',', ' ') }} DH
        </span>
        <small class="text-muted ms-2">— Réglement uniquement, basé sur date de saisie</small>
    </div>
    @endif

    <div class="card"><div class="card-body">
        @include('backoffice.crm.partials._table')
    </div></div>
@endsection
