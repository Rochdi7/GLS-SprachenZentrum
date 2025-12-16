@extends('frontoffice.layouts.app')

@section('title', __('faq.meta.title'))
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/faq/faq.css') }}">

@section('content')

{{-- ===============================
     HERO SECTION – FAQ PAGE
================================ --}}
<section class="hero-section section is-no-image is-faq reveal delay-1">
  <div class="container is-hero text-center reveal delay-2">

    <h1 class="hero_title reveal fade-blur-title delay-1">
      {{ __('faq.hero.title') }}
    </h1>

    <p class="text-light mt-3 reveal delay-2" style="max-width: 720px; margin: 0 auto; line-height: 1.6;">
      {!! __('faq.hero.subtitle') !!}
    </p>

    {{-- Search --}}
    <div class="faq-form w-form mt-4 reveal delay-3">
      <form id="faq-search-form" class="faq-form-block" aria-label="FAQ Search">
        <input
          type="text"
          id="faq-search"
          name="faq-search"
          class="faq-search-field w-input reveal delay-1"
          maxlength="256"
          placeholder="{{ __('faq.hero.search_placeholder') }}"
          autocomplete="off">
      </form>
    </div>

  </div>
</section>

{{-- ===============================
     FAQ SECTION – Filters + Accordion
================================ --}}
<section class="section faq-section py-5 reveal delay-1">

  <div class="container text-center mb-5 reveal delay-2">

    {{-- Filter Buttons --}}
    <div class="faq-filters d-flex flex-wrap justify-content-center gap-3 mb-4 reveal delay-3">
      <button class="faq-filter-btn active reveal delay-1" data-category="all">{{ __('faq.filters.all') }}</button>
      <button class="faq-filter-btn reveal delay-2" data-category="courses">{{ __('faq.filters.courses') }}</button>
      <button class="faq-filter-btn reveal delay-3" data-category="recognition">{{ __('faq.filters.recognition') }}</button>
      <button class="faq-filter-btn reveal delay-1" data-category="study">{{ __('faq.filters.study') }}</button>
      <button class="faq-filter-btn reveal delay-2" data-category="exams">{{ __('faq.filters.exams') }}</button>
      <button class="faq-filter-btn reveal delay-3" data-category="online">{{ __('faq.filters.online') }}</button>
      <button class="faq-filter-btn reveal delay-1" data-category="pricing">{{ __('faq.filters.pricing') }}</button>
    </div>

  </div>

  <div class="container faq-accordion reveal delay-2">

    {{-- COURSES --}}
    <div class="faq-item reveal delay-3" data-category="courses">
      <button class="faq-question reveal delay-1">
        {{ __('faq.questions.courses.q1.question') }}
        <span class="faq-icon reveal delay-2">+</span>
      </button>
      <div class="faq-answer reveal delay-3">
        <p class="reveal delay-1">{!! __('faq.questions.courses.q1.a1') !!}</p>
        <p class="reveal delay-2">{!! __('faq.questions.courses.q1.a2') !!}</p>
      </div>
    </div>

    {{-- RECOGNITION --}}
    <div class="faq-item reveal delay-1" data-category="recognition">
      <button class="faq-question reveal delay-2">
        {{ __('faq.questions.recognition.q1.question') }}
        <span class="faq-icon reveal delay-3">+</span>
      </button>
      <div class="faq-answer reveal delay-1">
        <p class="reveal delay-2">{!! __('faq.questions.recognition.q1.a1') !!}</p>
        <p class="reveal delay-3">{!! __('faq.questions.recognition.q1.a2') !!}</p>
      </div>
    </div>

    {{-- STUDY UNIVERSITY --}}
    <div class="faq-item reveal delay-2" data-category="study">
      <button class="faq-question reveal delay-3">
        {{ __('faq.questions.study.q1.question') }}
        <span class="faq-icon reveal delay-1">+</span>
      </button>
      <div class="faq-answer reveal delay-2">
        {!! __('faq.questions.study.q1.answer') !!}
      </div>
    </div>

    {{-- STUDY – AUSBILDUNG --}}
    <div class="faq-item reveal delay-3" data-category="study">
      <button class="faq-question reveal delay-1">
        {{ __('faq.questions.study.q2.question') }}
        <span class="faq-icon reveal delay-2">+</span>
      </button>
      <div class="faq-answer reveal delay-3">
        {!! __('faq.questions.study.q2.answer') !!}
      </div>
    </div>

    {{-- STUDY – WORK CONTRACT --}}
    <div class="faq-item reveal delay-1" data-category="study">
      <button class="faq-question reveal delay-2">
        {{ __('faq.questions.study.q3.question') }}
        <span class="faq-icon reveal delay-3">+</span>
      </button>
      <div class="faq-answer reveal delay-1">
        {!! __('faq.questions.study.q3.answer') !!}
      </div>
    </div>

    {{-- STUDY – INVITATION --}}
    <div class="faq-item reveal delay-2" data-category="study">
      <button class="faq-question reveal delay-3">
        {{ __('faq.questions.study.q4.question') }}
        <span class="faq-icon reveal delay-1">+</span>
      </button>
      <div class="faq-answer reveal delay-2">
        {!! __('faq.questions.study.q4.answer') !!}
      </div>
    </div>

    {{-- EXAMS --}}
    <div class="faq-item reveal delay-3" data-category="exams">
      <button class="faq-question reveal delay-1">
        {{ __('faq.questions.exams.q1.question') }}
        <span class="faq-icon reveal delay-2">+</span>
      </button>
      <div class="faq-answer reveal delay-3">
        {!! __('faq.questions.exams.q1.answer') !!}
      </div>
    </div>

    {{-- ONLINE --}}
    <div class="faq-item reveal delay-1" data-category="online">
      <button class="faq-question reveal delay-2">
        {{ __('faq.questions.online.q1.question') }}
        <span class="faq-icon reveal delay-3">+</span>
      </button>
      <div class="faq-answer reveal delay-1">
        {!! __('faq.questions.online.q1.answer') !!}
      </div>
    </div>

    {{-- PRICING --}}
    <div class="faq-item reveal delay-2" data-category="pricing">
      <button class="faq-question reveal delay-3">
        {{ __('faq.questions.pricing.q1.question') }}
        <span class="faq-icon reveal delay-1">+</span>
      </button>
      <div class="faq-answer reveal delay-2">
        {!! __('faq.questions.pricing.q1.answer') !!}
      </div>
    </div>

  </div>
