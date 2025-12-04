@extends('frontoffice.layouts.app')

@section('title', __('niveaux/a2.title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/niveau/a1.css') }}">

@section('content')

<section class="intensive-course-gls section reveal delay-1">

    <div class="gls-hero-wrapper reveal delay-1">

        <!-- LEFT IMAGE -->
        <div class="gls-hero-image reveal delay-1">
            <img src="{{ asset('assets/images/niveaux/hero-a2.avif') }}" 
                 alt="{{ __('niveaux/a2.hero_alt') }}"
                 class="reveal delay-2">
        </div>

        <!-- RIGHT CONTENT -->
        <div class="div-block-37 reveal delay-2">

            <!-- A2 BADGE -->
            <div class="div-block-39 reveal delay-1">
                <div class="course-level-circle-copy reveal delay-2">A</div>
                <div class="course-level-circle-copy reveal delay-3">2</div>
            </div>

            <!-- SUBTITLE -->
            <div class="text-block-4 reveal delay-2">
                {!! __('niveaux/a2.subtitle') !!}
                <br>
            </div>

            <!-- TITLE -->
            <h1 class="hero_title is-course fade-blur-title reveal delay-3">
                {!! __('niveaux/a2.title_main') !!}
                <br>
                <span style="font-size:1.4rem; color:var(--light--blue); font-weight:500;" class="reveal delay-3">
                    {!! __('niveaux/a2.title_secondary') !!}
                </span>
            </h1>

            <!-- PARAGRAPH -->
            <p class="course-hero_paragraph reveal delay-2">{!! __('niveaux/a2.paragraph') !!}</p>

            <!-- CTA BUTTON -->
            <a href="#" class="button w-button reveal delay-3">
                {{ __('niveaux/a2.btn_inscrire') }}
            </a>

        </div>

    </div>

</section>



<!-- ===========================================================
     AVANTAGES
=========================================================== -->
<div class="section is-off-white reveal delay-1">
    <div class="container is-2-col-grid is-flipped reveal delay-2">

        <!-- LEFT TEXT -->
        <div class="get-started-contents reveal delay-1">

            <div class="box-rich-text w-richtext reveal delay-2">
                <h2 class="fade-blur-title reveal delay-3">{{ __('niveaux/a2.avantages_title') }}</h2>

                <ul role="list" class="reveal delay-1">
                    <li class="reveal delay-2">{!! __('niveaux/a2.adv_1') !!}</li>
                    <li class="reveal delay-3">{!! __('niveaux/a2.adv_2') !!}</li>
                    <li class="reveal delay-1">{!! __('niveaux/a2.adv_3') !!}</li>
                    <li class="reveal delay-2">{!! __('niveaux/a2.adv_4') !!}</li>
                    <li class="reveal delay-3">{!! __('niveaux/a2.adv_5') !!}</li>
                    <li class="reveal delay-1">{!! __('niveaux/a2.adv_6') !!}</li>
                    <li class="reveal delay-2">{!! __('niveaux/a2.adv_7') !!}</li>
                </ul>

            </div>

            <a href="#" class="button w-button reveal delay-3">
                {{ __('niveaux/a2.btn_inscrire') }}
            </a>
        </div>

        <!-- RIGHT IMAGE -->
        <div class="image-block reveal delay-2">
            <img src="https://cdn.prod.website-files.com/66d00cd15406f5f0f85bbb6e/6834a395674579c82be0c154_die-deutschule-06.avif"
                 alt="{{ __('niveaux/a2.class_alt') }}"
                 class="full-image reveal delay-3">
        </div>

    </div>
</div>



<!-- ===========================================================
     INFO CARDS
