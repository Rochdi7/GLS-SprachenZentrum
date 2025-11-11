@extends('frontoffice.layouts.app')

@section('title', 'About | GLS Sprachenzentrum')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/about/about.css') }}">

@section('content')
<section class="hero-section section about-hero">
  <div class="container is-hero">
    <div class="hero_subtitle">We take German courses seriously!</div>
    <h1 class="hero_title">German Language School ‘GLS Sprachenzentrum’</h1>

    <div class="hero-image">
      <img 
        src="{{ asset('assets/images/about/Centre-GLS-de-langue-Allemande.jpg') }}" 
        alt="Students learning German at GLS Sprachenzentrum" 
        class="full-image" 
        loading="lazy"
      >
    </div>
  </div>
</section>

<section class="rich-text-section section">
  <div class="container">
    <div class="rich-text w-richtext">

      <h2>Learn German with the Experts in Morocco</h2>
      <p>
        Welcome to <strong>GLS Sprachenzentrum</strong> — Morocco’s leading center for learning German.  
        With branches in <strong>Marrakech, Rabat, Kénitra, Salé, Casablanca,</strong> and <strong>Agadir</strong>,  
        we provide an immersive and modern environment where students can learn German effectively and confidently.
      </p>
      <p>
        Our goal is to make German accessible to everyone through high-quality teaching and real-life communication.  
        Whether you’re learning for work, studies, or travel — our programs are designed to help you reach your goals.
      </p>

      <h2>Intensive and Online German Courses</h2>
      <h3>Learn wherever you are in Morocco</h3>
      <p>
        At <strong>GLS Sprachenzentrum</strong>, <strong>intensive courses</strong> offer you language training with a high degree of learning intensity and effectiveness.  
        Daily language practice helps you improve your expression and comprehension in a short time.
      </p>
      <p>
        For students who cannot attend in person, our <strong>online courses</strong> bring the GLS classroom experience directly to you — interactive, flexible, and personalized.
      </p>

    </div>
  </div>
</section>

<section class="section is-off-white">

  {{-- ===== FIRST ROW: Text Left / Image Right ===== --}}
  <div class="container is-2-col-grid">
    <div class="get-started-contents">
      <div class="box-rich-text w-richtext">
        <h2>Fair and Accessible Prices</h2>
        <h3>Quality German education for everyone</h3>
        <p>
          At <strong>GLS Sprachenzentrum</strong>, we believe that learning German should be both <strong>excellent</strong> and <strong>affordable</strong>.  
          Our centers across Morocco offer flexible schedules and transparent pricing — so that everyone can access world-class language training.
        </p>
        <p>
          We regularly offer <strong>trial sessions</strong> and placement tests to help you start at the right level.  
          Experience the difference of GLS — where quality teaching meets real results.
        </p>
      </div>
    </div>

    <div class="image-block">
      <img
        src="{{ asset('assets/images/about/grid-1.png') }}"
        alt="GLS Sprachenzentrum students during class"
        class="full-image"
        loading="lazy">
    </div>
  </div>

  {{-- ===== SECOND ROW: Image Left / Text Right ===== --}}
  <div class="container is-2-col-grid">
    <div class="image-block is-1-1">
      <img
        src="{{ asset('assets/images/about/grid1.jpeg') }}"
        alt="Students at GLS Café relaxing between classes"
        class="full-image is-ratio"
        loading="lazy">
    </div>

    <div class="get-started-contents">
      <div class="box-rich-text w-richtext">
        <h2>More Than Just German Classes</h2>
        <p>
          Each GLS center is a space for <strong>learning, connection, and culture</strong>.  
          Between lessons, students enjoy a welcoming environment where they can practice German naturally and meet people from different cities and backgrounds.
        </p>
        <p>
          Our classrooms are equipped with modern tools and free Wi-Fi, while our teachers focus on interactive communication.  
          At GLS, you don’t just study German — you live it.
        </p>
      </div>
    </div>
  </div>

</section>

<section class="rich-text-section section">
  <div class="container">
    <div class="rich-text w-richtext">
      <h2>Exams and Certifications</h2>
      <h3>Get ready for something new in Morocco</h3>
      <p>
        <strong>GLS Sprachenzentrum</strong> is proud to bring international German exams to Morocco.  
        In addition to our intensive and online courses, we will soon offer official <strong>ÖSD</strong> and <strong>GLS exams</strong> — 
        giving students the opportunity to certify their language level locally.
      </p>
      <p>
        Our programs cover levels <strong>A1 to B2</strong>, helping you build a solid foundation for academic, professional, and personal success in Germany or anywhere else.
      </p>
      <p>
        Our teachers are certified professionals who guide you step by step, ensuring that each student achieves their potential.
      </p>
      <p>
        Join us and be among the first in Morocco to take part in our <strong>GLS German exams</strong> — coming soon!
      </p>
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
