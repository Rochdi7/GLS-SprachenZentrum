@extends('frontoffice.layouts.app')

@section('title', __('niveaux/a1.title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/niveau/a1.css') }}">

@section('content')

    <section class="intensive-course-gls section">

        <div class="gls-hero-wrapper">

            <!-- LEFT IMAGE -->
            <div class="gls-hero-image">
                <img src="{{ asset('assets/images/niveaux/hero-a1.avif') }}" alt="{{ __('niveaux/a1.hero_alt') }}">
            </div>

            <!-- RIGHT CONTENT -->
            <div class="div-block-37">

                <!-- A1 BADGE -->
                <div class="div-block-39">
                    <div class="course-level-circle-copy">A</div>
                    <div class="course-level-circle-copy">1</div>
                </div>

                <!-- SUBTITLE -->
                <div class="text-block-4">
                    {{ __('niveaux/a1.subtitle') }}
                    <br>
                </div>

                <!-- TITLE -->
                <h1 class="hero_title is-course">
                    {{ __('niveaux/a1.title_main') }}
                    <br>
                    <span style="font-size:1.4rem; color:var(--light--blue); font-weight:500;">
                        {{ __('niveaux/a1.title_secondary') }}
                    </span>
                </h1>

                <!-- PARAGRAPH -->
                <p class="course-hero_paragraph">{!! __('niveaux/a1.paragraph') !!}</p>

                <!-- CTA BUTTON -->
                <a href="#" class="button w-button">
                    {{ __('niveaux/a1.btn_inscrire') }}
                </a>

            </div>

        </div>

    </section>



    <!-- ===========================================================
         AVANTAGES
    =========================================================== -->

    <div class="section is-off-white">
        <div class="container is-2-col-grid is-flipped">

            <div class="get-started-contents">

                <div class="box-rich-text w-richtext">
                    <h2>{{ __('niveaux/a1.avantages_title') }}</h2>

                    <ul role="list">
                        <li>{!! __('niveaux/a1.adv_1') !!}</li>
                        <li>{!! __('niveaux/a1.adv_2') !!}</li>
                        <li>{!! __('niveaux/a1.adv_3') !!}</li>
                        <li>{!! __('niveaux/a1.adv_4') !!}</li>
                        <li>{!! __('niveaux/a1.adv_5') !!}</li>
                        <li>{!! __('niveaux/a1.adv_6') !!}</li>
                        <li>{!! __('niveaux/a1.adv_7') !!}</li>
                    </ul>

                </div>

                <a href="#" class="button w-button">
                    {{ __('niveaux/a1.btn_inscrire') }}
                </a>

            </div>

            <!-- RIGHT IMAGE -->
            <div class="image-block">
                <img src="https://cdn.prod.website-files.com/66d00cd15406f5f0f85bbb6e/6834a395d8a1e54d6d1ab11a_die-deutschule-04.avif"
                    alt="{{ __('niveaux/a1.class_alt') }}" class="full-image">
            </div>

        </div>
    </div>

    <!-- ===========================================================
         INFO CARDS
    =========================================================== -->

    <section class="gls-info-section gls-section">

        <div class="gls-container">

            <h2 class="gls-info-title">{{ __('niveaux/a1.info_title') }}</h2>

            <!-- LEVEL SWITCHER -->
            <div class="gls-niveau-tabs">
                <button class="gls-niveau-btn active" data-level="A1">A1</button>
                <button class="gls-niveau-btn" data-level="A2">A2</button>
                <button class="gls-niveau-btn" data-level="B1">B1</button>
                <button class="gls-niveau-btn" data-level="B2">B2</button>
            </div>

            <!-- GRID -->
            <div class="gls-info-grid">

                <!-- GRADUATION -->
                <div class="gls-info-card">
                    <div class="gls-info-icon">@include('frontoffice.svg.sites-info')</div>
                    <h3 class="gls-info-card-title">{{ __('niveaux/a1.graduation') }}</h3>
                    <div class="gls-info-text" id="graduation-text"></div>
                </div>

                <!-- DURATION -->
                <div class="gls-info-card">
                    <div class="gls-info-icon">@include('frontoffice.svg.sites-duration')</div>
                    <h3 class="gls-info-card-title">{{ __('niveaux/a1.duration') }}</h3>
                    <div class="gls-info-text" id="duration-text"></div>
                </div>

                <!-- TIMES -->
                <div class="gls-info-card">
                    <div class="gls-info-icon">@include('frontoffice.svg.sites-times')</div>
                    <h3 class="gls-info-card-title">{{ __('niveaux/a1.times') }}</h3>
                    <div class="gls-info-text" id="times-text"></div>
                </div>

                <!-- PRICE -->
                <div class="gls-info-card">
                    <div class="gls-info-icon">@include('frontoffice.svg.sites-price')</div>
                    <h3 class="gls-info-card-title">{{ __('niveaux/a1.price') }}</h3>
                    <div class="gls-info-text" id="price-text"></div>
                </div>

            </div>

        </div>

    </section>

    <!-- ===========================================================
         RICH TEXT A2 – Fully Dynamic (Same Classes as A1)
    =========================================================== -->
    <section class="gls-a1-rich-section reveal delay-1">

        <div class="gls-a1-rich-container reveal delay-2">

            <div class="gls-a1-rich-text reveal delay-3">

                <!-- BLOCK 1 -->
                <h2 class="fade-blur-title reveal delay-3">
                    {{ __('niveaux/a1.rich_1_title') }}
                </h2>

                <h3 class="fade-blur-title reveal delay-1">
                    {{ __('niveaux/a1.rich_1_sub') }}
                </h3>

                <p class="reveal delay-2">
                    {!! __('niveaux/a1.rich_1_p1') !!}
                </p>

                <p class="reveal delay-3">
                    {!! __('niveaux/a1.rich_1_p2') !!}
                </p>


                <!-- BLOCK 2 -->
                <h2 class="fade-blur-title reveal delay-1">
                    {{ __('niveaux/a1.rich_2_title') }}
                </h2>

                <h3 class="fade-blur-title reveal delay-2">
                    {{ __('niveaux/a1.rich_2_sub') }}
                </h3>

                <p class="reveal delay-3">
                    {!! __('niveaux/a1.rich_2_p1') !!}
                </p>

                <p class="reveal delay-1">
                    {!! __('niveaux/a1.rich_2_p2') !!}
                </p>


                <!-- BLOCK 3 -->
                <h2 class="fade-blur-title reveal delay-2">
                    {{ __('niveaux/a1.rich_3_title') }}
                </h2>

                <p class="reveal delay-3">
                    {!! __('niveaux/a1.rich_3_p1') !!}
                </p>


                <!-- BLOCK 4 -->
                <h2 class="fade-blur-title reveal delay-1">
                    {{ __('niveaux/a1.rich_4_title') }}
                </h2>

                <p class="reveal delay-2">
                    {!! __('niveaux/a1.rich_4_p1') !!}
                </p>

                <p class="reveal delay-3">
                    {!! __('niveaux/a1.rich_4_p2') !!}
                </p>

            </div>

        </div>

    </section>


    <!-- ===========================================================
         PATH
    =========================================================== -->

    <section class="gls-path-section">

        <div class="gls-path-container">

            <h2 class="gls-path-title">{{ __('niveaux/a1.path_title') }}</h2>

            <div class="gls-path-grid">

                <!-- CARD 1 -->
                <div class="gls-path-card">
                    <div class="gls-path-icon">@include('frontoffice.svg.info-arrow')</div>
                    <h3 class="gls-path-card-title">{!! __('niveaux/a1.path_1_title') !!}</h3>
                    <div class="gls-path-text">{!! __('niveaux/a1.path_1_text') !!}</div>
                    <div class="gls-path-spacer"></div>
                    <a href="/courses" class="gls-path-button w-button">{{ __('niveaux/a1.path_1_btn') }}</a>
                </div>

                <!-- CARD 2 -->
                <div class="gls-path-card">
                    <div class="gls-path-icon">@include('frontoffice.svg.info-arrow')</div>
                    <h3 class="gls-path-card-title">{!! __('niveaux/a1.path_2_title') !!}</h3>
                    <div class="gls-path-text">{!! __('niveaux/a1.path_2_text') !!}</div>
                    <div class="gls-path-spacer"></div>
                    <a href="/exams/osd" class="gls-path-button w-button">{{ __('niveaux/a1.path_2_btn') }}</a>
                </div>

                <!-- CARD 3 -->
                <div class="gls-path-card">
                    <div class="gls-path-icon">@include('frontoffice.svg.info-arrow')</div>
                    <h3 class="gls-path-card-title">{!! __('niveaux/a1.path_3_title') !!}</h3>
                    <div class="gls-path-text">{!! __('niveaux/a1.path_3_text') !!}</div>
                    <div class="gls-path-spacer"></div>
                    <a href="/exams/osd#dates" class="gls-path-button w-button">{{ __('niveaux/a1.path_3_btn') }}</a>
                </div>

                <!-- CARD 4 -->
                <div class="gls-path-card">
                    <div class="gls-path-icon">@include('frontoffice.svg.info-arrow')</div>
                    <h3 class="gls-path-card-title">{!! __('niveaux/a1.path_4_title') !!}</h3>
                    <div class="gls-path-text">{!! __('niveaux/a1.path_4_text') !!}</div>
                    <div class="gls-path-spacer"></div>
                    <a href="/exams/osd" class="gls-path-button w-button">{{ __('niveaux/a1.path_4_btn') }}</a>
                </div>

            </div>

        </div>

    </section>



    <!-- ===========================================================
         CTA
    =========================================================== -->

    <section class="inline-cta-section section">
        <div class="inline-cta-block">

            <h2 class="heading-cta">{{ __('niveaux/a1.cta_title') }}</h2>

            <p class="cta-box-subtext">
                {{ __('niveaux/a1.cta_text') }}
            </p>

            <a href="/online-registration" class="cta-btn">
                {{ __('niveaux/a1.cta_btn') }}
            </a>
        </div>
    </section>



    <!-- ===========================================================
         DYNAMIC SCRIPT – USING TRANSLATIONS
    =========================================================== -->
    <script>
        const data = {
            A1: {
                graduation: `{!! __('niveaux/a1.data_graduation') !!}`,
                duration: `{!! __('niveaux/a1.data_duration') !!}`,
                times: `{!! __('niveaux/a1.data_times') !!}`,
                price: `{!! __('niveaux/a1.data_price') !!}`,
            },
            A2: {
                graduation: `{!! __('niveaux/a1.data_graduation') !!}`,
                duration: `{!! __('niveaux/a1.data_duration') !!}`,
                times: `{!! __('niveaux/a1.data_times') !!}`,
                price: `{!! __('niveaux/a1.data_price') !!}`,
            },
            B1: {
                graduation: `{!! __('niveaux/b1.data_graduation') !!}`,
                duration: `{!! __('niveaux/b1.data_duration') !!}`,
                times: `{!! __('niveaux/b1.data_times') !!}`,
                price: `{!! __('niveaux/b1.data_price') !!}`,
            },
            B2: {
                graduation: `{!! __('niveaux/b2.data_graduation') !!}`,
                duration: `{!! __('niveaux/b2.data_duration') !!}`,
                times: `{!! __('niveaux/b2.data_times') !!}`,
                price: `{!! __('niveaux/b2.data_price') !!}`,
            },
        };

        function updateCards(level) {
            document.getElementById("graduation-text").innerHTML = data[level].graduation;
            document.getElementById("duration-text").innerHTML = data[level].duration;
            document.getElementById("times-text").innerHTML = data[level].times;
            document.getElementById("price-text").innerHTML = data[level].price;
        }

        document.querySelectorAll(".gls-niveau-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                document.querySelectorAll(".gls-niveau-btn").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                updateCards(btn.dataset.level);
            });
        });

        updateCards("A1");
    </script>

@endsection
