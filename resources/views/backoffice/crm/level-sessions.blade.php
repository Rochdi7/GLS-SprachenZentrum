@extends('layouts.main')

@section('title', 'CRM — Level sessions')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Level sessions')

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', ['fields' => [
        'schoolYearId' => ['label' => 'School Year ID', 'type' => 'number'],
        'strStoreId'   => ['label' => 'Store ID', 'type' => 'number'],
    ]])

    <div class="card"><div class="card-body">
        @include('backoffice.crm.partials._table')
    </div></div>
@endsection
