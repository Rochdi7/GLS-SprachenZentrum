@extends('frontoffice.layouts.app')

@section('title', 'Contact | GLS Sprachenzentrum')

{{-- Bootstrap CSS (CDN) --}}
<link
  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
  rel="stylesheet"
  integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
  crossorigin="anonymous"
/>
{{-- Bootstrap Icons --}}
<link
  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
  rel="stylesheet"
/>

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/contact/contact.css') }}">

@section('content')

{{-- ================================
     CONTACT HERO SECTION
================================ --}}
<section class="hero-section section is-no-image">
  <div class="container is-hero">
    <h1 class="hero_title">Contact Us</h1>
    <div class="hero_subtitle">
      Our team will be happy to assist you!<br>
    </div>
  </div>
</section>

{{-- ================================
     CONTACT FORM SECTION
================================ --}}
<section id="contact-form" class="contact-form-section section py-5">
  <div class="container py-4">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Send Us a Message</h2>
      <p class="text-muted">We’ll respond as soon as possible</p>
    </div>

    <form action="#" method="POST" class="mx-auto" style="max-width: 700px;">
      @csrf
      <div class="row g-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Full Name</label>
          <input type="text" name="name" class="form-control rounded-3 p-3" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Email Address</label>
          <input type="email" name="email" class="form-control rounded-3 p-3" required>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Subject</label>
          <input type="text" name="subject" class="form-control rounded-3 p-3">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Your Message</label>
          <textarea name="message" rows="5" class="form-control rounded-3 p-3" required></textarea>
        </div>
        <div class="col-12 text-center">
          <button type="submit" class="btn btn-success px-5 py-3 rounded-pill fw-semibold">
            Send Message
          </button>
        </div>
      </div>
    </form>
  </div>
</section>

<!-- =========================================================
     BOOK YOUR CONSULTATION SECTION
========================================================= -->
<section class="consultation-section text-center position-relative">
  <div class="consultation-gradient"></div>

  <div class="container position-relative z-2 py-5">
    <h2 class="fw-bold mb-4 text-white">Book Your Free Consultation</h2>
    <p class="text-white-50 mb-5 mx-auto" style="max-width: 720px;">
      Are you new to Germany or just starting to plan your study or work journey?  
      Our <strong>administrative assistance team</strong> is here to guide you.  
      We’ll explain every step — from learning German to understanding university, Ausbildung, or visa procedures.
    </p>

    <a href="{{ route('front.contact') }}" class="btn btn-light px-4 py-3 rounded-pill fw-semibold">
      Book My Consultation
    </a>
  </div>
</section>


{{-- Bootstrap JS Bundle --}}
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
  crossorigin="anonymous"
></script>

{{-- Auto Switch Logic --}}
<script>
  const slides = document.querySelectorAll('.location-slide');
  const dots = document.querySelectorAll('.dot');
  let currentIndex = 0;

  // Animate cards on load
  function animateCards(slide) {
    const cards = slide.querySelectorAll('.location-card');
    cards.forEach((card, i) => {
      card.style.opacity = 0;
      card.style.transform = 'translateY(20px)';
      setTimeout(() => {
        card.style.opacity = 1;
        card.style.transform = 'translateY(0)';
      }, i * 400);
    });
  }

  function showSlide(index) {
  slides.forEach((slide, i) => {
    if (i === index) {
      slide.classList.add('active');
    } else {
      slide.classList.remove('active');
      slide.style.opacity = 0;
      slide.style.visibility = 'hidden';
      slide.style.display = 'none';
    }
    dots[i].classList.toggle('active', i === index);
  });
  slides[index].style.display = 'block';
  slides[index].style.visibility = 'visible';
  slides[index].style.opacity = 1;
  animateCards(slides[index]);
}


  // Initial animation on page load
  animateCards(slides[0]);

  // Auto switch every 3s
  setInterval(() => {
    currentIndex = (currentIndex + 1) % slides.length;
    showSlide(currentIndex);
  }, 3000);
</script>

@endsection
