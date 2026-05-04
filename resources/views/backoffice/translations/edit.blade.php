@extends('layouts.main')

@section('title', 'Modifier la commande de traduction')
@section('breadcrumb-item', 'Traductions')
@section('breadcrumb-item-active', 'Modifier')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@php
    $defaultPrice = 0;
@endphp

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0">Modifier la commande #{{ $translation->id }}</h5>
            <small class="text-muted">{{ $translation->student_name }} — CIN : <code>{{ $translation->cin }}</code></small>
        </div>
    </div>

    @include('backoffice.translations._form')

@endsection
