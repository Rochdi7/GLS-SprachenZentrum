@extends('frontoffice.layouts.app')

@section('title', __('legal/terms.title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/legal/terms.css') }}">

@section('content')

    <!-- ============================
                                 HERO
                            ============================= -->
    <section class="hero-section section is-no-image reveal delay-1">
        <div class="container is-hero reveal delay-2">
            <h1 class="hero_title fade-blur-title reveal delay-1">
                {{ __('legal/terms.hero.title') }}
            </h1>
            <div class="hero_subtitle reveal delay-2">
                {!! __('legal/terms.hero.subtitle') !!}
            </div>
        </div>
    </section>

    <!-- ============================
                                 TERMS CONTENT
                            ============================= -->
    <div class="rich-text-section section">
        <div class="container">
            <div class="legal-rich-text w-richtext">

                <h2><strong>{{ __('legal/terms.content.title') }}</strong></h2>

                <ol role="list">
                    <li><strong>{{ __('legal/terms.content.s1_title') }}</strong></li>
                </ol>
                <p>{!! __('legal/terms.content.s1_text') !!}</p>

                <ol start="2" role="list">
                    <li><strong>{{ __('legal/terms.content.s2_title') }}</strong></li>
                </ol>
                <p>{!! __('legal/terms.content.s2_text_1') !!}</p>
                <p>{!! __('legal/terms.content.s2_text_2') !!}</p>
                <p>{!! __('legal/terms.content.s2_text_3') !!}</p>

                <ol start="3" role="list">
                    <li><strong>{{ __('legal/terms.content.s3_title') }}</strong></li>
                </ol>
                <p>{!! __('legal/terms.content.s3_text') !!}</p>

                <ol start="4" role="list">
                    <li><strong>{{ __('legal/terms.content.s4_title') }}</strong></li>
                </ol>
                <p>{!! __('legal/terms.content.s4_text_1') !!}</p>
                <p>{!! __('legal/terms.content.s4_text_2') !!}</p>

                <ol start="5" role="list">
                    <li><strong>{{ __('legal/terms.content.s5_title') }}</strong></li>
                </ol>
                <p>{!! __('legal/terms.content.s5_text_1') !!}</p>
                <p>{!! __('legal/terms.content.s5_text_2') !!}</p>
                <p>{!! __('legal/terms.content.s5_text_3') !!}</p>

                <ol start="6" role="list">
                    <li><strong>{{ __('legal/terms.content.s6_title') }}</strong></li>
                </ol>
                <p>{!! __('legal/terms.content.s6_text_1') !!}</p>
                <p>{!! __('legal/terms.content.s6_text_2') !!}</p>

                <ol start="7" role="list">
                    <li><strong>{{ __('legal/terms.content.s7_title') }}</strong></li>
                </ol>
                <p>{!! __('legal/terms.content.s7_text') !!}</p>

                <ol start="8" role="list">
                    <li><strong>{{ __('legal/terms.content.s8_title') }}</strong></li>
                </ol>
                <p>{!! __('legal/terms.content.s8_text') !!}</p>

                <ol start="9" role="list">
                    <li><strong>{{ __('legal/terms.content.s9_title') }}</strong></li>
                </ol>
                <p>{!! __('legal/terms.content.s9_text') !!}</p>

                <h4><strong>{{ __('legal/terms.content.addition_title') }}</strong></h4>
                <p><strong>{{ __('legal/terms.content.preamble_title') }}</strong></p>
                <p>{!! __('legal/terms.content.preamble_text') !!}</p>

                <p><strong>1. {{ __('legal/terms.content.a1_title') }}</strong></p>
                <p>{!! __('legal/terms.content.a1_text') !!}</p>

                <p><strong>2. {{ __('legal/terms.content.a2_title') }}</strong></p>
                <p>{!! __('legal/terms.content.a2_text') !!}</p>

                <p><strong>3. {{ __('legal/terms.content.a3_title') }}</strong></p>
                <p>{!! __('legal/terms.content.a3_text') !!}</p>

                <p><strong>4. {{ __('legal/terms.content.a4_title') }}</strong></p>
                <p>{!! __('legal/terms.content.a4_text') !!}</p>

                <p><strong>5. {{ __('legal/terms.content.a5_title') }}</strong></p>
                <p>{!! __('legal/terms.content.a5_text') !!}</p>

                <p><strong>6. {{ __('legal/terms.content.a6_title') }}</strong></p>
                <p>{!! __('legal/terms.content.a6_text') !!}</p>

                <p><strong>{{ __('legal/terms.content.thanks') }}</strong></p>

            </div>
        </div>
    </div>

    <!-- =========================================================
                                 CONTACT SECTION
                            ========================================================= -->
    <section class="contact-section section reveal delay-1">
        <div class="container is-2-col-grid reveal delay-2">

            <div class="div-block-5-copy reveal delay-3">

                <h2 class="contact-section-subtitle reveal fade-blur-title delay-1">
                    {!! __('goethe.contact.title') !!}
                </h2>


                <div class="div-block-21 reveal delay-2">

                    <div class="link-block reveal delay-1">
                        <div class="text-block-3 reveal delay-2">
                            <span class="text-span reveal delay-3">{{ __('intensive.contact.call') }}<br></span>
                            <a href="tel:+212669515019">+212 6 69 51 50 19</a><br>
                            <a href="tel:+212537372003">+212 5 37 37 20 03</a>
                        </div>
                    </div>

                    <a href="mailto:info@gls-sprachzentrum.ma" class="link-block-2 reveal delay-3">
                        <div class="text-block-3 reveal delay-1">
                            <span class="text-span reveal delay-2">{{ __('intensive.contact.email') }}<br></span>
                            info@gls-sprachzentrum.ma
                        </div>
                    </a>

                </div>

                <div class="text-block-3 visit-block reveal delay-3">
                    <span class="text-span reveal delay-1">{{ __('intensive.contact.visit') }}</span><br>
                    @include('frontoffice.partials.gls-centers-links')
                </div>

                <div class="footer-socials-block reveal delay-1">
                    <div class="text-block-3 reveal delay-2">
                        <span class="text-span reveal delay-3">{{ __('intensive.contact.follow') }}</span>
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

            <a href="https://maps.app.goo.gl/g4PjrPB7wHQAqrSZA" target="_blank" class="div-block-7 reveal delay-3">
                <iframe src="{{ __('intensive.contact.map_url') }}" loading="lazy" allowfullscreen
                    class="reveal delay-1"></iframe>
            </a>

        </div>
    </section>

@endsection
