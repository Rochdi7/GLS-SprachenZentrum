@extends('frontoffice.layouts.app')

@section('title', __('gls-exam.meta.title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/exam/gls.css') }}">

@section('content')

    <!-- ============================
         HERO
    ============================= -->
    <section class="hero-section section about-hero reveal delay-1">
        <div class="container is-hero reveal delay-2">

            <div class="hero_subtitle reveal delay-1">
                {{ __('gls-exam.hero.subtitle') }}
            </div>

            <h1 class="hero_title fade-blur-title reveal delay-2">
                {!! __('gls-exam.hero.title') !!}
            </h1>

            <div class="hero-image reveal delay-3">
                <img src="{{ asset('assets/images/about/Centre-GLS-de-langue-Allemande.jpg') }}"
                    alt="{{ __('gls-exam.hero.alt') }}" class="full-image reveal delay-1" loading="lazy">
            </div>

        </div>
    </section>

    <!-- ============================
         INTRO SECTION
    ============================= -->
    <div class="rich-text-section section reveal delay-1">
        <div class="container reveal delay-2">
            <div class="rich-text w-richtext reveal delay-3">

                <p class="reveal delay-1">{!! __('gls-exam.intro.p1') !!}</p>
                <p class="reveal delay-2">{!! __('gls-exam.intro.p2') !!}</p>
                <p class="reveal delay-3"><strong>{!! __('gls-exam.intro.p3') !!}</strong></p>

            </div>
        </div>
    </div>


@endsection
