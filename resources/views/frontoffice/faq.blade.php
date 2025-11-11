@extends('frontoffice.layouts.app')

@section('title', 'FAQ – Frequently Asked Questions')
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/faq/faq.css') }}">

@section('content')
{{-- ===============================
     HERO SECTION – FAQ PAGE
     =============================== --}}
<section class="hero-section section is-no-image is-faq">
  <div class="container is-hero text-center">
    <h1 class="hero_title">Frequently Asked Questions</h1>

    <p class="text-light mt-3" style="max-width: 720px; margin: 0 auto; line-height: 1.6;">
      At GLS Sprachenzentrum Morocco, learning German means building a strong foundation for your academic and professional future.
      We help students prepare for studies, Ausbildung programs, and career opportunities in Germany.
    </p>

    {{-- ===== Search ===== --}}
    <div class="faq-form w-form mt-4">
      <form id="faq-search-form" class="faq-form-block" aria-label="FAQ Search">
        <input
          type="text"
          id="faq-search"
          name="faq-search"
          class="faq-search-field w-input"
          maxlength="256"
          placeholder="Search your question..."
          autocomplete="off">
      </form>
    </div>
  </div>
</section>

{{-- ===============================
     FAQ SECTION – Filters + Accordion
     =============================== --}}
<section class="section faq-section py-5">
  <div class="container text-center mb-5">
    {{-- ===== FILTER BUTTONS ===== --}}
    <div class="faq-filters d-flex flex-wrap justify-content-center gap-3 mb-4">
      <button class="faq-filter-btn active" data-category="all">View All</button>
      <button class="faq-filter-btn" data-category="courses">Courses</button>
      <button class="faq-filter-btn" data-category="recognition">Recognition</button>
      <button class="faq-filter-btn" data-category="study">Study & Ausbildung</button>
      <button class="faq-filter-btn" data-category="exams">Exams & Certifications</button>
      <button class="faq-filter-btn" data-category="online">Online & In-person</button>
      <button class="faq-filter-btn" data-category="pricing">Pricing & Services</button>
    </div>
  </div>

  {{-- ===== ACCORDION SECTION ===== --}}
  <div class="container faq-accordion">

    {{-- =============================== --}}
    {{-- COURSES --}}
    {{-- =============================== --}}
    <div class="faq-item" data-category="courses">
      <button class="faq-question">
        What German course levels does GLS offer?
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-answer">
        <p>
          GLS Sprachenzentrum offers German courses for all levels — <strong>A1, A2, B1, and B2</strong>.
          You can start from beginner and progress to advanced proficiency, according to your visa or academic goals.
        </p>
        <p>
          Our professors are among the most qualified and experienced in Morocco, and our method ensures quick, confident language acquisition.
        </p>
      </div>
    </div>

    {{-- =============================== --}}
    {{-- RECOGNITION --}}
    {{-- =============================== --}}
    <div class="faq-item" data-category="recognition">
      <button class="faq-question">
        Are GLS courses officially recognized?
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-answer">
        <p>
          Yes. Our courses follow international standards and are aligned with embassy and institution requirements such as
          <strong>Goethe-Institut</strong>, <strong>telc</strong>, and <strong>ÖSD</strong>.
        </p>
        <p>
          Our training certificates are accepted by official German authorities for visa, study, and work applications.
        </p>
      </div>
    </div>

    {{-- =============================== --}}
    {{-- STUDY PATHS – UNIVERSITY --}}
    {{-- =============================== --}}
    <div class="faq-item" data-category="study">
      <button class="faq-question">
        I want to complete my university studies in Germany. How can GLS help?
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-answer">
        <p>GLS provides full support for students who wish to pursue higher education in Germany:</p>
        <ul style="text-align:left; list-style-type:'• '; padding-left:1.2rem;">
          <li>Preparation for B1–B2 level language requirements</li>
          <li>Visa-compliant training certificates</li>
          <li>Assistance with blocked bank account setup</li>
          <li>Document preparation and translation</li>
          <li>Free follow-up of your student visa dossier</li>
        </ul>
      </div>
    </div>

    {{-- =============================== --}}
    {{-- STUDY PATHS – AUSBILDUNG --}}
    {{-- =============================== --}}
    <div class="faq-item" data-category="study">
      <button class="faq-question">
        I want to start an Ausbildung in Germany. What support do you provide?
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-answer">
        <p>GLS supports candidates who wish to start vocational training (Ausbildung) in Germany:</p>
        <ul style="text-align:left; list-style-type:'• '; padding-left:1.2rem;">
          <li>Language training to reach A2–B1 level</li>
          <li>Understanding Ausbildung requirements and contracts</li>
          <li>Preparation and translation of all necessary documents</li>
          <li>Continuous guidance until visa approval</li>
        </ul>
      </div>
    </div>

    {{-- =============================== --}}
    {{-- STUDY PATHS – WORK CONTRACT --}}
    {{-- =============================== --}}
    <div class="faq-item" data-category="study">
      <button class="faq-question">
        I have a work contract in Germany. Can GLS assist me?
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-answer">
        <p>Yes. GLS assists professionals who already have or are applying for a German work contract:</p>
        <ul style="text-align:left; list-style-type:'• '; padding-left:1.2rem;">
          <li>Specialized German courses for work and communication</li>
          <li>Visa-compliant language certificates</li>
          <li>Administrative and document preparation support</li>
        </ul>
      </div>
    </div>

    {{-- =============================== --}}
    {{-- STUDY PATHS – INVITATION --}}
    {{-- =============================== --}}
    <div class="faq-item" data-category="study">
      <button class="faq-question">
        I received an invitation from someone in Germany. What should I do?
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-answer">
        <p>GLS assists individuals traveling to Germany through private invitations by providing:</p>
        <ul style="text-align:left; list-style-type:'• '; padding-left:1.2rem;">
          <li>Language support for embassy interviews</li>
          <li>Guidance on visa documentation</li>
          <li>Translation and verification of official papers</li>
        </ul>
      </div>
    </div>

    {{-- =============================== --}}
    {{-- EXAMS & CERTIFICATIONS --}}
    {{-- =============================== --}}
    <div class="faq-item" data-category="exams">
      <button class="faq-question">
        Which exams and certifications does GLS prepare students for?
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-answer">
        <p>GLS prepares students for all major international German language exams:</p>
        <ul style="text-align:left; list-style-type:'• '; padding-left:1.2rem;">
          <li><strong>ÖSD (Österreichisches Sprachdiplom Deutsch)</strong> – recognized across Europe and accepted for visa and study applications</li>
          <li><strong>Goethe-Institut Exams</strong> – globally recognized certification for all proficiency levels</li>
          <li><strong>GLS Certified Exam</strong> – our upcoming in-house examination designed to meet embassy and training center standards (coming soon)</li>
        </ul>
        <p>
          All GLS courses are aligned with these certification systems, ensuring that you are fully prepared for your official test.
        </p>
      </div>
    </div>

    {{-- =============================== --}}
    {{-- ONLINE & IN-PERSON COURSES --}}
    {{-- =============================== --}}
    <div class="faq-item" data-category="online">
      <button class="faq-question">
        What is the difference between online and in-person courses at GLS?
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-answer">
        <h6><strong>1. Online Courses</strong></h6>
        <p>
          Online courses are conducted live by our teachers in real-time virtual classrooms.  
          The pricing for online programs is fixed for all cities in Morocco.
        </p>

        <h6 class="mt-3"><strong>2. In-person Courses</strong></h6>
        <p>
          In-person courses are available in all GLS centers — Rabat, Salé, Kénitra, Casablanca, Agadir, and Marrakesh.  
          Course prices vary slightly depending on the city and center.
        </p>
      </div>
    </div>

    {{-- =============================== --}}
    {{-- PRICING & SERVICES --}}
    {{-- =============================== --}}
    <div class="faq-item" data-category="pricing">
      <button class="faq-question">
        How does registration and pricing work at GLS?
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-answer">
        <p>
          Registration at GLS requires a <strong>one-time fee</strong> only. It includes:
        </p>
        <ul style="text-align:left; list-style-type:'• '; padding-left:1.2rem;">
          <li>Books for all language levels (A1–B2)</li>
          <li>Free follow-up of your visa dossier</li>
          <li>Administrative assistance and document support</li>
        </ul>
        <p>
          Course prices differ by city and teaching mode.  
          Each GLS center may have its own rates depending on local operations and class schedules.
        </p>
        <p>
          We also provide professional <strong>translation services</strong> at a cost of  
          <strong>150 MAD per document</strong>.
        </p>
      </div>
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

