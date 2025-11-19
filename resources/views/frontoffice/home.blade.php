@extends('frontoffice.layouts.app')

@section('content')
    {{-- ===========================

     HERO SECTION
=========================== --}}
    <section class="hero" aria-label="Intro">
        <div class="hero__bg" style="background-image: url('{{ asset('assets/images/hero-2.png') }}');"></div>

        <div class="badge b-blue b1">Jawohl</div>
        <div class="badge b-green b2">Wunderbar</div>
        <div class="badge b-orange b3">Guten Tag</div>
        <div class="badge b-violet b4">Freunde</div>

        <div class="hero__inner text-center">
            <h1 class="hero-title">
                Learn German in Morocco with GLS
            </h1>
        </div>
    </section>


    {{-- ===========================
     INTRO SECTION
=========================== --}}
    <section class="intro-section position-relative text-center">
        <!-- Gradient background layer -->
        <div class="intro-gradient"></div>

        <div class="container position-relative z-2 py-5">
            <!-- Floating white card -->
            <div class="intro-card shadow rounded-4 mx-auto p-4" style="max-width: 1020px;">
                <!-- Logo + Tagline -->
                <div class="text-center mb-4" style="margin-top: 20px !important;">
                    <img src="{{ asset('build/images/logo/gls-noir.png') }}" alt="GLS Logo" class="intro-logo mb-3"
                        style="width: 120px; height: auto;">
                    <p class="text-primary fw-medium small mb-0 letter-spacing-1">
                        Learn German in Berlin
                    </p>
                </div>

                <!-- Heading -->
                <h1 class="fw-bold mb-3" style="font-size: var(--h1-size); line-height: 1.2;">
                    Learn. Connect. Discover.
                </h1>

                <!-- Description -->
                <p class="lead text-muted mb-4" style="max-width: 620px; margin-inline: auto;">
                    Our goal is to empower our students through language and provide them with a safe environment
                    surrounded by like-minded individuals, allowing them to feel identified and represented.
                </p>

                <!-- Button -->
                <a href="#" class="btn btn-success px-4 py-2 rounded-pill fw-semibold">
                    View Our Courses
                </a>
            </div>
        </div>
    </section>



    {{-- ===========================
      REVIEWS SECTION (Updated for Morocco + GLS)
=========================== --}}
    <section class="reviews-carousel-section section">
        <div class="container is-reviews-title-block">
            <h2 class="h-section-subtitle is-reviews">Join the top rated German School in Morocco</h2>

            {{-- Custom block to display the overall rating --}}
            <div class="div-block-29 w-inline-block">
                {{-- SVG stars path data omitted here for brevity, assumed to be correct --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 176 32" fill="none"
                    class="review-stars-title">
                    <path
                        d="M15.0489 2.92705C15.3483 2.00574 16.6517 2.00574 16.9511 2.92705L19.3677 10.3647C19.5016 10.7768 19.8855 11.0557 20.3188 11.0557H28.1392C29.1079 11.0557 29.5107 12.2953 28.727 12.8647L22.4001 17.4615C22.0496 17.7161 21.903 18.1675 22.0369 18.5795L24.4535 26.0172C24.7529 26.9385 23.6984 27.7047 22.9147 27.1353L16.5878 22.5385C16.2373 22.2839 15.7627 22.2839 15.4122 22.5385L9.08533 27.1353C8.30162 27.7047 7.24714 26.9385 7.54649 26.0172L9.96315 18.5795C10.097 18.1675 9.95036 17.7161 9.59987 17.4615L3.27299 12.8647C2.48928 12.2953 2.89206 11.0557 3.86078 11.0557H11.6812C12.1145 11.0557 12.4984 10.7768 12.6323 10.3647L15.0489 2.92705Z"
                        fill="currentColor"></path>
                    <path
                        d="M51.0489 2.92705C51.3483 2.00574 52.6517 2.00574 52.9511 2.92705L55.3677 10.3647C55.5016 10.7768 55.8855 11.0557 56.3188 11.0557H64.1392C65.1079 11.0557 65.5107 12.2953 64.727 12.8647L58.4001 17.4615C58.0496 17.7161 57.903 18.1675 58.0369 18.5795L60.4535 26.0172C60.7529 26.9385 59.6984 27.7047 58.9147 27.1353L52.5878 22.5385C52.2373 22.2839 51.7627 22.2839 51.4122 22.5385L45.0853 27.1353C44.3016 27.7047 43.2471 26.9385 43.5465 26.0172L45.9631 18.5795C46.097 18.1675 45.9504 17.7161 45.5999 17.4615L39.273 12.8647C38.4893 12.2953 38.8921 11.0557 39.8608 11.0557H47.6812C48.1145 11.0557 48.4984 10.7768 48.6323 10.3647L51.0489 2.92705Z"
                        fill="currentColor"></path>
                    <path
                        d="M87.0489 2.92705C87.3483 2.00574 88.6517 2.00574 88.9511 2.92705L91.3677 10.3647C91.5016 10.7768 91.8855 11.0557 92.3188 11.0557H100.139C101.108 11.0557 101.511 12.2953 100.727 12.8647L94.4001 17.4615C94.0496 17.7161 93.903 18.1675 94.0369 18.5795L96.4535 26.0172C96.7529 26.9385 95.6984 27.7047 94.9147 27.1353L88.5878 22.5385C88.2373 22.2839 87.7627 22.2839 87.4122 22.5385L81.0853 27.1353C80.3016 27.7047 79.2471 26.9385 79.5465 26.0172L81.9631 18.5795C82.097 18.1675 81.9504 17.7161 81.5999 17.4615L75.273 12.8647C74.4893 12.2953 74.8921 11.0557 75.8608 11.0557H83.6812C84.1145 11.0557 84.4984 10.7768 84.6323 10.3647L87.0489 2.92705Z"
                        fill="currentColor"></path>
                    <path
                        d="M123.049 2.92705C123.348 2.00574 124.652 2.00574 124.951 2.92705L127.368 10.3647C127.502 10.7768 127.886 11.0557 128.319 11.0557H136.139C137.108 11.0557 137.511 12.2953 136.727 12.8647L130.4 17.4615C130.05 17.7161 129.903 18.1675 130.037 18.5795L132.454 26.0172C132.753 26.9385 131.698 27.7047 130.915 27.1353L124.588 22.5385C124.237 22.2839 123.763 22.2839 123.412 22.5385L117.085 27.1353C116.302 27.7047 115.247 26.9385 115.546 26.0172L117.963 18.5795C118.097 18.1675 117.95 17.7161 117.6 17.4615L111.273 12.8647C110.489 12.2953 110.892 11.0557 111.861 11.0557H119.681C120.114 11.0557 120.498 10.7768 120.632 10.3647L123.049 2.92705Z"
                        fill="currentColor"></path>
                    <path
                        d="M159.049 2.92705C159.348 2.00574 160.652 2.00574 160.951 2.92705L163.368 10.3647C163.502 10.7768 163.886 11.0557 164.319 11.0557H172.139C173.108 11.0557 173.511 12.2953 172.727 12.8647L166.4 17.4615C166.05 17.7161 165.903 18.1675 166.037 18.5795L168.454 26.0172C168.753 26.9385 167.698 27.7047 166.915 27.1353L160.588 22.5385C160.237 22.2839 159.763 22.2839 159.412 22.5385L153.085 27.1353C152.302 27.7047 151.247 26.9385 151.546 26.0172L153.963 18.5795C154.097 18.1675 153.95 17.7161 153.6 17.4615L147.273 12.8647C146.489 12.2953 146.892 11.0557 147.861 11.0557H155.681C156.114 11.0557 156.498 10.7768 156.632 10.3647L159.049 2.92705Z"
                        fill="currentColor"></path>
                </svg>
                <div><strong>4.9 / 5 (+677 Reviews)</strong></div>
            </div>
        </div>

        <div class="div-block-28 review-grid-layout">

            {{-- Track 1: Moves Leftwards --}}
            <div class="review-carousel_track is-animating-left">

                <div class="review-block review-card-inspired">
                    <div class="review-stars">
                        @include('frontoffice.partials.svg-stars')
                    </div>
                    <div class="text-block-9">
                        "GLS m’a beaucoup aidé à améliorer mon allemand. Les professeurs sont très patients et expliquent
                        avec passion."
                    </div>
                    <div class="text-block-10">– Salma Benyahia (2025)</div>
                </div>

                <div class="review-block review-card-inspired">
                    <div class="review-stars">
                        @include('frontoffice.partials.svg-stars')
                    </div>
                    <div class="text-block-9">
                        "Ich habe bei GLS einen großartigen Fortschritt gemacht. Die Atmosphäre war sehr freundlich und
                        motivierend!"
                    </div>
                    <div class="text-block-10">– Youssef El Amrani (2024)</div>
                </div>

                <div class="review-block review-card-inspired">
                    <div class="review-stars">
                        @include('frontoffice.partials.svg-stars')
                    </div>
                    <div class="text-block-9">
                        "The online course is so well structured, I finally understand German grammar! Totally worth it."
                    </div>
                    <div class="text-block-10">– Lina Zahraoui (2025)</div>
                </div>

                <div class="review-block review-card-inspired">
                    <div class="review-stars">
                        @include('frontoffice.partials.svg-stars')
                    </div>
                    <div class="text-block-9">
                        "Je recommande vivement GLS à tous ceux qui veulent apprendre l’allemand sérieusement mais dans une
                        ambiance agréable."
                    </div>
                    <div class="text-block-10">– Rachid El Khattabi (2024)</div>
                </div>

                <div class="review-block review-card-inspired">
                    <div class="review-stars">
                        @include('frontoffice.partials.svg-stars')
                    </div>
                    <div class="text-block-9">
                        "My teacher was amazing! The lessons were fun, interactive, and super helpful for my exams in Goethe
                        Institut."
                    </div>
                    <div class="text-block-10">– Imane Ait Lhaj (2025)</div>
                </div>

                <div class="review-block review-card-inspired">
                    <div class="review-stars">
                        @include('frontoffice.partials.svg-stars')
                    </div>
                    <div class="text-block-9">
                        "Ich liebe die Energie der Lehrer bei GLS. Sie geben sich wirklich Mühe, jeden Schüler zu
                        motivieren."
                    </div>
                    <div class="text-block-10">– Hamza Belkadi (2024)</div>
                </div>

                <div class="review-block review-card-inspired">
                    <div class="review-stars">
                        @include('frontoffice.partials.svg-stars')
                    </div>
                    <div class="text-block-9">
                        "GLS Sprachenzentrum est un endroit incroyable pour apprendre l’allemand à ton rythme. Très
                        professionnel."
                    </div>
                    <div class="text-block-10">– Nadia Cherkaoui (2025)</div>
                </div>

                <div class="review-block review-card-inspired">
                    <div class="review-stars">
                        @include('frontoffice.partials.svg-stars')
                    </div>
                    <div class="text-block-9">
                        "The school’s environment feels like a family. Everyone is kind, and the support for students is
                        excellent."
                    </div>
                    <div class="text-block-10">– Karim Berrada (2024)</div>
                </div>

                <div class="review-block review-card-inspired">
                    <div class="review-stars">
                        @include('frontoffice.partials.svg-stars')
                    </div>
                    <div class="text-block-9">
                        "Je suis très contente de mes cours à GLS, les professeurs sont dynamiques et les activités
                        culturelles sont superbes!"
                    </div>
                    <div class="text-block-10">– Hajar Bouziane (2025)</div>
                </div>

                <div class="review-block review-card-inspired">
                    <div class="review-stars">
                        @include('frontoffice.partials.svg-stars')
                    </div>
                    <div class="text-block-9">
                        "GLS really helped me prepare for my B1 exam. Great methodology and very clear explanations."
                    </div>
                    <div class="text-block-10">– Ayoub Idrissi (2025)</div>
                </div>

                {{-- Duplicate all 10 reviews for looping --}}
                @for ($i = 0; $i < 10; $i++)
                    <div class="review-block review-card-inspired">
                        <div class="review-stars">@include('frontoffice.partials.svg-stars')</div>
                        <div class="text-block-9">
                            "{{ ['GLS m’a beaucoup aidé...', 'Ich habe bei GLS...', 'The online course...', 'Je recommande vivement...', 'My teacher was amazing!', 'Ich liebe die Energie...', 'GLS Sprachenzentrum est...', 'The school’s environment...', 'Je suis très contente...', 'GLS really helped me...'][$i % 10] }}"
                        </div>
                        <div class="text-block-10">–
                            {{ ['Salma Benyahia', 'Youssef El Amrani', 'Lina Zahraoui', 'Rachid El Khattabi', 'Imane Ait Lhaj', 'Hamza Belkadi', 'Nadia Cherkaoui', 'Karim Berrada', 'Hajar Bouziane', 'Ayoub Idrissi'][$i % 10] }}
                            ({{ 2024 + ($i % 2) }})</div>
                    </div>
                @endfor

            </div>

            {{-- Track 2: Moves Rightwards --}}
            <div class="review-carousel_track is-alt is-animating-right">

                {{-- Duplicate same 10 reviews --}}
                @for ($i = 0; $i < 10; $i++)
                    <div class="review-block review-card-inspired">
                        <div class="review-stars">@include('frontoffice.partials.svg-stars')</div>
                        <div class="text-block-9">
                            "{{ ['GLS m’a beaucoup aidé...', 'Ich habe bei GLS...', 'The online course...', 'Je recommande vivement...', 'My teacher was amazing!', 'Ich liebe die Energie...', 'GLS Sprachenzentrum est...', 'The school’s environment...', 'Je suis très contente...', 'GLS really helped me...'][$i % 10] }}"
                        </div>
                        <div class="text-block-10">–
                            {{ ['Salma Benyahia', 'Youssef El Amrani', 'Lina Zahraoui', 'Rachid El Khattabi', 'Imane Ait Lhaj', 'Hamza Belkadi', 'Nadia Cherkaoui', 'Karim Berrada', 'Hajar Bouziane', 'Ayoub Idrissi'][$i % 10] }}
                            ({{ 2024 + ($i % 2) }})</div>
                    </div>
                @endfor

            </div>

        </div>

    </section>

    {{-- ===========================
     NOS SITES AU MAROC (Masonry Grid with Hover Video)
=========================== --}}
    <section class="section sites-maroc-section">
        <div class="container text-center mb-5">
            <h2 class="sites-title">Our Sites GLS</h2>
            <p class="sites-subtitle">Find your nearest German language center in Morocco.</p>
        </div>

        <div class="container sites-grid">

            <!-- 1. Rabat -->
            <div class="site-card small">
                <div class="site-video-wrapper">
                    <img src="{{ asset('assets/images/sites/rabat.jpg') }}" alt="GLS Kénitra" class="site-image">
                    <iframe
                        src="https://www.youtube.com/embed/MN6_-R2wvhY?autoplay=1&mute=1&controls=0&loop=1&playlist=MN6_-R2wvhY&modestbranding=1&playsinline=1"
                        frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen>
                    </iframe>
                </div>
                <div class="site-overlay">
                    <h3>RABAT</h3>
                </div>
            </div>

            <!-- 2. Kénitra -->
            <div class="site-card small">
                <img src="{{ asset('assets/images/sites/kenitra.jpg') }}" alt="GLS Kénitra" class="site-image">
                <video class="site-video" muted loop preload="metadata" playsinline>
                    <source src="https://yourcdn.com/videos/kenitra-cinematic.mp4" type="video/mp4">
                </video>
                <div class="site-overlay">
                    <h3>Kénitra</h3>
                </div>
            </div>

            <!-- 3. Marrakech (wide) -->
            <div class="site-card wide">
                <img src="{{ asset('assets/images/sites/marrakech.webp') }}" alt="GLS Marrakech" class="site-image">
                <video class="site-video" muted loop preload="metadata" playsinline>
                    <source src="https://raw.githubusercontent.com/Rochdi7/Gls-Videos/main/agadir-cinematic.mp4"
                        type="video/mp4">
                </video>
                <div class="site-overlay">
                    <h3>Marrakech</h3>
                </div>
            </div>

            <!-- 4. Salé -->
            <div class="site-card wide">
                <img src="{{ asset('assets/images/sites/sale.webp') }}" alt="GLS Salé" class="site-image">
                <video class="site-video" muted loop preload="metadata" playsinline>
                    <source src="https://yourcdn.com/videos/sale-cinematic.mp4" type="video/mp4">
                </video>
                <div class="site-overlay">
                    <h3>Salé</h3>
                </div>
            </div>

            <!-- 5. Agadir -->
            <div class="site-card small">
                <img src="{{ asset('assets/images/sites/agadir.avif') }}" alt="GLS Agadir" class="site-image">
                <video class="site-video" muted loop preload="metadata" playsinline>
                    <source src="https://yourcdn.com/videos/agadir-cinematic.mp4" type="video/mp4">
                </video>
                <div class="site-overlay">
                    <h3>Agadir</h3>
                </div>
            </div>

            <!-- 6. Casablanca -->
            <div class="site-card small">
                <img src="{{ asset('assets/images/sites/casablanca.jpg') }}" alt="GLS Casablanca" class="site-image">
                <video class="site-video" muted loop preload="metadata" playsinline>
                    <source src="https://yourcdn.com/videos/casablanca-cinematic.mp4" type="video/mp4">
                </video>
                <div class="site-overlay">
                    <h3>Casablanca</h3>
                </div>
            </div>


        </div>
    </section>
    <script>
        document.querySelectorAll('.site-card').forEach(card => {
            const video = card.querySelector('video');

            card.addEventListener('mouseenter', () => {
                video.play();
            });

            card.addEventListener('mouseleave', () => {
                video.pause();
                video.currentTime = 0;
            });
        });
    </script>


    <section class="home-courses-section section">

        {{-- 1. Banner Photo Block --}}
        <div class="container is-home-courses-photo">
            <h2 class="h-section-title">Our Courses</h2>
        </div>

        {{-- 2. German Intensive Courses (A1-B2) --}}
        <div class="container is-h-courses">
            <h2 class="h-section-subtitle-courses">German Intensive Courses</h2>
            {{-- Updated title to reflect the removal of C1 --}}
            <div class="subtitle">German Language Courses A1–B2</div>
            <p class="paragraph-2">
                {{-- Updated course schedule as requested --}}
                monday to friday 2hm30min per seacnce
            </p>

            {{-- Grid is now implicitly 4 columns wide on desktop via CSS changes --}}
            <div class="courses-cards">
                {{-- A1 Card (Default/Blue) --}}
                <div class="course-card">
                    <div class="couse-card_level">
                        <div class="course-card_level-circle">A</div>
                        <div class="course-card_level-circle">1</div>
                    </div>
                    <h3 class="course-card_title">Learn<br>German A1</h3>
                    <div class="course-card_text">Learn German basics.<br>Perfect for getting started!</div>
                    <a href="#" class="button is-course-card w-button">Learn More</a>
                </div>

                {{-- A2 Card (is-green) --}}
                <div class="course-card is-green">
                    <div class="couse-card_level">
                        <div class="course-card_level-circle">A</div>
                        <div class="course-card_level-circle">2</div>
                    </div>
                    <h3 class="course-card_title">Learn<br>German A2</h3>
                    <div class="course-card_text">Build a solid foundation of the german language.</div>
                    <a href="#" class="button is-course-card w-button">Learn More</a>
                </div>

                {{-- B1 Card (is-purple) --}}
                <div class="course-card is-purple">
                    <div class="couse-card_level">
                        <div class="course-card_level-circle">B</div>
                        <div class="course-card_level-circle">1</div>
                    </div>
                    <h3 class="course-card_title">Learn<br>German B1</h3>
                    <div class="course-card_text">Expand your German language knowledge!</div>
                    <a href="#" class="button is-course-card w-button">Learn More</a>
                </div>

                {{-- B2 Card (is-yellow) --}}
                <div class="course-card is-yellow">
                    <div class="couse-card_level">
                        <div class="course-card_level-circle">B</div>
                        <div class="course-card_level-circle">2</div>
                    </div>
                    <h3 class="course-card_title">Learn<br>German B2</h3>
                    <div class="course-card_text">Learn advanced German in our B2 course.</div>
                    <a href="#" class="button is-course-card w-button">Learn More</a>
                </div>

                {{-- C1 Card REMOVED --}}
            </div>

        </div>

        {{-- 3. Online courses & exams --}}
        <div class="container is-h-courses">
            {{-- Updated Section Title --}}
            <h2 class="h-section-subtitle-courses">Online courses & exams</h2>
            <div class="subtitle">Preparation, flexibility, and certification</div>

            <div class="courses-cards is-home-other-german-courses">

                {{-- 1. Online Courses (is-orange) --}}
                <div class="course-card is-orange">
                    <h3 class="course-card_title is-others">Online<br>Courses</h3>
                    <div class="course-card_text">Learn German from the comfort of your own home!</div>
                    <a href="#" class="button is-course-card w-button">Learn More</a>
                </div>

                {{-- 2. OSD Exam Preparation (is-green) --}}
                <div class="course-card is-green">
                    <h3 class="course-card_title is-others">GLS Exam<br>Preparation</h3>
                    <div class="course-card_text">
                        Prepare for the official GLS German Language Certification Exams in Morocco.
                    </div>
                    <a href="#" class="button is-course-card w-button">View Exam Programs</a>
                </div>


                {{-- 3. Goethe Exam Preparation (is-purple) --}}
                <div class="course-card is-purple">
                    <h3 class="course-card_title is-others">Goethe Exam<br>Preparation</h3>
                    <div class="course-card_text">Achieve the internationally recognized Goethe certificate.</div>
                    <a href="#" class="button is-course-card w-button">View Programs</a>
                </div>

                {{-- All old special courses removed --}}

            </div>
        </div>
    </section>

    {{-- ===========================
     LEARN MORE SECTION
=========================== --}}
    <section class="learn-more-section py-5 text-light" style="background-color: var(--off-black);">
        <div class="container py-5">
            <div class="row align-items-center g-5">

                {{-- Left Text Column --}}
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4 learn-more-title">
                        Learn German<br>With<br>GLS Morocco
                    </h2>
                    <p class="lead opacity-75 mb-0">
                        At GLS Morocco, learning German feels personal and inspiring.
                        Our small classes and experienced teachers ensure every student gets the attention they deserve -
                        from beginner to advanced level.
                    </p>
                </div>

                {{-- Right Cards Column --}}
                <div class="col-lg-6">
                    <div class="row g-4">

                        {{-- Card 1 – Pricing Information --}}
                        <div class="col-md-6">
                            <a href="#" class="h-learn-more-card">
                                <div class="h-learn-more-card_icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                        stroke="var(--light--green)" stroke-width="2" viewBox="0 0 24 24">
                                        <path
                                            d="M4.72787 16.1372C3.18287 14.5912 2.40987 13.8192 2.12287 12.8162C1.83487 11.8132 2.08087 10.7482 2.57287 8.61925L2.85587 7.39125C3.26887 5.59925 3.47587 4.70325 4.08887 4.08925C4.70187 3.47525 5.59887 3.26925 7.39087 2.85625L8.61887 2.57225C10.7489 2.08125 11.8129 1.83525 12.8159 2.12225C13.8189 2.41025 14.5909 3.18325 16.1359 4.72825L17.9659 6.55825C20.6569 9.24825 21.9999 10.5922 21.9999 12.2622C21.9999 13.9332 20.6559 15.2772 17.9669 17.9662C15.2769 20.6562 13.9329 22.0002 12.2619 22.0002C10.5919 22.0002 9.24687 20.6562 6.55787 17.9672L4.72787 16.1372Z">
                                        </path>
                                        <path
                                            d="M10.02 10.2892C10.801 9.50816 10.801 8.24183 10.02 7.46079C9.23894 6.67974 7.97261 6.67974 7.19156 7.46079C6.41051 8.24183 6.41051 9.50816 7.19156 10.2892C7.97261 11.0703 9.23894 11.0703 10.02 10.2892Z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="learn-card-bottom d-flex align-items-center justify-content-between w-100">
                                    <h3 class="fw-bold fs-4 mb-0">Pricing<br>Information</h3>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.84451 20L7.33722 17.5502L13.1778 11.799H0V8.20096H13.1778L7.33722 2.45933L9.84451 0L20 10L9.84451 20Z" />
                                    </svg>
                                </div>
                            </a>
                        </div>

                        {{-- Card 2 – Our Groups --}}
                        <div class="col-md-6">
                            <a href="#" class="h-learn-more-card">
                                <div class="h-learn-more-card_icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                        stroke="var(--light--green)" stroke-width="2" viewBox="0 0 24 24">
                                        <path
                                            d="M2 12C2 8.229 2 6.343 3.172 5.172C4.344 4.001 6.229 4 10 4H14C17.771 4 19.657 4 20.828 5.172C21.999 6.344 22 8.229 22 12V14C22 17.771 22 19.657 20.828 20.828C19.656 21.999 17.771 22 14 22H10C6.229 22 4.343 22 3.172 20.828C2.001 19.656 2 17.771 2 14V12Z">
                                        </path>
                                        <path d="M7 4V2.5M17 4V2.5M2.5 9H21.5" stroke-linecap="round"></path>
                                    </svg>
                                </div>
                                <div class="learn-card-bottom d-flex align-items-center justify-content-between w-100">
                                    <h3 class="fw-bold fs-4 mb-0">Our<br>Groups</h3>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.84451 20L7.33722 17.5502L13.1778 11.799H0V8.20096H13.1778L7.33722 2.45933L9.84451 0L20 10L9.84451 20Z" />
                                    </svg>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>
{{-- <script>
window.addEventListener('scroll', function () {
    const header = document.querySelector('.site-header');
    if (window.scrollY > 20) header.classList.add('is-stuck');
    else header.classList.remove('is-stuck');
});
</script> --}}

    {{-- ===========================
     ABOUT GLS MOROCCO SECTION – 9onsol’s Talks
=========================== --}}
    <section class="home-about-section section">
        <div class="container about-grid">

            {{-- Left Gradient Card --}}
            <div class="about-card text-light">
                <h2 class="h-section-subtitle mb-4">
                    Willkommen to<br>9onsol’s Talks
                </h2>
                <p class="lead mb-4">
                    Willkommen to <strong>9onsol’s Talks</strong>- the podcast that fuels your drive to conquer challenges
                    and achieve your goals! <br><br>
                    Hosted by <strong>@l9onsol</strong>, each episode brings authentic conversations with students,
                    professors,
                    and inspiring guests from <strong>GLS Morocco</strong> and beyond.
                    Tune in, learn, and get motivated to level up your journey - one talk at a time.
                </p>
                <a href="https://www.youtube.com/@9onsolsTalks" target="_blank"
                    class="btn btn-light rounded-pill fw-semibold px-4 py-2 mt-auto">
                    Listen Now
                </a>
            </div>

            {{-- Right Video Embed (Podcast Episode) --}}
            <div class="about-video">
                <iframe src="https://www.youtube.com/embed/wPYANoRURpU?si=p__Fgz2v7VuF_ubl"
                    title="9onsol’s Talks – GLS Morocco Podcast"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy">
                </iframe>
            </div>

        </div>
    </section>

    {{-- ===============================
     COOPERATION PARTNERS – Auto Marquee (All breakpoints)
================================ --}}
    <section class="partners-section text-center" aria-label="Our Cooperation Partners">
        <div class="container">
            <h2 class="partners-title">Our Cooperation Partners</h2>

            <div class="partners-marquee">
                <div class="partners-track">
                    {{-- ——— Set A ——— --}}
                    <img src="{{ asset('assets/images/home/goethe.png') }}" alt="Goethe-Institut" loading="lazy">
                    <img src="{{ asset('assets/images/home/marokkofc.png') }}" alt="Marokko FC" loading="lazy">
                    <img src="{{ asset('assets/images/home/osd.png') }}" alt="ÖSD Exam" loading="lazy">
                    <img src="{{ asset('assets/images/home/gizlogo-unternehmen-de-rgb-300.webp') }}"
                        alt="GIZ German Cooperation" loading="lazy">
                    <img src="{{ asset('assets/images/home/ECL_LOGO.png') }}" alt="ECL Language Certification"
                        loading="lazy">
                    <img src="{{ asset('assets/images/home/TLScontact_main.webp') }}" alt="TLScontact" loading="lazy">

                    {{-- ——— Set B (duplicate for seamless loop) ——— --}}
                    <img src="{{ asset('assets/images/home/goethe.png') }}" alt="Goethe-Institut" aria-hidden="true"
                        loading="lazy">
                    <img src="{{ asset('assets/images/home/marokkofc.png') }}" alt="Marokko FC" aria-hidden="true"
                        loading="lazy">
                    <img src="{{ asset('assets/images/home/osd.png') }}" alt="ÖSD Exam" aria-hidden="true"
                        loading="lazy">
                    <img src="{{ asset('assets/images/home/gizlogo-unternehmen-de-rgb-300.webp') }}"
                        alt="GIZ German Cooperation" aria-hidden="true" loading="lazy">
                    <img src="{{ asset('assets/images/home/ECL_LOGO.png') }}" alt="ECL Language Certification"
                        aria-hidden="true" loading="lazy">
                    <img src="{{ asset('assets/images/home/TLScontact_main.webp') }}" alt="TLScontact"
                        aria-hidden="true" loading="lazy">
                </div>
            </div>

            <noscript>
                <div class="partners-logos-noscript">
                    <img src="{{ asset('assets/images/home/goethe.png') }}" alt="Goethe-Institut">
                    <img src="{{ asset('assets/images/home/marokkofc.png') }}" alt="Marokko FC">
                    <img src="{{ asset('assets/images/home/osd.png') }}" alt="ÖSD Exam">
                    <img src="{{ asset('assets/images/home/gizlogo-unternehmen-de-rgb-300.webp') }}"
                        alt="GIZ German Cooperation">
                    <img src="{{ asset('assets/images/home/ECL_LOGO.png') }}" alt="ECL Language Certification">
                    <img src="{{ asset('assets/images/home/TLScontact_main.webp') }}" alt="TLScontact">
                </div>
            </noscript>
        </div>
    </section>


    <section class="contact-section section">
        <div class="container is-2-col-grid">

            {{-- LEFT SIDE: CONTACT CARD --}}
            <div class="div-block-5-copy">
                <h2 class="h-section-subtitle">Got Questions?<br>Get in touch!</h2>

                <div class="div-block-21">
                    <a href="tel:+212600000000" class="link-block">
                        <div class="text-block-3">
                            <span class="text-span">CALL US<br></span>+212 6 69 51 50 19
                        </div>
                    </a>
                    <a href="mailto:info@glssprachenzentrum.ma" class="link-block-2">
                        <div class="text-block-3">
                            <span class="text-span">EMAIL US<br></span>info@glssprachenzentrum.ma
                        </div>
                    </a>
                </div>

                <div class="text-block-3 visit-block">
                    <span class="text-span">VISIT US<br></span>
                    14 Bd de Paris, 1er étage N°8, Casablanca 20000<br>
                    Avenue Yacoub El Mansour, Immeuble Espace Guéliz, 3ème étage Bureau 28, Marrakech<br>
                    Avenue Fal Ould Oumeir, Immeuble 77, 1er étage N°1, Agdal, Rabat<br>
                    Avenue Mohammed V, Bureaux Rania, 7ème étage, Kénitra<br>
                    Avenue Mohamed V Rue Halima N°12 Diyar, Salé<br>
                    Av. Massoude Al Wafkaoui, Place des taxis, Hay Essalam, Agadir
                </div>

                <div class="footer-socials-block">
                    <div class="text-block-3"><span class="text-span">FOLLOW US</span></div>
                    <div class="div-block-20">
                        <a href="#" class="footer-social-link ig"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="footer-social-link fb"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="footer-social-link yt"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="footer-social-link wa"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
            </div>

            {{-- RIGHT SIDE: MAP --}}
            <a href="https://maps.app.goo.gl/g4PjrPB7wHQAqrSZA" target="_blank" class="div-block-7">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3331.744621379457!2d-6.836039!3d33.978558!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda76b6d63b66b1d%3A0x3c6ee0a64f273aa2!2sAgdal%2C%20Rabat!5e0!3m2!1sen!2sma!4v1700000000000"
                    allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </a>

        </div>
    </section>
@endsection
