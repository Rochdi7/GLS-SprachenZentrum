@extends('frontoffice.layouts.app')

@section('title', 'GLS Blog – Insights & Resources')
@section('meta_description', 'Insights, guides, and updates from GLS Sprachenzentrum to help you learn German and prepare your journey to Germany.')
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/blog/blog.css') }}">

@section('content')
<main class="blog-page">

    {{-- ===============================
     BLOG HERO SECTION
=============================== --}}
<section class="hero-section section blog-hero-section blog-hero-margin">
    <div class="container">
        <div class="blog-hero-inner">
            <div class="blog-hero-badge">Blog</div>
            <h1 class="blog-hero-title">
                GLS Insights & Resources
            </h1>
            <p class="blog-hero-subtitle">
                Practical guides, exam tips, and updates to help you learn German
                and prepare your journey to Germany with confidence.
            </p>
            <div class="blog-hero-meta">
                <span>German Courses · ÖSD & Goethe Exams · Study & Work in Germany</span>
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
                                     alt="Student preparing for ÖSD exam with GLS"
                                     class="blog-card-image">
                            </a>
                            <div class="blog-card-category">
                                Exams
                            </div>
                        </div>

                        <div class="blog-card-body">
                            <h2 class="blog-card-title">
                                <a href="#">Your Roadmap from GLS Courses to the ÖSD Exam</a>
                            </h2>
                            <p class="blog-card-excerpt">
                                Discover how our structured A1–B2 program prepares you step-by-step
                                for official ÖSD certification, from the first lesson to the exam day.
                            </p>

                            <div class="blog-card-meta">
                                <span class="blog-meta-item">⏱ 6 min read</span>
                                <span class="blog-meta-dot">•</span>
                                <span class="blog-meta-item">Updated Nov 2025</span>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- SIDEBAR --}}
                <div class="col-lg-4">
                    <aside class="blog-sidebar">

                        {{-- Search --}}
                        <div class="blog-sidebar-block">
                            <h3 class="blog-sidebar-title">Search</h3>
                            <form action="#" method="GET" class="blog-search-form">
                                <div class="blog-search-input-wrap">
                                    <input type="text"
                                           name="q"
                                           class="blog-search-input"
                                           placeholder="Search articles...">
                                    <button type="submit" class="blog-search-button">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Categories --}}
                        <div class="blog-sidebar-block">
                            <h3 class="blog-sidebar-title">Categories</h3>
                            <ul class="blog-sidebar-list">
                                <li><a href="#">All articles</a></li>
                                <li><a href="#">German Courses</a></li>
                                <li><a href="#">Exams: ÖSD & Goethe</a></li>
                                <li><a href="#">Study in Germany</a></li>
                                <li><a href="#">Ausbildung & Work</a></li>
                                <li><a href="#">Visa & Admin Tips</a></li>
                            </ul>
                        </div>

                        {{-- Popular Posts (static demo) --}}
                        <div class="blog-sidebar-block">
                            <h3 class="blog-sidebar-title">Popular</h3>
                            <ul class="blog-sidebar-posts">
                                <li>
                                    <a href="#">
                                        How Many Hours Do You Need for Each German Level?
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        A1 vs A2 vs B1: What Changes in Class?
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Studying in Germany: First 5 Steps With GLS Support
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </aside>
                </div>
            </div>
        </div>
    </section>

    {{-- ===============================
         BLOG GRID
       =============================== --}}
    <section class="section blog-list-section">
        <div class="container">
            <div class="blog-list-header">
                <h2 class="h-section-subtitle blog-list-title">
                    Latest Articles
                </h2>
                <p class="blog-list-subtitle">
                    Explore our newest posts about German learning, exams, and life in Germany.
                </p>
            </div>

            <div class="row g-4 blog-grid-row">
                {{-- EXAMPLE POST CARD 1 --}}
                <div class="col-md-6 col-lg-4">
                    <article class="blog-card">
                        <div class="blog-card-image-wrapper">
                            <a href="#">
                                <img src="{{ asset('assets/images/blog/blog-test.jpeg') }}"
                                     alt="German levels explained"
                                     class="blog-card-image">
                            </a>
                            <div class="blog-card-category">
                                German Courses
                            </div>
                        </div>
                        <div class="blog-card-body">
                            <h3 class="blog-card-title">
                                <a href="#">How Long Does It Take to Reach B1 in German?</a>
                            </h3>
                            <p class="blog-card-excerpt">
                                A clear breakdown of hours, course rhythm, and realistic expectations
                                for students aiming at B1 with GLS.
                            </p>
                            <div class="blog-card-meta">
                                <span class="blog-meta-item">⏱ 4 min read</span>
                                <span class="blog-meta-dot">•</span>
                                <span class="blog-meta-item">Courses</span>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- EXAMPLE POST CARD 2 --}}
                <div class="col-md-6 col-lg-4">
                    <article class="blog-card">
                        <div class="blog-card-image-wrapper">
                            <a href="#">
                                <img src="{{ asset('assets/images/blog/blog-test.jpeg') }}"
                                     alt="Online German course"
                                     class="blog-card-image">
                            </a>
                            <div class="blog-card-category">
                                Online Learning
                            </div>
                        </div>
                        <div class="blog-card-body">
                            <h3 class="blog-card-title">
                                <a href="#">Is Online German Learning with GLS Right for You?</a>
                            </h3>
                            <p class="blog-card-excerpt">
                                Compare in-person and online formats and discover which option fits
                                your schedule and goals better.
                            </p>
                            <div class="blog-card-meta">
                                <span class="blog-meta-item">⏱ 5 min read</span>
                                <span class="blog-meta-dot">•</span>
                                <span class="blog-meta-item">Online</span>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- EXAMPLE POST CARD 3 --}}
                <div class="col-md-6 col-lg-4">
                    <article class="blog-card">
                        <div class="blog-card-image-wrapper">
                            <a href="#">
                                <img src="{{ asset('assets/images/blog/blog-test.jpeg') }}"
                                     alt="Study in Germany"
                                     class="blog-card-image">
                            </a>
                            <div class="blog-card-category">
                                Study in Germany
                            </div>
                        </div>
                        <div class="blog-card-body">
                            <h3 class="blog-card-title">
                                <a href="#">From Morocco to German University: First Steps</a>
                            </h3>
                            <p class="blog-card-excerpt">
                                Learn how GLS guides you through language preparation,
                                document checks, and university applications.
                            </p>
                            <div class="blog-card-meta">
                                <span class="blog-meta-item">⏱ 7 min read</span>
                                <span class="blog-meta-dot">•</span>
                                <span class="blog-meta-item">Study</span>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- EXAMPLE POST CARD 4 --}}
                <div class="col-md-6 col-lg-4">
                    <article class="blog-card">
                        <div class="blog-card-image-wrapper">
                            <a href="#">
                                <img src="{{ asset('assets/images/blog/blog-test.jpeg') }}"
                                     alt="Ausbildung in Germany"
                                     class="blog-card-image">
                            </a>
                            <div class="blog-card-category">
                                Ausbildung
                            </div>
                        </div>
                        <div class="blog-card-body">
                            <h3 class="blog-card-title">
                                <a href="#">What Is Ausbildung and How Can GLS Help You?</a>
                            </h3>
                            <p class="blog-card-excerpt">
                                Understand the Ausbildung system in Germany and what German level
                                you need to start.
                            </p>
                            <div class="blog-card-meta">
                                <span class="blog-meta-item">⏱ 5 min read</span>
                                <span class="blog-meta-dot">•</span>
                                <span class="blog-meta-item">Career</span>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- EXAMPLE POST CARD 5 --}}
                <div class="col-md-6 col-lg-4">
                    <article class="blog-card">
                        <div class="blog-card-image-wrapper">
                            <a href="#">
                                <img src="{{ asset('assets/images/blog/blog-test.jpeg') }}"
                                     alt="Visa tips"
                                     class="blog-card-image">
                            </a>
                            <div class="blog-card-category">
                                Visa & Admin
                            </div>
                        </div>
                        <div class="blog-card-body">
                            <h3 class="blog-card-title">
                                <a href="#">Visa, Blocked Account, Proof of Language: What You Need</a>
                            </h3>
                            <p class="blog-card-excerpt">
                                An overview of documents, language certificates, and how GLS
                                supports your visa process.
                            </p>
                            <div class="blog-card-meta">
                                <span class="blog-meta-item">⏱ 8 min read</span>
                                <span class="blog-meta-dot">•</span>
                                <span class="blog-meta-item">Visa</span>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- EXAMPLE POST CARD 6 --}}
                <div class="col-md-6 col-lg-4">
                    <article class="blog-card">
                        <div class="blog-card-image-wrapper">
                            <a href="#">
                                <img src="{{ asset('assets/images/blog/blog-test.jpeg') }}"
                                     alt="Student life at GLS"
                                     class="blog-card-image">
                            </a>
                            <div class="blog-card-category">
                                GLS Stories
                            </div>
                        </div>
                        <div class="blog-card-body">
                            <h3 class="blog-card-title">
                                <a href="#">Inside GLS: What Your First Week Looks Like</a>
                            </h3>
                            <p class="blog-card-excerpt">
                                From placement test to first speaking exercises – get a feel
                                for your start at GLS.
                            </p>
                            <div class="blog-card-meta">
                                <span class="blog-meta-item">⏱ 3 min read</span>
                                <span class="blog-meta-dot">•</span>
                                <span class="blog-meta-item">Student Life</span>
                            </div>
                        </div>
                    </article>
                </div>

            </div>

            {{-- PAGINATION (static demo) --}}
            <div class="blog-pagination">
                <button class="blog-pagination-btn is-active">1</button>
                <button class="blog-pagination-btn">2</button>
                <button class="blog-pagination-btn">3</button>
                <button class="blog-pagination-btn">Next →</button>
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
                    <h2>Ready to Start Your German Course?</h2>
                    <p>
                        Turn what you read into real progress. Join one of our GLS German courses
                        in Rabat, Salé, Kénitra, Casablanca, Agadir or online.
                    </p>
                </div>
                <div class="blog-cta-actions">
                    <a href="/courses" class="btn btn-primary gls-btn-main">
                        View Courses
                    </a>
                    <a href="/contact" class="btn btn-outline-light gls-btn-outline">
                        Book a Consultation
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>
@endsection
