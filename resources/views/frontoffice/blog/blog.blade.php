@extends('frontoffice.layouts.app')

@section('title', __('blog.meta.title'))
@section('meta_description', __('blog.meta.description'))
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/blog/blog.css') }}">

@section('content')
<main class="blog-page">

    {{-- ===============================
         HERO SECTION
    =============================== --}}
    <section class="hero-section section blog-hero-section blog-hero-margin">
        <div class="container">
            <div class="blog-hero-inner">
                <div class="blog-hero-badge">
                    {{ __('blog.hero.badge') }}
                </div>

                <h1 class="blog-hero-title">
                    {{ __('blog.hero.title') }}
                </h1>

                <p class="blog-hero-subtitle">
                    {{ __('blog.hero.subtitle') }}
                </p>

                <div class="blog-hero-meta">
                    <span>{{ __('blog.hero.meta') }}</span>
                </div>
            </div>
        </div>
    </section>


    {{-- ===============================
         FEATURED + SIDEBAR
    =============================== --}}
    <section class="section blog-featured-section">
        <div class="container">
            <div class="row g-4 align-items-start">

                {{-- FEATURED ARTICLE --}}
                <div class="col-lg-8">
                    <article class="blog-card blog-card--featured">
                        <div class="blog-card-image-wrapper">
                            <a href="#">
                                <img src="{{ asset('assets/images/blog/blog-test.jpeg') }}"
                                     alt="Featured Article"
                                     class="blog-card-image">
                            </a>
                            <div class="blog-card-category">
                                {{ __('blog.featured.category') }}
                            </div>
                        </div>

                        <div class="blog-card-body">
                            <h2 class="blog-card-title">
                                <a href="#">{{ __('blog.featured.title') }}</a>
                            </h2>

                            <p class="blog-card-excerpt">
                                {{ __('blog.featured.excerpt') }}
                            </p>

                            <div class="blog-card-meta">
                                <span class="blog-meta-item">
                                    {{ __('blog.featured.meta_read') }}
                                </span>
                                <span class="blog-meta-dot">•</span>
                                <span class="blog-meta-item">
                                    {{ __('blog.featured.meta_updated') }}
                                </span>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- SIDEBAR --}}
                <div class="col-lg-4">
                    <aside class="blog-sidebar">

                        {{-- Search --}}
                        <div class="blog-sidebar-block">
                            <h3 class="blog-sidebar-title">{{ __('blog.sidebar.search.title') }}</h3>

                            <form action="#" method="GET" class="blog-search-form">
                                <div class="blog-search-input-wrap">
                                    <input type="text" name="q" class="blog-search-input"
                                           placeholder="{{ __('blog.sidebar.search.placeholder') }}">
                                    <button type="submit" class="blog-search-button">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Categories --}}
                        <div class="blog-sidebar-block">
                            <h3 class="blog-sidebar-title">{{ __('blog.sidebar.categories.title') }}</h3>
                            <ul class="blog-sidebar-list">
                                <li><a href="#">{{ __('blog.sidebar.categories.all') }}</a></li>
                                <li><a href="#">{{ __('blog.sidebar.categories.courses') }}</a></li>
                                <li><a href="#">{{ __('blog.sidebar.categories.exams') }}</a></li>
                                <li><a href="#">{{ __('blog.sidebar.categories.study') }}</a></li>
                                <li><a href="#">{{ __('blog.sidebar.categories.ausbildung') }}</a></li>
                                <li><a href="#">{{ __('blog.sidebar.categories.visa') }}</a></li>
                            </ul>
                        </div>

                        {{-- Popular --}}
                        <div class="blog-sidebar-block">
                            <h3 class="blog-sidebar-title">{{ __('blog.sidebar.popular.title') }}</h3>
                            <ul class="blog-sidebar-posts">
                                <li><a href="#">{{ __('blog.sidebar.popular.p1') }}</a></li>
                                <li><a href="#">{{ __('blog.sidebar.popular.p2') }}</a></li>
                                <li><a href="#">{{ __('blog.sidebar.popular.p3') }}</a></li>
                            </ul>
                        </div>

                    </aside>
                </div>

            </div>
        </div>
    </section>


    {{-- ===============================
         BLOG GRID (STATIC DEMO)
    =============================== --}}
    <section class="section blog-list-section">
        <div class="container">

            <div class="blog-list-header">
                <h2 class="h-section-subtitle blog-list-title">
                    {{ __('blog.latest.title') }}
                </h2>
                <p class="blog-list-subtitle">
                    {{ __('blog.latest.subtitle') }}
                </p>
            </div>

            <div class="row g-4 blog-grid-row">

                {{-- Loop through all demo cards --}}
                @foreach(__('blog.cards') as $card)
                    <div class="col-md-6 col-lg-4">
                        <article class="blog-card">
                            <div class="blog-card-image-wrapper">
                                <a href="#">
                                    <img src="{{ asset('assets/images/blog/blog-test.jpeg') }}"
                                         alt="{{ $card['title'] }}"
                                         class="blog-card-image">
                                </a>
                                <div class="blog-card-category">
                                    {{ $card['category'] }}
                                </div>
                            </div>

                            <div class="blog-card-body">
                                <h3 class="blog-card-title">
                                    <a href="#">{{ $card['title'] }}</a>
                                </h3>

                                <p class="blog-card-excerpt">
                                    {{ $card['excerpt'] }}
                                </p>

                                <div class="blog-card-meta">
                                    <span class="blog-meta-item">{{ $card['meta_read'] }}</span>
                                    <span class="blog-meta-dot">•</span>
                                    <span class="blog-meta-item">{{ $card['meta_cat'] }}</span>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach

            </div>

            {{-- Pagination --}}
            <div class="blog-pagination">
                <button class="blog-pagination-btn is-active">1</button>
                <button class="blog-pagination-btn">2</button>
                <button class="blog-pagination-btn">3</button>
                <button class="blog-pagination-btn">{{ __('blog.pagination.next') }}</button>
            </div>

        </div>
    </section>


    {{-- ===============================
         CTA SECTION
    =============================== --}}
    <section class="section blog-cta-section">
        <div class="container">
            <div class="blog-cta-block">

                <div class="blog-cta-text">
                    <h2>{{ __('blog.cta.title') }}</h2>
                    <p>{{ __('blog.cta.subtitle') }}</p>
                </div>

                <div class="blog-cta-actions">
                    <a href="/courses" class="btn btn-primary gls-btn-main">
                        {{ __('blog.cta.btn_courses') }}
                    </a>
                    <a href="/contact" class="btn btn-outline-light gls-btn-outline">
                        {{ __('blog.cta.btn_contact') }}
                    </a>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection
