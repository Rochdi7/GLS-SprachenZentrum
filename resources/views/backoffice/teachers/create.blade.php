@extends('layouts.main')

@section('title', 'Ajouter un Enseignant')
@section('breadcrumb-item', 'GLS Centres')
@section('breadcrumb-item-active', 'Nouvel Enseignant')
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
            action="{{ route('backoffice.teachers.store') }}" 
            method="POST"
            enctype="multipart/form-data"
            class="needs-validation"
            novalidate
        >
            @csrf

            <div id="teacher-form-card" class="card animate__animated animate__rollIn">

                <!-- HEADER -->
                <div class="card-header">
                    <h5>Ajouter un nouvel enseignant</h5>
                </div>

                <!-- BODY -->
                <div class="card-body">
                    @include('backoffice.teachers._form')
                </div>

                <!-- FOOTER -->
                <div class="card-footer text-end">
                    <a 
                        href="{{ route('backoffice.teachers.index') }}" 
                        class="btn btn-secondary"
                        onclick="rollOutCard(event, this, 'teacher-form-card')"
                    >
                        Annuler
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Enregistrer
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
    // CKEditor for BIO
    ClassicEditor
        .create(document.querySelector('#bio-editor'))
        .catch(error => console.error(error));

    // Bootstrap Validation
    (function () {
        'use strict';
        window.addEventListener('load', function () {
            const forms = document.getElementsByClassName('needs-validation');
            [...forms].forEach(form => {
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

    // Roll-Out animation when clicking Cancel
    function rollOutCard(event, link, cardId = 'teacher-form-card') {
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
