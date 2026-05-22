@extends('layouts.main')

@section('title', 'CRM — Salaires employés')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Salaires employés')

@php
    // Month / year options for the filter bar — driven by Carbon so French
    // labels match the rest of the CRM views.
    $monthOpts = collect(range(1, 12))->map(fn ($m) => [
        'id'   => $m,
        'name' => \Carbon\Carbon::create(null, $m, 1)->locale('fr')->isoFormat('MMMM') . " ({$m})",
    ])->all();

    $currentYear = (int) now()->year;
    $yearOpts = collect(range($currentYear - 3, $currentYear + 1))->map(fn ($y) => [
        'id' => $y, 'name' => (string) $y,
    ])->all();
@endphp

@section('content')
    @include('backoffice.crm.partials._center')

    @include('backoffice.crm.partials._filters', ['fields' => [
        'employeeId'     => ['label' => 'ID employé', 'type' => 'number'],
        'classId'        => ['label' => 'Classe', 'type' => 'select', 'options' => $lovClasses, 'empty' => '— Toutes —'],
        'operationMonth' => ['label' => 'Mois', 'type' => 'select', 'options' => $monthOpts, 'empty' => '— Tous les mois —'],
        'operationYear'  => ['label' => 'Année', 'type' => 'select', 'options' => $yearOpts, 'empty' => '— Toutes les années —'],
    ]])

    <div class="card"><div class="card-body">
        <div class="alert alert-info py-2 small mb-3">
            <i class="ti ti-info-circle me-1"></i>
            Période active : <strong>{{ \Carbon\Carbon::create(null, $operationMonth, 1)->locale('fr')->isoFormat('MMMM') }} {{ $operationYear }}</strong>.
            Sans filtre, le mois et l'année en cours sont utilisés (l'API exige ces deux paramètres).
        </div>
        @include('backoffice.crm.partials._table')
    </div></div>
@endsection
