@extends('layouts.main')

@section('title', 'CRM — Classes')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Classes')

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', ['fields' => [
        'employeeTeacherId'  => ['label' => 'Teacher ID', 'type' => 'number'],
        'schoolLevelId'      => ['label' => 'School Level ID', 'type' => 'number'],
        'schoolStageId'      => ['label' => 'School Stage ID', 'type' => 'number'],
        'schoolDepartmentId' => ['label' => 'School Department ID', 'type' => 'number'],
        'statusId'           => ['label' => 'Status ID', 'type' => 'number'],
        'history'            => ['label' => 'History (Y/N)', 'placeholder' => 'N'],
        'schoolYearId'       => ['label' => 'School Year ID', 'type' => 'number'],
        'strStoreId'         => ['label' => 'Store ID (override)', 'type' => 'number'],
    ]])

    <div class="card"><div class="card-body">
        @include('backoffice.crm.partials._table', ['rowKind' => 'class'])
    </div></div>
@endsection
