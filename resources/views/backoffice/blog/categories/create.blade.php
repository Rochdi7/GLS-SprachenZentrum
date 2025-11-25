@extends('layouts.main')

@section('title', 'Créer une Catégorie')
@section('breadcrumb-item', 'Blog')
@section('breadcrumb-item-active', 'Nouvelle Catégorie')
@section('page-animation', 'animate__rollIn')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/animate.min.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">

        {{-- Global Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger animate__animated animate__shakeX">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form 
            action="{{ route('backoffice.blog.categories.store') }}" 
            method="POST" 
            class="needs-validation" 
            novalidate
        >
            @csrf

            <div id="category-form-card" class="card animate__animated animate__rollIn">
                
                <!-- Header -->
                <div class="card-header">
                    <h5>Créer une nouvelle catégorie</h5>
                </div>

                <!-- Body -->
                <div class="card-body">
                    @include('backoffice.blog.categories._form')
                </div>

                <!-- Footer -->
                <div class="card-footer text-end">
                    <a 
                        href="{{ route('backoffice.blog.categories.index') }}" 
                        class="btn btn-secondary"
                        onclick="rollOutCard(event, this, 'category-form-card')"
                    >
                        Annuler
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Créer la catégorie
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
    // Bootstrap Form Validation
    (function () {
        'use strict';
        window.addEventListener('load', function () {
            const forms = document.getElementsByClassName('needs-validation');
            Array.prototype.forEach.call(forms, function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    // RollOut animation for cancel button
    function rollOutCard(event, link, cardId = 'category-form-card') {
        event.preventDefault();
        const card = document.getElementById(cardId);
        if (!card) return;

        card.classList.remove('animate__rollIn');
        card.classList.add('animate__animated', 'animate__rollOut');

        setTimeout(() => {
            window.location.href = link.href;
        }, 800);
    }
</script>
@endsection
