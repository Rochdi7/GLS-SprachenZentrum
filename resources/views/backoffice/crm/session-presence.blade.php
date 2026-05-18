@extends('layouts.main')

@section('title', 'CRM — Présences')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Présences sessions')

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', ['fields' => [
        'date'         => ['label' => 'Date', 'type' => 'date'],
        'presence'     => ['label' => 'Présence (Y)'],
        'absence'      => ['label' => 'Absence (Y)'],
        'classId'      => ['label' => 'Class ID', 'type' => 'number'],
        'studentId'    => ['label' => 'Student ID', 'type' => 'number'],
        'schoolYearId' => ['label' => 'School Year ID', 'type' => 'number'],
        'strStoreId'   => ['label' => 'Store ID', 'type' => 'number'],
        'size'         => ['label' => 'Page size', 'type' => 'number', 'placeholder' => '20'],
    ]])

    <div class="card"><div class="card-body">
        @include('backoffice.crm.partials._table')
    </div></div>
@endsection
