@extends('frontoffice.layouts.app')

@section('title', __('osd.meta.title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/exam/osd.css') }}">

@section('content')

<!-- ============================
     HERO
============================= -->
<section class="hero-section section about-hero">
    <div class="container is-hero">
        <div class="hero_subtitle">{{ __('osd.hero.subtitle') }}</div>
        <h1 class="hero_title">{{ __('osd.hero.title') }}</h1>

        <div class="hero-image">
            <img 
                src="{{ asset('assets/images/about/Centre-GLS-de-langue-Allemande.jpg') }}" 
                alt="{{ __('osd.hero.alt') }}"
                class="full-image"
                loading="lazy"
            >
        </div>
    </div>
</section>

<!-- ============================
     INTRO
============================= -->
<div class="rich-text-section section">
  <div class="container">
    <div class="rich-text w-richtext">
      <p>{!! __('osd.intro.p1') !!}</p>
      <p>{!! __('osd.intro.p2') !!}</p>
      <p>{!! __('osd.intro.p3') !!}</p>
      <p><strong>{!! __('osd.intro.p4') !!}</strong></p>
    </div>
  </div>
</div>

<!-- ============================
     4 STEPS
============================= -->
<section class="gls-more-info section">
    <div class="container gls-more-info-container">

        <h2 class="h-section-subtitle gls-more-info-title">
            {{ __('osd.path.title') }}
        </h2>

        <div class="gls-more-info-grid">

            <!-- CARD 1 -->
            <div class="gls-info-card">
                <div class="gls-info-icon">@include('frontoffice.svg.info-arrow')</div>
                <h3 class="gls-info-title">{!! __('osd.path.card1.title') !!}</h3>
                <div class="gls-info-text">{!! __('osd.path.card1.text') !!}</div>
                <div class="gls-info-spacer"></div>
                <a href="/courses" class="gls-info-button w-button">{{ __('osd.path.card1.button') }}</a>
            </div>

            <!-- CARD 2 -->
            <div class="gls-info-card">
                <div class="gls-info-icon">@include('frontoffice.svg.info-arrow')</div>
                <h3 class="gls-info-title">{!! __('osd.path.card2.title') !!}</h3>
                <div class="gls-info-text">{!! __('osd.path.card2.text') !!}</div>
                <div class="gls-info-spacer"></div>
                <a href="/exams/osd" class="gls-info-button w-button">{{ __('osd.path.card2.button') }}</a>
            </div>

            <!-- CARD 3 -->
            <div class="gls-info-card">
                <div class="gls-info-icon">@include('frontoffice.svg.info-arrow')</div>
                <h3 class="gls-info-title">{!! __('osd.path.card3.title') !!}</h3>
                <div class="gls-info-text">{!! __('osd.path.card3.text') !!}</div>
                <div class="gls-info-spacer"></div>
                <a href="/exams/osd#dates" class="gls-info-button w-button">{{ __('osd.path.card3.button') }}</a>
            </div>

            <!-- CARD 4 -->
            <div class="gls-info-card">
                <div class="gls-info-icon">@include('frontoffice.svg.info-arrow')</div>
                <h3 class="gls-info-title">{!! __('osd.path.card4.title') !!}</h3>
                <div class="gls-info-text">{!! __('osd.path.card4.text') !!}</div>
                <div class="gls-info-spacer"></div>
                <a href="/exams/osd" class="gls-info-button w-button">{{ __('osd.path.card4.button') }}</a>
            </div>

        </div>
    </div>
</section>

<!-- ============================
     LEVEL DETAILS
============================= -->
<div class="rich-text-section section">
  <div class="container">
    <div class="rich-text w-richtext">

      <h2>{{ __('osd.levels.title1') }}</h2>
      <p>{!! __('osd.levels.text1') !!}</p>
      <p>{!! __('osd.levels.text2') !!}</p>

      <h2>{{ __('osd.levels.title2') }}</h2>

      <h3>{{ __('osd.levels.a1_listen') }}</h3>
      <p>{!! __('osd.levels.a1_listen_text') !!}</p>

      <h3>{{ __('osd.levels.a1_grammar') }}</h3>
      <p>{!! __('osd.levels.a1_grammar_text') !!}</p>

      <h3>{{ __('osd.levels.a1_read') }}</h3>
      <p>{!! __('osd.levels.a1_read_text') !!}</p>

      <h3>{{ __('osd.levels.a1_write') }}</h3>
      <p>{!! __('osd.levels.a1_write_text') !!}</p>

      <h2>{{ __('osd.levels.title3') }}</h2>

      <h3>{{ __('osd.levels.gls_to_osd') }}</h3>
      <p>{!! __('osd.levels.gls_to_osd_text1') !!}</p>
      <p>{!! __('osd.levels.gls_to_osd_text2') !!}</p>

    </div>
  </div>