</section>

{{-- ===============================
     GET STARTED CTA
================================ --}}
<section class="get-started-section section reveal delay-1">

  <div class="container is-2-col-grid reveal delay-2">

    <div class="get-started-image reveal delay-3">
      <img 
        src="{{ asset('assets/images/about/subscribe.jpeg') }}" 
        alt="{{ __('faq.get_started.alt') }}"
        class="full-image rounded-4 reveal delay-1"
        loading="lazy">
    </div>

    <div class="get-started-card reveal delay-2">
      <div class="box-rich-text w-richtext reveal delay-3">
        <h2 class="reveal fade-blur-title delay-1">{{ __('faq.get_started.h2') }}</h2>
        <h3 class="reveal fade-blur-title delay-1">{{ __('faq.get_started.h3') }}</h3>
        <p class="reveal delay-2">{!! __('faq.get_started.p1') !!}</p>
        <p class="reveal delay-3">{!! __('faq.get_started.p2') !!}</p>
        <p class="reveal delay-1">{!! __('faq.get_started.p3') !!}</p>
      </div>

      <a href="#" class="button w-button reveal delay-2">
        {{ __('faq.get_started.button') }}
      </a>
    </div>

  </div>
</section>

{{-- ===============================
     CONTACT SECTION
================================ --}}
<section class="contact-section section reveal delay-1">
  <div class="container is-2-col-grid reveal delay-2">

    <div class="div-block-5-copy reveal delay-3">

      <h2 class="h-section-subtitle reveal fade-blur-title delay-1">
        {!! __('faq.contact.title') !!}
      </h2>

      <div class="div-block-21 reveal delay-2">

        <a href="tel:+212669515019" class="link-block reveal delay-1">
          <div class="text-block-3 reveal delay-2">
            <span class="text-span reveal delay-3">{{ __('faq.contact.call') }}<br></span>
            +212 6 69 51 50 19
          </div>
        </a>

        <a href="mailto:info@glssprachenzentrum.ma" class="link-block-2 reveal delay-3">
          <div class="text-block-3 reveal delay-1">
            <span class="text-span reveal delay-2">{{ __('faq.contact.email') }}<br></span>
            info@glssprachenzentrum.ma
          </div>
        </a>

      </div>

      <div class="text-block-3 visit-block reveal delay-3">
        <span class="text-span reveal delay-1">{{ __('faq.contact.visit') }}</span><br>
        {!! __('faq.contact.addresses') !!}
      </div>

      <div class="footer-socials-block reveal delay-1">
        <div class="text-block-3 reveal delay-2">
          <span class="text-span reveal delay-3">{{ __('faq.contact.follow') }}</span>
        </div>

        <div class="div-block-20 reveal delay-1">
          <a href="#" class="footer-social-link ig reveal delay-2"><i class="bi bi-instagram"></i></a>
          <a href="#" class="footer-social-link fb reveal delay-3"><i class="bi bi-facebook"></i></a>
          <a href="#" class="footer-social-link yt reveal delay-1"><i class="bi bi-youtube"></i></a>
          <a href="#" class="footer-social-link wa reveal delay-2"><i class="bi bi-whatsapp"></i></a>
        </div>
      </div>

    </div>

    <a href="https://maps.app.goo.gl/g4PjrPB7wHQAqrSZA" target="_blank" class="div-block-7 reveal delay-3">
      <iframe
        src="{{ __('faq.contact.map_iframe') }}"
        loading="lazy"
        allowfullscreen
        referrerpolicy="no-referrer-when-downgrade"
        class="reveal delay-1"></iframe>
    </a>

  </div>
</section>


{{-- ===============================
     JS
================================ --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

  const filterBtns = document.querySelectorAll('.faq-filter-btn');
  const faqItems = document.querySelectorAll('.faq-item');
  const questions = document.querySelectorAll('.faq-question');
  const searchInput = document.getElementById('faq-search');

  let activeCategory = 'all';
  let searchQuery = '';

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeCategory = btn.dataset.category;
      filterFAQs();
    });
  });

  searchInput.addEventListener('input', () => {
    searchQuery = searchInput.value.toLowerCase().trim();
    filterFAQs();
  });

  function filterFAQs() {
    faqItems.forEach(item => {
      const category = item.dataset.category;
      const content = item.textContent.toLowerCase();
      const showCategory = activeCategory === 'all' || category === activeCategory;
      const showSearch = content.includes(searchQuery);
      item.style.display = showCategory && showSearch ? 'block' : 'none';
    });
  }

  questions.forEach(q => {
    q.addEventListener('click', () => {
      const parent = q.parentElement;
      const isOpen = parent.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => {
        i.classList.remove('open');
        i.querySelector('.faq-icon').textContent = '+';
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
