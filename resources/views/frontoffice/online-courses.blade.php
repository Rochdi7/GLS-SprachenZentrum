@extends('frontoffice.layouts.app')

@section('title', __('online.meta.title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/online/online-courses.css') }}">

@section('content')

    <section class="intensive-course section reveal delay-1">

        <!-- OPTIONAL HERO (HIDDEN) -->
        <div class="container is-course-hero hide reveal delay-2">
            <div class="reveal delay-3">
                <div class="couse-card_level is-big reveal delay-1">
                    <div class="course-level-circle reveal delay-2">A</div>
                    <div class="course-level-circle reveal delay-3">1</div>
                </div>
            </div>

            <div class="text-block-4 reveal delay-1">{{ __('online.hero.hidden.subtitle') }}</div>

            <h1 class="hero_title is-course reveal fade-blur-title delay-2">
                {{ __('online.hero.hidden.title') }}
            </h1>

            <p class="course-hero_paragraph reveal delay-3">
                {!! __('online.hero.hidden.text') !!}
            </p>

            <a href="{{ LaravelLocalization::localizeURL('/online-registration') }}"
                class="button is-big is-white w-button reveal delay-1">
                {{ __('online.buttons.enroll') }}
            </a>
        </div>

        <!-- MAIN HERO -->
        <div class="container is-online-course-hero-content reveal delay-1">

            <!-- LEFT IMAGE -->
            <div id="w-node-online-left" class="div-block-38 reveal delay-2">
                <img src="{{ asset('assets/images/online-courses/hero.png') }}" loading="lazy"
                    class="full-image rounded reveal delay-3" alt="">
            </div>

            <!-- RIGHT TEXT -->
            <div class="div-block-37 reveal delay-1">

                <div class="text-block-4-copy reveal delay-1">
                    {{ __('online.hero.subtitle') }}
                </div>

                <h1 class="hero_title is-course is-online-course reveal fade-blur-title delay-2">
                    {{ __('online.hero.title') }}
                </h1>

                <p class="course-hero_paragraph reveal delay-3">
                    {!! __('online.hero.description') !!}
                </p>

                <a href="{{ LaravelLocalization::localizeURL('/online-registration') }}"
                    class="button is-big w-button reveal delay-1">
                    {{ __('online.buttons.enroll') }}
                </a>
            </div>

        </div>

    </section>

    <section class="info-section section is-online-classes reveal delay-1">

        <div class="container is-h-courses reveal delay-2">
            <h2 class="h-section-subtitle is-info reveal fade-blur-title delay-1">
                {!! __('online.info.title') !!}
            </h2>

            <!-- INFO CARDS -->
            <div class="info reveal delay-2">

                <div class="info-card reveal delay-1">
                    <div class="couse-card_level is-black reveal delay-2">@include('frontoffice.svg.info-graduation')</div>
                    <h3 class="course-card_title reveal fade-blur-title delay-3">
                        <strong>{{ __('online.info.graduation.title') }}</strong>
                    </h3>
                    <div class="course-card_text reveal delay-1">{{ __('online.info.graduation.text') }}</div>
                </div>

                <div class="info-card reveal delay-2">
                    <div class="couse-card_level is-black reveal delay-3">@include('frontoffice.svg.info-duration')</div>
                    <h3 class="course-card_title reveal fade-blur-title delay-1">
                        <strong>{{ __('online.info.duration.title') }}</strong>
                    </h3>
                    <div class="course-card_text reveal delay-2">{!! nl2br(__('online.info.duration.text')) !!}</div>
                </div>

                <div class="info-card reveal delay-3">
                    <div class="couse-card_level is-black reveal delay-1">@include('frontoffice.svg.info-times')</div>
                    <h3 class="course-card_title reveal fade-blur-title delay-2">
                        <strong>{{ __('online.info.times.title') }}</strong>
                    </h3>
                    <div class="course-card_text reveal delay-3">{!! nl2br(__('online.info.times.text')) !!}</div>
                </div>

                <div class="info-card reveal delay-1">
                    <div class="couse-card_level is-black reveal delay-2">@include('frontoffice.svg.info-cost')</div>
                    <h3 class="course-card_title reveal fade-blur-title delay-3">
                        <strong>{{ __('online.info.cost.title') }}</strong>
                    </h3>
                    <div class="course-card_text reveal delay-1">{!! nl2br(__('online.info.cost.text')) !!}</div>
                </div>

            </div>

            <!-- INLINE CTA -->
            <div class="inline-cta-block is-online-courses reveal delay-3">

                <h2 class="heading-4 reveal fade-blur-title delay-1">{{ __('online.highlights.title') }}</h2>

                <div class="div-block-14 reveal delay-2">

                    <div class="highlight-pill a no-outline reveal delay-1">
                        {{ __('online.highlights.items.0') }}
                    </div>

                    <div class="highlight-pill b no-outline reveal delay-2">
                        {{ __('online.highlights.items.1') }}
                    </div>

                    <div class="highlight-pill c no-outline reveal delay-3">
                        {{ __('online.highlights.items.2') }}
                    </div>

                    <div class="highlight-pill d no-outline reveal delay-1">
                        {{ __('online.highlights.items.3') }}
                    </div>

                    <div class="highlight-pill e no-outline reveal delay-2">
                        {{ __('online.highlights.items.4') }}
                    </div>

                    <div class="highlight-pill f no-outline reveal delay-3">
                        {{ __('online.highlights.items.5') }}
                    </div>

                    <div class="highlight-pill g no-outline reveal delay-1">
                        {{ __('online.highlights.items.6') }}
                    </div>

                </div>

                <a href="{{ LaravelLocalization::localizeURL('/online-registration') }}"
                    class="button is-big is-white w-button reveal delay-3">
                    {{ __('online.buttons.enroll') }}
                </a>

            </div>

        </div>
    </section>

    <section class="gls-online-info section reveal delay-1">

        <div class="gls-online-info-container container reveal delay-2">

            <div class="gls-online-info-text reveal delay-3">
                <div class="gls-online-info-richtext w-richtext reveal delay-1">
                    <h2 class="reveal fade-blur-title delay-1">{{ __('online.block1.title') }}</h2>
                    <h3 class="reveal fade-blur-title delay-2">{{ __('online.block1.subtitle') }}</h3>
                    <p class="reveal delay-3">{!! __('online.block1.text1') !!}</p>
                    <p class="reveal delay-1">{!! __('online.block1.text2') !!}</p>
                </div>

                <a href="{{ LaravelLocalization::localizeURL('/online-registration') }}"
                    class="gls-button-big w-button reveal delay-2">
                    {{ __('online.buttons.enroll') }}
                </a>
            </div>

            <div class="gls-online-info-image reveal delay-3">
                <img src="{{ asset('assets/images/online-courses/online.png') }}" class="gls-full-image reveal delay-1"
                    alt="">
            </div>
        </div>

    </section>

    <section class="gls-more-info section reveal delay-1">
        <div class="container gls-more-info-container reveal delay-2">

            <h2 class="h-section-subtitle gls-more-info-title reveal fade-blur-title delay-1">
                {{ __('online.more.title') }}
            </h2>

            <div class="gls-more-info-grid reveal delay-3">

                <div class="gls-info-card reveal delay-1">
                    <div class="gls-info-icon reveal delay-2">@include('frontoffice.svg.info-arrow')</div>
                    <h3 class="gls-info-title reveal fade-blur-title delay-3">{!! __('online.more.card1.title') !!}</h3>
                    <div class="gls-info-text reveal delay-1">{{ __('online.more.card1.text') }}</div>
                    <div class="gls-info-spacer"></div>
                    <a href="{{ LaravelLocalization::localizeURL('/courses/pricing') }}"
                        class="gls-info-button w-button reveal delay-2">
                        {{ __('online.more.card1.button') }}
                    </a>
                </div>

                <div class="gls-info-card reveal delay-2">
                    <div class="gls-info-icon reveal delay-3">@include('frontoffice.svg.info-arrow')</div>
                    <h3 class="gls-info-title reveal fade-blur-title delay-1">{!! __('online.more.card2.title') !!}</h3>
                    <div class="gls-info-text reveal delay-2">{{ __('online.more.card2.text') }}</div>
                    <div class="gls-info-spacer"></div>
                    <a href="{{ LaravelLocalization::localizeURL('/german-exams') }}"
                        class="gls-info-button w-button reveal delay-3">
                        {{ __('online.more.card2.button') }}
                    </a>
                </div>

                <div class="gls-info-card reveal delay-3">
                    <div class="gls-info-icon reveal delay-1">@include('frontoffice.svg.info-arrow')</div>
                    <h3 class="gls-info-title reveal fade-blur-title delay-2">{!! __('online.more.card3.title') !!}</h3>
                    <div class="gls-info-text reveal delay-3">{{ __('online.more.card3.text') }}</div>
                    <div class="gls-info-spacer"></div>
                    <a href="{{ LaravelLocalization::localizeURL('/courses/course-schedules') }}"
                        class="gls-info-button w-button reveal delay-1">
                        {{ __('online.more.card3.button') }}
                    </a>
                </div>

                <div class="gls-info-card reveal delay-1">
                    <div class="gls-info-icon reveal delay-2">@include('frontoffice.svg.info-arrow')</div>
                    <h3 class="gls-info-title reveal fade-blur-title delay-3">{!! __('online.more.card4.title') !!}</h3>
                    <div class="gls-info-text reveal delay-1">{{ __('online.more.card4.text') }}</div>
                    <div class="gls-info-spacer"></div>
                    <a href="{{ LaravelLocalization::localizeURL('/online-registration') }}"
                        class="gls-info-button w-button reveal delay-2">
                        {{ __('online.more.card4.button') }}
                    </a>
                </div>

            </div>

        </div>
    </section>

    <section class="rich-text-section section reveal delay-1">
        <div class="container reveal delay-2">
            <div class="rich-text w-richtext reveal delay-3">

                <h2 class="reveal fade-blur-title delay-1">{{ __('online.podcast.title') }}</h2>
                <h3 class="reveal fade-blur-title delay-2">{{ __('online.podcast.subtitle') }}</h3>

               <iframe width="760" height="415"
        src="{!! __('online.podcast.video_url') !!}"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen
        class="gls-podcast-video">
</iframe>




                <h2 class="reveal fade-blur-title delay-1">{{ __('online.podcast.overview') }}</h2>

                <p class="reveal delay-2">{!! __('online.podcast.text') !!}</p>

            </div>
        </div>
    </section>

    <section class="inline-cta-section section reveal delay-1">
        <div class="inline-cta-block reveal delay-2">

            <h2 class="heading-cta reveal fade-blur-title delay-1">
                {!! __('online.cta.title') !!}
            </h2>

            <p class="cta-box-subtext reveal delay-2">
                {{ __('online.cta.text') }}
            </p>

            <a href="{{ LaravelLocalization::localizeURL('/online-registration') }}" class="cta-btn reveal delay-3">
                {{ __('online.buttons.enroll') }}
            </a>

        </div>
    </section>


@endsection
