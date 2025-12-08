@extends('layouts.main')

@section('title', 'Créer un Groupe')
@section('breadcrumb-item', 'GLS Centres')
@section('breadcrumb-item-active', 'Nouveau Groupe')
@section('page-animation', 'animate__rollIn')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/animate.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/flatpickr.min.css') }}">
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

                    {{-- Include the form fields --}}
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
<script src="{{ URL::asset('build/js/plugins/flatpickr.min.js') }}"></script>

<script>
    // CKEditor Init
    ClassicEditor
        .create(document.querySelector('#group-description'))
        .catch(error => console.error(error));

    // Date Range Picker Init
    flatpickr("#date_range_picker", {
    mode: "range",
    dateFormat: "Y-m-d",

    // Style weekends visually
    onDayCreate: function(dObj, dStr, fp, dayElem) {
        const date = dayElem.dateObj;
        const day = date.getDay();

        if (day === 0 || day === 6) {
            dayElem.classList.add("flatpickr-disabled-day"); 
        }
    },

    // Prevent selecting weekend as start or end
    onChange: function(selectedDates, dateStr, instance) {
        if (selectedDates.length === 1) {
            const day = selectedDates[0].getDay();

            if (day === 0 || day === 6) {
                alert("Vous ne pouvez pas choisir un weekend comme date de début.");
                instance.clear();
                return;
            }
        }

        if (selectedDates.length === 2) {
            const start = selectedDates[0];
            const end = selectedDates[1];

            // Prevent weekend END date
            if (end.getDay() === 0 || end.getDay() === 6) {
                alert("Vous ne pouvez pas choisir un weekend comme date de fin.");
                instance.setDate([start]); // reset end
                return;
            }

            // Set hidden inputs
            document.getElementById('date_debut_value').value = start.toISOString().split('T')[0];
            document.getElementById('date_fin_value').value = end.toISOString().split('T')[0];
        }
    },
});


    // Validation
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

    // RollOut Animation Cancel
    function rollOutCard(event, link, cardId = 'group-form-card') {
        event.preventDefault();
        const card = document.getElementById(cardId);

        card.classList.remove('animate__rollIn');
        card.classList.add('animate__animated', 'animate__rollOut');

        setTimeout(() => window.location.href = link.href, 800);
    }
</script>
<style>
    .flatpickr-disabled-day {
    background: #f5d7d7 !important;
    color: #a33 !important;
    border-radius: 6px;
}

</style>
@endsection
