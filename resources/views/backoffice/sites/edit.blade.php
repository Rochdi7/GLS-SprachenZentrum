@extends('layouts.main')

@section('title', 'Modifier un Site')
@section('breadcrumb-item', 'GLS Centres')
@section('breadcrumb-item-active', 'Modifier le Site')
@section('page-animation', 'animate__rollIn')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/animate.min.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">

        {{-- Global errors --}}
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
            action="{{ route('backoffice.sites.update', $site->id) }}" 
            method="POST"
            enctype="multipart/form-data"
            class="needs-validation"
            novalidate
        >
            @csrf
            @method('PUT')

            <div id="site-form-card" class="card animate__animated animate__rollIn">

                <!-- HEADER -->
                <div class="card-header">
                    <h5>Modifier le site GLS</h5>
                </div>

                <!-- BODY -->
                <div class="card-body">
                    @include('backoffice.sites._form')
                </div>

                <!-- FOOTER -->
                <div class="card-footer text-end">
                    <a 
                        href="{{ route('backoffice.sites.index') }}" 
                        class="btn btn-secondary"
                        onclick="rollOutCard(event, this, 'site-form-card')"
                    >
                        Annuler
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Mettre à jour
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
    // Initialize CKEditor
    ClassicEditor.create(document.querySelector('#about-editor')).catch(error => console.error(error));
    ClassicEditor.create(document.querySelector('#offer-editor')).catch(error => console.error(error));

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
    function rollOutCard(event, link, cardId = 'site-form-card') {
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
