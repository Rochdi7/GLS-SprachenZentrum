@extends('frontoffice.layouts.app')

@section('title', $post->title())
@section('meta_description', Str::limit(strip_tags($post->content()), 150))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/blog/blog-details.css') }}">

@section('content')
<main class="blog-page reveal delay-1">

    {{-- ===============================
         HERO IMAGE WITH CATEGORY BADGE
    =============================== --}}
    <section class="blog-details-hero reveal delay-1">

        <img src="{{ $post->getFirstMediaUrl('blog_images') }}"
             alt="{{ $post->title() }}"
             class="blog-details-hero-img reveal delay-2">

        <div class="blog-details-hero-overlay reveal delay-1"></div>

        <div class="container reveal delay-2">
            <div class="blog-details-hero-content reveal delay-3">

                <div class="blog-hero-badge mb-3 reveal delay-1">
                    {{ $post->category->name() }}
                </div>

                <h1 class="blog-details-title fade-blur-title reveal delay-2">
                    {{ $post->title() }}
                </h1>

                <div class="blog-details-meta reveal delay-3">
                    <span>⏱ {{ $post->reading_time }} {{ __('blog.featured.meta_read') }}</span>
                    <span class="dot">•</span>
                    <span>{{ $post->updated_at->translatedFormat('d M Y') }}</span>
                </div>

            </div>
        </div>
    </section>



    {{-- ===============================
         BREADCRUMB
    =============================== --}}
    <section class="blog-breadcrumb-section py-3 reveal delay-1">
        <div class="container reveal delay-2">
            <nav class="breadcrumb reveal delay-3">
                <a href="{{ url('/') }}">{{ __('blog.breadcrumb.home') }}</a>
                <span class="sep">›</span>

                <a href="{{ route('blog.index') }}">{{ __('blog.breadcrumb.blog') }}</a>
                <span class="sep">›</span>

                <span class="current">{{ $post->title() }}</span>
            </nav>
        </div>
    </section>



    {{-- ===============================
         ARTICLE CONTENT
    =============================== --}}
    <section class="section blog-details-content-section reveal delay-1">
        <div class="container reveal delay-2">
            <div class="blog-details-content reveal delay-3">

                {!! $post->content() !!}

                <div class="blog-share mt-5 reveal delay-1">
                    <p class="fw-bold mb-2 reveal delay-1">{{ __('blog.details.share') }}</p>
                    <div class="d-flex gap-3 reveal delay-2">

                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(Request::url()) }}"
                           target="_blank"
                           class="social-icon reveal delay-1">
                            <i class="bi bi-facebook"></i>
                        </a>

                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(Request::url()) }}"
                           target="_blank"
                           class="social-icon reveal delay-2">
                            <i class="bi bi-twitter-x"></i>
                        </a>

                        <a href="https://api.whatsapp.com/send?text={{ urlencode(Request::url()) }}"
                           target="_blank"
                           class="social-icon reveal delay-3">
                            <i class="bi bi-whatsapp"></i>
                        </a>

                        <a href="#" onclick="navigator.clipboard.writeText('{{ Request::url() }}'); return false;"
                           class="social-icon reveal delay-1">
                            <i class="bi bi-link-45deg"></i>
                        </a>

                    </div>
                </div>

            </div>
        </div>
    </section>



    {{-- ===============================
         RELATED POSTS
    =============================== --}}
    @if($relatedPosts->count() > 0)
    <section class="section blog-related-section reveal delay-1">
        <div class="container reveal delay-2">

            <h2 class="h-section-subtitle mb-4 fade-blur-title reveal delay-1">
                {{ __('blog.details.related') }}
            </h2>

            <div class="row g-4 reveal delay-3">

                @foreach($relatedPosts as $r)
                    <div class="col-md-6 col-lg-4 reveal delay-1">
                        <article class="blog-card reveal delay-2">

                            <div class="blog-card-image-wrapper reveal delay-1">
                                <a href="{{ route('blog.show', $r->slug) }}">
                                    <img src="{{ $r->getFirstMediaUrl('blog_images') }}"
                                         class="blog-card-image reveal delay-2"
                                         alt="{{ $r->title() }}">
                                </a>

                                <div class="blog-card-category reveal delay-3">
                                    {{ $r->category->name() }}
                                </div>
                            </div>

                            <div class="blog-card-body reveal delay-2">
                                <h3 class="blog-card-title fade-blur-title reveal delay-1">
                                    <a href="{{ route('blog.show', $r->slug) }}">
                                        {{ $r->title() }}
                                    </a>
                                </h3>

                                <p class="blog-card-excerpt reveal delay-2">
                                    {{ Str::words(strip_tags($r->content()), 20) }}
                                </p>
                            </div>

                        </article>
                    </div>
                @endforeach

            </div>

        </div>
    </section>
    @endif

</main>

@endsection

