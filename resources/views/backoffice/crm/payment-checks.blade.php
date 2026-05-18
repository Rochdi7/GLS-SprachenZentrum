@extends('layouts.main')

@section('title', 'CRM — Chèques')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Chèques')

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', ['fields' => [
        'checkNumber'          => ['label' => 'N° chèque'],
        'studentId'            => ['label' => 'Student ID', 'type' => 'number'],
        'paymentCheckStatusId' => ['label' => 'Status ID', 'type' => 'number'],
        'bankId'               => ['label' => 'Bank ID', 'type' => 'number'],
        'dueDateMin'           => ['label' => 'Due date (min)', 'type' => 'date'],
        'dueDateMax'           => ['label' => 'Due date (max)', 'type' => 'date'],
        'schoolYearId'         => ['label' => 'School Year ID', 'type' => 'number'],
        'strStoreId'           => ['label' => 'Store ID', 'type' => 'number'],
    ]])

    <div class="card"><div class="card-body">
        @include('backoffice.crm.partials._table')
    </div></div>
@endsection
