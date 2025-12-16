@extends('frontoffice.layouts.app')

@section('title', __('pricing.meta_title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/pricing/pricing.css') }}">

@section('content')

    <!-- ============================
         HERO SECTION
    ============================ -->
    <section class="hero-section section about-hero reveal delay-1">
        <div class="container is-hero reveal delay-2">

            <div class="hero_subtitle reveal delay-1">
                {{ __('pricing.hero.subtitle') }}
            </div>

            <h1 class="hero_title reveal fade-blur-title delay-2">
                {{ __('pricing.hero.title') }}
            </h1>

            <div class="hero-image reveal delay-3">
                <img src="{{ asset('assets/images/about/Centre-GLS-de-langue-Allemande.jpg') }}"
                    alt="{{ __('pricing.meta_title') }}" class="full-image reveal delay-1" loading="lazy">
            </div>

        </div>
    </section>


    <!-- ============================
         PRICING TABS
    ============================ -->
    <div class="container py-5 reveal delay-1">

        <div class="gls-tabs-menu d-flex flex-wrap justify-content-center gap-2 mb-4 reveal delay-2">

            <button data-tab="online" class="gls-tab-link active btn reveal delay-1">
                {{ __('pricing.tabs.online') }}
            </button>

            <button data-tab="casablanca" class="gls-tab-link btn reveal delay-2">
                {{ __('pricing.tabs.casablanca') }}
            </button>

            <button data-tab="marrakech" class="gls-tab-link btn reveal delay-3">
                {{ __('pricing.tabs.marrakech') }}
            </button>

            <button data-tab="rabat" class="gls-tab-link btn reveal delay-1">
                {{ __('pricing.tabs.rabat') }}
            </button>

            <button data-tab="kenitra" class="gls-tab-link btn reveal delay-2">
                {{ __('pricing.tabs.kenitra') }}
            </button>

            <button data-tab="sale" class="gls-tab-link btn reveal delay-3">
                {{ __('pricing.tabs.sale') }}
            </button>

            <button data-tab="agadir" class="gls-tab-link btn reveal delay-1">
                {{ __('pricing.tabs.agadir') }}
            </button>

            <button data-tab="exams" class="gls-tab-link btn reveal delay-2">
                {{ __('pricing.tabs.exams') }}
            </button>

        </div>


        <div class="div-block-17 text-center reveal delay-3">
            <h2 id="pricing-title" class="pricelist_header reveal fade-blur-title delay-1">
                {{ __('pricing.headers.online.title') }}
            </h2>

            <div id="pricing-subtitle" class="text-block-6 reveal delay-2">
                {{ __('pricing.headers.online.subtitle') }}
            </div>
        </div>


        <div class="table-wrapper reveal delay-3">
            <div id="pricing-table" class="price-table-rich-text no-gap reveal delay-1">
                <!-- JS inserted content -->
            </div>
        </div>

    </div>



    <!-- ============================
         JS DYNAMIC PRICING
    ============================ -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const buttons = document.querySelectorAll(".gls-tab-link");
            const table = document.getElementById("pricing-table");
            const title = document.getElementById("pricing-title");
            const subtitle = document.getElementById("pricing-subtitle");

            const pricing = @json(__('pricing.data'));
            const headers = @json(__('pricing.headers'));

            function loadTable(tab) {

                title.textContent = headers[tab].title;
                subtitle.textContent = headers[tab].subtitle;

                const p = pricing[tab];

                table.innerHTML = `
            <div class="table-rich-text-pricing2 w-richtext">
                ${p.col1.map(v => `<p>${v}</p>`).join("")}
            </div>

            <div class="table-rich-text-pricing w-richtext text-center">
                ${p.col2.map(v => `<p>${v}</p>`).join("")}
            </div>

            <div class="table-rich-text-pricing w-richtext text-center">
                ${p.col3.map(v => `<p>${v}</p>`).join("")}
            </div>
        `;
            }

            loadTable("online");

            buttons.forEach(btn => {
                btn.addEventListener("click", () => {

                    buttons.forEach(b => b.classList.remove("active"));
                    btn.classList.add("active");

                    loadTable(btn.dataset.tab);
                });
            });

        });
    </script>

    <!-- ============================
         CTA BLOCK
    ============================ -->
    <section class="get-started-section section reveal delay-1">
        <div class="container is-2-col-grid reveal delay-2">

            <div class="get-started-image reveal delay-3">
                <img src="{{ asset('assets/images/about/subscribe.jpeg') }}" alt="{{ __('pricing.cta.title') }}"
                    class="full-image rounded-4 reveal delay-1" loading="lazy">
            </div>

            <div class="get-started-card reveal delay-2">
                <div class="box-rich-text w-richtext reveal delay-3">

                    <h2 class="reveal fade-blur-title delay-1">{{ __('pricing.cta.title') }}</h2>
                    <h3 class="reveal fade-blur-title delay-2">{{ __('pricing.cta.subtitle') }}</h3>

                    <p class="reveal delay-3">{!! __('pricing.cta.p1') !!}</p>
                    <p class="reveal delay-1">{!! __('pricing.cta.p2') !!}</p>

                </div>

                <a href="{{ route('front.contact') }}" class="button w-button reveal delay-2">
                    {{ __('pricing.cta.button') }}
                </a>

            </div>

        </div>
    </section>

@endsection
