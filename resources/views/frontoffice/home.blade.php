@extends('frontoffice.layouts.app')

@section('content')

{{-- ===========================
     HERO SECTION
=========================== --}}
<section class="hero" aria-label="Intro">
  <div class="hero__bg" style="background-image: url('{{ asset('assets/images/hero.png') }}');"></div>

  <div class="badge b-blue b1">Rabat</div>
  <div class="badge b-green b2">Casablanca</div>
  <div class="badge b-orange b3">Marrakesh</div>
  <div class="badge b-violet b4">Sale</div>

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
    <div class="intro-card bg-light shadow rounded-4 mx-auto p-5" style="max-width: 720px;">
      <!-- Logo + Tagline -->
      <div class="text-center mb-4">
        <img 
          src="{{ asset('build/images/logo/gls-noir.png') }}" 
          alt="GLS Logo"
          class="intro-logo mb-3"
          style="width: 90px; height: auto;"
        >
        <p class="text-primary fw-medium small mb-0 letter-spacing-1 text-uppercase">
          Learn German in Morocco
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

@endsection
