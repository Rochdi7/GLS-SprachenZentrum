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
                <img src="{{ asset('assets/images/online-courses/hero.png') }}" loading="lazy"
                    sizes="(max-width: 2880px) 100vw, 2880px"
                    srcset="
        {{ asset('assets/images/online-courses/hero.png') }} 500w,
        {{ asset('assets/images/online-courses/hero.png') }} 800w,
        {{ asset('assets/images/online-courses/hero.png') }} 1080w,
        {{ asset('assets/images/online-courses/hero.png') }} 2880w
    "
                    alt="" class="full-image rounded" />

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

    <section class="gls-online-info section">

        <div class="gls-online-info-container container">

            <!-- LEFT TEXT BLOCK -->
            <div class="gls-online-info-text">

                <div class="gls-online-info-richtext w-richtext">
                    <h2>GLS is also online!</h2>
                    <h3>Find your Online German Course</h3>
                    <p>
                        Learning German online works extremely well — and at GLS you get the same teaching
                        quality as in our in-person classes. All our materials are digitized, our teachers
                        are live, and the experience is seamless from anywhere in the world.
                    </p>
                    <p>
                        Whether you're a complete beginner or preparing for advanced exams, GLS offers
                        online classes from A1 to C1 with interactive lessons, small groups and amazing
                        teachers. Improve your German skills right from home!
                    </p>
                </div>

                <a href="/en/online-registration" class="gls-button-big w-button">
                    Enroll Today
                </a>
            </div>

            <!-- RIGHT IMAGE BLOCK -->
            <div class="gls-online-info-image">
                <img src="{{ asset('assets/images/online-courses/online.png') }}" alt="Online German Classes"
                    class="gls-full-image">

            </div>

        </div>

    </section>

    <section class="gls-more-info section">

        <div class="container gls-more-info-container">

            <h2 class="h-section-subtitle gls-more-info-title">
                Learn More about our Online German Courses!
            </h2>

            <div class="gls-more-info-grid">

                <!-- CARD 1 -->
                <div class="gls-info-card">
                    <div class="gls-info-icon">
                        @include('frontoffice.svg.info-arrow')
                    </div>
                    <h3 class="gls-info-title">Pricing<br>Information</h3>
                    <div class="gls-info-text">
                        Good and effective education should not be a luxury. Discover affordable, high-quality education.
                    </div>
                    <div class="gls-info-spacer"></div>
                    <a href="/en/courses/pricing" class="gls-info-button w-button">Pricing</a>
                </div>

                <!-- CARD 2 -->
                <div class="gls-info-card">
                    <div class="gls-info-icon">
                        @include('frontoffice.svg.info-arrow')
                    </div>
                    <h3 class="gls-info-title">Exam<br>Registration</h3>
                    <div class="gls-info-text">
                        Licensed TestDaF Centre & Licensed telc Examination Centre.
                    </div>
                    <div class="gls-info-spacer"></div>
                    <a href="/en/german-exams" class="gls-info-button w-button">Learn More</a>
                </div>

                <!-- CARD 3 -->
                <div class="gls-info-card">
                    <div class="gls-info-icon">
                        @include('frontoffice.svg.info-arrow')
                    </div>
                    <h3 class="gls-info-title">Schedule<br>Information</h3>
                    <div class="gls-info-text">
                        Want to start your German course today? No problem — join a free trial lesson!
                    </div>
                    <div class="gls-info-spacer"></div>
                    <a href="/en/courses/course-schedules" class="gls-info-button w-button">Course Schedules</a>
                </div>

                <!-- CARD 4 -->
                <div class="gls-info-card">
                    <div class="gls-info-icon">
                        @include('frontoffice.svg.info-arrow')
                    </div>
                    <h3 class="gls-info-title">Course<br>Registration</h3>
                    <div class="gls-info-text">
                        GLS prepares you for university, skilled jobs, or your new life in Berlin.
                    </div>
                    <div class="gls-info-spacer"></div>
                    <a href="/en/online-registration" class="gls-info-button w-button">Enroll Today</a>
                </div>

            </div>

        </div>

    </section>

    <section class="rich-text-section section">
        <div class="container">
            <div class="rich-text w-richtext">

                <h2>9onsol Talks – The German Dream | Episode 1</h2>
                <h3>Should you choose university studies or vocational training (Ausbildung) in Germany? 🇩🇪</h3>

                <!-- Video Embed -->
                <figure style="padding-bottom: 56.25%" class="w-richtext-align-fullwidth w-richtext-figure-type-video">
                    <div>
                        <iframe width="560" height="315"
                            src="https://www.youtube.com/embed/LaCmmzYKQG4?si=2JtbYg2r430zCAx1"
                            title="YouTube video player" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                </figure>

                <h2>Episode Overview</h2>

                <p>
                    In this first episode of <strong>9onsol Talks</strong>, titled "<strong>The German Dream</strong>," the
                    GLS Sprachenzentrum team discusses the real options available to young people dreaming of moving to
                    Germany—whether through university studies or vocational training (Ausbildung).<br><br>

                    The podcast explores why Germany has become such a powerful dream for many Moroccan youth. It features
                    real-life migration stories, challenges related to visa applications (such as blocked accounts and
                    consular delays), and the psychological pressure of moving abroad.<br><br>

                    The discussion dives into the key differences between university education and Ausbildung, what level of
                    German you need (like the DSH exam), and how to choose the right path depending on your goals, budget,
                    and timeline. It's an insightful episode for anyone thinking seriously about building a future in
                    Germany.
                </p>

            </div>
        </div>
    </section>

    <section class="section is-off-white">

        <!-- BLOCK 1 -->
        <div class="container is-2-col-grid">

            <div class="get-started-contents">
                <div class="box-rich-text w-richtext">
                    <h2>Learn on your terms!</h2>
                    <p>
                        No matter whether your level is A1 or C1 – in our online German course you will learn everything you
                        need.
                        <strong>You can join in at any time and participate for as long as you like.</strong>
                    </p>
                    <p>
                        In the A1 Online German Course you can do just that. Or would you like to prepare for a university
                        entry exam and want to hone your skills? Then the C1 Online German Course is perfect for you.
                    </p>
                </div>

                <a href="/en/online-registration" class="button is-big w-button">Enroll Today</a>
            </div>

            <div class="image-block">
                <img src="{{ asset('assets/images/online-courses/right-img.png') }}" alt="GLS Online German Course"
                    class="full-image" loading="lazy">

            </div>

        </div>

        <!-- BLOCK 2 -->
        <div class="container is-2-col-grid">

            <div class="image-block">
                <img src="{{ asset('assets/images/online-courses/left-img.png') }}" alt="GLS Online Course Students"
                    class="full-image" loading="lazy">

            </div>

            <div class="get-started-contents">
                <h2 class="h-section-subtitle">Online German Courses</h2>
                <div class="subtitle">Fight boredom by starting an online German course</div>

                <div class="box-rich-text w-richtext">
                    <p>
                        Our experienced and highly motivated teachers offer entertaining and highly effective German courses
                        online.
                        In small groups you will train all relevant skills.
                    </p>

                    <p>
                        The online German courses last 180 minutes per day and include a short break, combining excitement
                        with
                        structured learning.
                    </p>
                </div>
            </div>

        </div>

    </section>


    <!-- CTA -->
    <section class="inline-cta-section section">
        <div class="inline-cta-block">
            <h2 class="heading-cta">Ganz Einfach!<br>Don't Overthink It.</h2>

            <p class="cta-box-subtext">
                This way we can prevent headaches, and make sure you are full of energy
                to keep learning effectively. The online German course at GLS requires
                active participation — no passive watching, real learning.
            </p>

            <a href="/online-registration" class="cta-btn">Enroll Today!</a>
        </div>
    </section>




@endsection
