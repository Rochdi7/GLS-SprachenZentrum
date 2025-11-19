@extends('frontoffice.layouts.app')

@section('title', 'GLS Exam | Coming Soon')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/exam/gls.css') }}">

@section('content')
<section class="hero-section section about-hero">
    <div class="container is-hero">

        <div class="hero_subtitle">
            GLS Exams in Morocco
        </div>

        <h1 class="hero_title">
            Something Big is Coming.<br>GLS Official Exam - Soon in Morocco!
        </h1>

        <div class="hero-image">
            <img src="{{ asset('assets/images/about/Centre-GLS-de-langue-Allemande.jpg') }}"
                 alt="GLS Exam Coming Soon Morocco"
                 class="full-image"
                 loading="lazy">
        </div>
    </div>
</section>

<div class="rich-text-section section">
    <div class="container">
        <div class="rich-text w-richtext">

            <p>
                A brand-new, official GLS exam experience is coming to Morocco.  
                Designed for students, workers, and anyone planning their journey to Germany.
            </p>

            <p>
                With GLS, you will soon be able to access standardized, internationally recognized
                German exams — directly here in Morocco — with professional preparation and full guidance.
            </p>

            <p>
                Stay tuned. A major announcement is on the way.  
                <strong>The future of German certification in Morocco starts here.</strong>
            </p>

        </div>
    </div>
</div>

@endsection
