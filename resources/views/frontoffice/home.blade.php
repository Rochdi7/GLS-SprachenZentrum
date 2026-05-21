@extends('frontoffice.layouts.app')

@push('head')
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    <link rel="preconnect" href="https://basemaps.cartocdn.com" crossorigin>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@section('content')
    <main class="home-page">
        {{-- ===========================
     HERO SECTION
=========================== --}}
        <section class="hero reveal delay-1" aria-label="Intro">
            <div class="hero__bg reveal delay-2"
                style="background-image: url('{{ asset('assets/images/IMG_4399.webp') }}');">
            </div>

            {{-- Badges --}}
            <div class="badge b-blue b1 reveal delay-3">{{ __('home.hero.badge1') }}</div>
            <div class="badge b-green b2 reveal delay-1">{{ __('home.hero.badge2') }}</div>
            <div class="badge b-orange b3 reveal delay-2">{{ __('home.hero.badge3') }}</div>
            <div class="badge b-violet b4 reveal delay-3">{{ __('home.hero.badge4') }}</div>

            <div class="hero__inner text-center {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1">
                <h1 class="hero-title reveal fade-blur-title delay-1">
                    {{ __('home.hero.title') }}
                </h1>
            </div>
        </section>

        {{-- ===========================
 INTRO SECTION
=========================== --}}
        <section
            class="intro-section position-relative text-center {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-2">

            <div class="intro-gradient reveal delay-3"></div>

            <div class="container position-relative z-2 py-5 reveal delay-1">
                <div class="intro-card shadow rounded-4 mx-auto reveal delay-2" style="max-width: 1020px;">

                    {{-- Logo + Tagline --}}
                    <div class="text-center mb-4 reveal delay-3">
                        <img src="{{ asset('assets/images/logo/gls-round.png') }}" alt="GLS Logo"
                            class="intro-logo reveal delay-1">

                        <p class="text-primary fw-medium small mb-0 letter-spacing-1 reveal delay-2">
                            {{ __('home.intro.tagline') }}
                        </p>
                    </div>

                    {{-- Heading --}}
                    <h1 class="fw-bold mb-3 intro-heading reveal fade-blur-title delay-1">
                        {{ __('home.intro.heading') }}
                    </h1>

                    {{-- Description --}}
                    <p class="lead text-muted mb-4 intro-desc reveal delay-2">
                        {{ __('home.intro.description') }}
                    </p>

                    {{-- Button --}}
                    <a href="{{ LaravelLocalization::localizeUrl(route('front.intensive-courses')) }}"
                        class="btn btn-success px-4 py-2 rounded-pill fw-semibold reveal delay-3">
                        {{ __('home.intro.button') }}
                    </a>
                </div>
            </div>
        </section>

        {{-- =========================
SITES — Images only (NO iframe, NO yt-holder, NO video)
========================= --}}
        <section class="section sites-maroc-section">
            <div class="container text-center mb-5">
                <h2 class="sites-title">{{ __('gls.sites.title') }}</h2>
                <p class="sites-subtitle">{{ __('gls.sites.subtitle') }}</p>
            </div>

            <div class="container sites-grid">

                <!-- 1. Rabat -->
                <a href="{{ route('front.sites.show', 'gls-rabat') }}" class="site-card small">
                    <div class="site-video-wrapper">
                        <img src="{{ asset('assets/images/sites/rabat.jpg') }}" alt="GLS Rabat" class="site-image">
                    </div>

                    <div class="site-overlay">
                        <h3>{{ __('gls.sites.rabat') }}</h3>
                    </div>
                </a>

                <!-- 2. Kénitra -->
                <a href="{{ route('front.sites.show', 'gls-kenitra') }}" class="site-card small">
                    <div class="site-video-wrapper">
                        <img src="{{ asset('assets/images/sites/kenitra.jpg') }}" alt="GLS Kénitra" class="site-image">
                    </div>

                    <div class="site-overlay">
                        <h3>{{ __('gls.sites.kenitra') }}</h3>
                    </div>
                </a>

                <!-- 3. Marrakech -->
                <a href="{{ route('front.sites.show', 'gls-marrakech') }}" class="site-card wide">
                    <div class="site-video-wrapper">
                        <img src="{{ asset('assets/images/sites/marrakech.webp') }}" alt="GLS Marrakech"
                            class="site-image">
                    </div>

                    <div class="site-overlay">
                        <h3>{{ __('gls.sites.marrakech') }}</h3>
                    </div>
                </a>

                <!-- 4. Salé -->
                <a href="{{ route('front.sites.show', 'gls-sale') }}" class="site-card wide">
                    <div class="site-video-wrapper">
                        <img src="{{ asset('assets/images/sites/sale.webp') }}" alt="GLS Salé" class="site-image">
                    </div>

                    <div class="site-overlay">
                        <h3>{{ __('gls.sites.sale') }}</h3>
                    </div>
                </a>

                <!-- 5. Agadir -->
                <a href="{{ route('front.sites.show', 'gls-agadir') }}" class="site-card small">
                    <div class="site-video-wrapper">
                        <img src="{{ asset('assets/images/sites/agadir.avif') }}" alt="GLS Agadir" class="site-image">
                    </div>

                    <div class="site-overlay">
                        <h3>{{ __('gls.sites.agadir') }}</h3>
                    </div>
                </a>

                <!-- 6. Casablanca -->
                <a href="{{ route('front.sites.show', 'gls-casablanca') }}" class="site-card small">
                    <div class="site-video-wrapper">
                        <img src="{{ asset('assets/images/sites/casablanca.jpg') }}" alt="GLS Casablanca"
                            class="site-image">
                    </div>

                    <div class="site-overlay">
                        <h3>{{ __('gls.sites.casablanca') }}</h3>
                    </div>
                </a>

            </div>
        </section>


        {{-- ===========================
  REVIEWS SECTION
=========================== --}}
        <section class="reviews-carousel-section section {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1">

            <div class="container is-reviews-title-block reveal delay-2">

                {{-- Title --}}
                <h2 class="h-section-subtitle is-reviews reveal fade-blur-title delay-1">
                    {{ __('home.reviews.title') }}
                </h2>

                {{-- Rating block --}}
                <div class="div-block-29 w-inline-block reveal delay-3">

                    {{-- SVG Stars --}}
                    <div class="reveal delay-1">
                        @include('frontoffice.partials.svg-stars-big')
                    </div>

                    <div class="reveal delay-2"><strong>{{ __('home.reviews.rating_line') }}</strong></div>
                </div>
            </div>

            <div class="div-block-28 review-grid-layout reveal delay-3">

                {{-- Track 1 (Left) --}}
                <div class="review-carousel_track is-animating-left reveal delay-1">

                    @foreach (__('home.reviews.items') as $review)
                        <div class="review-block review-card-inspired reveal delay-2">
                            <div class="review-stars reveal delay-3">@include('frontoffice.partials.svg-stars')</div>
                            <div class="text-block-9 reveal delay-1">"{{ $review['text'] }}"</div>
                            <div class="text-block-10 reveal delay-2">– {{ $review['name'] }} ({{ $review['year'] }})
                            </div>
                        </div>
                    @endforeach

                    {{-- Duplicate --}}
                    @foreach (__('home.reviews.items') as $review)
                        <div class="review-block review-card-inspired reveal delay-3">
                            <div class="review-stars reveal delay-1">@include('frontoffice.partials.svg-stars')</div>
                            <div class="text-block-9 reveal delay-2">"{{ $review['text'] }}"</div>
                            <div class="text-block-10 reveal delay-3">– {{ $review['name'] }} ({{ $review['year'] }})
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Track 2 (Right) --}}
                <div class="review-carousel_track is-alt is-animating-right reveal delay-1">

                    @foreach (__('home.reviews.items') as $review)
                        <div class="review-block review-card-inspired reveal delay-2">
                            <div class="review-stars reveal delay-3">@include('frontoffice.partials.svg-stars')</div>
                            <div class="text-block-9 reveal delay-1">"{{ $review['text'] }}"</div>
                            <div class="text-block-10 reveal delay-2">– {{ $review['name'] }} ({{ $review['year'] }})
                            </div>
                        </div>
                    @endforeach

                    @foreach (__('home.reviews.items') as $review)
                        <div class="review-block review-card-inspired reveal delay-3">
                            <div class="review-stars reveal delay-1">@include('frontoffice.partials.svg-stars')</div>
                            <div class="text-block-9 reveal delay-2">"{{ $review['text'] }}"</div>
                            <div class="text-block-10 reveal delay-3">– {{ $review['name'] }} ({{ $review['year'] }})
                            </div>
                        </div>
                    @endforeach

                </div>

            </div>
        </section>

        {{-- Testimonial Videos Section --}}
        @include('frontoffice.partials.marketing-videos-testimonials')

        {{-- ===========================
  HIGHLIGHTS SECTION - Starting Soon
=========================== --}}
        <section class="hh-highlights reveal delay-1">
            <div class="container hh-container reveal delay-2">
                <h2 class="hh-section-title reveal fade-blur-title delay-1">{{ __('home.highlights.title') }}</h2>

                <div class="hh-card hh-card-big reveal delay-2">
                    <div class="hh-block-31 reveal delay-3">
                        <h3 class="hh-title hh-title-big reveal delay-1">
                            {!! __('home.highlights.big_card.title') !!}
                        </h3>

                        <div class="hh-text-block reveal delay-2">
                            {{ __('home.highlights.big_card.subtitle') }}
                        </div>

                        <p class="hh-text hh-text-highlight reveal delay-3">
                            <strong>{{ __('home.highlights.big_card.start_date') }}<br></strong>
                            {{ __('home.highlights.big_card.description') }}
                        </p>

                        <div class="hh-buttons reveal delay-1">
                            <a href="https://maps.app.goo.gl/Q1AU8bPr5kp3K3Sm8" target="_blank"
                                class="button is-white">{{ __('home.highlights.big_card.button_directions') }}</a>

                            <a href="https://www.instagram.com/gls.maroc" target="_blank"
                                class="button is-white">{{ __('home.highlights.big_card.button_learn_more') }}</a>
                        </div>
                    </div>

                    <div class="hh-block-30 reveal delay-1">
                        <img src="{{ asset('assets/images/IMG_4399.webp') }}" loading="lazy"
                            alt="{{ __('home.highlights.big_card.title') }}">
                    </div>
                </div>

                <div class="hh-row reveal delay-3">
                    <div class="hh-card hh-card-first reveal delay-1">
                        <h3 class="hh-title reveal delay-2">
                            {!! __('home.highlights.card_a1.title') !!}
                        </h3>

                        <p class="hh-text reveal delay-3">
                            <strong>{{ __('home.highlights.card_a1.spots_available') }}<br></strong>
                            <span class="hh-muted">
                                {!! __('home.highlights.card_a1.description') !!}
                            </span>
                        </p>

                        <a href="{{ LaravelLocalization::localizeUrl(route('front.gls-inscription')) }}"
                            class="button is-white reveal delay-1"
                            style="background:#ffffff !important; 
          color: var(--dark--off-black) !important; 
          border-color: var(--dark--off-black) !important;">
                            {{ __('home.highlights.card_a1.button') }}
                        </a>
                    </div>

                    <div class="hh-card reveal delay-2">
                        <h3 class="hh-title reveal delay-3">{{ __('home.highlights.card_intensive.title') }}</h3>

                        <p class="hh-text reveal delay-1">
                            <strong>{{ __('home.highlights.card_intensive.join_anytime') }}<br></strong>
                            <span class="hh-muted">
                                {!! __('home.highlights.card_intensive.description') !!}
                            </span>
                        </p>

                        <a href="{{ LaravelLocalization::localizeUrl(route('front.gls-inscription')) }}"
                            class="button is-white reveal delay-2"
                            style="background:#ffffff !important; color: var(--dark--off-black) !important; border-color: var(--dark--off-black) !important;">
                            {{ __('home.highlights.card_a1.button') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>


        <section class="home-courses-section section">

            {{-- 1. Banner Photo Block --}}
            <div class="container is-home-courses-photo">
                <h2 class="h-section-title">{{ __('home.courses.title') }}</h2>
            </div>

            {{-- 2. German Intensive Courses (A1-B2) --}}
            <div class="container is-h-courses">
                <h2 class="h-section-subtitle-courses">{{ __('home.courses.intensive.title') }}</h2>
                <div class="subtitle">{{ __('home.courses.intensive.subtitle') }}</div>
                <p class="paragraph-2">{{ __('home.courses.intensive.description') }}</p>

                <div class="courses-cards">

                    {{-- A1 --}}
                    <div class="course-card">
                        <div class="couse-card_level">
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.a1.letter') }}</div>
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.a1.number') }}</div>
                        </div>
                        <h3 class="course-card_title">{!! __('home.courses.intensive.cards.a1.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.intensive.cards.a1.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.a1')) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.intensive.cards.a1.button') }}
                        </a>

                    </div>

                    {{-- A2 --}}
                    <div class="course-card is-green">
                        <div class="couse-card_level">
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.a2.letter') }}</div>
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.a2.number') }}</div>
                        </div>
                        <h3 class="course-card_title">{!! __('home.courses.intensive.cards.a2.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.intensive.cards.a2.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.a2')) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.intensive.cards.a2.button') }}
                        </a>

                    </div>

                    {{-- B1 --}}
                    <div class="course-card is-purple">
                        <div class="couse-card_level">
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.b1.letter') }}</div>
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.b1.number') }}</div>
                        </div>
                        <h3 class="course-card_title">{!! __('home.courses.intensive.cards.b1.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.intensive.cards.b1.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.b1')) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.intensive.cards.b1.button') }}
                        </a>
                    </div>

                    {{-- B2 --}}
                    <div class="course-card is-yellow">
                        <div class="couse-card_level">
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.b2.letter') }}</div>
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.b2.number') }}</div>
                        </div>
                        <h3 class="course-card_title">{!! __('home.courses.intensive.cards.b2.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.intensive.cards.b2.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.b2')) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.intensive.cards.b2.button') }}
                        </a>
                    </div>

                </div>
            </div>

            {{-- 3. Online courses & exams --}}
            <div class="container is-h-courses">
                <h2 class="h-section-subtitle-courses">{{ __('home.courses.online.title') }}</h2>
                <div class="subtitle">{{ __('home.courses.online.subtitle') }}</div>

                <div class="courses-cards is-home-other-german-courses">

                    {{-- Online Courses --}}
                    <div class="course-card is-orange">
                        <h3 class="course-card_title is-others">{!! __('home.courses.online.cards.online.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.online.cards.online.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route(__('home.courses.online.cards.online.route'))) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.online.cards.online.button') }}
                        </a>
                    </div>

                    {{-- GLS Exam Preparation --}}
                    <div class="course-card is-green">
                        <h3 class="course-card_title is-others">{!! __('home.courses.online.cards.gls.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.online.cards.gls.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route(__('home.courses.online.cards.gls.route'))) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.online.cards.gls.button') }}
                        </a>
                    </div>

                    {{-- Goethe Exam Preparation --}}
                    <div class="course-card is-purple">
                        <h3 class="course-card_title is-others">{!! __('home.courses.online.cards.goethe.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.online.cards.goethe.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route(__('home.courses.online.cards.goethe.route'))) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.online.cards.goethe.button') }}
                        </a>
                    </div>

                    <div class="course-card is-purple is-others">
                        <h3 class="course-card_title is-others">
                            {!! __('home.courses.online.cards.goethe.title') !!}
                        </h3>

                        <div class="course-card_text">
                            {!! __('home.courses.online.cards.goethe.text') !!}
                        </div>

                        <a href="{{ LaravelLocalization::localizeUrl(route(__('home.courses.online.cards.goethe.route'))) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.online.cards.goethe.button') }}
                        </a>
                    </div>

                </div>
            </div>

        </section>


        {{-- ===========================
 LEARN MORE SECTION
=========================== --}}
        <section class="learn-more-section py-5 text-light" style="background-color: var(--off-black);">
            <div class="container py-5">
                <div class="row align-items-center g-5">

                    {{-- Left Text Column --}}
                    <div class="col-lg-6">
                        <h2 class="fw-bold mb-4 learn-more-title">
                            {!! __('home.learn_more.title') !!}
                        </h2>

                        <p class="lead opacity-75 mb-0">
                            {!! __('home.learn_more.description') !!}
                        </p>
                    </div>

                    {{-- Right Cards Column --}}
                    <div class="col-lg-6">
                        <div class="row g-4">
                            @foreach (__('home.learn_more.cards') as $card)
                                <div class="col-md-6">
                                    <a href="{{ !empty($card['route']) ? route($card['route']) : $card['link'] ?? '#' }}"
                                        class="h-learn-more-card"
                                        @if (!empty($card['action'])) data-action="{{ $card['action'] }}"
                                    role="button"
                                    aria-haspopup="dialog" @endif>
                                        <div class="h-learn-more-card_icon">
                                            {!! $card['icon'] !!}
                                        </div>

                                        <div
                                            class="learn-card-bottom d-flex align-items-center justify-content-between w-100">
                                            <h3 class="fw-bold fs-4 mb-0">{!! $card['title'] !!}</h3>

                                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.84451 20L7.33722 17.5502L13.1778 11.799H0V8.20096H13.1778L7.33722 2.45933L9.84451 0L20 10L9.84451 20Z" />
                                            </svg>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

            {{-- Site Selector Modal --}}

            <div class="modal fade gls-site-modal" id="groupsSiteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered gls-site-modal__dialog">
                    <div class="modal-content gls-site-modal__content">

                        <div class="gls-site-modal__header">
                            <div>
                                <div class="gls-site-modal__kicker">{{ __('home.site_modal.kicker') }}</div>
                                <h5 class="gls-site-modal__title">{{ __('home.site_modal.title') }}</h5>
                            </div>

                            <button type="button" class="gls-site-modal__close" data-bs-dismiss="modal"
                                aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>

                        <div class="gls-site-modal__body">
                            <div class="gls-site-grid">
                                <a class="gls-site-pill"
                                    href="{{ route('front.sites.show', 'gls-marrakech') }}">{{ __('home.site_modal.marrakech') }}</a>

                                <a class="gls-site-pill"
                                    href="{{ route('front.sites.show', 'gls-casablanca') }}">{{ __('home.site_modal.casablanca') }}</a>

                                <a class="gls-site-pill"
                                    href="{{ route('front.sites.show', 'gls-rabat') }}">{{ __('home.site_modal.rabat') }}</a>

                                <a class="gls-site-pill"
                                    href="{{ route('front.sites.show', 'gls-kenitra') }}">{{ __('home.site_modal.kenitra') }}</a>

                                <a class="gls-site-pill"
                                    href="{{ route('front.sites.show', 'gls-sale') }}">{{ __('home.site_modal.sale') }}</a>

                                <a class="gls-site-pill"
                                    href="{{ route('front.sites.show', 'gls-agadir') }}">{{ __('home.site_modal.agadir') }}</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


        </section>


        {{-- ===========================
 STUDENTENAKTE — Editorial student services dossier
=========================== --}}
        <section class="ssn-section reveal delay-1" aria-label="{{ __('home.student_services.eyebrow') }}">
            <div class="ssn-inner">

                {{-- HEADER --}}
                <header class="ssn-header reveal delay-2">
                    <div>
                        <div class="ssn-eyebrow">
                            <img class="flag" src="{{ asset('assets/images/germany-flag-icon.svg') }}" alt=""
                                aria-hidden="true">
                            {{ __('home.student_services.eyebrow') }}
                        </div>
                        <h2 class="ssn-title">
                            {{ __('home.student_services.title_lead') }}
                            <em>{{ __('home.student_services.title_accent') }}</em>{{ __('home.student_services.title_tail') }}
                        </h2>
                    </div>
                    <div class="ssn-meta">
                        <strong>{{ __('home.student_services.meta_label') }}</strong>
                        {{ __('home.student_services.meta_text') }}
                    </div>
                </header>

                <div class="ssn-body" data-state="track">

                    {{-- LEFT — FEATURED : DYNAMIC PANEL (3 variants stacked) --}}
                    <div class="ssn-feature-stack reveal delay-3">

                        {{-- VARIANT : TRACK (default / featured) --}}
                        <a class="ssn-feature ssn-variant ssn-variant--track"
                            href="{{ LaravelLocalization::localizeUrl(route('front.translations.track')) }}">
                            <span class="ssn-feature-tag">
                                <span class="pulse"></span>
                                {{ __('home.student_services.feature_tag') }}
                            </span>
                            <h3 class="ssn-feature-title">{{ __('home.student_services.feature_title') }}</h3>
                            <p class="ssn-feature-desc">{{ __('home.student_services.feature_desc') }}</p>

                            <div class="ssn-preview" aria-hidden="true">
                                <div class="ssn-preview-row">
                                    <strong>{{ __('home.student_services.preview_order') }}</strong>
                                    <span>{{ __('home.student_services.preview_pages') }}</span>
                                </div>
                                <div class="ssn-preview-row">
                                    <span style="color:var(--ssn-gold-bright);font-weight:600;">
                                        <i class="bi bi-translate"></i> {{ __('home.student_services.preview_status') }}
                                    </span>
                                    <span>05.05.2026</span>
                                </div>
                                <div class="ssn-track">
                                    <span class="ssn-dot done"></span>
                                    <span class="ssn-bar done"></span>
                                    <span class="ssn-dot now"></span>
                                    <span class="ssn-bar"></span>
                                    <span class="ssn-dot"></span>
                                </div>
                            </div>

                            <span class="ssn-feature-cta">
                                {{ __('home.student_services.feature_cta') }}
                                <i class="bi bi-arrow-up-right"></i>
                            </span>
                        </a>

                        {{-- VARIANT : CERTIFICATE --}}
                        <a class="ssn-feature ssn-variant ssn-variant--cert"
                            href="{{ LaravelLocalization::localizeUrl(route('front.certificate.check')) }}">
                            <span class="ssn-feature-tag">
                                <i class="bi bi-patch-check-fill"></i>
                                {{ __('home.student_services.cert_tag') }}
                            </span>
                            <h3 class="ssn-feature-title">{{ __('home.student_services.item1_title') }}</h3>
                            <p class="ssn-feature-desc">{{ __('home.student_services.item1_desc') }}</p>

                            <div class="ssn-preview" aria-hidden="true">
                                <div class="ssn-preview-row">
                                    <strong>{{ __('home.student_services.cert_preview_label') }}</strong>
                                    <span class="ssn-cert-num">GLS-2024-A1F8K</span>
                                </div>
                                <div class="ssn-preview-row">
                                    <span style="color:#5dd29c;font-weight:600;">
                                        <i class="bi bi-shield-check"></i>
                                        {{ __('home.student_services.cert_preview_status') }}
                                    </span>
                                    <span>{{ __('home.student_services.cert_preview_level') }} · B2</span>
                                </div>
                                <div class="ssn-cert-stamp">
                                    <i class="bi bi-patch-check-fill"></i>
                                    <span>{{ __('home.student_services.cert_preview_authentic') }}</span>
                                </div>
                            </div>

                            <span class="ssn-feature-cta">
                                {{ __('home.student_services.cert_cta') }}
                                <i class="bi bi-arrow-up-right"></i>
                            </span>
                        </a>

                        {{-- VARIANT : ATTESTATION --}}
                        <a class="ssn-feature ssn-variant ssn-variant--att"
                            href="{{ LaravelLocalization::localizeUrl(route('front.attestation-request.create')) }}">
                            <span class="ssn-feature-tag">
                                <i class="bi bi-envelope-paper-fill"></i>
                                {{ __('home.student_services.att_tag') }}
                            </span>
                            <h3 class="ssn-feature-title">{{ __('home.student_services.item2_title') }}</h3>
                            <p class="ssn-feature-desc">{{ __('home.student_services.item2_desc') }}</p>

                            <div class="ssn-preview" aria-hidden="true">
                                <div class="ssn-preview-row">
                                    <strong>{{ __('home.student_services.att_preview_request') }}</strong>
                                    <span>05.05.2026</span>
                                </div>
                                <div class="ssn-preview-row">
                                    <span style="color:var(--ssn-gold-bright);font-weight:600;">
                                        <i class="bi bi-hourglass-split"></i>
                                        {{ __('home.student_services.att_preview_status') }}
                                    </span>
                                    <span>{{ __('home.student_services.att_preview_level') }} · A2</span>
                                </div>
                                <div class="ssn-att-flow">
                                    <div class="ssn-att-step done"><i
                                            class="bi bi-check"></i><span>{{ __('home.student_services.att_step1') }}</span>
                                    </div>
                                    <div class="ssn-att-step now"><span
                                            class="dot"></span><span>{{ __('home.student_services.att_step2') }}</span>
                                    </div>
                                    <div class="ssn-att-step"><i
                                            class="bi bi-envelope"></i><span>{{ __('home.student_services.att_step3') }}</span>
                                    </div>
                                </div>
                            </div>

                            <span class="ssn-feature-cta">
                                {{ __('home.student_services.att_cta') }}
                                <i class="bi bi-arrow-up-right"></i>
                            </span>
                        </a>

                    </div>

                    {{-- RIGHT — NUMBERED LIST --}}
                    <div class="ssn-list reveal delay-4">

                        {{-- 00 — TRACK TRANSLATION (resets to default) --}}
                        <a class="ssn-item ssn-item--track" data-target="track"
                            href="{{ LaravelLocalization::localizeUrl(route('front.translations.track')) }}">
                            <span class="ssn-num">01</span>
                            <div class="ssn-item-body">
                                <h4 class="ssn-item-title">{{ __('home.student_services.tr_title_short') }}</h4>
                                <p class="ssn-item-desc">{{ __('home.student_services.tr_desc_short') }}</p>
                            </div>
                            <span class="ssn-arrow"><i class="bi bi-arrow-right"></i></span>
                        </a>

                        {{-- 02 — VERIFY CERTIFICATE --}}
                        <a class="ssn-item ssn-item--cert" data-target="cert"
                            href="{{ LaravelLocalization::localizeUrl(route('front.certificate.check')) }}">
                            <span class="ssn-num">02</span>
                            <div class="ssn-item-body">
                                <h4 class="ssn-item-title">{{ __('home.student_services.item1_title') }}</h4>
                                <p class="ssn-item-desc">{{ __('home.student_services.item1_desc') }}</p>
                            </div>
                            <span class="ssn-arrow"><i class="bi bi-arrow-right"></i></span>
                        </a>

                        {{-- 03 — ATTESTATION REQUEST --}}
                        <a class="ssn-item ssn-item--att" data-target="att"
                            href="{{ LaravelLocalization::localizeUrl(route('front.attestation-request.create')) }}">
                            <span class="ssn-num">03</span>
                            <div class="ssn-item-body">
                                <h4 class="ssn-item-title">{{ __('home.student_services.item2_title') }}</h4>
                                <p class="ssn-item-desc">{{ __('home.student_services.item2_desc') }}</p>
                            </div>
                            <span class="ssn-arrow"><i class="bi bi-arrow-right"></i></span>
                        </a>

                    </div>
                </div>

                <script>
                    (function() {
                        const body = document.currentScript.previousElementSibling;
                        if (!body || !body.matches('.ssn-body')) return;

                        const STATES = ['track', 'cert', 'att'];
                        const items = Array.from(body.querySelectorAll('.ssn-item[data-target]'));
                        const mqMobile = window.matchMedia('(max-width: 992px)');

                        let autoTimer = null;
                        let userTouched = false;
                        let resumeTimer = null;

                        function setState(s) {
                            if (!STATES.includes(s) || body.dataset.state === s) return;
                            body.dataset.state = s;
                            items.forEach(it => it.classList.toggle('is-active', it.dataset.target === s));
                        }

                        function startAuto() {
                            stopAuto();
                            autoTimer = setInterval(() => {
                                const cur = body.dataset.state || 'track';
                                const next = STATES[(STATES.indexOf(cur) + 1) % STATES.length];
                                setState(next);
                            }, 3000);
                        }

                        function stopAuto() {
                            if (autoTimer) {
                                clearInterval(autoTimer);
                                autoTimer = null;
                            }
                        }

                        function configureForViewport() {
                            if (mqMobile.matches) {
                                // Mobile : autoplay (unless user just interacted)
                                if (!userTouched) startAuto();
                            } else {
                                // Desktop : hover-driven, no autoplay
                                stopAuto();
                                setState('track');
                            }
                        }

                        // ── Desktop hover/focus ──
                        items.forEach(it => {
                            it.addEventListener('mouseenter', () => {
                                if (mqMobile.matches) return;
                                setState(it.dataset.target);
                            });
                            it.addEventListener('focus', () => setState(it.dataset.target));
                        });
                        body.addEventListener('mouseleave', () => {
                            if (mqMobile.matches) return;
                            setState('track');
                        });

                        // ── Mobile : pause autoplay briefly on tap, then resume ──
                        items.forEach(it => {
                            it.addEventListener('touchstart', () => {
                                if (!mqMobile.matches) return;
                                userTouched = true;
                                stopAuto();
                                setState(it.dataset.target);
                                clearTimeout(resumeTimer);
                                resumeTimer = setTimeout(() => {
                                    userTouched = false;
                                    startAuto();
                                }, 6000);
                            }, {
                                passive: true
                            });
                        });

                        // ── Pause when section is offscreen, resume when visible ──
                        if ('IntersectionObserver' in window) {
                            const io = new IntersectionObserver((entries) => {
                                entries.forEach(e => {
                                    if (!mqMobile.matches) return;
                                    if (e.isIntersecting && !userTouched) startAuto();
                                    else stopAuto();
                                });
                            }, {
                                threshold: 0.25
                            });
                            io.observe(body);
                        }

                        mqMobile.addEventListener('change', configureForViewport);
                        configureForViewport();
                    })();
                </script>

                <div class="ssn-foot">
                    <span class="ssn-stamp">
                        <i class="bi bi-shield-lock-fill"></i>
                        {{ __('home.student_services.foot_text') }}
                    </span>
                    <span class="ssn-locale">MA</span>
                </div>
            </div>
        </section>

        {{-- ===========================
 ABOUT GLS MOROCCO – 9onsol’s Talks
=========================== --}}
        <section class="home-about-section section {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1">
            <div class="container about-grid reveal delay-2">

                {{-- LEFT CARD --}}
                <div class="about-card text-light reveal delay-3">

                    <h2 class="h-section-subtitle mb-4 reveal fade-blur-title delay-1">
                        {!! __('home.9onsol.title') !!}
                    </h2>

                    <p class="lead mb-4 reveal delay-2">
                        {!! __('home.9onsol.description') !!}
                    </p>

                    <a href="https://www.youtube.com/@9onsolsTalks" target="_blank"
                        class="btn btn-light rounded-pill fw-semibold px-4 py-2 mt-auto reveal delay-3">
                        {{ __('home.9onsol.button') }}
                    </a>
                </div>

                {{-- VIDEO BLOCK --}}
                <div class="about-video reveal delay-1">
                    <iframe src="https://www.youtube.com/embed/wPYANoRURpU?si=p__Fgz2v7VuF_ubl"
                        title="9onsol’s Talks – GLS Morocco Podcast"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"
                        class="reveal delay-2"></iframe>
                </div>

            </div>
        </section>
        {{-- Marketing Videos Section (GLS Videos) --}}
        @include('frontoffice.partials.marketing-videos')

        {{-- ===============================
 COOPERATION PARTNERS – Auto Marquee
================================ --}}
        <section class="partners-section text-center reveal delay-1 {{ app()->getLocale() == 'ar' ? 'rtl' : '' }}"
            aria-label="{{ __('home.partners.aria_label') }}">
            <div class="container reveal delay-2">

                <h2 class="partners-title reveal fade-blur-title delay-1">{{ __('home.partners.title') }}</h2>

                <div class="partners-marquee reveal delay-2">
                    <div class="partners-track reveal delay-3">

                        {{-- ——— Set A ——— --}}
                        <img src="{{ asset('assets/images/home/goethe.png') }}" alt="Goethe-Institut" loading="lazy">
                        <img src="{{ asset('assets/images/home/marokkofc.png') }}" alt="Marokko FC" loading="lazy">
                        <img src="{{ asset('assets/images/home/osd.png') }}" alt="ÖSD Exam" loading="lazy">
                        <img src="{{ asset('assets/images/home/gizlogo-unternehmen-de-rgb-300.webp') }}"
                            alt="GIZ German Cooperation" loading="lazy">
                        <img src="{{ asset('assets/images/home/ECL_LOGO.png') }}" alt="ECL Language Certification"
                            loading="lazy">
                        <img src="{{ asset('assets/images/home/TLScontact_main.webp') }}" alt="TLScontact"
                            loading="lazy">

                        {{-- ——— Set B ——— --}}
                        <img src="{{ asset('assets/images/home/goethe.png') }}" alt="Goethe-Institut"
                            aria-hidden="true" loading="lazy">
                        <img src="{{ asset('assets/images/home/marokkofc.png') }}" alt="Marokko FC" aria-hidden="true"
                            loading="lazy">
                        <img src="{{ asset('assets/images/home/osd.png') }}" alt="ÖSD Exam" aria-hidden="true"
                            loading="lazy">
                        <img src="{{ asset('assets/images/home/gizlogo-unternehmen-de-rgb-300.webp') }}"
                            alt="GIZ German Cooperation" aria-hidden="true" loading="lazy">
                        <img src="{{ asset('assets/images/home/ECL_LOGO.png') }}" alt="ECL Language Certification"
                            aria-hidden="true" loading="lazy">
                        <img src="{{ asset('assets/images/home/TLScontact_main.webp') }}" alt="TLScontact"
                            aria-hidden="true" loading="lazy">

                    </div>
                </div>

                <noscript>
                    <div class="partners-logos-noscript">
                        <img src="{{ asset('assets/images/home/goethe.png') }}" alt="Goethe-Institut">
                        <img src="{{ asset('assets/images/home/marokkofc.png') }}" alt="Marokko FC">
                        <img src="{{ asset('assets/images/home/osd.png') }}" alt="ÖSD Exam">
                        <img src="{{ asset('assets/images/home/gizlogo-unternehmen-de-rgb-300.webp') }}"
                            alt="GIZ German Cooperation">
                        <img src="{{ asset('assets/images/home/ECL_LOGO.png') }}" alt="ECL Language Certification">
                        <img src="{{ asset('assets/images/home/TLScontact_main.webp') }}" alt="TLScontact">
                    </div>
                </noscript>

            </div>
        </section>

        {{-- ===============================
 CONTACT SECTION
================================ --}}
        <section class="contact-section section {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1">
            <div class="container is-2-col-grid reveal delay-2">

                {{-- LEFT SIDE --}}
                <div class="div-block-5-copy reveal delay-3">

                    <h2 class="contact-section-subtitle reveal fade-blur-title delay-1">
                        {!! __('home.contact.title') !!}
                    </h2>


                    <div class="div-block-21 reveal delay-2">

                        <div class="link-block reveal delay-1">
                            <div class="text-block-3 reveal delay-2">
                                <span class="text-span reveal delay-3">{!! __('home.contact.call_label') !!}<br></span>
                                <a href="tel:+212669515019">+212 6 69 51 50 19</a><br>
                                <a href="tel:+212537372003">+212 5 37 37 20 03</a>
                            </div>
                        </div>

                        <a href="mailto:info@gls-sprachzentrum.ma" class="link-block-2 reveal delay-3">
                            <div class="text-block-3 reveal delay-1">
                                <span class="text-span reveal delay-2">{!! __('home.contact.email_label') !!}<br></span>
                                info@gls-sprachzentrum.ma
                            </div>
                        </a>

                    </div>

                    <div class="text-block-3 visit-block reveal delay-3">
                        <span class="text-span reveal delay-1">{!! __('home.contact.visit_label') !!}</span>
                        @include('frontoffice.partials.gls-centers-links')
                    </div>

                    <div class="footer-socials-block reveal delay-1">

                        <div class="text-block-3 reveal delay-2">
                            <span class="text-span reveal delay-3">{!! __('home.contact.follow_label') !!}</span>
                        </div>

                        <div class="div-block-20 reveal delay-1">
                            <a href="https://www.instagram.com/gls.sprachenzentrum/" class="footer-social-link ig"
                                target="_blank" rel="noopener noreferrer" aria-label="GLS Sprachenzentrum sur Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>

                            <a href="https://www.facebook.com/gls.sale/" class="footer-social-link fb" target="_blank"
                                rel="noopener noreferrer" aria-label="GLS Sprachenzentrum sur Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>

                            <a href="https://www.youtube.com/@9onsolsTalks" class="footer-social-link yt" target="_blank"
                                rel="noopener noreferrer" aria-label="GLS Sprachenzentrum sur YouTube">
                                <i class="bi bi-youtube"></i>
                            </a>

                            <a href="https://api.whatsapp.com/send/?phone=0669515019&text&type=phone_number&app_absent=0"
                                class="footer-social-link wa" target="_blank" rel="noopener noreferrer"
                                aria-label="Contacter GLS Sprachenzentrum sur WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>

                            <a href="https://www.tiktok.com/@gls.sprachenzentrum?is_from_webapp=1&sender_device=pc"
                                class="footer-social-link tt" target="_blank" rel="noopener noreferrer"
                                aria-label="GLS Sprachenzentrum sur TikTok">
                                <i class="bi bi-tiktok"></i>
                            </a>

                        </div>

                    </div>

                </div>

                {{-- RIGHT SIDE : INTERACTIVE MOROCCO MAP — 6 GLS centres --}}
                <div class="gls-map-wrap reveal delay-3"
                    style="position:relative;border-radius:22px;overflow:hidden;border:1px solid #e6e2c5;background:#fffee8;box-shadow:0 18px 44px rgba(0,0,0,.08);width:100%;max-width:100%;aspect-ratio:4/5;min-height:520px;">
                    <div id="glsCentresMap" style="position:absolute;inset:0;width:100%;height:100%;"
                        aria-label="GLS Sprachenzentrum centres au Maroc"></div>
                    <ul class="gls-map-legend" id="glsMapLegend"></ul>
                </div>

                <script>
                    (function initGlsMap() {
                        if (typeof L === 'undefined') {
                            return setTimeout(initGlsMap, 80);
                        }
                        if (!document.getElementById('glsCentresMap')) return;
                        if (document.getElementById('glsCentresMap').dataset.ready === '1') return;
                        document.getElementById('glsCentresMap').dataset.ready = '1';

                        const centres = [{
                                name: 'GLS Rabat',
                                slug: 'gls-rabat',
                                lat: 33.9976668,
                                lng: -6.8485901,
                                color: '#1c45db',
                                gmap: 'https://maps.app.goo.gl/mUnSAVYEnGToS8i2A'
                            },
                            {
                                name: 'GLS Salé',
                                slug: 'gls-sale',
                                lat: 34.0400773,
                                lng: -6.8172275,
                                color: '#009d5a',
                                gmap: 'https://maps.app.goo.gl/pbSW4y4tt9RThx4a7'
                            },
                            {
                                name: 'GLS Kénitra',
                                slug: 'gls-kenitra',
                                lat: 34.2582587,
                                lng: -6.5876841,
                                color: '#ff7a08',
                                gmap: 'https://maps.app.goo.gl/pEsso9L8ygWpdSor5'
                            },
                            {
                                name: 'GLS Casablanca',
                                slug: 'gls-casablanca',
                                lat: 33.5936893,
                                lng: -7.6210973,
                                color: '#9767f8',
                                gmap: 'https://maps.app.goo.gl/EdqBoa3KWEYjuzoq7'
                            },
                            {
                                name: 'GLS Marrakech',
                                slug: 'gls-marrakech',
                                lat: 31.6379228,
                                lng: -8.009762,
                                color: '#d22730',
                                gmap: 'https://maps.app.goo.gl/krR8pGZue3DW3yyv6'
                            },
                            {
                                name: 'GLS Agadir',
                                slug: 'gls-agadir',
                                lat: 30.4017457,
                                lng: -9.5471754,
                                color: '#fc0',
                                gmap: 'https://maps.app.goo.gl/VX48ZDGFyXCyxsGU7'
                            },
                        ];

                        const map = L.map('glsCentresMap', {
                            zoomControl: true,
                            scrollWheelZoom: false,
                            attributionControl: false,
                        }).setView([32.0, -7.5], 6);

                        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                            maxZoom: 18,
                        }).addTo(map);

                        const baseUrl = @json(LaravelLocalization::localizeUrl('/sites/'));
                        const legend = document.getElementById('glsMapLegend');
                        const markers = {};

                        centres.forEach((c, i) => {
                            const html = `
                            <div class="gls-pin-wrap" style="--pin-color:${c.color};">
                                <div class="gls-pin"></div>
                                <div class="gls-pin-label">${c.name.replace('GLS ', '')}</div>
                            </div>`;
                            const icon = L.divIcon({
                                html,
                                className: 'gls-pin-icon',
                                iconSize: [120, 56],
                                iconAnchor: [12, 28],
                            });
                            const m = L.marker([c.lat, c.lng], {
                                icon
                            }).addTo(map);
                            m.bindPopup(
                                `<strong>${c.name}</strong><br><a href="${c.gmap}" target="_blank" rel="noopener" style="color:${c.color};font-weight:600;">Voir sur Google Maps →</a>`
                            );
                            markers[c.slug] = m;

                            const li = document.createElement('li');
                            li.innerHTML =
                                `<span class="dot" style="background:${c.color}"></span> ${c.name.replace('GLS ', '')}`;
                            li.dataset.slug = c.slug;
                            li.addEventListener('click', () => {
                                map.flyTo([c.lat, c.lng], 9, {
                                    duration: 0.6
                                });
                                m.openPopup();
                            });
                            legend.appendChild(li);
                        });

                        // Force size recompute (in case container measured 0 at init)
                        setTimeout(() => map.invalidateSize(), 200);
                        window.addEventListener('resize', () => map.invalidateSize());
                    })();
                </script>

            </div>
        </section>

    </main>
@endsection
