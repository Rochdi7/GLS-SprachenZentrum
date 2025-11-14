@extends('frontoffice.layouts.app')

@section('title', 'Online German Courses – GLS Sprachenzentrum')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/online/online-courses.css') }}">

@section('content')

<section class="intensive-course section">

    <!-- OPTIONAL COURSE HERO (HIDDEN BY DEFAULT) -->
    <div class="container is-course-hero hide">
        <div>
            <div class="couse-card_level is-big">
                <div class="course-level-circle">A</div>
                <div class="course-level-circle">1</div>
            </div>
        </div>

        <div class="text-block-4">German A1 Course</div>

        <h1 class="hero_title is-course">
            Learn German in Berlin at Level A1
        </h1>

        <p class="course-hero_paragraph">
            You have little or no previous knowledge of the German language?<br>
            Then German A1 is your course of choice.
        </p>

        <a href="/en/online-registration" class="button is-big is-white w-button">
            Enroll Today!
        </a>
    </div>

    <!-- MAIN ONLINE COURSE HERO BLOCK -->
    <div class="container is-online-course-hero-content">

        <!-- LEFT IMAGE -->
        <div id="w-node-online-left" class="div-block-38">
            <img 
                src="https://cdn.prod.website-files.com/66d00cd15406f5f0f85bbb6e/6834a391aa55e02c99fa7396_die-deutschule-12.avif"
                loading="lazy"
                sizes="(max-width: 2880px) 100vw, 2880px"
                srcset="
                    https://cdn.prod.website-files.com/66d00cd15406f5f0f85bbb6e/6834a391aa55e02c99fa7396_die-deutschule-12-p-500.avif 500w,
                    https://cdn.prod.website-files.com/66d00cd15406f5f0f85bbb6e/6834a391aa55e02c99fa7396_die-deutschule-12-p-800.avif 800w,
                    https://cdn.prod.website-files.com/66d00cd15406f5f0f85bbb6e/6834a391aa55e02c99fa7396_die-deutschule-12-p-1080.avif 1080w,
                    https://cdn.prod.website-files.com/66d00cd15406f5f0f85bbb6e/6834a391aa55e02c99fa7396_die-deutschule-12.avif 2880w"
                alt=""
                class="full-image rounded"
            >
        </div>

        <!-- RIGHT TEXT -->
        <div class="div-block-37">
            <div class="text-block-4-copy">
                From the comfort of your own home
            </div>

            <h1 class="hero_title is-course is-online-course">
                Learn German Online
            </h1>

            <p class="course-hero_paragraph">
                From A1 to C1, courses for all levels and for students all across the world!  
                Learn German online with live classes, small groups and amazing teachers —  
                all from the comfort of your own home.
            </p>

            <a href="/en/online-registration" class="button is-big w-button">
                Enroll Today!
            </a>
        </div>

    </div>

</section>

<section class="info-section section is-online-classes">

    <div class="container is-h-courses">
        <h2 class="h-section-subtitle is-info">
    Online Courses Information <br>
</h2>


        <!-- INFO CARDS GRID -->
        <div class="info">

            <!-- Graduation -->
            <div class="info-card">
                <div class="couse-card_level is-black">
                    @include('frontoffice.svg.info-graduation')
                </div>
                <h3 class="course-card_title"><strong>Graduation</strong></h3>
                <div class="course-card_text">
                    German Certificate after Exam (CEFR)
                </div>
            </div>

            <!-- Duration -->
            <div class="info-card">
                <div class="couse-card_level is-black">
                    @include('frontoffice.svg.info-duration')
                </div>
                <h3 class="course-card_title"><strong>Duration</strong></h3>
                <div class="course-card_text">
                    8 weeks<br>
                    16 lessons/week (Tuesday–Friday)
                </div>
            </div>

            <!-- Course Times -->
            <div class="info-card">
                <div class="couse-card_level is-black">
                    @include('frontoffice.svg.info-times')
                </div>
                <h3 class="course-card_title"><strong>Course Times</strong></h3>
                <div class="course-card_text">
                    09.15 – 12.30<br>
                    17.00 – 20.15
                </div>
            </div>

            <!-- Cost -->
            <div class="info-card">
                <div class="couse-card_level is-black">
                    @include('frontoffice.svg.info-cost')
                </div>
                <h3 class="course-card_title"><strong>Cost</strong></h3>
                <div class="course-card_text">
                    318€ (4 weeks)<br>
                    636€ (8 weeks)<br><br>
                    Exam: 118€ (internal)<br>
                    Exam: 149€ (external)
                </div>
            </div>

        </div>

        <!-- INLINE CTA -->
        <div class="inline-cta-block is-online-courses">

    <h2 class="heading-4">Online Course Highlights</h2>

    <div class="div-block-14">
        <div class="highlight-pill b no-outline">Live Classes</div>
        <div class="highlight-pill a no-outline">Tuesday to Friday</div>
        <div class="highlight-pill d no-outline">Small groups</div>
        <div class="highlight-pill c no-outline">All levels from A1 to C1</div>
        <div class="highlight-pill e no-outline">Online resources</div>
        <div class="highlight-pill f no-outline">Students worldwide</div>
        <div class="highlight-pill g no-outline">Enroll anytime</div>
    </div>

    <a href="/en/online-registration" class="button is-big is-white w-button">
        Enroll Today!
    </a>

</div>


    </div>
</section>


@endsection