=========================================================== -->
<section class="gls-info-section gls-section reveal delay-1">

    <div class="gls-container reveal delay-2">

        <h2 class="gls-info-title fade-blur-title reveal delay-3">{{ __('niveaux/a2.info_title') }}</h2>

        <!-- LEVEL SWITCHER -->
        <div class="gls-niveau-tabs reveal delay-1">
            <button class="gls-niveau-btn reveal delay-1" data-level="A1">A1</button>
            <button class="gls-niveau-btn active reveal delay-2" data-level="A2">A2</button>
            <button class="gls-niveau-btn reveal delay-3" data-level="B1">B1</button>
            <button class="gls-niveau-btn reveal delay-1" data-level="B2">B2</button>
        </div>

        <!-- INFO GRID -->
        <div class="gls-info-grid reveal delay-2">

            <div class="gls-info-card reveal delay-1">
                <div class="gls-info-icon reveal delay-2">@include('frontoffice.svg.sites-info')</div>
                <h3 class="gls-info-card-title fade-blur-title reveal delay-3">{{ __('niveaux/a2.graduation') }}</h3>
                <div class="gls-info-text reveal delay-1" id="graduation-text"></div>
            </div>

            <div class="gls-info-card reveal delay-2">
                <div class="gls-info-icon reveal delay-3">@include('frontoffice.svg.sites-duration')</div>
                <h3 class="gls-info-card-title fade-blur-title reveal delay-1">{{ __('niveaux/a2.duration') }}</h3>
                <div class="gls-info-text reveal delay-2" id="duration-text"></div>
            </div>

            <div class="gls-info-card reveal delay-3">
                <div class="gls-info-icon reveal delay-1">@include('frontoffice.svg.sites-times')</div>
                <h3 class="gls-info-card-title fade-blur-title reveal delay-2">{{ __('niveaux/a2.times') }}</h3>
                <div class="gls-info-text reveal delay-3" id="times-text"></div>
            </div>

            <div class="gls-info-card reveal delay-1">
                <div class="gls-info-icon reveal delay-2">@include('frontoffice.svg.sites-price')</div>
                <h3 class="gls-info-card-title fade-blur-title reveal delay-3">{{ __('niveaux/a2.price') }}</h3>
                <div class="gls-info-text reveal delay-1" id="price-text"></div>
            </div>

        </div>

    </div>

</section>

<!-- ===========================================================
     RICH TEXT A2 – Fully Dynamic
=========================================================== -->
<section class="gls-a1-rich-section reveal delay-1">

        <div class="gls-a1-rich-container reveal delay-2">

            <div class="gls-a1-rich-text reveal delay-3">

                <!-- BLOCK 1 -->
                <h2 class="fade-blur-title reveal delay-3">
                    {{ __('niveaux/a2.rich_1_title') }}
                </h2>

                <h3 class="fade-blur-title reveal delay-1">
                    {{ __('niveaux/a2.rich_1_sub') }}
                </h3>

                <p class="reveal delay-2">
                    {!! __('niveaux/a2.rich_1_p1') !!}
                </p>

                <p class="reveal delay-3">
                    {!! __('niveaux/a2.rich_1_p2') !!}
                </p>


                <!-- BLOCK 2 -->
                <h2 class="fade-blur-title reveal delay-1">
                    {{ __('niveaux/a2.rich_2_title') }}
                </h2>

                <h3 class="fade-blur-title reveal delay-2">
                    {{ __('niveaux/a2.rich_2_sub') }}
                </h3>

                <p class="reveal delay-3">
                    {!! __('niveaux/a2.rich_2_p1') !!}
                </p>

                <p class="reveal delay-1">
                    {!! __('niveaux/a2.rich_2_p2') !!}
                </p>


                <!-- BLOCK 3 -->
                <h2 class="fade-blur-title reveal delay-2">
                    {{ __('niveaux/a2.rich_3_title') }}
                </h2>

                <p class="reveal delay-3">
                    {!! __('niveaux/a2.rich_3_p1') !!}
                </p>


                <!-- BLOCK 4 -->
                <h2 class="fade-blur-title reveal delay-1">
                    {{ __('niveaux/a2.rich_4_title') }}
                </h2>

                <p class="reveal delay-2">
                    {!! __('niveaux/a2.rich_4_p1') !!}
                </p>

                <p class="reveal delay-3">
                    {!! __('niveaux/a2.rich_4_p2') !!}
                </p>

            </div>

        </div>

    </section>


<!-- ===========================================================
     PATH GLS → ÖSD
