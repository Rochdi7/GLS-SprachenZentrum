@extends('layouts.main')

@section('title', 'Créer un Groupe')
@section('breadcrumb-item', 'GLS Centres')
@section('breadcrumb-item-link', route('backoffice.groups.index'))
@section('breadcrumb-item-active', 'Nouveau Groupe')
@section('page-animation', 'animate__rollIn')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/animate.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/datepicker-bs5.min.css') }}">
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
<script src="{{ URL::asset('build/js/plugins/datepicker-full.min.js') }}"></script>

<script>
    // Date Picker Init — BS5 datepicker, single start date, end auto-calculated (+10 months)
    const dpEl = document.querySelector('#date_range_picker');
    if (dpEl) {
        const dp = new Datepicker(dpEl, {
            buttonClass: 'btn',
            format: 'yyyy-mm-dd',
            autohide: true,
            daysOfWeekDisabled: [0, 6],
            todayHighlight: true,
        });

        dpEl.addEventListener('changeDate', function (e) {
            if (e.detail && e.detail.date) {
                const d = e.detail.date;
                const pad = n => String(n).padStart(2, '0');
                const startYMD = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                if (typeof window.__syncGroupDatesFromPicker === 'function') {
                    window.__syncGroupDatesFromPicker(startYMD);
                }
            }
        });
    }

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
@endsection
