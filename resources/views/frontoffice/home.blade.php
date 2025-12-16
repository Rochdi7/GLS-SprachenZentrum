@extends('frontoffice.layouts.app')

@section('content')
    {{-- ===========================
     HERO SECTION
=========================== --}}
    <section class="hero reveal delay-1" aria-label="Intro">
        <div class="hero__bg reveal delay-2"
            style="background-image: url('{{ asset('assets/images/home/Centre-GLS-de-langue-Allemande.jpg') }}');"></div>

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

            <div class="intro-card shadow rounded-4 mx-auto p-4 reveal delay-2" style="max-width: 1020px;">

                {{-- Logo + Tagline --}}
                <div class="text-center mb-4 reveal delay-3" style="margin-top: 20px !important;">
                    <img src="{{ asset('build/images/logo/gls-noir.png') }}" alt="GLS Logo"
                        class="intro-logo mb-3 reveal delay-1" style="width: 120px; height: auto;">

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
                        <div class="text-block-10 reveal delay-2">– {{ $review['name'] }} ({{ $review['year'] }})</div>
                    </div>
                @endforeach

                {{-- Duplicate --}}
                @foreach (__('home.reviews.items') as $review)
                    <div class="review-block review-card-inspired reveal delay-3">
                        <div class="review-stars reveal delay-1">@include('frontoffice.partials.svg-stars')</div>
                        <div class="text-block-9 reveal delay-2">"{{ $review['text'] }}"</div>
                        <div class="text-block-10 reveal delay-3">– {{ $review['name'] }} ({{ $review['year'] }})</div>
                    </div>
                @endforeach
            </div>

            {{-- Track 2 (Right) --}}
            <div class="review-carousel_track is-alt is-animating-right reveal delay-1">

                @foreach (__('home.reviews.items') as $review)
                    <div class="review-block review-card-inspired reveal delay-2">
                        <div class="review-stars reveal delay-3">@include('frontoffice.partials.svg-stars')</div>
                        <div class="text-block-9 reveal delay-1">"{{ $review['text'] }}"</div>
                        <div class="text-block-10 reveal delay-2">– {{ $review['name'] }} ({{ $review['year'] }})</div>
                    </div>
                @endforeach

                @foreach (__('home.reviews.items') as $review)
                    <div class="review-block review-card-inspired reveal delay-3">
                        <div class="review-stars reveal delay-1">@include('frontoffice.partials.svg-stars')</div>
                        <div class="text-block-9 reveal delay-2">"{{ $review['text'] }}"</div>
                        <div class="text-block-10 reveal delay-3">– {{ $review['name'] }} ({{ $review['year'] }})</div>
                    </div>
                @endforeach

            </div>

        </div>
    </section>

