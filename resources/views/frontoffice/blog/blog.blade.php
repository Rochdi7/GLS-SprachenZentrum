@extends('frontoffice.layouts.app')

@section('title', __('blog.meta.title'))
@section('meta_description', __('blog.meta.description'))
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/blog/blog.css') }}">

@section('content')
    <main class="blog-page">

        {{-- HERO --}}
        <section class="hero-section section blog-hero-section blog-hero-margin reveal delay-1">
            <div class="container reveal delay-2">
                <div class="blog-hero-inner reveal delay-3">

                    <div class="blog-hero-badge reveal delay-1">{{ __('blog.hero.badge') }}</div>

                    <h1 class="blog-hero-title fade-blur-title reveal delay-2">
                        {{ __('blog.hero.title') }}
                    </h1>

                    <p class="blog-hero-subtitle reveal delay-3">
                        {{ __('blog.hero.subtitle') }}
                    </p>

                    <div class="blog-hero-meta reveal delay-1">
                        <span>{{ __('blog.hero.meta') }}</span>
                    </div>

                </div>
            </div>
        </section>

        {{-- FEATURED + SIDEBAR --}}
        <section class="section blog-featured-section reveal delay-1">
            <div class="container reveal delay-2">
                <div class="row g-4 align-items-start reveal delay-3">

                    {{-- FEATURED ARTICLE --}}
                    <div class="col-lg-8 reveal delay-1">
                        @if ($featured)
                            @php
                                $media = $featured->getFirstMedia('blog_images');
                            @endphp

                            <article class="blog-card blog-card--featured reveal delay-2">

                                <div class="blog-card-image-wrapper reveal delay-1">
                                    <a href="{{ route('blog.show', $featured->slug) }}">

                                        <img src="{{ $media
                                            ? route('media.custom', ['id' => $media->id, 'filename' => $media->file_name])
                                            : asset('assets/images/placeholder.webp') }}"
                                            alt="{{ $featured->title }}" class="blog-card-image reveal delay-2">

                                    </a>

                                    <div class="blog-card-category reveal delay-3">
                                        {{ $featured->category->name }}
                                    </div>
                                </div>

                                <div class="blog-card-body reveal delay-2">
                                    <h2 class="blog-card-title fade-blur-title reveal delay-1">
                                        <a href="{{ route('blog.show', $featured->slug) }}">
                                            {{ $featured->title }}
                                        </a>
                                    </h2>

                                    <p class="blog-card-excerpt reveal delay-2">
                                        {{ Str::words(strip_tags($featured->content), 22) }}
                                    </p>

                                    <div class="blog-card-meta reveal delay-3">
                                        <span class="blog-meta-item">
                                            {{ $featured->reading_time }} {{ __('blog.featured.meta_read') }}
                                        </span>
                                        <span class="blog-meta-dot">•</span>
                                        <span class="blog-meta-item">
                                            {{ optional($featured->updated_at ?? $featured->created_at)->diffForHumans() }}
                                        </span>

                                    </div>
                                </div>

                            </article>
                        @endif
                    </div>

                    {{-- SIDEBAR --}}
                    <div class="col-lg-4 reveal delay-2">
                        <aside class="blog-sidebar reveal delay-3">

                            {{-- SEARCH --}}
                            <div class="blog-sidebar-block reveal delay-1">
                                <h3 class="blog-sidebar-title fade-blur-title reveal delay-1">
                                    {{ __('blog.sidebar.search.title') }}
                                </h3>

                                <form action="{{ route('blog.index') }}" method="GET"
                                    class="blog-search-form reveal delay-2">
                                    <div class="blog-search-input-wrap">
                                        <input type="text" name="q" class="blog-search-input"
                                            placeholder="{{ __('blog.sidebar.search.placeholder') }}"
                                            value="{{ request('q') }}">
                                        <button type="submit" class="blog-search-button">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- CATEGORIES --}}
                            <div class="blog-sidebar-block reveal delay-2">
                                <h3 class="blog-sidebar-title fade-blur-title reveal delay-1">
                                    {{ __('blog.sidebar.categories.title') }}
                                </h3>

                                <ul class="blog-sidebar-list reveal delay-2">
                                    <li><a href="{{ route('blog.index') }}">{{ __('blog.sidebar.categories.all') }}</a>
                                    </li>

                                    @foreach ($categories as $cat)
                                        <li class="reveal delay-3">
                                            <a href="{{ route('blog.index', ['category' => $cat->slug]) }}">
                                                {{ $cat->name }}
                                            </a>
                                        </li>
                                    @endforeach

                                </ul>
                            </div>

                            {{-- POPULAR --}}
                            <div class="blog-sidebar-block reveal delay-3">
                                <h3 class="blog-sidebar-title fade-blur-title reveal delay-1">
                                    {{ __('blog.sidebar.popular.title') }}
                                </h3>

                                <ul class="blog-sidebar-posts reveal delay-2">
                                    @foreach ($popular as $p)
                                        <li class="reveal delay-3">
                                            <a href="{{ route('blog.show', $p->slug) }}">
                                                {{ $p->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                        </aside>
                    </div>

                </div>
            </div>
        </section>

        {{-- BLOG GRID --}}
        <section class="section blog-list-section reveal delay-1">
            <div class="container reveal delay-2">

                <div class="blog-list-header reveal delay-1">
                    <h2 class="h-section-subtitle blog-list-title fade-blur-title reveal delay-1">
                        {{ __('blog.latest.title') }}
                    </h2>
                    <p class="blog-list-subtitle reveal delay-2">
                        {{ __('blog.latest.subtitle') }}
                    </p>
                </div>

                <div class="row g-4 blog-grid-row reveal delay-3">

                    @foreach ($posts as $post)
                        @php
                            $media = $post->getFirstMedia('blog_images');
                        @endphp

                        <div class="col-md-6 col-lg-4 reveal delay-1">
                            <article class="blog-card reveal delay-2">

                                <div class="blog-card-image-wrapper reveal delay-1">
                                    <a href="{{ route('blog.show', $post->slug) }}">
                                        <img src="{{ $media
                                            ? route('media.custom', ['id' => $media->id, 'filename' => $media->file_name])
                                            : asset('assets/images/placeholder.webp') }}"
                                            alt="{{ $post->title }}" class="blog-card-image reveal delay-2">
                                    </a>

                                    <div class="blog-card-category reveal delay-3">
                                        {{ $post->category->name }}
                                    </div>
                                </div>

                                <div class="blog-card-body reveal delay-2">
                                    <h3 class="blog-card-title fade-blur-title reveal delay-1">
                                        <a href="{{ route('blog.show', $post->slug) }}">
                                            {{ $post->title }}
                                        </a>
                                    </h3>

                                    <p class="blog-card-excerpt reveal delay-2">
                                        {{ Str::words(strip_tags($post->content), 18) }}
                                    </p>

                                    <div class="blog-card-meta reveal delay-3">
                                        <span class="blog-meta-item">
                                            {{ $post->reading_time }} {{ __('blog.featured.meta_read') }}
                                        </span>
                                        <span class="blog-meta-dot">•</span>
                                        <span class="blog-meta-item">
                                            {{ $post->category->name }}
                                        </span>
                                    </div>
                                </div>

                            </article>
                        </div>
                    @endforeach

                </div>

                {{-- PAGINATION --}}
                <div class="blog-pagination reveal delay-2">
                    {{ $posts->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </section>

        {{-- CTA --}}
        <section class="section blog-cta-section reveal delay-1">
            <div class="container reveal delay-2">
                <div class="blog-cta-block reveal delay-3">

                    <div class="blog-cta-text reveal delay-1">
                        <h2 class="fade-blur-title reveal delay-1">{{ __('blog.cta.title') }}</h2>
                        <p class="reveal delay-2">{{ __('blog.cta.subtitle') }}</p>
                    </div>

                    <div class="blog-cta-actions reveal delay-3">
                        <a href="/courses" class="btn btn-primary gls-btn-main">{{ __('blog.cta.btn_courses') }}</a>
                        <a href="/contact"
                            class="btn btn-outline-light gls-btn-outline">{{ __('blog.cta.btn_contact') }}</a>
                    </div>

                </div>
            </div>
        </section>

    </main>
@endsection
