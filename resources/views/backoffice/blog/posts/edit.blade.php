@extends('layouts.main')

@section('title', 'Modifier un Article')
@section('breadcrumb-item', 'Blog')
@section('breadcrumb-item-active', 'Modifier l’Article')
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
            action="{{ route('backoffice.blog.posts.update', $post->id) }}" 
            method="POST"
            enctype="multipart/form-data"
            class="needs-validation"
            novalidate
        >
            @csrf
            @method('PUT')

            <div id="post-form-card" class="card animate__animated animate__rollIn">

                <!-- HEADER -->
                <div class="card-header">
                    <h5>Modifier l’article</h5>
                </div>

                <!-- BODY -->
                <div class="card-body">
                    @include('backoffice.blog.posts._form')
                </div>

                <!-- FOOTER -->
                <div class="card-footer text-end">
                    <a 
                        href="{{ route('backoffice.blog.posts.index') }}" 
                        class="btn btn-secondary"
                        onclick="rollOutCard(event, this, 'post-form-card')"
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
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="{{ asset('build/js/plugins/ckeditor/classic/ckeditor.js') }}"></script>

<script>
    // =======================================
    // CKEditor – FR + EN
    // =======================================
    ClassicEditor
        .create(document.querySelector('#editor-fr'))
        .catch(error => {
            console.error('CKEditor FR error:', error);
        });

    ClassicEditor
        .create(document.querySelector('#editor-en'))
        .catch(error => {
            console.error('CKEditor EN error:', error);
        });


    // =======================================
    // Choices.js – Category select
    // =======================================
    document.addEventListener("DOMContentLoaded", function () {
        const selectCategory = document.getElementById("blog_category_id");

        if (selectCategory) {
            new Choices(selectCategory, {
                searchPlaceholderValue: 'Rechercher...',
                noResultsText: 'Aucun résultat trouvé',
                itemSelectText: 'Sélectionner',
                shouldSort: false
            });

            // Force correct name (fix Choices removing it sometimes)
            selectCategory.setAttribute('name', 'category_id');
        }
    });


    // =======================================
    // Bootstrap Validation
    // =======================================
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


    // =======================================
    // Cancel Animation – Roll Out
    // =======================================
    function rollOutCard(event, link, cardId = 'post-form-card') {
        event.preventDefault();

        const card = document.getElementById(cardId);

        if (card) {
            card.classList.remove('animate__rollIn');
            card.classList.add('animate__animated', 'animate__rollOut');

            setTimeout(() => {
                window.location.href = link.href;
            }, 800);
        } else {
            window.location.href = link.href;
        }
    }
</script>

@endsection
