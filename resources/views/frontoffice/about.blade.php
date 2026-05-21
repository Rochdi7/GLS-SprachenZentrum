@extends('frontoffice.layouts.app')

@section('title', __('about.meta.title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/about/about.css') }}">

@section('content')

    <section class="hero-section section about-hero reveal delay-1">
        <div class="container is-hero reveal">
            <div class="hero_subtitle reveal delay-2 fade-blur-title">{{ __('about.hero.subtitle') }}</div>

            <h1 class="hero_title reveal delay-3 fade-blur-title">
                {{ __('about.hero.title') }}
            </h1>

            <div class="hero-image reveal delay-2">
                <img src="{{ asset('assets/images/about/Centre-GLS-de-langue-Allemande.jpg') }}"
                    alt="{{ __('about.hero.image_alt') }}" class="full-image" loading="lazy">
            </div>
        </div>
    </section>

    <section class="rich-text-section section reveal">
        <div class="container reveal delay-1">
            <div class="rich-text w-richtext reveal delay-2">
                <h2 class="fade-blur-title">{{ __('about.intro.h2_1') }}</h2>
                <p>{!! __('about.intro.p1') !!}</p>
                <p>{!! __('about.intro.p2') !!}</p>

                <h2 class="fade-blur-title">{{ __('about.intro.h2_2') }}</h2>
                <h3 class="fade-blur-title">{{ __('about.intro.h3_1') }}</h3>
                <p>{!! __('about.intro.p3') !!}</p>
                <p>{!! __('about.intro.p4') !!}</p>
            </div>
        </div>
    </section>

    <section class="section is-off-white reveal">

        <div class="container is-2-col-grid reveal delay-1">
            <div class="get-started-contents reveal delay-2">
                <div class="box-rich-text w-richtext">
                    <h2 class="fade-blur-title">{{ __('about.grid.row1.h2') }}</h2>
                    <h3 class="fade-blur-title">{{ __('about.grid.row1.h3') }}</h3>
                    <p>{!! __('about.grid.row1.p1') !!}</p>
                    <p>{!! __('about.grid.row1.p2') !!}</p>

                </div>
            </div>

            <div class="image-block reveal delay-3">
                <img src="{{ asset('assets/images/about/grid-1.png') }}" alt="{{ __('about.grid.row1.image_alt') }}"
                    class="full-image" loading="lazy">
            </div>
        </div>

        <div class="container is-2-col-grid reveal delay-1">
            <div class="image-block is-1-1 reveal delay-2">
                <img src="{{ asset('assets/images/about/grid1.webp') }}" alt="{{ __('about.grid.row2.image_alt') }}"
                    class="full-image is-ratio" loading="lazy">
            </div>

            <div class="get-started-contents reveal delay-3">
                <div class="box-rich-text w-richtext">
                    <h2 class="fade-blur-title">{{ __('about.grid.row2.h2') }}</h2>
                    <p>{!! __('about.grid.row2.p1') !!}</p>
                    <p>{!! __('about.grid.row2.p2') !!}</p>
                </div>
            </div>
        </div>

    </section>

    <section class="rich-text-section section reveal">
        <div class="container reveal delay-1">
            <div class="rich-text w-richtext reveal delay-2">
                <h2 class="fade-blur-title">{{ __('about.exams.h2') }}</h2>
                <h3 class="fade-blur-title">{{ __('about.exams.h3') }}</h3>
                <p>{!! __('about.exams.p1') !!}</p>
                <p>{!! __('about.exams.p2') !!}</p>
                <p>{!! __('about.exams.p3') !!}</p>
                <p>{!! __('about.exams.p4') !!}</p>
            </div>
        </div>
    </section>

    <section class="get-started-section section reveal">
        <div class="container is-2-col-grid reveal delay-1">

            <div class="get-started-image reveal delay-2">
                <img src="{{ asset('assets/images/about/ctagls.jpg') }}" alt="{{ __('about.cta.image_alt') }}"
                    class="full-image rounded-4" loading="lazy">
            </div>

            <div class="get-started-card reveal delay-3">
                <div class="box-rich-text w-richtext">
                    <h2 class="fade-blur-title">{{ __('about.cta.h2') }}</h2>
                    <h3 class="fade-blur-title">{{ __('about.cta.h3') }}</h3>
                    <p>{!! __('about.cta.p1') !!}</p>
                    <p>{!! __('about.cta.p2') !!}</p>
                    <p>{!! __('about.cta.p3') !!}</p>
                </div>

                <a href="{{ route('front.intensive-courses') }}" class="button w-button">
                    {{ __('about.cta.button') }}
                </a>
            </div>
        </div>
    </section>

    <section class="contact-section section reveal">
        <div class="container is-2-col-grid reveal delay-1">

            <div class="div-block-5-copy reveal delay-2">
                <h2 class="h-section-subtitle fade-blur-title">{!! __('about.contact.title') !!}</h2>

                <div class="div-block-21">
                    <a href="tel:+212669515019" class="link-block">
                        <div class="text-block-3">
                            <span class="text-span">{{ __('about.contact.call') }}<br></span>+212 6 69 51 50 19
                        </div>
                    </a>
                    <a href="mailto:info@gls-sprachzentrum.ma" class="link-block-2">
                        <div class="text-block-3">
                            <span class="text-span">{{ __('about.contact.email') }}<br></span>info@gls-sprachzentrum.ma
                        </div>
                    </a>
                </div>

                <div class="text-block-3 visit-block">
                    <span class="text-span">{{ __('about.contact.visit') }}<br></span>
                    {!! __('about.contact.addresses') !!}
                </div>

                <div class="footer-socials-block">
                    <div class="text-block-3"><span class="text-span">{{ __('about.contact.follow') }}</span></div>
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

                    </div>
                </div>
            </div>

            <a href="https://maps.app.goo.gl/g4PjrPB7wHQAqrSZA" target="_blank" class="div-block-7 reveal delay-3">
                <iframe src="{{ __('about.contact.map_url') }}" allowfullscreen loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </a>

        </div>
    </section>

@endsection
