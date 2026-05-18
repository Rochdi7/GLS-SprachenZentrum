@extends('layouts.main')

@section('title', 'CRM — Étudiants')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Étudiants')

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', ['fields' => [
        'reference'          => ['label' => 'Référence'],
        'firstName'          => ['label' => 'Prénom'],
        'lastName'           => ['label' => 'Nom'],
        'phoneNumber'        => ['label' => 'Téléphone'],
        'categoryId'         => ['label' => 'Category ID', 'type' => 'number'],
        'registrationStatus' => ['label' => 'Statut inscription'],
        'strStoreId'         => ['label' => 'Store ID', 'type' => 'number'],
        'size'               => ['label' => 'Page size', 'type' => 'number', 'placeholder' => '20'],
    ]])

    <div class="card">
        <div class="card-body">
            @include('backoffice.crm.partials._table')
        </div>
    </div>
@endsection
