@extends('frontoffice.layouts.app')

@section('title', 'About | GLS Sprachenzentrum')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/exam/osd.css') }}">

@section('content')
<section class="hero-section section about-hero">
  <div class="container is-hero">
    <div class="hero_subtitle">ÖSD Exam in Morocco</div>
    <h1 class="hero_title">ÖSD German Certification at GLS</h1>

    <div class="hero-image">
      <img 
        src="{{ asset('assets/images/about/Centre-GLS-de-langue-Allemande.jpg') }}" 
        alt="ÖSD Exam GLS Morocco"
        class="full-image"
        loading="lazy"
      >
    </div>
  </div>
</section>

<div class="rich-text-section section">
  <div class="container">
    <div class="rich-text w-richtext">

      <p>
        Ever wondered how strong your German really is? How well you can speak, understand, and use the language in daily situations? 
        These are common questions for learners preparing for studies, work, or visa applications. This is exactly where the 
        <strong>ÖSD German exam</strong> becomes important.
      </p>

      <p>
        At <strong>GLS Sprachenzentrum Morocco</strong>, you can prepare for the official ÖSD exams and clearly find out your current 
        German level. Our training, mock tests, and exam-focused sessions help you understand where you stand and what you need to improve.
      </p>

      <p>
        Which ÖSD exam level fits you? How does the exam work? And how can you prepare effectively? Here you will find simple and clear 
        answers about A1, A2, B1, and B2 — plus our recommended preparation path with GLS.
      </p>

      <p>
        <strong>If you're interested in taking an ÖSD exam with GLS, you're in the right place!</strong>
      </p>

    </div>
  </div>
</div>

<section class="gls-more-info section">

    <div class="container gls-more-info-container">

        <h2 class="h-section-subtitle gls-more-info-title">
            Your Path From GLS Courses to the ÖSD Exam
        </h2>

        <div class="gls-more-info-grid">

            <!-- CARD 1 -->
            <div class="gls-info-card">
                <div class="gls-info-icon">
                    @include('frontoffice.svg.info-arrow')
                </div>
                <h3 class="gls-info-title">Complete Your<br>Level at GLS</h3>
                <div class="gls-info-text">
                    Each student finishes their German level (A1–B2) with structured training, practice, and internal evaluations.
                </div>
                <div class="gls-info-spacer"></div>
                <a href="/courses" class="gls-info-button w-button">Courses</a>
            </div>

            <!-- CARD 2 -->
            <div class="gls-info-card">
                <div class="gls-info-icon">
                    @include('frontoffice.svg.info-arrow')
                </div>
                <h3 class="gls-info-title">Internal Exam<br>Preparation</h3>
                <div class="gls-info-text">
                    After completing each level, students receive focused ÖSD-style preparation to ensure exam readiness.
                </div>
                <div class="gls-info-spacer"></div>
                <a href="/exams/osd" class="gls-info-button w-button">ÖSD Prep</a>
            </div>

            <!-- CARD 3 -->
            <div class="gls-info-card">
                <div class="gls-info-icon">
                    @include('frontoffice.svg.info-arrow')
                </div>
                <h3 class="gls-info-title">Exam<br>Scheduling</h3>
                <div class="gls-info-text">
                    Once ready, GLS schedules each student's official ÖSD exam date directly with the exam center.
                </div>
                <div class="gls-info-spacer"></div>
                <a href="/exams/osd#dates" class="gls-info-button w-button">View Dates</a>
            </div>

            <!-- CARD 4 -->
            <div class="gls-info-card">
                <div class="gls-info-icon">
                    @include('frontoffice.svg.info-arrow')
                </div>
                <h3 class="gls-info-title">The Official<br>ÖSD Exam</h3>
                <div class="gls-info-text">
                    Students take their official ÖSD exam and receive an internationally recognized certificate.
                </div>
                <div class="gls-info-spacer"></div>
                <a href="/exams/osd" class="gls-info-button w-button">Take Exam</a>
            </div>

        </div>

    </div>

</section>


<div class="rich-text-section section">
  <div class="container">
    <div class="rich-text w-richtext">

      <h2>Your Path From GLS Level Exams to the Official ÖSD Certification</h2>
      <p>
        Every student at <strong>GLS Sprachenzentrum Morocco</strong> follows a clear learning path. 
        After completing each level (A1–B2), you take an <strong>internal GLS exam</strong> with your teacher. 
        This ensures that your skills match the official ÖSD requirements before registering for the final exam.
      </p>
      <p>
        Once your teacher confirms you’re ready, GLS schedules your <strong>official ÖSD exam</strong> based on your level. 
        This guarantees that every student goes to the ÖSD exam fully prepared and confident.
      </p>

      <h2>What You Must Pass Before Taking the ÖSD Exam</h2>

      <h3>A1 – Listening Comprehension</h3>
      <p>
        You show that you can understand simple conversations, announcements, and basic everyday speech —
        exactly the situations practiced in the A1 course.
      </p>

      <h3>A1 – Grammar</h3>
      <p>
        This part checks your use of articles, sentence structure, and basic forms learned in class. 
        The tasks are practical and connected to daily real-life situations.
      </p>

      <h3>A1 – Reading Comprehension</h3>
      <p>
        You read short texts and show you can find key information. 
        This skill is essential for messages, emails, and simple written communication.
      </p>

      <h3>A1 – Written Expression</h3>
      <p>
        You respond to a short message or situation. 
        This proves you can communicate in writing at the basic level.
      </p>

      <h2>After Passing the Level Exam</h2>
      <h3>From GLS → ÖSD Certification</h3>
      <p>
        Once you pass your GLS level exam, we guide you directly to the <strong>official ÖSD exam</strong>.  
        GLS schedules your date, prepares your documents, and ensures you enter the certification fully ready.
      </p>
      <p>
        Your final <strong>ÖSD certificate</strong> is internationally recognized and valid for studies, work, Ausbildung, 
        or visa applications.
      </p>

    </div>
  </div>
</div>


<div class="courses-section section">
  <div class="container is-h-courses">

    <h2 class="h-section-subtitle">All Exams In-House!</h2>
    <div class="subtitle">Official ÖSD Exam Centre & GLS Examination (Soon)</div>

    <div class="exam-cards">

      <div class="exam-card">
        <h3 class="course-card_title">ÖSD Exam</h3>
        <div class="course-card_text">
          Official Austrian German exam recognized for study, work, Ausbildung, and visa applications. Available from A1 to B2 at GLS Morocco.
        </div>
        <a href="/exams/osd" class="button is-course-card w-button">Learn More</a>
      </div>

      <div class="exam-card is-orange">
        <h3 class="course-card_title">GLS Exam</h3>
        <div class="course-card_text">
          A new modern German exam developed by GLS for fast and clear level validation. Coming soon to all GLS centres.
        </div>
        <a href="#" class="button is-course-card w-button">Coming Soon</a>
      </div>

      <div class="exam-card is-yellow">
        <h3 class="course-card_title">Placement Test</h3>
        <div class="course-card_text">
          Not sure about your German level? Our quick placement test guides you to the right ÖSD preparation or GLS certification path.
        </div>
        <a href="/placement-test" class="button is-course-card w-button">Start Test</a>
      </div>

    </div>

  </div>
</div>


<section class="contact-section section">
        <div class="container is-2-col-grid">

            {{-- LEFT SIDE: CONTACT CARD --}}
            <div class="div-block-5-copy">
                <h2 class="h-section-subtitle-contact">Got Questions?<br>Get in touch!</h2>

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