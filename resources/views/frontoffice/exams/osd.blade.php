@extends('frontoffice.layouts.app')

@section('title', __('osd.meta.title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/exam/osd.css') }}">

@section('content')

    <!-- ============================
                                     HERO
                                ============================= -->
    <section class="hero-section section about-hero reveal delay-1">
        <div class="container is-hero reveal delay-2">
            <div class="hero_subtitle reveal delay-1">{{ __('osd.hero.subtitle') }}</div>

            <h1 class="hero_title fade-blur-title reveal delay-2">
                {{ __('osd.hero.title') }}
            </h1>

            <div class="hero-image reveal delay-3">
                <img src="{{ asset('assets/images/about/Centre-GLS-de-langue-Allemande.jpg') }}"
                    alt="{{ __('osd.hero.alt') }}" class="full-image reveal delay-1" loading="lazy">
            </div>
        </div>
    </section>

    <!-- ============================
                                     INTRO
                                ============================= -->
    <div class="rich-text-section section reveal delay-1">
        <div class="container reveal delay-2">
            <div class="rich-text w-richtext reveal delay-3">
                <p class="reveal delay-1">{!! __('osd.intro.p1') !!}</p>
                <p class="reveal delay-2">{!! __('osd.intro.p2') !!}</p>
                <p class="reveal delay-3">{!! __('osd.intro.p3') !!}</p>
                <p class="reveal delay-1"><strong>{!! __('osd.intro.p4') !!}</strong></p>
            </div>
        </div>
    </div>

    <!-- ============================
                                     4 STEPS
                                ============================= -->
    <section class="gls-more-info section reveal delay-1">
        <div class="container gls-more-info-container reveal delay-2">

            <h2 class="h-section-subtitle gls-more-info-title fade-blur-title reveal delay-1">
                {{ __('osd.path.title') }}
            </h2>

            <div class="gls-more-info-grid">

                <!-- CARD 1 -->
                <div class="gls-info-card reveal delay-1">
                    <div class="gls-info-icon">@include('frontoffice.svg.info-arrow')</div>
                    <h3 class="gls-info-title">{!! __('osd.path.card1.title') !!}</h3>
                    <div class="gls-info-text">{!! __('osd.path.card1.text') !!}</div>
                    <div class="gls-info-spacer"></div>
                    <a href="#" class="gls-info-button w-button">{{ __('osd.path.card1.button') }}</a>
                </div>

                <!-- CARD 2 -->
                <div class="gls-info-card reveal delay-2">
                    <div class="gls-info-icon">@include('frontoffice.svg.info-arrow')</div>
                    <h3 class="gls-info-title">{!! __('osd.path.card2.title') !!}</h3>
                    <div class="gls-info-text">{!! __('osd.path.card2.text') !!}</div>
                    <div class="gls-info-spacer"></div>
                    <a href="#" class="gls-info-button w-button">{{ __('osd.path.card2.button') }}</a>
                </div>

                <!-- CARD 3 -->
                <div class="gls-info-card reveal delay-3">
                    <div class="gls-info-icon">@include('frontoffice.svg.info-arrow')</div>
                    <h3 class="gls-info-title">{!! __('osd.path.card3.title') !!}</h3>
                    <div class="gls-info-text">{!! __('osd.path.card3.text') !!}</div>
                    <div class="gls-info-spacer"></div>
                    <a href="#" class="gls-info-button w-button">{{ __('osd.path.card3.button') }}</a>
                </div>

                <!-- CARD 4 -->
                <div class="gls-info-card reveal delay-1">
                    <div class="gls-info-icon">@include('frontoffice.svg.info-arrow')</div>
                    <h3 class="gls-info-title">{!! __('osd.path.card4.title') !!}</h3>
                    <div class="gls-info-text">{!! __('osd.path.card4.text') !!}</div>
                    <div class="gls-info-spacer"></div>
                    <a href="#" class="gls-info-button w-button">{{ __('osd.path.card4.button') }}</a>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================
                                     LEVEL DETAILS
                                ============================= -->
    <div class="rich-text-section section reveal delay-1">
        <div class="container reveal delay-2">
            <div class="rich-text w-richtext reveal delay-3">

                <h2 class="fade-blur-title reveal delay-1">{{ __('osd.levels.title1') }}</h2>
                <p class="reveal delay-1">{!! __('osd.levels.text1') !!}</p>
                <p class="reveal delay-2">{!! __('osd.levels.text2') !!}</p>

                <h2 class="fade-blur-title reveal delay-1">{{ __('osd.levels.title2') }}</h2>

                <h3 class="reveal delay-1">{{ __('osd.levels.a1_listen') }}</h3>
                <p class="reveal delay-1">{!! __('osd.levels.a1_listen_text') !!}</p>

                <h3 class="reveal delay-2">{{ __('osd.levels.a1_grammar') }}</h3>
                <p class="reveal delay-2">{!! __('osd.levels.a1_grammar_text') !!}</p>

                <h3 class="reveal delay-3">{{ __('osd.levels.a1_read') }}</h3>
                <p class="reveal delay-3">{!! __('osd.levels.a1_read_text') !!}</p>

                <h3 class="reveal delay-1">{{ __('osd.levels.a1_write') }}</h3>
                <p class="reveal delay-1">{!! __('osd.levels.a1_write_text') !!}</p>

                <h2 class="fade-blur-title reveal delay-2">{{ __('osd.levels.title3') }}</h2>

                <h3 class="reveal delay-1">{{ __('osd.levels.gls_to_osd') }}</h3>
                <p class="reveal delay-2">{!! __('osd.levels.gls_to_osd_text1') !!}</p>
                <p class="reveal delay-3">{!! __('osd.levels.gls_to_osd_text2') !!}</p>

            </div>
        </div>
    </div>

    <!-- ============================
                                     EXAM CARDS
                                ============================= -->
    <div class="courses-section section reveal delay-1">
        <div class="container is-h-courses reveal delay-2">

            <h2 class="h-section-subtitle fade-blur-title reveal delay-1">
                {{ __('osd.exams.title') }}
            </h2>

            <div class="subtitle reveal delay-2">
                {{ __('osd.exams.subtitle') }}
            </div>

            <div class="exam-cards">

                {{-- CARD 1 --}}
                <div class="exam-card reveal delay-1">
                    <h3 class="course-card_title">{{ __('osd.exams.card1.title') }}</h3>
                    <div class="course-card_text">{!! __('osd.exams.card1.text') !!}</div>

                    <a href="{{ LaravelLocalization::localizeUrl(route(__('osd.exams.card1.route'))) }}"
                        class="button is-course-card w-button">
                        {{ __('osd.exams.card1.button') }}
                    </a>
                </div>

                {{-- CARD 2 --}}
                <div class="exam-card is-orange reveal delay-2">
                    <h3 class="course-card_title">{{ __('osd.exams.card2.title') }}</h3>
                    <div class="course-card_text">{!! __('osd.exams.card2.text') !!}</div>

                    <a href="{{ LaravelLocalization::localizeUrl(route(__('osd.exams.card2.route'))) }}"
                        class="button is-course-card w-button">
                        {{ __('osd.exams.card2.button') }}
                    </a>
                </div>

                {{-- CARD 3 --}}
                <div class="exam-card is-yellow reveal delay-3">
                    <h3 class="course-card_title">{{ __('osd.exams.card3.title') }}</h3>
                    <div class="course-card_text">{!! __('osd.exams.card3.text') !!}</div>

                    <a href="{{ LaravelLocalization::localizeUrl(route(__('osd.exams.card3.route'))) }}"
                        class="button is-course-card w-button">
                        {{ __('osd.exams.card3.button') }}
                    </a>
                </div>

            </div>

        </div>
    </div>

    <!-- ============================
                                     CONTACT SECTION
                                ============================= -->
    <section class="contact-section section reveal delay-1">
        <div class="container is-2-col-grid reveal delay-2">

            <!-- LEFT -->
            <div class="div-block-5-copy reveal delay-1">

                <h2 class="h-section-subtitle-contact fade-blur-title reveal delay-1">
                    {{ __('osd.contact.title') }}
                </h2>

                <div class="div-block-21">
                    <div class="link-block reveal delay-1">
                        <div class="text-block-3">
                            <span class="text-span">{{ __('osd.contact.call') }}<br></span>
                            <a href="tel:+212669515019">+212 6 69 51 50 19</a><br>
                            <a href="tel:+212537372003">+212 5 37 37 20 03</a>
                        </div>
                    </div>

                    <a href="mailto:info@gls-sprachzentrum.ma" class="link-block-2 reveal delay-2">
                        <div class="text-block-3">
                            <span class="text-span">{{ __('osd.contact.email') }}<br></span>info@gls-sprachzentrum.ma
                        </div>
                    </a>
                </div>

                <div class="text-block-3 visit-block reveal delay-3">
                    <span class="text-span">{{ __('osd.contact.visit') }}<br></span>
                    @include('frontoffice.partials.gls-centers-links')
                </div>

                <div class="footer-socials-block reveal delay-1">
                    <div class="text-block-3"><span class="text-span">{{ __('osd.contact.follow') }}</span></div>
                    <div class="div-block-20">
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

            <!-- RIGHT MAP -->
            <a href="https://maps.app.goo.gl/g4PjrPB7wHQAqrSZA" target="_blank" class="div-block-7 reveal delay-2">
                <iframe src="{{ __('osd.contact.map_url') }}" loading="lazy" allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </a>

        </div>
    </section>


@endsection