</div>

<!-- ============================
     EXAM CARDS
============================= -->
<div class="courses-section section">
  <div class="container is-h-courses">

    <h2 class="h-section-subtitle">{{ __('osd.exams.title') }}</h2>
    <div class="subtitle">{{ __('osd.exams.subtitle') }}</div>

    <div class="exam-cards">

      <div class="exam-card">
        <h3 class="course-card_title">{{ __('osd.exams.card1.title') }}</h3>
        <div class="course-card_text">{!! __('osd.exams.card1.text') !!}</div>
        <a href="/exams/osd" class="button is-course-card w-button">{{ __('osd.exams.card1.button') }}</a>
      </div>

      <div class="exam-card is-orange">
        <h3 class="course-card_title">{{ __('osd.exams.card2.title') }}</h3>
        <div class="course-card_text">{!! __('osd.exams.card2.text') !!}</div>
        <a href="#" class="button is-course-card w-button">{{ __('osd.exams.card2.button') }}</a>
      </div>

      <div class="exam-card is-yellow">
        <h3 class="course-card_title">{{ __('osd.exams.card3.title') }}</h3>
        <div class="course-card_text">{!! __('osd.exams.card3.text') !!}</div>
        <a href="/placement-test" class="button is-course-card w-button">{{ __('osd.exams.card3.button') }}</a>
      </div>

    </div>

  </div>
</div>

<!-- ============================
     CONTACT SECTION (FULL, NO INCLUDE)
============================= -->
<section class="contact-section section">
    <div class="container is-2-col-grid">

        <!-- LEFT -->
        <div class="div-block-5-copy">

            <h2 class="h-section-subtitle-contact">
                {{ __('contact.title') }}
            </h2>

            <div class="div-block-21">
                <a href="tel:+212669515019" class="link-block">
                    <div class="text-block-3">
                        <span class="text-span">{{ __('contact.call') }}<br></span>+212 6 69 51 50 19
                    </div>
                </a>

                <a href="mailto:info@glssprachenzentrum.ma" class="link-block-2">
                    <div class="text-block-3">
                        <span class="text-span">{{ __('contact.email') }}<br></span>info@glssprachenzentrum.ma
                    </div>
                </a>
            </div>

            <div class="text-block-3 visit-block">
                <span class="text-span">{{ __('contact.visit') }}<br></span>
                14 Bd de Paris, 1er étage N°8, Casablanca 20000<br>
                Avenue Yacoub El Mansour, 3ème étage Bureau 28, Marrakech<br>
                Avenue Fal Ould Oumeir, 1er étage N°1, Agdal, Rabat<br>
                Avenue Mohammed V, 7ème étage, Kénitra<br>
                Avenue Mohamed V Rue Halima N°12 Diyar, Salé<br>
                Av. Massoude Al Wafkaoui, Hay Essalam, Agadir
            </div>

            <div class="footer-socials-block">
                <div class="text-block-3"><span class="text-span">{{ __('contact.follow') }}</span></div>
                <div class="div-block-20">
                    <a href="#" class="footer-social-link ig"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="footer-social-link fb"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="footer-social-link yt"><i class="bi bi-youtube"></i></a>
                    <a href="#" class="footer-social-link wa"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>
        </div>

        <!-- RIGHT MAP -->
        <a href="https://maps.app.goo.gl/g4PjrPB7wHQAqrSZA" target="_blank" class="div-block-7">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3331.744621379457!2d-6.836039!3d33.978558!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda76b6d63b66b1d%3A0x3c6ee0a64f273aa2!2sAgdal%2C%20Rabat!5e0!3m2!1sen!2sma!4v1700000000000"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </a>

    </div>
</section>

@endsection