<section class="section sites-maroc-section">
    <div class="container text-center mb-5">
        <h2 class="sites-title">{{ __('gls.sites.title') }}</h2>
        <p class="sites-subtitle">{{ __('gls.sites.subtitle') }}</p>
    </div>

    <div class="container sites-grid">

        <!-- 1. Rabat -->
        <div class="site-card small">
            <div class="site-video-wrapper">
                <img src="{{ asset('assets/images/sites/rabat.jpg') }}" alt="GLS Rabat" class="site-image">

                <iframe
                    src="https://www.youtube.com/embed/f-DKSeGa5zI?autoplay=1&mute=1&controls=0&loop=1&playlist=f-DKSeGa5zI&modestbranding=1&playsinline=1&start=5"
                    width="560" height="315" frameborder="0" allow="autoplay; encrypted-media">
                </iframe>
            </div>

            <div class="site-video-play">
                <a class="play-btn"
                   data-video="https://www.youtube.com/embed/f-DKSeGa5zI?autoplay=1&mute=0&controls=1&modestbranding=1&rel=0">
                    <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
                    <svg viewBox="0 0 448 512"> <path d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z"/> </svg>
                </a>
            </div>

            <div class="site-overlay"><h3>{{ __('gls.sites.rabat') }}</h3></div>
        </div>


        <!-- 2. Kénitra -->
        <div class="site-card small">
            <div class="site-video-wrapper">
                <img src="{{ asset('assets/images/sites/kenitra.jpg') }}" alt="GLS Kénitra" class="site-image">

                <iframe
                    src="https://www.youtube.com/embed/eNry-9YjhYI?autoplay=1&mute=1&controls=0&loop=1&playlist=eNry-9YjhYI&modestbranding=1&playsinline=1"
                    width="560" height="315" frameborder="0">
                </iframe>
            </div>

            <div class="site-video-play">
                <a class="play-btn"
                   data-video="https://www.youtube.com/embed/eNry-9YjhYI?autoplay=1&controls=1&modestbranding=1&rel=0">
                    <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
                    <svg viewBox="0 0 448 512"> <path d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z"/> </svg>
                </a>
            </div>

            <div class="site-overlay"><h3>{{ __('gls.sites.kenitra') }}</h3></div>
        </div>


        <!-- 3. Marrakech -->
        <div class="site-card wide">
            <div class="site-video-wrapper">
                <img src="{{ asset('assets/images/sites/marrakech.webp') }}" class="site-image">

                <video class="site-video" muted loop preload="metadata" playsinline>
                    <source src="https://raw.githubusercontent.com/Rochdi7/Gls-Videos/main/agadir-cinematic.mp4">
                </video>
            </div>

            <div class="site-video-play">
                <a class="play-btn"
                   data-video="https://www.youtube.com/embed/GJx04L_KQLk?autoplay=1&controls=1&modestbranding=1&rel=0">
                    <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
                    <svg viewBox="0 0 448 512"> <path d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z"/> </svg>
                </a>
            </div>

            <div class="site-overlay"><h3>{{ __('gls.sites.marrakech') }}</h3></div>
        </div>


        <!-- 4. Salé -->
        <div class="site-card wide">
            <div class="site-video-wrapper">
                <img src="{{ asset('assets/images/sites/sale.webp') }}" alt="GLS Salé" class="site-image">

                <iframe
                    src="https://www.youtube.com/embed/GJx04L_KQLk?autoplay=1&mute=1&controls=0&loop=1&playlist=GJx04L_KQLk&modestbranding=1&playsinline=1&start=1"
                    width="620" height="315" frameborder="0">
                </iframe>
            </div>

            <div class="site-video-play">
                <a class="play-btn"
                   data-video="https://www.youtube.com/embed/GJx04L_KQLk?autoplay=1&controls=1&modestbranding=1&rel=0">
                    <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
                    <svg viewBox="0 0 448 512"> <path d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z"/> </svg>
                </a>
            </div>

            <div class="site-overlay"><h3>{{ __('gls.sites.sale') }}</h3></div>
        </div>


        <!-- 5. Agadir -->
        <div class="site-card small">
            <div class="site-video-wrapper">
                <img src="{{ asset('assets/images/sites/agadir.avif') }}" alt="GLS Agadir" class="site-image">

                <iframe
                    src="https://www.youtube.com/embed/MN6_-R2wvhY?autoplay=1&mute=1&controls=0&loop=1&playlist=MN6_-R2wvhY&modestbranding=1&playsinline=1&start=1"
                    width="560" height="315">
                </iframe>
            </div>

            <div class="site-video-play">
                <a class="play-btn"
                   data-video="https://www.youtube.com/embed/MN6_-R2wvhY?autoplay=1&controls=1&modestbranding=1&rel=0">
                    <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
                    <svg viewBox="0 0 448 512"> <path d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z"/> </svg>
                </a>
            </div>

            <div class="site-overlay"><h3>{{ __('gls.sites.agadir') }}</h3></div>
        </div>


        <!-- 6. Casablanca -->
        <div class="site-card small">
            <div class="site-video-wrapper">
                <img src="{{ asset('assets/images/sites/casablanca.jpg') }}" alt="GLS Casablanca" class="site-image">

                <iframe
                    src="https://www.youtube.com/embed/GJx04L_KQLk?autoplay=1&mute=1&controls=0&loop=1&playlist=GJx04L_KQLk&modestbranding=1&playsinline=1&start=1"
                    width="560" height="315">
                </iframe>
            </div>

            <div class="site-video-play">
                <a class="play-btn"
                   data-video="https://www.youtube.com/embed/GJx04L_KQLk?autoplay=1&controls=1&modestbranding=1&rel=0">
                    <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
                    <svg viewBox="0 0 448 512"> <path d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z"/> </svg>
                </a>
            </div>

            <div class="site-overlay"><h3>{{ __('gls.sites.casablanca') }}</h3></div>
        </div>

    </div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {

    // When clicking any play button
    document.querySelectorAll(".play-btn").forEach(btn => {
        btn.addEventListener("click", e => {
            e.preventDefault();

            const videoUrl = btn.getAttribute("data-video");
            const autoplayUrl = videoUrl + "&autoplay=1";

            const popup = document.getElementById("videoPopup");
            const frame = document.getElementById("videoPopupFrame");

            // Load video in iframe
            frame.src = autoplayUrl;

            // Show popup
            popup.style.display = "flex";
        });
    });

    // CLOSE BUTTON
    document.getElementById("videoPopupClose").addEventListener("click", () => {
        closePopup();
    });

    // CLICK OUTSIDE TO CLOSE
    document.getElementById("videoPopup").addEventListener("click", e => {
        if (e.target.id === "videoPopup") {
            closePopup();
        }
    });

    // FUNCTION: CLOSE POPUP
    function closePopup() {
        const popup = document.getElementById("videoPopup");
        const frame = document.getElementById("videoPopupFrame");

        popup.style.display = "none";
        frame.src = ""; // stop the video
    }
});
</script>


    <section class="home-courses-section section {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1">

        {{-- 1. Banner Photo Block --}}
        <div class="container is-home-courses-photo reveal delay-2">
            <h2 class="h-section-title reveal fade-blur-title delay-1">
                {{ __('home.courses.title') }}
            </h2>
        </div>

        {{-- 2. German Intensive Courses --}}
        <div class="container is-h-courses reveal delay-3">

            <h2 class="h-section-subtitle-courses reveal fade-blur-title delay-1">
                {{ __('home.courses.intensive.title') }}
            </h2>

            <div class="subtitle reveal delay-2">
                {{ __('home.courses.intensive.subtitle') }}
            </div>

            <p class="paragraph-2 reveal delay-3">
                {{ __('home.courses.intensive.description') }}
            </p>

            <div class="courses-cards reveal delay-1">

                @foreach (__('home.courses.intensive.levels') as $lvl)
                    <div class="course-card {{ $lvl['color'] }} reveal delay-2">

                        <div class="couse-card_level reveal delay-3">
                            <div class="course-card_level-circle reveal delay-1">{{ $lvl['letter'] }}</div>
                            <div class="course-card_level-circle reveal delay-2">{{ $lvl['number'] }}</div>
                        </div>

                        <h3 class="course-card_title reveal fade-blur-title delay-1">
                            {!! $lvl['title'] !!}
                        </h3>

                        <div class="course-card_text reveal delay-2">
                            {{ $lvl['text'] }}
                        </div>

                        <a href="#" class="button is-course-card w-button reveal delay-3">
                            {{ __('home.courses.buttons.learn_more') }}
                        </a>

                    </div>
                @endforeach

            </div>
        </div>

        {{-- 3. Online Courses + Exams --}}
        <div class="container is-h-courses reveal delay-2">

            <h2 class="h-section-subtitle-courses reveal fade-blur-title delay-1">
                {{ __('home.courses.online.title') }}
            </h2>

            <div class="subtitle reveal delay-2">
                {{ __('home.courses.online.subtitle') }}
            </div>

            <div class="courses-cards is-home-other-german-courses reveal delay-3">

                @foreach (__('home.courses.online.items') as $item)
                    <div class="course-card {{ $item['color'] }} reveal delay-1">

                        <h3 class="course-card_title is-others reveal fade-blur-title delay-1">
                            {!! $item['title'] !!}
                        </h3>

                        <div class="course-card_text reveal delay-2">
                            {{ $item['text'] }}
                        </div>

                        <a href="#" class="button is-course-card w-button reveal delay-3">
                            {{ $item['button'] }}
                        </a>

                    </div>
                @endforeach

            </div>
        </div>

    </section>

    {{-- ===========================
 LEARN MORE SECTION
=========================== --}}
    <section class="learn-more-section py-5 text-light {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1"
        style="background-color: var(--off-black);">

        <div class="container py-5 reveal delay-2">
            <div class="row align-items-center g-5 reveal delay-3">

                {{-- LEFT TEXT --}}
                <div class="col-lg-6 reveal delay-1">
                    <h2 class="fw-bold mb-4 learn-more-title reveal fade-blur-title delay-1">
                        {!! __('home.learn_more.title') !!}
                    </h2>

                    <p class="lead opacity-75 mb-0 reveal delay-2">
                        {{ __('home.learn_more.description') }}
                    </p>
                </div>

                {{-- RIGHT CARDS --}}
                <div class="col-lg-6 reveal delay-3">
                    <div class="row g-4 reveal delay-1">

                        @foreach (__('home.learn_more.cards') as $card)
                            <div class="col-md-6 reveal delay-2">
                                <a href="{{ $card['link'] }}" class="h-learn-more-card reveal delay-3">

                                    <div class="h-learn-more-card_icon reveal delay-1">
                                        {!! $card['icon'] !!}
                                    </div>

                                    <div
                                        class="learn-card-bottom d-flex align-items-center justify-content-between w-100 reveal delay-2">
                                        <h3 class="fw-bold fs-4 mb-0 reveal fade-blur-title delay-1">
                                            {!! $card['title'] !!}</h3>

                                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                            fill="currentColor" viewBox="0 0 20 20" class="reveal delay-3">
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
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen loading="lazy"
                class="reveal delay-2"></iframe>
        </div>

    </div>
