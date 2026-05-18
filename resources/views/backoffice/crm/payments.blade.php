@extends('layouts.main')

@section('title', 'CRM — Paiements')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Paiements')

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', ['fields' => [
        'reference'        => ['label' => 'Référence'],
        'studentId'        => ['label' => 'Student ID', 'type' => 'number'],
        'paymentTypeId'    => ['label' => 'Type ID', 'type' => 'number'],
        'paymentStatusId'  => ['label' => 'Status ID', 'type' => 'number'],
        'paymentMethodeId' => ['label' => 'Méthode ID', 'type' => 'number'],
        'startDate'        => ['label' => 'Start date', 'type' => 'date'],
        'endDate'          => ['label' => 'End date', 'type' => 'date'],
        'schoolYearId'     => ['label' => 'School Year ID', 'type' => 'number'],
        'strStoreId'       => ['label' => 'Store ID', 'type' => 'number'],
    ]])

    <div class="card"><div class="card-body">
        @include('backoffice.crm.partials._table')
    </div></div>
@endsection
