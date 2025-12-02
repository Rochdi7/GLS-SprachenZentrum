@extends('frontoffice.layouts.app')

@section('title', __('gls-exam.meta.title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/exam/gls.css') }}">

@section('content')

<!-- ============================
     HERO
============================= -->
<section class="hero-section section about-hero">
    <div class="container is-hero">

        <div class="hero_subtitle">
            {{ __('gls-exam.hero.subtitle') }}
        </div>

        <h1 class="hero_title">
            {!! __('gls-exam.hero.title') !!}
        </h1>

        <div class="hero-image">
            <img src="{{ asset('assets/images/about/Centre-GLS-de-langue-Allemande.jpg') }}"
                 alt="{{ __('gls-exam.hero.alt') }}"
                 class="full-image"
                 loading="lazy">
        </div>
    </div>
</section>

<!-- ============================
     INTRO SECTION
============================= -->
<div class="rich-text-section section">
    <div class="container">
        <div class="rich-text w-richtext">

            <p>{!! __('gls-exam.intro.p1') !!}</p>
            <p>{!! __('gls-exam.intro.p2') !!}</p>
            <p><strong>{!! __('gls-exam.intro.p3') !!}</strong></p>

        </div>
    </div>
</div>

@endsection
