@extends('frontoffice.layouts.app')

@section('content')

<section class="hero" aria-label="Intro">
  {{-- Hero background using Laravel asset() --}}
  <div class="hero__bg" style="background-image: url('{{ asset('assets/images/hero.png') }}');"></div>

  {{-- Badges for locations --}}
  <div class="badge b-blue b1">Rabat</div>
  <div class="badge b-green b2">Casablanca</div>
  <div class="badge b-orange b3">Marrakesh</div>
  <div class="badge b-violet b4">Sale</div>

  {{-- Hero Text --}}
  <div class="hero__inner">
    <h1 class="hero-title">
      Learn German in Morocco with GLS
    </h1>
  </div>
</section>

@endsection
