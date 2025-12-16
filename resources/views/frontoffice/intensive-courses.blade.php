@extends('frontoffice.layouts.app')

@section('title', __('intensive.meta_title'))

{{-- Page-specific stylesheet --}}
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/intensive/intensive-courses.css') }}">

@section('content')

    <!-- =========================================================
         HERO SECTION
    ========================================================= -->
    <section class="hero-section section intensive-hero reveal delay-1">
        <div class="container is-hero reveal delay-2">

            <div class="hero_subtitle reveal delay-1">
                {{ __('intensive.hero.subtitle') }}
            </div>

            <h1 class="hero_title reveal fade-blur-title delay-2">
                {{ __('intensive.hero.title') }}
            </h1>

            <div class="hero-image reveal delay-3">
                <img src="{{ asset('assets/images/intensive-courses/hero.png') }}" alt="{{ __('intensive.hero.img_alt') }}"
                    class="full-image reveal delay-1" loading="lazy">
            </div>

        </div>
    </section>

    <!-- =========================================================
         RICH TEXT SECTION
    ========================================================= -->
    <section class="rich-text-section section reveal delay-1">
        <div class="container reveal delay-2">
            <div class="rich-text w-richtext reveal delay-3">
                {!! __('intensive.rich.p1') !!}
                {!! __('intensive.rich.p2') !!}
                {!! __('intensive.rich.p3') !!}
            </div>
        </div>
    </section>

    <!-- =========================================================
         COURSES GRID
    ========================================================= -->
    <section class="home-courses-section section reveal delay-1">
        <div class="container is-h-courses reveal delay-2">

            <h2 class="h-section-subtitle-courses reveal fade-blur-title delay-1">
                {{ __('intensive.courses.section_title') }}
            </h2>

            <div class="subtitle reveal delay-2">
                {{ __('intensive.courses.subtitle') }}
            </div>

            <p class="paragraph-2 reveal delay-3">
                {{ __('intensive.courses.schedule') }}
            </p>

            <div class="courses-cards reveal delay-1">

                {{-- A1 --}}
                <div class="course-card reveal delay-2">
                    <div class="couse-card_level reveal delay-3">
                        <div class="course-card_level-circle reveal delay-1">A</div>
                        <div class="course-card_level-circle reveal delay-2">1</div>
                    </div>
                    <h3 class="course-card_title reveal fade-blur-title delay-1">{!! __('intensive.courses.a1.title') !!}</h3>
                    <div class="course-card_text reveal delay-2">
                        {!! __('intensive.courses.a1.text') !!}
                    </div>
                    <a href="{{ route('front.niveaux.a1') }}" class="button is-course-card w-button reveal delay-3">
                        {{ __('intensive.courses.learn_more') }}
                    </a>
                </div>

                {{-- A2 --}}
                <div class="course-card is-green reveal delay-3">
                    <div class="couse-card_level reveal delay-1">
                        <div class="course-card_level-circle reveal delay-2">A</div>
                        <div class="course-card_level-circle reveal delay-3">2</div>
                    </div>
                    <h3 class="course-card_title reveal fade-blur-title delay-1">{!! __('intensive.courses.a2.title') !!}</h3>
                    <div class="course-card_text reveal delay-2">
                        {!! __('intensive.courses.a2.text') !!}
                    </div>
                    <a href="{{ route('front.niveaux.a2') }}" class="button is-course-card w-button reveal delay-3">
                        {{ __('intensive.courses.learn_more') }}
                    </a>
                </div>

                {{-- B1 --}}
                <div class="course-card is-purple reveal delay-1">
                    <div class="couse-card_level reveal delay-2">
                        <div class="course-card_level-circle reveal delay-3">B</div>
                        <div class="course-card_level-circle reveal delay-1">1</div>
                    </div>
                    <h3 class="course-card_title reveal fade-blur-title delay-2">{!! __('intensive.courses.b1.title') !!}</h3>
                    <div class="course-card_text reveal delay-3">
                        {!! __('intensive.courses.b1.text') !!}
                    </div>
                    <a href="{{ route('front.niveaux.b1') }}" class="button is-course-card w-button reveal delay-1">
                        {{ __('intensive.courses.learn_more') }}
                    </a>
                </div>

                {{-- B2 --}}
                <div class="course-card is-yellow reveal delay-2">
                    <div class="couse-card_level reveal delay-3">
                        <div class="course-card_level-circle reveal delay-1">B</div>
                        <div class="course-card_level-circle reveal delay-2">2</div>
                    </div>
                    <h3 class="course-card_title reveal fade-blur-title delay-1">{!! __('intensive.courses.b2.title') !!}</h3>
                    <div class="course-card_text reveal delay-2">
                        {!! __('intensive.courses.b2.text') !!}
                    </div>
                    <a href="{{ route('front.niveaux.b2') }}" class="button is-course-card w-button reveal delay-3">
                        {{ __('intensive.courses.learn_more') }}
                    </a>
                </div>

            </div>

        </div>
    </section>

    <!-- =========================================================
         QUESTIONS + CONSULTATION SECTION
    ========================================================= -->
    <section class="rich-text-section section reveal delay-1">
        <div class="container reveal delay-2">
            <div class="rich-text w-richtext reveal delay-3">
                {!! __('intensive.questions.intro') !!}

                <ol class="reveal delay-1">
                    <li class="reveal delay-2">{!! __('intensive.questions.q1') !!}</li>
                    <li class="reveal delay-3">{!! __('intensive.questions.q2') !!}</li>
                    <li class="reveal delay-1">{!! __('intensive.questions.q3') !!}</li>
                    <li class="reveal delay-2">{!! __('intensive.questions.q4') !!}</li>
                </ol>

                {!! __('intensive.questions.outro') !!}
            </div>
        </div>
    </section>

    <!-- =========================================================
         INLINE CTA
    ========================================================= -->
    <section class="inline-cta-section my-5 reveal delay-1">
        <div class="container reveal delay-2">
            <div class="inline-cta-block mx-auto reveal delay-3">

                <h2 class="heading-4 overlay-text reveal fade-blur-title delay-1">
                    {{ __('intensive.cta.title') }}
                </h2>

                <a href="{{ route('front.contact') }}" class="button is-big is-white w-button reveal delay-2">
                    {{ __('intensive.cta.button') }}
                </a>

            </div>
        </div>
    </section>

    <!-- =========================================================
         GET STARTED
    ========================================================= -->
    <section class="get-started-section section reveal delay-1">
        <div class="container is-2-col-grid reveal delay-2">

            <div class="get-started-image reveal delay-3">
                <img src="{{ asset('assets/images/about/subscribe.jpeg') }}" alt="GLS Students"
                    class="full-image rounded-4 reveal delay-1" loading="lazy">
            </div>

            <div class="get-started-card reveal delay-2">
                <div class="box-rich-text w-richtext reveal delay-3">
                    <h2 class="reveal fade-blur-title delay-1">{{ __('intensive.start.title') }}</h2>
                    <h3 class="reveal fade-blur-title delay-1">{{ __('intensive.start.subtitle') }}</h3>

                    <p class="reveal delay-2">{!! __('intensive.start.p1') !!}</p>
                    <p class="reveal delay-3">{!! __('intensive.start.p2') !!}</p>
                    <p class="reveal delay-1">{!! __('intensive.start.p3') !!}</p>
                </div>

                <a href="#" class="button w-button reveal delay-2">
                    {{ __('intensive.start.button') }}
                </a>
            </div>

        </div>
    </section>

    <!-- =========================================================
         CONTACT SECTION
    ========================================================= -->
    <section class="contact-section section reveal delay-1">
        <div class="container is-2-col-grid reveal delay-2">

            <div class="div-block-5-copy reveal delay-3">

                <h2 class="h-section-subtitle reveal fade-blur-title delay-1">
                    {!! __('intensive.contact.title') !!}
                </h2>

                <div class="div-block-21 reveal delay-2">

                    <a href="tel:+212669515019" class="link-block reveal delay-1">
                        <div class="text-block-3 reveal delay-2">
                            <span class="text-span reveal delay-3">{{ __('intensive.contact.call') }}<br></span>
                            +212 6 69 51 50 19
                        </div>
                    </a>

                    <a href="mailto:info@glssprachenzentrum.ma" class="link-block-2 reveal delay-3">
                        <div class="text-block-3 reveal delay-1">
                            <span class="text-span reveal delay-2">{{ __('intensive.contact.email') }}<br></span>
                            info@glssprachenzentrum.ma
                        </div>
                    </a>

                </div>

                <div class="text-block-3 visit-block reveal delay-3">
                    <span class="text-span reveal delay-1">{{ __('intensive.contact.visit') }}</span><br>
                    {!! __('intensive.contact.addresses') !!}
                </div>

                <div class="footer-socials-block reveal delay-1">
                    <div class="text-block-3 reveal delay-2">
                        <span class="text-span reveal delay-3">{{ __('intensive.contact.follow') }}</span>
                    </div>

                    <div class="div-block-20 reveal delay-1">
                        <a href="#" class="footer-social-link ig reveal delay-2"><i
                                class="bi bi-instagram"></i></a>
                        <a href="#" class="footer-social-link fb reveal delay-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="footer-social-link yt reveal delay-1"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="footer-social-link wa reveal delay-2"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>

            </div>

            <a href="https://maps.app.goo.gl/g4PjrPB7wHQAqrSZA" target="_blank" class="div-block-7 reveal delay-3">
                <iframe src="{{ __('intensive.contact.map_url') }}" loading="lazy" allowfullscreen
                    class="reveal delay-1"></iframe>
            </a>

        </div>
    </section>

@endsection
