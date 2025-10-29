@extends('frontoffice.layouts.app')

@section('content')

<section class="hero" aria-label="Intro">
  {{-- Hero background using Laravel asset() --}}
  <div class="hero__bg" style="background-image: url('{{ asset('assets/images/hero.png') }}');"></div>

  {{-- Badges for locations --}}
  <div class="badge b-blue b1">Rabat</div>
  <div class="badge b-green b2">Casablanca</div>
  <div class="badge b-orange b3">Marrakesh</div>
  <div class="badge b-violet b4">Agadir</div>

  {{-- Hero Text --}}
  <div class="hero__inner">
    <h1 class="headline">
      Learn German in Morocco<br>
      <span class="sub">with the GLS Language Center</span>
    </h1>
  </div>
</section>

@endsection
