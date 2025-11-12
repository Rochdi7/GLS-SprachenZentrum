@extends('frontoffice.layouts.app')

@section('title', 'Intensive German Courses – A1 to B2')

{{-- Page-specific stylesheet --}}
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/intensive/intensive-courses.css') }}">

@section('content')
<!-- =========================================================
     INTENSIVE COURSES – HERO SECTION
========================================================= -->
<section class="hero-section section intensive-hero">
  <div class="container is-hero">
    <div class="hero_subtitle">
      Intensive German courses from A1 to B2
    </div>

    <h1 class="hero_title">German Intensive Courses GLS</h1>

    <div class="hero-image">
      <img
  src="{{ asset('assets/images/intensive-courses/hero.png') }}"
  alt="Intensive German Courses A1–B2"
  class="full-image"
  loading="lazy"
/>

    </div>
  </div>
</section>

<!-- =========================================================
     INTENSIVE COURSES – RICH TEXT SECTION
========================================================= -->
<section class="rich-text-section section">
  <div class="container">
    <div class="rich-text w-richtext">
      <p>
        You’ve decided to take an <strong>intensive German course</strong> in Berlin — a great choice! 
        What could be more effective than learning German in Germany itself?
      </p>
      <p>
        The intensive German courses offered by <strong>GLS Sprachenzentrum</strong> in Berlin-Neukölln 
        are perfect for learners from <strong>A1 to B2 levels</strong>. 
        Our method combines structured grammar, active conversation, and real-life immersion — 
        so you don’t just learn German, you start <em>living</em> it.
      </p>
      <p>
        Whether you’re a beginner or preparing for a professional or academic path, 
        our intensive courses help you reach your goals quickly and confidently — 
        with experienced native-speaking teachers and a vibrant international community.
      </p>
    </div>
  </div>
</section>

<section class="home-courses-section section">

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
        </section>
            
       

<!-- =========================================================
     INTENSIVE COURSES – QUESTIONS & FREE CONSULTATION
========================================================= -->
<section class="rich-text-section section">
  <div class="container">
    <div class="rich-text w-richtext">
      <p>
        Many students want to learn German but don’t know where to start. They ask:
        <em>Do I need prior knowledge? Is there free dossier follow-up? If I get my language certificate, can I leave Morocco?</em>
        This section clarifies how GLS supports you from <strong>A1 to B2</strong> with real guidance.
      </p>

      <p>Here’s what you should know before choosing an intensive course with GLS:</p>

      <ol role="list">
        <li><strong>No prior knowledge?</strong> That’s okay. We place you at the right level (A1–B2) and guide your path step by step.</li>
        <li><strong>Free “suivi dossier” (application follow-up)?</strong> Yes — GLS includes free follow-up on your dossier for enrolled students (appointments, documents, and progress checks).</li>
        <li><strong>Language certificate & next steps.</strong> After obtaining your certificate (A1–B2), we advise on realistic pathways (study, Ausbildung, work)—including timelines and required documents.</li>
        <li><strong>Stress & intensity.</strong> Our intensive format is focused and structured so you progress quickly without burning out.</li>
      </ol>

      <p>Still unsure? Speak with our team — we’ll explain the process in detail, adapted to your goals.</p>
    </div>    
  </div>
</section>

<!-- ===============================
     INLINE CTA SECTION
================================ -->
<section class="inline-cta-section my-5">
  <div class="container">
    <div class="inline-cta-block mx-auto">
      <h2 class="heading-4 overlay-text">Book a Free Consultation</h2>
      <a href="{{ url('/booking-request') }}" class="button is-big is-white w-button">
        GLS assistance
      </a>
    </div>
  </div>
</section>

<section class="get-started-section section">
  <div class="container is-2-col-grid">
    
    {{-- ===== Left: Image Block ===== --}}
    <div class="get-started-image">
      <img 
        src="{{ asset('assets/images/about/subscribe.jpeg') }}" 
        alt="Students smiling at GLS Sprachenzentrum" 
        class="full-image rounded-4"
        loading="lazy">
    </div>

    {{-- ===== Right: Content Block ===== --}}
    <div class="get-started-card">
      <div class="box-rich-text w-richtext">
        <h2>Get Started Today!</h2>
        <h3>Start your German journey with GLS Morocco</h3>
        <p>
          Begin your path to mastering German at <strong>GLS Sprachenzentrum</strong>.  
          Our intensive and online courses are open to everyone — from absolute beginners to advanced learners.
        </p>
        <p>
          Visit one of our centers in <strong>Marrakech, Rabat, Kénitra, Salé, Casablanca,</strong> or <strong>Agadir</strong>,  
          and discover the most effective way to learn German in Morocco.
        </p>
        <p>
          Our team is here to guide you — step by step — toward your goals in language, study, and career.
        </p>
      </div>

      <a href="#" class="button w-button">Learn More</a>
    </div>
  </div>
</section>

<section class="contact-section section">
  <div class="container is-2-col-grid">

    {{-- LEFT SIDE: CONTACT CARD --}}
    <div class="div-block-5-copy">
      <h2 class="h-section-subtitle">Got Questions?<br>Get in touch!</h2>

      <div class="div-block-21">
        <a href="tel:+212669515019" class="link-block">
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