=========================================================== -->
<section class="gls-path-section reveal delay-1">

    <div class="gls-path-container reveal delay-2">

        <h2 class="gls-path-title fade-blur-title reveal delay-3">{{ __('niveaux/a2.path_title') }}</h2>

        <div class="gls-path-grid reveal delay-1">

            <div class="gls-path-card reveal delay-1">
                <div class="gls-path-icon reveal delay-2">@include('frontoffice.svg.info-arrow')</div>
                <h3 class="gls-path-card-title fade-blur-title reveal delay-3">{!! __('niveaux/a2.path_1_title') !!}</h3>
                <div class="gls-path-text reveal delay-1">{!! __('niveaux/a2.path_1_text') !!}</div>
                <a href="/courses" class="gls-path-button w-button reveal delay-2">{{ __('niveaux/a2.path_1_btn') }}</a>
            </div>

            <div class="gls-path-card reveal delay-2">
                <div class="gls-path-icon reveal delay-3">@include('frontoffice.svg.info-arrow')</div>
                <h3 class="gls-path-card-title fade-blur-title reveal delay-1">{!! __('niveaux/a2.path_2_title') !!}</h3>
                <div class="gls-path-text reveal delay-2">{!! __('niveaux/a2.path_2_text') !!}</div>
                <a href="/exams/osd" class="gls-path-button w-button reveal delay-3">{{ __('niveaux/a2.path_2_btn') }}</a>
            </div>

            <div class="gls-path-card reveal delay-3">
                <div class="gls-path-icon reveal delay-1">@include('frontoffice.svg.info-arrow')</div>
                <h3 class="gls-path-card-title fade-blur-title reveal delay-2">{!! __('niveaux/a2.path_3_title') !!}</h3>
                <div class="gls-path-text reveal delay-3">{!! __('niveaux/a2.path_3_text') !!}</div>
                <a href="/exams/osd#dates" class="gls-path-button w-button reveal delay-1">{{ __('niveaux/a2.path_3_btn') }}</a>
            </div>

            <div class="gls-path-card reveal delay-1">
                <div class="gls-path-icon reveal delay-2">@include('frontoffice.svg.info-arrow')</div>
                <h3 class="gls-path-card-title fade-blur-title reveal delay-3">{!! __('niveaux/a2.path_4_title') !!}</h3>
                <div class="gls-path-text reveal delay-1">{!! __('niveaux/a2.path_4_text') !!}</div>
                <a href="/exams/osd" class="gls-path-button w-button reveal delay-2">{{ __('niveaux/a2.path_4_btn') }}</a>
            </div>

        </div>

    </div>

</section>

<!-- CTA -->
<section class="inline-cta-section section reveal delay-1">
    <div class="inline-cta-block reveal delay-2">

        <h2 class="heading-cta fade-blur-title reveal delay-3">{!! __('niveaux/a2.cta_title') !!}</h2>

        <p class="cta-box-subtext reveal delay-1">{!! __('niveaux/a2.cta_text') !!}</p>

        <a href="/online-registration" class="cta-btn reveal delay-2">
            {{ __('niveaux/a2.cta_btn') }}
        </a>

    </div>
</section>


<!-- ===========================================================
     DYNAMIC JS (TRANSLATION SAFE)
=========================================================== -->
<script>
const data = {
    A1: {
        graduation: @json(__('niveaux/a1.data_graduation')),
        duration: @json(__('niveaux/a1.data_duration')),
        times: @json(__('niveaux/a1.data_times')),
        price: @json(__('niveaux/a1.data_price')),
    },
    A2: {
        graduation: @json(__('niveaux/a2.data_graduation')),
        duration: @json(__('niveaux/a2.data_duration')),
        times: @json(__('niveaux/a2.data_times')),
        price: @json(__('niveaux/a2.data_price')),
    },
    B1: {
        graduation: @json(__('niveaux/b1.data_graduation')),
        duration: @json(__('niveaux/b1.data_duration')),
        times: @json(__('niveaux/b1.data_times')),
        price: @json(__('niveaux/b1.data_price')),
    },
    B2: {
        graduation: @json(__('niveaux/b2.data_graduation')),
        duration: @json(__('niveaux/b2.data_duration')),
        times: @json(__('niveaux/b2.data_times')),
        price: @json(__('niveaux/b2.data_price')),
    },
};

// UPDATE CARDS
function updateCards(level) {
    document.getElementById("graduation-text").innerHTML = data[level].graduation;
    document.getElementById("duration-text").innerHTML = data[level].duration;
    document.getElementById("times-text").innerHTML = data[level].times;
    document.getElementById("price-text").innerHTML = data[level].price;
}

// TAB CLICK
document.querySelectorAll(".gls-niveau-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        document.querySelectorAll(".gls-niveau-btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        updateCards(btn.dataset.level);
    });
});

// default A2
updateCards("A2");
</script>

@endsection
