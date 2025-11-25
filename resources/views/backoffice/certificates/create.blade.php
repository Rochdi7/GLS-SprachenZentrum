@extends('layouts.main')

@section('title', 'Ajouter un Certificat')
@section('breadcrumb-item', 'Examens')
@section('breadcrumb-item-active', 'Nouveau Certificat')
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
            action="{{ route('backoffice.certificates.store') }}" 
            method="POST"
            class="needs-validation"
            novalidate
        >
            @csrf

            <div id="certificate-form-card" class="card animate__animated animate__rollIn">

                <div class="card-header">
                    <h5>Créer un Certificat</h5>
                </div>

                <div class="card-body">
                    {{-- Form content --}}
                    @include('backoffice.certificates._form')
                </div>

                <div class="card-footer text-end">
                    <a 
                        href="{{ route('backoffice.certificates.index') }}" 
                        class="btn btn-secondary"
                        onclick="rollOutCard(event, this, 'certificate-form-card')"
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
<script>
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

    function rollOutCard(event, link, cardId = 'certificate-form-card') {
        event.preventDefault();
        const card = document.getElementById(cardId);

        card.classList.remove('animate__rollIn');
        card.classList.add('animate__animated', 'animate__rollOut');

        setTimeout(() => window.location.href = link.href, 800);
    }
</script>
@endsection
