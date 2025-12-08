@extends('layouts.main')

@section('title', 'Modifier un Groupe')
@section('breadcrumb-item', 'GLS Centres')
@section('breadcrumb-item-active', 'Modifier Groupe')
@section('page-animation', 'animate__rollIn')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/animate.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/flatpickr.min.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">

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
            action="{{ route('backoffice.groups.update', $group->id) }}" 
            method="POST"
            enctype="multipart/form-data"
            class="needs-validation"
            novalidate
        >
            @csrf
            @method('PUT')

            <div id="group-form-card" class="card animate__animated animate__rollIn">

                <div class="card-header">
                    <h5>Modifier le groupe</h5>
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
<script src="{{ URL::asset('build/js/plugins/flatpickr.min.js') }}"></script>

<script>
    ClassicEditor
        .create(document.querySelector('#group-description'))
        .catch(error => console.error(error));

    // Date Range Picker Init for Edit
    flatpickr("#date_range_picker", {
        mode: "range",
        dateFormat: "Y-m-d",

        disable: [
            date => (date.getDay() === 0 || date.getDay() === 6)
        ],

        defaultDate: [
            "{{ old('date_debut', $group->date_debut) }}",
            "{{ old('date_fin', $group->date_fin) }}"
        ].filter(Boolean),

        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                let start = selectedDates[0].toISOString().split('T')[0];
                let end = selectedDates[1].toISOString().split('T')[0];
                document.getElementById('date_debut_value').value = start;
                document.getElementById('date_fin_value').value = end;
            }
        }
    });

    // Validation
    (() => {
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

    // Cancel Animation
    function rollOutCard(event, link, cardId = 'group-form-card') {
        event.preventDefault();
        const card = document.getElementById(cardId);

        card.classList.remove('animate__rollIn');
        card.classList.add('animate__animated', 'animate__rollOut');

        setTimeout(() => window.location.href = link.href, 800);
    }
</script>
@endsection