{{-- ===== JS ===== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const filterBtns = document.querySelectorAll('.faq-filter-btn');
  const faqItems = document.querySelectorAll('.faq-item');
  const questions = document.querySelectorAll('.faq-question');
  const searchInput = document.getElementById('faq-search');

  let activeCategory = 'all';
  let searchQuery = '';

  // Filter by category
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeCategory = btn.getAttribute('data-category');
      filterFAQs();
    });
  });

  // Filter by search
  searchInput.addEventListener('input', () => {
    searchQuery = searchInput.value.toLowerCase().trim();
    filterFAQs();
  });

  // Combine search + category
  function filterFAQs() {
    faqItems.forEach(item => {
      const category = item.dataset.category;
      const questionText = item.querySelector('.faq-question').textContent.toLowerCase();
      const answerText = item.querySelector('.faq-answer').textContent.toLowerCase();
      const matchesCategory = activeCategory === 'all' || category === activeCategory;
      const matchesSearch = questionText.includes(searchQuery) || answerText.includes(searchQuery);
      item.style.display = matchesCategory && matchesSearch ? 'block' : 'none';
    });
  }

  // Accordion open/close
  questions.forEach(q => {
    q.addEventListener('click', () => {
      const parent = q.parentElement;
      const isOpen = parent.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(item => {
        item.classList.remove('open');
        item.querySelector('.faq-icon').textContent = '+';
      });
      if (!isOpen) {
        parent.classList.add('open');
        q.querySelector('.faq-icon').textContent = '×';
      }
    });
  });
});
</script>
@endsection
