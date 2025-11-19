@extends('frontoffice.layouts.app')

@section('title', $post->title ?? 'Blog Details')
@section('meta_description', $post->excerpt ?? '')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/blog/blog-details.css') }}">

@section('content')
<main class="blog-page">

    {{-- ===============================
         HERO IMAGE WITH CATEGORY BADGE
       =============================== --}}
    <section class="blog-details-hero">
        <img src="{{ asset('assets/images/blog/blog-test.jpeg') }}" alt="{{ $post->title ?? '' }}">
        <div class="blog-details-hero-overlay"></div>

        <div class="container">
            <div class="blog-details-hero-content">

                {{-- CATEGORY BADGE (for test use only, not dynamic) --}}
                <div class="blog-hero-badge mb-3">Exams</div>

                <h1 class="blog-details-title">{{ $post->title ?? 'Blog Title Example' }}</h1>
                <div class="blog-details-meta">
                    <span>⏱ 6 min read</span>
                    <span class="dot">•</span>
                    <span>Updated Nov 2025</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===============================
         BREADCRUMB DESIGN (Static)
       =============================== --}}
    <section class="blog-breadcrumb-section py-3">
        <div class="container">
            <nav class="breadcrumb">
                <a href="/">Home</a>
                <span class="sep">›</span>
                <a href="/blog">Blog</a>
                <span class="sep">›</span>
                <span class="current">Blog Title Example</span>
            </nav>
        </div>
    </section>

    {{-- ===============================
         ARTICLE CONTENT
       =============================== --}}
    <section class="section blog-details-content-section">
        <div class="container">
            <div class="blog-details-content">

                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis pharetra lorem sed 
                    urna gravida, sit amet suscipit est lobortis. Cras ut dictum leo. Aliquam erat volutpat.
                </p>

                <h2>Subtitle Section</h2>
                <p>
                    Vivamus eget suscipit lorem. Mauris ullamcorper, mauris ut semper luctus, sapien 
                    nisi sodales est, quis feugiat sapien lorem in nunc.
                </p>

                <p>
                    Proin vestibulum egestas dolor, ac vulputate odio luctus vitae. Integer egestas 
                    cursus nulla, vitae condimentum purus vehicula sit amet.
                </p>

                {{-- ===============================
                     SHARE BUTTONS (Static)
                   =============================== --}}
                <div class="blog-share mt-5">
                    <p class="fw-bold mb-2">Share this article:</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-whatsapp"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-link-45deg"></i></a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ===============================
         RELATED POSTS
       =============================== --}}
    <section class="section blog-related-section">
        <div class="container">
            <h2 class="h-section-subtitle mb-4">Related Posts</h2>

            <div class="row g-4">

                {{-- POST 1 --}}
                <div class="col-md-6 col-lg-4">
                    <article class="blog-card">
                        <div class="blog-card-image-wrapper">
                            <a href="#">
                                <img src="{{ asset('assets/images/blog/blog-test.jpeg') }}" class="blog-card-image">
                            </a>
                            <div class="blog-card-category">Exams</div>
                        </div>
                        <div class="blog-card-body">
                            <h3 class="blog-card-title">
                                <a href="#">Your Roadmap to the ÖSD Exam</a>
                            </h3>
                            <p class="blog-card-excerpt">
                                Discover how to move from A1 to ÖSD certification step by step.
                            </p>
                        </div>
                    </article>
                </div>

                {{-- POST 2 --}}
                <div class="col-md-6 col-lg-4">
                    <article class="blog-card">
                        <div class="blog-card-image-wrapper">
                            <a href="#">
                                <img src="{{ asset('assets/images/blog/blog-test.jpeg') }}" class="blog-card-image">
                            </a>
                            <div class="blog-card-category">Courses</div>
                        </div>
                        <div class="blog-card-body">
                            <h3 class="blog-card-title">
                                <a href="#">How Many Hours Do You Need per Level?</a>
                            </h3>
                            <p class="blog-card-excerpt">
                                Learn how long GLS students take to reach each milestone.
                            </p>
                        </div>
                    </article>
                </div>

                {{-- POST 3 --}}
                <div class="col-md-6 col-lg-4">
                    <article class="blog-card">
                        <div class="blog-card-image-wrapper">
                            <a href="#">
                                <img src="{{ asset('assets/images/blog/blog-test.jpeg') }}" class="blog-card-image">
                            </a>
                            <div class="blog-card-category">Study in Germany</div>
                        </div>
                        <div class="blog-card-body">
                            <h3 class="blog-card-title">
                                <a href="#">Study in Germany: Your First Steps</a>
                            </h3>
                            <p class="blog-card-excerpt">
                                A beginner-friendly overview for Moroccan students.
                            </p>
                        </div>
                    </article>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection
