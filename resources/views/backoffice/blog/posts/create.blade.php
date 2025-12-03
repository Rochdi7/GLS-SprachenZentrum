@extends('layouts.main')

@section('title', 'Créer un Article')
@section('breadcrumb-item', 'Blog')
@section('breadcrumb-item-active', 'Nouvel Article')
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
            action="{{ route('backoffice.blog.posts.store') }}" 
            method="POST"
            enctype="multipart/form-data"
            class="needs-validation"
            novalidate
        >
            @csrf

            <div id="post-form-card" class="card animate__animated animate__rollIn">

                <div class="card-header">
                    <h5>Créer un nouvel article</h5>
                </div>

                <div class="card-body">
                    @include('backoffice.blog.posts._form')
                </div>

                <div class="card-footer text-end">
                    <a 
                        href="{{ route('backoffice.blog.posts.index') }}" 
                        class="btn btn-secondary"
                        onclick="rollOutCard(event, this, 'post-form-card')"
                    >
                        Annuler
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Publier l’article
                    </button>
                </div>

            </div>

        </form>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="{{ asset('build/js/plugins/ckeditor/classic/ckeditor.js') }}"></script>

<script>
    // ===============================
    // CKEditor – FR + EN
    // ===============================
    ClassicEditor
        .create(document.querySelector('#editor-fr'))
        .catch(error => console.error(error));

    ClassicEditor
        .create(document.querySelector('#editor-en'))
        .catch(error => console.error(error));


    // ===============================
    // Choices.js – Fix select category
    // ===============================
    document.addEventListener("DOMContentLoaded", function () {
        const element = document.getElementById('blog_category_id');

        if (element) {
            new Choices(element, {
                searchPlaceholderValue: 'Rechercher...',
                itemSelectText: 'Sélectionner',
                shouldSort: false,
            });

            // HARD FIX : keep correct name="category_id"
            element.setAttribute('name', 'category_id');
        }
    });


    // ===============================
    // Form validation
    // ===============================
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


    // ===============================
    // Cancel animation (rollOut)
    // ===============================
    function rollOutCard(event, link, cardId = 'post-form-card') {
        event.preventDefault();

        const card = document.getElementById(cardId);
        if (!card) return window.location.href = link.href;

        card.classList.remove('animate__rollIn');
        card.classList.add('animate__animated', 'animate__rollOut');

        setTimeout(() => {
            window.location.href = link.href;
        }, 800);
    }
</script>

@endsection
