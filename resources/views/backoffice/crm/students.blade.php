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
        'categoryId'         => ['label' => 'Catégorie', 'type' => 'select', 'options' => $lovCategories, 'empty' => '— Toutes les catégories —'],
        'registrationStatus' => ['label' => 'Statut inscription'],
        'size'               => ['label' => 'Taille de page', 'type' => 'number', 'placeholder' => '20'],
    ]])

    <div class="card">
        <div class="card-body">
            @include('backoffice.crm.partials._table')
        </div>
    </div>
@endsection