</section>


{{-- ===============================
 COOPERATION PARTNERS – Auto Marquee
================================ --}}
<section class="partners-section text-center reveal delay-1" aria-label="Our Cooperation Partners">
    <div class="container reveal delay-2">

        <h2 class="partners-title reveal fade-blur-title delay-1">Our Cooperation Partners</h2>

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
                <img src="{{ asset('assets/images/home/TLScontact_main.webp') }}" alt="TLScontact" loading="lazy">

                {{-- ——— Set B ——— --}}
                <img src="{{ asset('assets/images/home/goethe.png') }}" alt="Goethe-Institut" aria-hidden="true" loading="lazy">
                <img src="{{ asset('assets/images/home/marokkofc.png') }}" alt="Marokko FC" aria-hidden="true" loading="lazy">
                <img src="{{ asset('assets/images/home/osd.png') }}" alt="ÖSD Exam" aria-hidden="true" loading="lazy">
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

            <h2 class="h-section-subtitle reveal fade-blur-title delay-1">
                {!! __('home.contact.title') !!}
            </h2>

            <div class="div-block-21 reveal delay-2">

                <a href="tel:+212669515019" class="link-block reveal delay-1">
                    <div class="text-block-3 reveal delay-2">
                        <span class="text-span reveal delay-3">{!! __('home.contact.call_label') !!}<br></span>
                        +212 6 69 51 50 19
                    </div>
                </a>

                <a href="mailto:info@glssprachenzentrum.ma" class="link-block-2 reveal delay-3">
                    <div class="text-block-3 reveal delay-1">
                        <span class="text-span reveal delay-2">{!! __('home.contact.email_label') !!}<br></span>
                        info@glssprachenzentrum.ma
                    </div>
                </a>

            </div>

            <div class="text-block-3 visit-block reveal delay-3">
                <span class="text-span reveal delay-1">{!! __('home.contact.visit_label') !!}</span><br>
                {!! __('home.contact.addresses') !!}
            </div>

            <div class="footer-socials-block reveal delay-1">

                <div class="text-block-3 reveal delay-2">
                    <span class="text-span reveal delay-3">{!! __('home.contact.follow_label') !!}</span>
                </div>

                <div class="div-block-20 reveal delay-1">
                    <a href="#" class="footer-social-link ig reveal delay-2"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="footer-social-link fb reveal delay-3"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="footer-social-link yt reveal delay-1"><i class="bi bi-youtube"></i></a>
                    <a href="#" class="footer-social-link wa reveal delay-2"><i class="bi bi-whatsapp"></i></a>
                </div>

            </div>

        </div>

        {{-- RIGHT SIDE: MAP --}}
        <a href="https://maps.app.goo.gl/g4PjrPB7wHQAqrSZA" target="_blank" class="div-block-7 reveal delay-3">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3331.744621379457!2d-6.836039!3d33.978558!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda76b6d63b66b1d%3A0x3c6ee0a64f273aa2!2sAgdal%2C%20Rabat!5e0!3m2!1sen!2sma!4v1700000000000"
                allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                class="reveal delay-1"></iframe>
        </a>

    </div>
</section>

@endsection
