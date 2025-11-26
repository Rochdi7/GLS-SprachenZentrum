@extends('layouts.main')

@section('title', 'Créer un Groupe')
@section('breadcrumb-item', 'GLS Centres')
@section('breadcrumb-item-active', 'Nouveau Groupe')
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
            action="{{ route('backoffice.groups.store') }}" 
            method="POST"
            enctype="multipart/form-data"
            class="needs-validation"
            novalidate
        >
            @csrf

            <div id="group-form-card" class="card animate__animated animate__rollIn">

                <div class="card-header">
                    <h5>Créer un nouveau groupe</h5>
                </div>

                <div class="card-body">
                    @include('backoffice.groups._form')
                </div>

                <div class="card-footer text-end">
                    <a 
                        href="{{ route('backoffice.groups.index') }}" 
                        class="btn btn-secondary"
                        onclick="rollOutCard(event, this, 'group-form-card')"
                    >
                        Annuler
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Enregistrer le groupe
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
    // CKEditor Description
    ClassicEditor
        .create(document.querySelector('#group-description'))
        .catch(error => console.error(error));

    // Validation
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

    // RollOut Animation Cancel
    function rollOutCard(event, link, cardId = 'group-form-card') {
        event.preventDefault();
        const card = document.getElementById(cardId);

        card.classList.remove('animate__rollIn');
        card.classList.add('animate__animated', 'animate__rollOut');

        setTimeout(() => window.location.href = link.href, 800);
    }
</script>
@endsection
