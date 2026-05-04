@extends('layouts.main')

@section('title', 'Nouvelle commande de traduction')
@section('breadcrumb-item', 'Traductions')
@section('breadcrumb-item-active', 'Nouvelle commande')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

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
            <h5 class="mb-0">Nouvelle commande de traduction</h5>
            <small class="text-muted">Saisir les informations de l'étudiant et la liste des documents.</small>
        </div>
    </div>

    @include('backoffice.translations._form', ['translation' => null])

@endsection
