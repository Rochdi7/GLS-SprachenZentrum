@extends('layouts.main')

@section('title', 'Créer un Site')
@section('breadcrumb-item', 'GLS Centres')
@section('breadcrumb-item-active', 'Nouveau Site')
@section('page-animation', 'animate__rollIn')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/animate.min.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">

        {{-- Errors --}}
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
            action="{{ route('backoffice.sites.store') }}" 
            method="POST"
            enctype="multipart/form-data"
            class="needs-validation"
            novalidate
        >
            @csrf

            <div id="site-form-card" class="card animate__animated animate__rollIn">

                <div class="card-header">
                    <h5>Ajouter un nouveau site GLS</h5>
                </div>

                <div class="card-body">
                    @include('backoffice.sites._form')
                </div>

                <div class="card-footer text-end">
                    <a 
                        href="{{ route('backoffice.sites.index') }}" 
                        class="btn btn-secondary"
                        onclick="rollOutCard(event, this, 'site-form-card')"
                    >
                        Annuler
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Enregistrer le site
                    </button>
                </div>

            </div>

        </form>

    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('build/js/plugins/ckeditor/classic/ckeditor.js') }}"></script>

<script>
    ClassicEditor.create(document.querySelector('#about-editor')).catch(console.error);
    ClassicEditor.create(document.querySelector('#offer-editor')).catch(console.error);

    // Validation
    (function () {
        'use strict';
        window.addEventListener('load', function () {
            const forms = document.getElementsByClassName('needs-validation');
            [...forms].forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
            });
        });
    })();

    function rollOutCard(event, link, cardId) {
        event.preventDefault();
        const card = document.getElementById(cardId);

        card.classList.remove('animate__rollIn');
        card.classList.add('animate__animated', 'animate__rollOut');

        setTimeout(() => {
            window.location.href = link.href;
        }, 800);
    }
</script>
@endsection
