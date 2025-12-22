@extends('layouts.main')

@section('title', 'Modifier un Studienkolleg')
@section('breadcrumb-item', 'Gestion GLS')
@section('breadcrumb-item-active', 'Modifier Studienkolleg')
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
            action="{{ route('backoffice.studienkollegs.update', $studienkolleg) }}"
            method="POST"
            enctype="multipart/form-data"
            class="needs-validation"
            novalidate
        >
            @csrf
            @method('PUT')

            <div id="studienkolleg-form-card"
                 class="card animate__animated animate__rollIn">

                <div class="card-header">
                    <h5>Modifier le Studienkolleg</h5>
                </div>

                <div class="card-body">
                    @include('backoffice.studienkollegs._form', ['studienkolleg' => $studienkolleg])
                </div>

                <div class="card-footer text-end">
                    <a
                        href="{{ route('backoffice.studienkollegs.index') }}"
                        class="btn btn-secondary"
                        onclick="rollOutCard(event, this, 'studienkolleg-form-card')"
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
<script>
    (function () {
        'use strict';
        window.addEventListener('load', () => {
            const forms = document.getElementsByClassName('needs-validation');
            [...forms].forEach(form => {
                form.addEventListener('submit', event => {
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
        setTimeout(() => window.location.href = link.href, 800);
    }
</script>
@endsection
